<?php

namespace App\Http\Controllers;



use App\Services\Service;

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
            dd(['code'=>10001, 'msg'=>'开始时间必须大于当前时间！','data'=>[]]);
        }
        $end = strtotime("2021-11-04 23:30:00");
        // 获取开始时间零点时间
        $day_start_at = strtotime(date('Y-m-d 00:00:00',$start));
        // 获取结束时间零点时间
        $day_end_at = strtotime(date('Y-m-d 00:00:00',$end));
        if($day_start_at != $day_end_at){
            dd(['code'=>10001, 'msg'=>'开始时间和结束时间必须是同一天！','data'=>[]]);
        }
        // 每个时间段区间时间（秒），300是5分钟
        $singleFieldAt = 300;
        $site_at = $end - $start;
        $site_num = $site_at/$singleFieldAt;
        if(!is_int($site_num)){
            dd(['code'=>10001, 'msg'=>'请选择正确的时间段！','data'=>[]]);
        }
        $timeArr = Service::splitTimeSlot($start, $end, $site_num, $day_start_at);
        dump($timeArr);
    }



}
