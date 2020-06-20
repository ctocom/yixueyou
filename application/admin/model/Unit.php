<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/11/28
 * Time: 15:16
 */

namespace app\admin\model;

use think\Model;

class Unit extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
    public static function getUnitInfo($section_id)
    {
        $data=model('unit')->where(['section_id'=>$section_id,'delete_time'=>0])->select();
        return $data;
    }
}