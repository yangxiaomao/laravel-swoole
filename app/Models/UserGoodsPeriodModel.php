<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoodsPeriodModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'user_goods_period';
    public $timestamps = false;

    /**
     * TODO 获取用户参与商品期次单条数据
     * DATE: 2021/06/29
     * Author: yxm
     */

    public static function userPeriod($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }

    /**
     * TODO 创建用户参与商品期次信息
     * DATE: 2021/06/29
     * Author: yxm
     */
    public static function createUserPeriod($data)
    {
        $date = time();
        $data['create_at'] = $date;
        return self::insertGetId($data);
    }

    /**
     * TODO 更新用户参与商品期次信息
     * DATE: 2021/06/30
     * Author: yxm
     */
    public static function updateUserPeriod($where,$data)
    {
        $date = time();
        $data['update_at'] = $date;
        return self::where($where)->update($data);
    }


    /**
     * TODO 我参与瓜分列表
     * DATE: 2021/06/30
     * Author: yxm
     */
    public static function userPeriodJson($where, $field, $order, $pageSize)
    {
        $data = self::from('user_goods_period as ugp')->where($where)
            ->leftJoin('activity_goods_period as agp', 'ugp.period_id', '=', 'agp.id')
            ->select($field)->orderByRaw($order)->paginate($pageSize);

        return collect($data);
    }

    /**
     * TODO 我参与瓜分详情
     * DATE: 2021/07/12
     * Author: yxm
     */
    public static function userPeriodJsonDetail($where, $field)
    {
        $data = self::from('user_goods_period as ugp')->where($where)
            ->leftJoin('activity_goods_period as agp', 'ugp.period_id', '=', 'agp.id')
            ->select($field)->first();

        return collect($data);
    }

}
