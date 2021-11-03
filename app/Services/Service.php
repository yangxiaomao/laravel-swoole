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

}
