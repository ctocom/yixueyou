<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/11/28
 * Time: 15:16
 */

namespace app\admin\model;

use think\Model;

class Section extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
    public static function getSectionInfo($course_id)
    {
        $data=model('section')->where(['course_id'=>$course_id,'is_show'=>1,'delete_time'=>0])->select();
        return $data;
    }
}