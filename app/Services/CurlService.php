<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Services;



class CurlService
{

    /**
     * TODO Curl模拟GET请求
     * @param   string     $url           请求地址
     * @return  string                    响应json数据
     * DATE: 2021/11/06
     * Author: Yxm
     */

    public static function curlGet($url){
        //初始化
        $ch = curl_init();
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // https请求 不验证hosts
        //执行命令
        $output = curl_exec($ch);//返回api的json对象
        curl_close($ch); //释放curl句柄
        return json_decode($output,true);
    }

    /**
     * TODO Curl模拟POST请求
     * @param   string     $url           请求地址
     * @param   array      $data          请求数据
     * @return  string                    响应json数据
     * DATE: 2021/11/06
     * Author: Yxm
     */
    public static function curlPost($url, $data){
        //初始化
        $ch = curl_init();
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // https请求 不验证hosts
        //设置post方式提交
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //执行命令
        $output = curl_exec($ch);//返回api的json对象
        curl_close($ch); //释放curl句柄
        return json_decode($output,true);

    }

    /**
     * TODO 模拟并发（curl请求）
     * @param   string          $url           请求地址
     * @param   int             $num           并发数量
     * DATE: 2021/11/06
     * Author: Yxm
     */

    public static function concurrentCurl($url, $aHeader, $num, $data){
        $conn = [];
        //创建批处理curl句柄
        $mh = curl_multi_init();
        for($i=0; $i<$num; $i++){
            // //初始化各个子连接
            $conn[$i] = curl_init();
            // 设置URL和相应的选项
            curl_setopt($conn[$i], CURLOPT_URL, $url);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($conn[$i], CURLOPT_HTTPHEADER, $aHeader);
            // POST数据
            curl_setopt($conn[$i], CURLOPT_POST, 1);
            $data = [
                'user_id'=>$i+1,
                'goods_id'=>1,
                'num'=>1
            ];
            curl_setopt($conn[$i], CURLOPT_POSTFIELDS, $data);
            //增加句柄
            curl_multi_add_handle($mh, $conn[$i]);   //加入多处理句柄
        }

        $active = null;     //连接数

        //防卡死写法:执行批处理句柄
        do {
            $mrc = curl_multi_exec($mh, $active);
            //这个循环的目的是尽可能地读写，直到无法继续读写为止
            //返回 CURLM_CALL_MULTI_PERFORM 表示还能继续向网络读写

        } while($mrc == CURLM_CALL_MULTI_PERFORM);


        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);

                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        for($j=0; $j<$num; $j++){
            $info = curl_multi_info_read($mh);

            $headers = curl_getinfo($conn[$j]);


            $res[$j] = curl_multi_getcontent($conn[$j]);

            //移除curl批处理句柄资源中的某一个句柄资源
            curl_multi_remove_handle($mh, $conn[$j]);

            //关闭curl会话
            curl_close($conn[$j]);
        }

        //关闭全部句柄
        curl_multi_close($mh);
        return $res;

    }


}
