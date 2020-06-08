<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use app\admin\model\Course as Courses;
class Course extends Common
{
    public  function courseList()
    {
        if ($this->request->isAjax())
        {
            $where=[
                'delete_time'=>0,
                'is_show'=>1
            ];
            $data=Courses::getCourseInfo($where);
            show($data,0,'');
        }else{
           return $this->fetch();
        }
    }
    public function edit()
    {

    }
    public function update()
    {

    }
    public function delete()
    {

    }
}