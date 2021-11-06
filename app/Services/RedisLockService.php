<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Services;


use Illuminate\Support\Facades\Redis;

class RedisLockService
{

    private $id;

    public function __construct($id)
    {
        $this->id= $id;
    }

    /**
     * TODO  加锁（设置10秒有效时间）
     * DATE: 2021/11/06
     * Author: Yxm
     */
    public function lock() {
        return Redis::set("orders:lock", $this->id, "nx", "ex", 10);
    }

    /**
     * TODO  释放锁
     * DATE: 2021/11/06
     * Author: Yxm
     */
    function unlock() {
        $script = <<<LUA
if redis.call("get",KEYS[1]) == ARGV[1]
then
    return redis.call("del",KEYS[1])
else
    return 0
end
LUA;
        return Redis::eval($script, 1, "orders:lock", intval($this->id));
    }

}
