<?php

namespace app\index\controller;


use app\index\model\UnitList;
use app\index\model\UnitListModule;
use think\Controller;
use app\index\model\Course;
use app\index\model\Section;
use app\index\model\Unit;
use app\index\model\UserUnit;
use think\facade\Config;
class StudentCourse extends Controller
{
    //课程数据
    public function index()
    {
       $course_info=Course::where(['is_show'=>1,'delete_time'=>0])->order('order','asc')->select();
       $domain=Config::get('domain');
       foreach ($course_info as $v){
           $v['icon']=$domain.$v['icon'];
       }
       show($course_info,200,'ok');
    }
    //章数据
    public function section()
    {
        $course_id=$this->request->post('course_id');
        if(!$course_id){
            show([],0,'course_id不能为空');
        }
        $section_info=Section::where(['is_show'=>1,'delete_time'=>0,'course_id'=>$course_id])->order('order','asc')->select();
        foreach ($section_info as $v){
            $v['icon']=Config::get('domain').$v['icon'];
        }
        show($section_info,200,'ok');
    }
    //节数据
    public function unit()
    {
        $section_id=$this->request->post('section_id');
        if(!$section_id){
            show([],0,'section_id不能为空');
        }
        $unit_info=Unit::where(['delete_time'=>0,'section_id'=>$section_id])->order('order','asc')->select()->toArray();
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
        $section=model('section')->where('id',$section_id)->find();
        $course_name=model('course')->where('id',$section['course_id'])->value('name');
        //知识点数据
        $unit_info=Unit::where(['delete_time'=>0])
            ->alias('u')
            ->where('u.section_id',$section_id)
            ->select()
            ->toArray();
            $user_unit=UserUnit::where('user_id',$user_id)->select()->toArray();
            if(!empty($user_unit)){
                foreach ($unit_info as $k=>$v){
                    foreach ($user_unit as $kk=>$vv){
                        if($v['id']==$vv['unit_id']){
                            $unit_info[$k]['complete_num']=$user_unit[$kk]['complete_num'];
                        }else{
                            $unit_info[$k]['complete_num']=0;
                        }
                    }
                }
            }else{
                foreach ($unit_info as $k=>$v){
                    $unit_info[$k]['complete_num']=0;
                }
            }
        //默认查找第一个知识点的队列
        if(empty($user_unit)){
            //默认
            $unit_id=$unit_info[0]["id"];
        }else{
            $unit_id= model('user_unit')
                ->where('user_id',$user_id)
                ->order('complete_num','asc')
                ->order('id','asc')
                ->value('unit_id');
        }
        $user_list_module=model('user_unit_list_module')->where('user_id',$user_id)->select()->toArray();
        $unit_list=[];
        $unit_list=UnitList
            ::alias('u')
            ->field('u.*,uu.icon')
            ->join('unit uu','uu.id = u.unit_id')
            ->where('u.unit_id',$unit_id)
            ->select()
            ->toArray();
        $user_unit_list=[];
        if($user_id){
            $user_unit_list=model('unit_user_list')->where('user_id',$user_id)->select()->toArray();
        }
        if(!empty($user_unit_list)){
            foreach ($unit_list as $k=>$v){
                foreach ($user_unit_list as $kk=>$vv){
                    if($vv['unit_list_id']==$v['id']){
                        $unit_list[$k]['complete_rate']=$vv['complete_rate'];
                    }else{
                        $unit_list[$k]['complete_rate']=0;
                    }
                }
            }
        }else{
            foreach ($unit_list as $k=>$v){
                $unit_list[$k]['complete_rate']=0;
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $unit_list[$k]['name']=$name;
                $unit_list[$k]['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select()->toArray();
                foreach ($unit_list[$k]['module'] as $kk=>$vv){
                    foreach ($user_list_module as $kkk=>$vvv){
                        if($vvv['unit_list_module_id']==$vv['id']){
                            $unit_list[$k]['module'][$kk]['is_complete']=1;
                        }else{
                            $unit_list[$k]['module'][$kk]['is_complete']=0;
                        }
                    }
                }
            }
        }
        if(empty($unit_list)){
            foreach ($unit_list as $v){
                $v['complete_rate']=0;
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $v['name']=$name;
                $unit_list[$k]['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select()->toArray();
                foreach ($unit_list[$k]['module'] as $kk=>$vv){
                    foreach ($user_list_module as $kkk=>$vvv){
                        if($vvv['unit_list_module_id']==$vv['id']){
                            $unit_list[$k]['module'][$kk]['is_complete']=1;
                        }else{
                            $unit_list[$k]['module'][$kk]['is_complete']=0;
                        }
                    }
                }
            }
        }else{
            foreach ($unit_list as $k=>$v){
                if(!$user_id){
                    $v['complete_rate']=0;
                }
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $unit_list[$k]['name']=$name;
                $unit_list[$k]['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select()->toArray();
                foreach ($unit_list[$k]['module'] as $kk=>$vv){
                    if(!empty($user_list_module)){
                        foreach ($user_list_module as $kkk=>$vvv){
                            if($vvv['unit_list_module_id']==$vv['id']){
                                $unit_list[$k]['module'][$kk]['is_complete']=1;
                            }else{
                                $unit_list[$k]['module'][$kk]['is_complete']=0;
                            }
                        }
                    }else{
                        $unit_list[$k]['module'][$kk]['is_complete']=0;
                    }
                }
            }
        }
        foreach ($unit_list as $v){
            $v['icon']=Config::get('domain').$v['icon'];
            foreach ($v['module'] as $vv){
                $vv['icon']=Config::get('domain').$vv['icon'];
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
