<?php

namespace app\index\controller;


use think\Controller;
use app\index\model\Course;
class StudentCourse extends Controller
{
    //课程数据
    public function index()
    {
       $course_info=Course::where(['is_show'=>1,'delete_time'=>0])->select();
       show($course_info,200,'ok');
    }
    //章节数据
    public function section()
    {
        $course_id=$this->request->post('course_id');
        $section_info=Course::where(['is_show'=>1,'delete_time'=>0,'course_id'=>$course_id])->select();
        show($section_info,200,'ok');
    }
}
