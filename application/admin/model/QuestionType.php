<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/11/28
 * Time: 15:16
 */

namespace app\admin\model;

use think\Model;

class QuestionType extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
    public static function getQuestionType()
    {
        $data=model('question_type')->where(['delete_time'=>0])->select();
        return $data;
    }
}