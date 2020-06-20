<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/11/28
 * Time: 15:16
 */

namespace app\admin\model;

use think\Model;

class Course extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
    public static function getCourseInfo($where)
    {
        $data=model('course')->where($where)->select();
        return $data;
    }
}