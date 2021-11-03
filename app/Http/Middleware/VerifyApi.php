<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;

class VerifyApi
{

    protected $errorBasic=['code'=>10001,'msg'=>''];    # 接口不合法原因
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(env('IS_DEV', false) == false){
            $header = $request->header();
            if(isset($header['client-sign']) && !empty($header['client-sign'])){
                $adminSign = $header['client-sign'][0];
                $this->safetyVerification($adminSign);
                //判断接口是否合法
                if($this->errorBasic['code'] == 200){
                    // 接口合法
                    $request->signData = $this->errorBasic['info'];
                }else{
                    Log::channel('apiverify')->alert($this->errorBasic['msg']);
                    // 接口不合法，（rsa解密失败）
                    return response()->json(['code' => 10085, 'msg' => '请登录'], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                }
            }else{
                // 接口不合法，（rsa解密失败）
                return response()->json(['code' => 10085, 'msg' => '请登录'], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            }
        }else{
            // 接口合法
            $request->signData = $request->all();
        }


        return $next($request);
    }
    /*
     * Rsa验证
     */
    protected function safetyVerification($sign)
    {
        if (is_null($sign)) {
            $this->errorBasic=['code'=>10085,'msg'=>'验签参数错误或为空'];
            return;
        }
        $rsa_private = env('RSA_PRIVATE');

        $private_key = openssl_pkey_get_private($rsa_private);
        if (is_null($private_key)) {
            $this->errorBasic=['code'=>10085,'msg'=>'私钥未启用'];
            return;
        }
        $decrypted = '';
        $decode = base64_decode($sign);

        $crypto = '';
        //批量循环处理加密密文
        foreach (str_split($decode, 128) as $chunk) {

            openssl_private_decrypt($chunk, $decryptData, $private_key);

            $decrypted .= $decryptData;
        }

        if (is_null($decrypted)) {
            $this->error_basic=['code'=>10085,'msg'=>'私钥资源未找到'];
            return;
        }


        $resu = json_decode($decrypted, true);
        if (empty($resu)) {
            $this->errorBasic=['code'=>10085,'msg'=>'用户信息decode反序列化异常'];
            return;
        }
        $this->errorBasic=['code'=>200,'info'=>$resu];
        return;
    }

}
