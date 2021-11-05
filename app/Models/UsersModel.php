<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'users';
    public $timestamps = false;

    /**
     * TODO 获取用户基本信息单条数据
     * DATE: 2021/03/29
     * Author: yxm
     */

    public static function getUserInfo($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }

    /**
     * TODO 获取用户关联资金单条数据
     * DATE: 2021/06/25
     * Author: yxm
     */

    public static function getUserJoinAmount($where, $field)
    {
        $data = self::from('users as u')->where($where)->leftJoin('user_amount as ua', 'ua.user_id', '=', 'u.id')->select($field)->first();

        return collect($data);
    }

    /**
     * TODO 创建用户信息
     * DATE: 2021/03/29
     * Author: yxm
     */
    public static function createUser($data)
    {
        $date = time();
        $data['create_at'] = $date;
        return self::insertGetId($data);
    }

    /**
     * TODO 更新用户信息
     * DATE: 2021/03/30
     * Author: yxm
     */
    public static function updateUser($where,$data)
    {
        $date = time();
        $data['update_at'] = $date;
        return self::where($where)->update($data);
    }



}
