<?php
/**
 * Created by PhpStorm.
 * User: 岳路川
 * Date: 2020/6/5
 * Time: 10:39
 */

namespace app\index\model;
use think\Model;

class Question extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $createTime = false;
}