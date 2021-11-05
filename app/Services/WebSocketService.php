<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Services;


// Websocket服务
use App\Models\ActivityGoodsPeriodModel;
use App\Models\UserAmountDetailedModel;
use App\Models\UserAmountModel;
use App\Models\UserBettingModel;
use App\Models\UserGoodsPeriodModel;
use App\Models\UsersModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WebSocketService
{
    private static $server = null;

    /**
     * TODO 获取Websocket服务
     * DATE: 2021/11/05
     * Author: Yxm
     */

    public static function getWebSocketServer(){
        if (!(self::$server instanceof \swoole_websocket_server)) {
            self::setWebSocketServer();
        }
        return self::$server;
    }

    /**
     * TODO 服务初始设置
     * DATE: 2021/11/05
     * Author: Yxm
     */
    protected static function setWebSocketServer():void
    {
        self::$server = new \swoole_websocket_server("0.0.0.0", 8005);
        self::$server->set([
            'worker_num' => 1,                  // 工作进程数量
            'heartbeat_check_interval' => 60,   // 60秒检测一次
            'heartbeat_idle_time' => 121,       // 121秒没活动的
        ]);
    }

    /**
     * TODO 启动Websocket服务
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function start()
    {
        self::$server->start();
    }

    /**
     * TODO Websocket 关闭回调代码
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function onClose($serv,$fd)
    {
        $this->line("客户端 {$fd} 关闭");
    }

    /**
     * TODO 校验客户端连接的合法性，无效的连接不允许连接
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function checkAccess($server, $request):bool
    {
        $bRes = true;
        if (!isset($request->get) || !isset($request->get['user_token'])) {
            self::$server->close($request->fd);
            echo "接口验证字段不全".PHP_EOL;
            $bRes = false;
        } else if (empty($request->get['user_token'])) {
            echo "接口验证错误".PHP_EOL;
            $bRes = false;
        }
        return $bRes;
    }

    /**
     * TODO 打开swoole websocket服务回调代码
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function onOpen($server, $request)
    {
        if ($this->checkAccess($server, $request)) {
            // 当用户uuid和token存在后，再验证合法性
            $data = $request->get;
            $key = 'client:login:token:'. $data['user_id'];
            $token = $data['user_token'];
            // redis中获取用户Token信息
            $value = ['user_id','user_token'];
            $result = $redis = Redis::connection('cache')->hmget($key, $value);
            if($result[1] != false && $token == $result[1]){
                //获取redis对象
                $redis = Redis::connection('cache');
                $goodsHx = 'period_id:'. $data['period_id'];
                $redis->hset($goodsHx, $data['user_id'], $request->fd);
                // 获取当前商品期次参与的用户组
                $userList = $redis->hgetall($goodsHx);
                if(!empty($userList)){
                    $returnData =  ['code'=>10001, 'msg'=>'用户ID：'.$data['user_id']."初始化成功", 'data'=>(object)[]];
                    self::$server->push($request->fd, json_encode($returnData));
                }else{
                    self::$server->push($request->fd, '参与失败');
                }
            }else{
                self::$server->push($request->fd, 'token验证失败');
            }
        }
    }

    /**
     * TODO 给swoole websocket 发送消息回调代码
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function onMessage($server, $frame)
    {
        $data = json_decode($frame->data, true);
        // 当用户uuid和token存在后，再验证合法性
        $key = 'client:login:token:'. $data['user_id'];
        $token = $data['user_token'];
        // redis中获取用户Token信息

        $value = ['user_id','user_token'];
        $result = $redis = Redis::connection('cache')->hmget($key, $value);
        if($result[1] != false && $token == $result[1]){
            $res = $this->participateIn($data['user_id'], $data['period_id']);
            //获取redis对象
            $redis = Redis::connection('cache');
            $goodsHx = 'period_id:'. $data['period_id'];
            $redis->hset($goodsHx, $data['user_id'], $frame->fd);
            // 获取当前商品期次参与的用户组
            $userList = $redis->hgetall($goodsHx);
            if(!empty($userList)){
                foreach($userList as $key => $val){
                    $server->push($val, json_encode($res));
                }
            }else{
                $server->push($frame->fd, '参与失败');
            }
        }else{
            $server->push($frame->fd, 'token验证失败');
        }
    }
    /**
     * TODO  接受请求
     * DATE: 2021/11/05
     * Author: Yxm
     */
    public function onRequest($server, $request,$response){
        // 接收http请求从post获取参数
        // 获取所有连接的客户端，验证uid给指定用户推送消息
        // token验证推送来源，避免恶意访问
        if ($request->post['token'] == '###') {

//            $clientId = [];
//            foreach ($clients as $value) {
//                $clientInfo = $this->ws->connection_info($value);
//                if (array_key_exists('uid', $clientInfo) && $clientInfo['uid'] == $request->post['s_id']) {
//                    $clientId[] = $value;
//                }
//            }
//            if (!empty($clientId)) {
//                foreach ($clientId as $v) {
//                    $this->ws->push($v, $request->post['info']);
//                }
//            }
        }
//        $server->push($request->fd, json_encode($clients));
//        $server->push($request->fd, '第几个用户给我发送'.$request->fd);
    }

    /**
     * TODO 用户参与期次商品入库
     * DATE: 2021/11/05
     * Author: Yxm
     */

    public function participateIn($userId, $periodId){
        // 根据不同业务处理对应逻辑，返回用户参与信息
        // 检测当前商品是否有存在有效瓜分的期次
        $where = ['id'=>$periodId, 'status'=>1];
        $field = ['id', 'num', 'divided_up_num', 'unit_price', 'activity_goods_id', 'period', 'goods_id'];
        $goodsPeriod = ActivityGoodsPeriodModel::getGoodsPeriod($where, $field);
        if($goodsPeriod->isEmpty()){
            return ['code'=>10001, 'msg'=>'请选择有效的商品参与', 'data'=>[]];
        }
        // 计算出剩余商品瓜分数量和用户预计瓜分数量对比，如果超出则不能瓜分
        $surplus_num = $goodsPeriod['num'] - $goodsPeriod['divided_up_num'];
        if($surplus_num == 0){
            return ['code'=>10001, 'msg'=>'该商品已被瓜分完', 'data'=>[]];
        }
        // 检测当前用户是否有足够金额瓜分该商品
        $userWhere = ['u.id'=>$userId];
        $userField = ['u.user_name', 'ua.available_soybean'];
        $userInfo = UsersModel::getUserJoinAmount($userWhere, $userField);
        if($userInfo->isEmpty()){
            return ['code'=>10001, 'msg'=>'用户异常！', 'data'=>[]];
        }

        if($surplus_num <= 1){
            $count = $surplus_num;
        }else{
            $count = 1;
        }

        if($userInfo['available_soybean'] < $count){
            return ['code'=>10001, 'msg'=>'当前余额不足！', 'data'=>[]];
        }

        $ubData = [
            'user_id'=>$userId,
            'user_name'=>$userInfo['user_name'],
            'goods_id'=>$goodsPeriod['goods_id'],
            'activity_goods_id'=>$goodsPeriod['activity_goods_id'],
            'period_id'=>$goodsPeriod['id'],
            'period'=>$goodsPeriod['period']
        ];
        $amountWhere = ['user_id'=>$userId];
        $amountData = [
            'available_soybean'=> intval($userInfo['available_soybean'] - $count),
        ];
        // 毛豆明细数据
        $adData = [
            'user_id'=>$userId,
            'soybean'=>$count,
            'before_soybean'=>$userInfo['available_soybean'],
            'after_soybean'=>$amountData['available_soybean'],
            'source'=>2,
            'opt_type'=>2,
        ];
        $ugpData = [
            'user_id'=>$userId,
            'goods_id'=>$goodsPeriod['goods_id'],
            'period_id'=>$goodsPeriod['id'],
            'status'=>1
        ];

        //检查用户是否存在参与的商品期次
        $ugpWhere = ['user_id'=>$userId, 'period_id'=>$goodsPeriod['id']];
        $ugpField = ['id'];
        $ugpInfo = UserGoodsPeriodModel::userPeriod($ugpWhere, $ugpField);
        // 开启事务
        DB::beginTransaction();
        try{
            //创建用户瓜分数据
            $ubRes = UserBettingModel::createUserBetting($ubData, $count);
            // 更新当前期次数据
            $pRes = ActivityGoodsPeriodModel::updateGoodsPeriod($goodsPeriod, $count);
            // 扣除当前用户毛豆信息
            $aRes = UserAmountModel::updateUserAmount($amountWhere, $amountData);
            // 记录当前用户毛豆明细信息
            $adRes = UserAmountDetailedModel::createAmountDetailed($adData);
            // 如果存在用户参与信息，则更新，否则添加
            if($ugpInfo->isEmpty()){
                UserGoodsPeriodModel::createUserPeriod($ugpData);
            }else{
                $upWhere = ['id'=>$ugpInfo['id']];
                UserGoodsPeriodModel::updateUserPeriod($upWhere, []);
            }

            if($ubRes && $pRes && $aRes && $adRes){
                DB::commit();
                $res = 1;
                $msg = '成功';
            }else{
                DB::rollback();//事务回滚
                $res = 0;
                $msg = '失败';
            }
        } catch (\Exception $e){
            DB::rollback();//事务回滚
            $res = 0;
            $msg = $e->getMessage();
        }

        if($res){
            $dividedUpNum = intval($goodsPeriod['divided_up_num'] + $count);
            $rate = bcmul(bcdiv($dividedUpNum, $goodsPeriod['num'], 4), 100, 2);
            $returnData = [
                'divided_up_num'=>$dividedUpNum,
                'speed_progress'=>$rate,
                'period_id'=>$periodId,
                'user_id'=>$userId
            ];
            return ['code'=>200, 'msg'=>$msg, 'data'=>$returnData];
        }else{
            return ['code'=>10001, 'msg'=>$msg, 'data'=>[]];
        }
    }


}
