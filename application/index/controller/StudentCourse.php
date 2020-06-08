<?php

namespace app\index\controller;


use app\index\model\UnitList;
use app\index\model\UnitListModule;
use think\Controller;
use app\index\model\Course;
use app\index\model\Section;
use app\index\model\Unit;
class StudentCourse extends Controller
{
    //课程数据
    public function index()
    {
       $course_info=Course::where(['is_show'=>1,'delete_time'=>0])->select();
       show($course_info,200,'ok');
    }
    //章数据
    public function section()
    {
        $course_id=$this->request->post('course_id');
        if(!$course_id){
            show([],200,'course_id不能为空');
        }
        $section_info=Section::where(['is_show'=>1,'delete_time'=>0,'course_id'=>$course_id])->select();
        show($section_info,200,'ok');
    }
    //节数据
    public function unit()
    {
        $section_id=$this->request->post('section_id');
        if(!$section_id){
            show([],200,'section_id不能为空');
        }
        $unit_info=Unit::where(['is_show'=>1,'delete_time'=>0,'section_id'=>$section_id])->select();
        show($unit_info,200,'ok');
    }
    //节任务队列数据
    public function unitList()
    {
        $section_id=$this->request->post('section_id');
        //任务队列数据
        $user_id=$this->request->post('user_id');
        $unit=Unit::where(['delete_time'=>0])->order('complete_num','asc')->order('id','asc')->find();
        $list=UnitList::where(['unit_id'=>$unit['id'],'user_id'=>$user_id])->select();
        foreach ($list as $v){
            $name=Unit::where(['id'=>$v['unit_id']])->value('name');
            $v['name']=$name;
            $v['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select();
        }
        //章节进度
        $unit_info=Unit::where(['delete_time'=>0])->order('id','asc')->select();
        $info=[
            'unit_info'=>$unit_info,
            'list'=>$list
        ];
        show($info,200,'ok');
    }

}
