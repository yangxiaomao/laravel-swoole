<?php

namespace App\Http\Controllers;



use App\Services\CurlService;
use App\Services\SeckillService;
use App\Services\Service;
use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{

    /**
     * TODO 测试（指定时间段切份）接口
     * DATE: 2021/11/03
     * Author: yxm
     */
    public function splitTimeSlot()
    {
        // 调用时间切分方法
        //创建时间段之前，先检测时间段是否整除
        $start = strtotime("2021-11-04 22:30:00");
        $time = time();
        // 预生成的时间段必须大于当前时间
        if($start <= $time){
            print_r(['code'=>10001, 'msg'=>'开始时间必须大于当前时间！','data'=>[]]);
        }
        $end = strtotime("2021-11-04 23:30:00");
        // 获取开始时间零点时间
        $day_start_at = strtotime(date('Y-m-d 00:00:00',$start));
        // 获取结束时间零点时间
        $day_end_at = strtotime(date('Y-m-d 00:00:00',$end));
        if($day_start_at != $day_end_at){
            print_r(['code'=>10001, 'msg'=>'开始时间和结束时间必须是同一天！','data'=>[]]);
        }
        // 每个时间段区间时间（秒），300是5分钟
        $singleFieldAt = 300;
        $site_at = $end - $start;
        $site_num = $site_at/$singleFieldAt;
        if(!is_int($site_num)){
            print_r(['code'=>10001, 'msg'=>'请选择正确的时间段！','data'=>[]]);
        }
        $timeArr = Service::splitTimeSlot($start, $end, $site_num, $day_start_at);
        print_r($timeArr);
    }


    /**
     * TODO 模拟微信群发红包（算法）接口
     * DATE: 2021/11/04
     * Author: yxm
     */
    public function wxGroupRed()
    {
        // 调用群发红包接口 $totalAmount (分)
        $arr = Service::wxGroupRed(10000, 10);
        print_r($arr);
    }

    /**
     * TODO websocket服务监听
     * DATE: 2021/11/05
     * Author: yxm
     */
    public function webSocketServer(){

        $server = WebSocketService::getWebSocketServer();
        $server->on('open',[$this,'onOpen']);
        $server->on('message', [$this, 'onMessage']);
        $server->on('close', [$this, 'onClose']);
        $server->on('request', [$this, 'onRequest']);
        echo "swoole服务启动成功 ...".PHP_EOL;
        echo "监听端口号为：0.0.0.0:8005".PHP_EOL;
        $server->start();

    }

    /**
     * TODO 模拟并发请求
     * DATE: 2021/11/06
     * Author: yxm
     */
    public function concurrentCurl(){
        try{
            $url = 'http://demo.swoolelaravel.com/api/v1/seckill';
            $header = [
                'client-sign:ID3smp4+l8a1mSVrWHUdb7UAxosBSe6OvDSsfP3Zv0pXn5B0zeU5+jNd3R681qwFd9Y/+bkKWhcCNgK6zwEpc1LoSgMwqKOkBF2Ds/OUs6XxnsvjjBu2JYTAlQsz+IAKFzHD3KcUU4WCbqT5GLgrBUxvPvvitN/X5gk4p9zIaTs='
            ];
            $num = 10;
            $data = [];

            $res = CurlService::concurrentCurl($url, $header, $num, $data);

            print_r($res);

        }catch (\Exception $e) {
            print_r($e->getMessage());
            die();
        }


    }

    /**
     * TODO  商品入redis（哈希类型存储）
     * DATE: 2021/11/06
     * Author: yxm
     */
    public function goodsToRedis(){
        $data = [
            'limitBuy'=>10,                     // 商品限购数量
            'price'=>1000,                      // 商品价格（分）
            'beginTime'=>'2021-11-06 10:00:00', // 活动开始时间
            'endTime'=>'2021-11-07 10:00:00',   // 活动结束时间
            'title'=>'苹果12手机'
        ];
        $goods_id = 1;
        $redisKey = 'seckill:goods:'.$goods_id;
        $res = Redis::connection('cache')->hmset($redisKey, $data);
        print_r($res);

    }


    /**
     * TODO  秒杀接口（防止超卖）
     * DATE: 2021/11/06
     * Author: yxm
     */
    public function seckill(Request $request){
        try{
            $param = $request->all();
            $res = SeckillService::seckill($param);
            echo json_encode($res);
        }catch (\Exception $e) {
            echo $e->getMessage();
        }

    }



}
