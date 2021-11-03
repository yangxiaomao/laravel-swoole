<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VerifyToken
{

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
            $signData = $request->signData;
            // 验证Token是否合法
            if(isset($signData['user_token']) && isset($signData['user_id'])
                && !empty($signData['user_token'] && !empty($signData['user_id']))){
                //当用户uuid和token存在后，再验证合法性
                $key = 'client:login:token:'. $signData['user_id'];
                $token = $signData['user_token'];
                // redis中获取用户Token信息

                $value = ['user_id','user_token'];
                $result = $redis = Redis::connection('cache')->hmget($key, $value);

                if($result[1] != false){
                    if($token == $result[1]){
                        $this->signData['user_id'] = $result[0];
                    }else{
                        Log::channel('apiverify')->alert('user_token验证失败');
                        // user_token不合法，（rsa解密失败）
                        return response()->json(['code' => 10086, 'msg' => 'user_token验证失败'], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    Log::channel('apiverify')->alert('user_token验证失败');
                    // user_token不合法，（rsa解密失败）
                    return response()->json(['code' => 10086, 'msg' => 'user_token验证失败'], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                }
            }else{
                Log::channel('apiverify')->alert('user_token不存在');
                // user_token不存在，（rsa解密失败）
                return response()->json(['code' => 10086, 'msg' => 'user_token不存在'], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            }
        }
        return $next($request);
    }

}
