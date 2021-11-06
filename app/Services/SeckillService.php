<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Services;



use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SeckillService
{

    /**
     * TODO 秒杀（防止超卖）
     * @param   string          $url           请求地址
     * @param   int             $num           并发数量
     * DATE: 2021/11/06
     * Author: Yxm
     */
    public static function seckill($data)
    {
        $goodsId = $data['goods_id'];
        $redis = Redis::connection('cache');
        $redisKey = "seckill:goods:" . $goodsId;
        //购买数量不得大于限购数量
        $value = ['limitBuy','price'];
        $goodsInfo = $redis->hmget($redisKey, $value);
        if ($goodsInfo[0] < $data['num']) {
            Log::channel('customlog')->info("每人限购" . $goodsInfo[0] . "件");
            return ["code" => 30017, "msg" => '每人限购' . $goodsInfo[0] . "件"];
        }
        #进入redis锁
        $redisLock = new RedisLockService($data['goods_id']);
        $lock = $redisLock->lock();
        if ($lock) {
            // 加完锁，先减库存，万一后续执行失败也不影响超卖，或此处加redis事务（严禁处理方案）
            $redis->hincrby($redisKey, 'limitBuy', -1);
            # 抢购成功的用户信息入队列，异步队列中取数据创建订单
//            $mysql_data = $this->storeOrder($userId, $count, '1');
            $redisData = $redis->lpush('goods_order_'.$goodsId, $data['user_id']);
            if ( !$redisData ) {
                $redisLock->unlock();
                Log::channel('customlog')->info("生成订单失败");
                return ['code'=>10001, 'msg'=>'fail'];
            } else {
                #关闭锁
                $redisLock->unlock();
                Log::channel('customlog')->info("抢购成功");
                return ['code'=>200, 'msg'=>'Success'];
            }
        }
        Log::channel('customlog')->info("生成订单失败02");
        return ['code'=>10001, 'msg'=>'fail'];

    }



}
