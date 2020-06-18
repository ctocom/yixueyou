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
//        if(!$user_id){
//            show([],0,'user_id不能为空');
//        }
        $section=model('section')->find();
        $course_name=model('course')->where('id',$section['course_id'])->value('name');
        //知识点数据
        if($user_id){
            $unit_info=Unit::where(['delete_time'=>0])
                ->alias('u')
                ->join('user_unit uu','u.id = uu.unit_id')
                ->where('uu.user_id',$user_id)
//                ->order('u.id','desc')
                ->order('uu.complete_num','desc')
                ->select()
            ->toArray();
            if(empty($unit_info)){
                $unit_info=Unit::where(['delete_time'=>0])
                    ->alias('u')
                    ->order('u.order','desc')
                    ->select();
                if(!empty($unit_info)){
                    foreach ($unit_info as $k=>$v){
                        $v['complete_num']=0;
                    }
                }
            }
        }else{
            $unit_info=Unit::where(['delete_time'=>0])
                ->alias('u')
                ->order('u.order','desc')
                ->select();
            if(!empty($unit_info)){
                foreach ($unit_info as $k=>$v){
                    $v['complete_num']=0;
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
        $unit_list=[];
        if($user_id){
            $unit_list=UnitList
                ::alias('u')
                ->field('u.*,uu.*,uul.*')
                ->join('user_unit uu','uu.unit_id = u.unit_id')
                ->join('unit_user_list uul','u.id= uul.unit_list_id')
                ->where('u.unit_id',$unit_id)
                ->where('uul.user_id',$user_id)
                ->select()->toArray();
            if(empty($unit_list)){
                $unit_list2= UnitList
                    ::alias('u')
                    ->field('u.*,uu.*')
                    ->join('unit uu','uu.id = u.unit_id')
                    ->where('u.unit_id',$unit_id)
                    ->select();
            }
        }else{
            $unit_list=UnitList
                ::alias('u')
                ->field('u.*,uu.*')
                ->join('unit uu','uu.id = u.unit_id')
                ->where('u.unit_id',$unit_id)
                ->select();
        }
        if(empty($unit_list)){
            $unit_list=$unit_list2;
            foreach ($unit_list as $v){
                $v['complete_rate']=0;
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $v['name']=$name;
                $v['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select();
            }
        }else{
            foreach ($unit_list as $v){
                if(!$user_id){
                    $v['complete_rate']=0;
                }
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $v['name']=$name;
                $v['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select();
            }
        }
        $info=[
            'nav_name'=>$course_name.'-'.$section['name'],
            'unit_list'=>$unit_list,
            'unit_info'=>$unit_info,
        ];
        show($info,200,'ok');
    }
    public function unitListInfo(){
        $course_id=$this->request->post('course_id',0,'intval');
        if(!$course_id){
            show([],0,'course_id必传');
        }
        $section_list=model('section')
            ->where('course_id',$course_id)
            ->where('delete_time',0)
            ->where('is_show',1)
            ->order('order','desc')
            ->select();
        show($section_list,200,'ok');
    }
}
