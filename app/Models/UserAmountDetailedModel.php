<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAmountDetailedModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'user_amount_detailed';
    public $timestamps = false;

    /**
     * TODO 获取用户资金明细信息单条数据
     * DATE: 2021/06/25
     * Author: yxm
     */

    public static function getAmountDetailed($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }

    /**
     * TODO 创建用户资金明细信息
     * DATE: 2021/06/25
     * Author: yxm
     */
    public static function createAmountDetailed($data)
    {
        $date = time();
        $data['create_at'] = $date;
        return self::insertGetId($data);
    }

    /**
     * TODO 更新用户资金明细信息
     * DATE: 2021/06/25
     * Author: yxm
     */
    public static function updateAmountDetailed($where,$data)
    {
        $date = time();
        $data['update_at'] = $date;
        return self::where($where)->update($data);
    }


    /**
     * TODO 获取用户资金明细列表数据
     * DATE: 2021/06/25
     * Author: yxm
     */

    public static function getAmountDetailedList($where, $field, $order = 'id DESC', $pageSize = 10) {
        if (empty($where)) {
            $data = self::select($field)->orderByRaw($order)->paginate($pageSize);
        } else {
            $data = self::where($where)->select($field)->orderByRaw($order)->paginate($pageSize);
        }

        return collect($data);
    }



}
