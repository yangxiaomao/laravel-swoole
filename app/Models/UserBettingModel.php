<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use App\Services\ActivityGoodsService;
use Illuminate\Database\Eloquent\Model;

class UserBettingModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'user_betting';
    public $timestamps = false;

    /**
     * TODO 获取用户瓜分单条数据
     * DATE: 2021/06/29
     * Author: yxm
     */

    public static function getUserBettingInfo($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }



    /**
     * TODO 批量创建用户瓜分信息
     * DATE: 2021/06/29
     * Author: yxm
     */
    public static function createUserBetting($data, $count)
    {
        $date = time();
        for($i=0; $i<$count; $i++){
            // 根据当前期次ID获取瓜分码
            $where = ['period_id'=>$data['period_id'], 'status'=>1];
            $field = ['id','partition_code'];
            $preCode = PrePartitionCodeModel::getPartitionCode($where,$field);
            $bData = [
                'user_id'=>$data['user_id'],
                'user_name'=>$data['user_name'],
                'goods_id'=>$data['goods_id'],
                'activity_goods_id'=>$data['activity_goods_id'],
                'period_id'=>$data['period_id'],
                'period'=>$data['period'],
                'partition_code'=>$preCode['partition_code'],
                'microsecond'=>date('His', time()) . ActivityGoodsService::msectime(),
                'create_at'=>$date,
            ];
            self::insertGetId($bData);
            $pWhere = ['id'=>$preCode['id']];
            $pData = ['status'=>2];
            PrePartitionCodeModel::updatePartitionCode($pWhere, $pData);
            unset($preCode);
            unset($bData);
        }

        return true;
    }

    /**
     * TODO 更新用户信息
     * DATE: 2021/03/30
     * Author: yxm
     */
    public static function updateUserBetting($where,$data)
    {
        $date = time();
        $data['update_at'] = $date;
        return self::where($where)->update($data);
    }


    /**
     * TODO 获取用户瓜分单条数据
     * DATE: 2021/06/29
     * Author: yxm
     */

    public static function getUserBettingAll($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->get();
        return collect($data);
    }


}
