<?php
//+---------------------------------------------------------------------------------------------------------------------
//| User: yxm
//+---------------------------------------------------------------------------------------------------------------------
namespace App\Models;

use App\Jobs\OpenReward;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityGoodsPeriodModel extends Model
{
    /**
     * TODO 模型绑定数据表名
     * @var string
     */
    protected $table = 'activity_goods_period';
    public $timestamps = false;

    /**
     * TODO 获取活动商品期次单条数据
     * DATE: 2021/06/28
     * Author: yxm
     */

    public static function getGoodsPeriod($where, $field)
    {
        $data = self::where($where)
            ->select($field)
            ->first();
        return collect($data);
    }


    /**
     * TODO 创建活动商品期次信息
     * DATE: 2021/06/28
     * Author: yxm
     */
    public static function createGoodsPeriod($data)
    {
        $date = time();
        $data['create_at'] = $date;
        return self::insertGetId($data);
    }

    /**
     * TODO 更新活动商品期次信息
     * DATE: 2021/06/28
     * Author: yxm
     */
    public static function updateGoodsPeriod($goodsPeriod,$count)
    {
        $where = ['id'=>$goodsPeriod['id']];
        $dividedUpNum = intval($goodsPeriod['divided_up_num'] + $count);
        $rate = bcmul(bcdiv($dividedUpNum, $goodsPeriod['num'], 4), 100, 2);
        $data = [
            'divided_up_num'=>$dividedUpNum,
            'speed_progress'=>$rate
        ];
        // 如果已瓜分数量等于总瓜分数量，则当前批次商品进入待开奖状态
        if($dividedUpNum == $goodsPeriod['num']){
            $queueData = [
                'period_id'=>$goodsPeriod['id']
            ];
            OpenReward::dispatch($queueData)->onQueue('openRewardRedis');
        }
        return self::where($where)->update($data);
    }

    /**
     * TODO 获取活动商品期次列表数据
     * DATE: 2021/06/28
     * Author: yxm
     */

    public static function getGoodsPeriodList($where, $field, $order = 'id DESC', $pageSize = 10) {
        if (empty($where)) {
            $data = self::select($field)->orderByRaw($order)->paginate($pageSize);
        } else {
            $data = self::where($where)->select($field)->orderByRaw($order)->paginate($pageSize);
        }

        return collect($data);
    }



}
