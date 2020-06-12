<?php

namespace app\index\controller;


use app\index\model\UnitList;
use app\index\model\UnitListModule;
use think\Controller;
use app\index\model\Course;
use app\index\model\Section;
use app\index\model\Unit;
use app\index\model\UserUnit;
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
            show([],0,'course_id不能为空');
        }
        $section_info=Section::where(['is_show'=>1,'delete_time'=>0,'course_id'=>$course_id])->select();
        show($section_info,200,'ok');
    }
    //节数据
    public function unit()
    {
        $section_id=$this->request->post('section_id');
        if(!$section_id){
            show([],0,'section_id不能为空');
        }
        $unit_info=Unit::where(['delete_time'=>0,'section_id'=>$section_id])->select();
        show($unit_info,200,'ok');
    }
    //节任务队列数据
    public function unitList()
    {
        $section_id=$this->request->post('section_id');
        if(!$section_id){
            show([],0,'section_id不能为空');
        }
        $user_id=intval($this->request->post('user_id'));
        if(!$user_id){
            show([],0,'user_id不能为空');
        }
        //知识点数据
        $unit_info=Unit::where(['delete_time'=>0])
            ->alias('u')
            ->order('u.order','desc')
            ->select();
        $unit_rate=Unit::where(['delete_time'=>0])
            ->alias('u')
            ->field('name,unit_id,complete_num')
            ->join('user_unit uu','u.id = uu.unit_id')
            ->where('uu.user_id',$user_id)
//            ->order('uu.complete_num','desc')
            ->order('u.order','desc')
            ->select();
        foreach ($unit_info as $k=>$v){
            $v['complete_num']=0;
        }
        foreach ($unit_info as $k=>$v){
            foreach ($unit_rate as $key=>$vv){
                if($unit_info[$key]['id']==$vv['unit_id']){
                    $unit_info[$key]['complete_num']=$vv['complete_num'];
                }
            }
        }
        //默认查找第一个知识点的队列
        if(empty($unit_rate)){
            //默认
            $unit_id=$unit_info[0]["id"];
        }else{
            $unit_id= model('user_unit')
                ->where('user_id',$user_id)
                ->order('complete_num','asc')
                ->order('id','asc')
                ->value('unit_id');
        }
        $unit_list=UnitList
            ::alias('u')
            ->field('u.*,uu.*,uul.*')
            ->join('user_unit uu','uu.unit_id = u.unit_id')
            ->join('unit_user_list uul','u.id= uul.unit_list_id')
            ->where('u.unit_id',$unit_id)
            ->where('uul.user_id',$user_id)
            ->select();
        foreach ($unit_list as $v){
            $name=Unit::where(['id'=>$v['unit_id']])->value('name');
            $v['name']=$name;
            $v['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select();
        }
        $info=[
//            'unit_rate'=>$unit_rate
            'unit_list'=>$unit_list,
            'unit_info'=>$unit_info,
        ];
        show($info,200,'ok');
    }
    public function unitListInfo(){
        $section_id=$this->request->post('section_id',0,'intval');
        if(!$section_id){
            show([],0,'section_id必传');
        }
        $unit_list=model('unit')
            ->where('section_id',$section_id)
            ->where('delete_time',0)
            ->order('order','desc')
            ->select();
        show($unit_list,200,'ok');
    }

}
