<?php

namespace App\Http\Controllers;


use GuzzleHttp\Promise\Coroutine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Swoole\Runtime;
use Swoole\Coroutine as Co;
use function Swoole\Coroutine\run;

class IndexController extends Controller
{

    /**
     * TODO 测试接口
     * DATE: 2021/10/13
     * Author: yxm
     */
    public function index()
    {
        for($i=0; $i<2000; $i++){
            DB::table('test')->insert([
                'user_name' => 'agl_'.rand(10000, 99999),
                'create_at' => time()
            ]);
        }
    }

    /**
     * TODO redis入列接口
     * DATE: 2021/10/22
     * Author: yxm
     */
    public function toRedisList(){
        //开始记录时间
        $start = microtime(true);

        /*执行的代码段*/
        // 生成随机手机号
        $mobilePList = ['131', '185', '137', '147', '132', '151', '162', '178'];
        for($i=0; $i<1000000; $i++){
            $mobileP = array_rand($mobilePList, 1);
            $mobile = $mobilePList[$mobileP].rand(1000000, 99999999);
            $c_id = rand(1, 9);
            $arrData = [
                'mobile'=>$mobile,
                'coupon_id'=>$c_id
            ];
            $jsonData = json_encode($arrData);
            Redis::lpush('swoole_list', $jsonData);
        }

        //结束时间
        $time = microtime(true)- $start;
        dump($time);
    }

    /**
     * TODO redis入库（同步阻塞方式）
     * DATE: 2021/10/23
     * Author: yxm
     */
    public function redisToSqlSync(){
        //开始记录时间
        $start = microtime(true);

        for($i=0; $i<500; $i++){
            // redis中获取用户信息，推送消息给用户，用户获取优惠劵
            $userInfo = Redis::rpop('swoole_list');
            $userArr = json_decode($userInfo, true);
            $data = [
                'mobile'=>$userArr['mobile'],
                'coupon_id'=>$userArr['coupon_id'],
                'create_at'=>time()
            ];
            // 发送消息到推送平台
            $pushRes = self::pushMessage();
            if($pushRes){
                DB::table('user_coupon')->insert($data);
                echo "发送成功".PHP_EOL;
            }else{
                echo "发送失败".PHP_EOL;
            }

            unset($userInfo);
            unset($userArr);
            unset($data);
        }

        //结束时间
        $time = microtime(true)- $start;
        dump($time);
    }

    /**
     * TODO redis入库（异步协程方式）
     * DATE: 2021/10/23
     * Author: yxm
     */
    public function redisToSqlAsync(){
        // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
        Runtime::enableCoroutine();
        //开始记录时间
        $start = microtime(true);
        run(function() {
            for($i=0; $i<500; $i++){
                Co::create(function () {
                    // redis中获取用户信息，推送消息给用户，用户获取优惠劵
                    $userInfo = Redis::rpop('swoole_list');
                    $userArr = json_decode($userInfo, true);
                    $data = [
                        'mobile'=>$userArr['mobile'],
                        'coupon_id'=>$userArr['coupon_id'],
                        'create_at'=>time()
                    ];
                    // 发送消息到推送平台
                    $pushRes = self::pushMessage();
                    if($pushRes){
                        DB::table('user_coupon')->insert($data);
                        echo "发送成功".PHP_EOL;
                    }else{
                        echo "发送失败".PHP_EOL;
                    }
                    unset($userInfo);
                    unset($userArr);
                    unset($data);
                });

            }
        });

        //结束时间
        $time = microtime(true)- $start;
        dump($time);
    }

    /**
     * TODO redis入库（异步协程方式）
     * DATE: 2021/10/23
     * Author: yxm
     */
    public function pushMessage(){
        usleep(100000);

        return true;
    }

}
