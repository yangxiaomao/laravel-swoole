<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAmountModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'user_amount';
    public $timestamps = false;

    /**
     * TODO 获取用户资金信息单条数据
     * DATE: 2021/06/24
     * Author: yxm
     */

    public static function getUserAmount($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }

    /**
     * TODO 创建用户资金信息
     * DATE: 2021/06/24
     * Author: yxm
     */
    public static function createUserAmount($data)
    {
        $date = time();
        $data['create_at'] = $date;
        return self::insertGetId($data);
    }

    /**
     * TODO 更新用户资金信息
     * DATE: 2021/06/24
     * Author: yxm
     */
    public static function updateUserAmount($where,$data)
    {
        $date = time();
        $data['update_at'] = $date;
        return self::where($where)->update($data);
    }



}
