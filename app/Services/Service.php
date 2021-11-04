<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Services;



class Service
{


    /**
     * @desc 返回json結果
     * @param int $code 状态码
     * @param string $msg 状态信息
     * @param array $date 返回数据
     * @return \Illuminate\Http\JsonResponse
     * @author yxm
     */
    public static function returnJson($code, $msg, $data = []) {
        if(empty($data)){
            $data = new \stdClass();
        }
        return response()->json(['code' => $code, 'msg' => $msg, 'data' => $data])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /*
     * 公共销毁变量接口
     * 2021-03-26
     * yxm
     */
    public static function unsetParameter($data){
        unset($data['current_page']);
        unset($data['first_page_url']);
        unset($data['from']);
        unset($data['last_page']);
        unset($data['last_page_url']);
        unset($data['next_page_url']);
        unset($data['path']);
        unset($data['per_page']);
        unset($data['prev_page_url']);
        unset($data['to']);
        return $data;

    }

    /**
     * TODO 邀请码、订单号，生成
     * DATE: 2021/03/26
     * Author: Yxm
     */

    public static function createCode($num, $digit = 6) {
        static $sourceString = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        ];

        $code = '';
        while ($num) {
            $mod = $num % 9;
            $num = (int) ($num / 9);
            $code = "{$sourceString[$mod]}{$code}";
        }
        //判断code的长度
        if (empty($code[$digit])) {
            $code = str_pad($code, $digit, rand(0,9), STR_PAD_RIGHT);
        }
        return $code;
    }

    /**
     * TODO 获取Token
     * DATE: 2021/05/03
     * Author: Yxm
     */

    public static function getToken() {
        $token = md5(time().rand(1, 100000));
        return $token;
    }

    /**
     * TODO 把指定时间段切分 - N份
     * @param   string  $start      开始时间
     * @param   string  $end        结束时间
     * @param   int     $nums       切分数目
     * @param   int     $day_at     开始时间零点时间
     * @param   boolean $format     是否格式化
     * @return  array               时间段数组
     * DATE: 2021/11/03
     * Author: Yxm
     */

    public static function splitTimeSlot($start, $end="", $nums = 7, $day_at, $format=true){
        //获取开始小时
        $start_at = $start - $day_at;
        $parts = ($end - $start)/$nums;
        $last= ($end - $start)%$nums;
        if ( $last > 0) {
            $parts = ($end - $start - $last)/$nums;
        }
        for ($i=1; $i <= $nums; $i++) {
            $_end= $start_at + $parts * $i;
            $arr[] = array($start_at + $parts * ($i-1), $_end);
        }
        $len = count($arr)-1;
        $arr[$len][1] = $arr[$len][1] + $last;
        if ($format) {
            $timeArr = [];
            foreach ($arr as $key => $value) {
                $timeArr[$key]['start_at'] = $value[0];
                $timeArr[$key]['end_at'] = $value[1];
            }
        }
        return $timeArr;
    }

    /**
     * TODO 模拟微信群发红包
     * @param   int     $totalAmount        红包总金额 (分)
     * @param   int     $count              红包数量
     * @return  array                       红包数组
     * DATE: 2021/11/04
     * Author: Yxm
     */

    public static function wxGroupRed($totalAmount, $count){
        $reward = [
            'count'=>$count,
            'amount'=>$totalAmount,
            'remainCount'=>$count,
            'remainAmount'=>$totalAmount,
            'bestAmount'=>0,
            'bestAmountIndex'=>0
        ];

        $redArr = [];

        for($i=0; $reward['remainCount'] > 0; $i++){
            $amount = self::grabReward($reward);
            if($amount > $reward['bestAmount']){
                $reward['bestAmountIndex'] = $i;
                $reward['bestAmount'] = $amount;
            }
            array_push($redArr, $amount);
        }

        $newRedArr = [];
        foreach($redArr as $key=>$val){
            $newRedArr[$key]['amount'] = $val;
            if($reward['bestAmountIndex'] == $key){
                // 手气最佳
                $newRedArr[$key]['isBest'] = 1;
            }else{
                $newRedArr[$key]['isBest'] = 0;
            }

        }
        return $newRedArr;

    }
    /**
     * TODO 模拟微信群发红包
     * @param   array     $reward           红包数据
     * @return  int                         红包金额
     * DATE: 2021/11/04
     * Author: Yxm
     */

    private static function grabReward(array &$reward){

        //如果剩余红包不存
        if ($reward['remainCount'] <= 0){
            return ['code'=>10001, 'msg'=>'RemmainCount <= 0', 'data'=>[]];
        }

        //如果还剩最后一个红包
        if ($reward['remainCount'] == 1){
            $amount = $reward['remainAmount'];
            $reward['remainCount'] = 0;
            $reward['remainAmount'] = 0;
            return $amount;
        }

        //是否可以直接0.01
        if (($reward['remainAmount'] / $reward['remainCount']) == 1) {
            $amount = 1;
            $reward['remainAmount'] -= $amount;
            $reward['remainCount']--;
            return $amount;
	    }

        //最大可领金额 = 剩余金额的平均值X2 = （剩余金额 / 剩余数量） * 2
        //领取金额范围 = 0.01 ~ 最大可领金额
        $maxAmount = intval($reward['remainAmount'] / $reward['remainCount']) * 2;
        $amount = rand(1, $maxAmount);
        $reward['remainAmount'] -= $amount;

        // 防止剩余金额为负数
        if ($reward['remainAmount'] < 0){
            $amount += $reward['remainAmount'];
            $reward['remainAmount'] = 0;
            $reward['remainCount'] = 0;
        }else{
            $reward['remainCount']--;
        }

        return $amount;

    }

}
