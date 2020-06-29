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
        if(empty($unit_info)){
            $info=[
                'nav_name'=>$course_name.'-'.$section['name'],
                'unit_id'=>0,
                'unit_list'=>[],
                'unit_info'=>[],
            ];
            show($info,200,'ok');
        }
            $user_unit=UserUnit::where('user_id',$user_id)->where('section_id',$section_id)->select()->toArray();
            if(!empty($user_unit)){
                foreach ($unit_info as $k=>$v){
                    $user_unit_num=model('user_unit')->where('unit_id',$v['id'])->where('user_id',$user_id)->find();
                    if($user_unit_num['unit_id']==$v['id']){
                        $unit_info[$k]['complete_num']=$user_unit_num['complete_num'];
                    }else{
                        $unit_info[$k]['complete_num']=0;
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
            $unit_id_big=Unit::where(['delete_time'=>0])
                ->alias('u')
                ->where('u.section_id',$section_id)
                ->where('u.delete_time',0)
                ->order('id','desc')
                ->value('id');
            $unit_res=model('user_unit')->where('unit_id',$unit_id_big)->find();
            if(!$unit_res){
                //第一遍循环
                $complete_unit_id=model('user_unit')
                    ->where('complete_num',1)
                    ->where('section_id',$section_id)
                    ->where('user_id',$user_id)
                    ->order('id')->value('unit_id');
                $unit_id=model('unit')
                    ->where('id','>',$complete_unit_id)
                    ->where('delete_time',0)
                    ->order('id')
                    ->value('id');
            }else if($unit_res['complete_num']==1){
                $unit_id=$unit_info[0]["id"];
            }

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
        if(empty($user_unit_list)){
            foreach ($unit_list as $k=>$v){
                $unit_list[$k]['complete_rate']=0;
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $v['name']=$name;
                $unit_list[$k]['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select()->toArray();
                foreach ($unit_list[$k]['module'] as $kk=>$vv){
                    $unit_list_module_res=model('user_unit_list_module')->where('user_id',$user_id)->where('unit_list_module_id',$vv['id'])->find();
                    if(!empty($unit_list_module_res)){
                        $unit_list[$k]['module'][$kk]['is_complete']=1;
                    }else{
                        $unit_list[$k]['module'][$kk]['is_complete']=0;
                    }
                }
            }
        }else{
            foreach ($unit_list as $k=>$v){
                $user_unit_rate=model('unit_user_list')->where('user_id',$user_id)->where('unit_list_id',$v['id'])->find();
                if($user_unit_rate){
                    $unit_list[$k]['complete_rate']=$user_unit_rate['complete_rate'];
                }else{
                    $unit_list[$k]['complete_rate']=0;
                }
                $name=Unit::where(['id'=>$v['unit_id']])->value('name');
                $unit_list[$k]['name']=$name;
                $unit_list[$k]['module']=UnitListModule::where(['unit_list_id'=>$v['id']])->select()->toArray();
                foreach ($unit_list[$k]['module'] as $kk=>$vv){
                    $unit_list_module_res=model('user_unit_list_module')->where('user_id',$user_id)->where('unit_list_module_id',$vv['id'])->find();
                    if($unit_list_module_res){
                        $unit_list[$k]['module'][$kk]['is_complete']=1;
                    }else{
                        $unit_list[$k]['module'][$kk]['is_complete']=0;
                    }
                }
            }
        }
        foreach ($unit_list as $k=>$v){
            $unit_list[$k]['icon']=Config::get('domain').$unit_list[$k]['icon'];
            $unit_list[$k]['name']=model('unit')->where('id',$v['unit_id'])->value('name');
            foreach ($v['module'] as $kk=>$vv){
                $unit_list[$k]['module'][$kk]['icon']=Config::get('domain').$v['module'][$kk]['icon'];
            }
        }
        $info=[
            'nav_name'=>$course_name.'-'.$section['name'],
            'unit_id'=>$unit_id,
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
    //所有未完成第2/3遍的知识点列表
    public function unitListBefore()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        $section_id=$this->request->post('section_id',0,'intval');
        $unit_id=$this->request->post('unit_id',0,'intval');
        $type=$this->request->post('type',0,'intval');
        if($type==2){
            $unit_list=model('user_unit')
                ->where('section_id',$section_id)
                ->where('user_id',$user_id)
                ->where('unit_id',$unit_id)
                ->where('compltet_num',1)
                ->select()->toArray();
        }else if($type==3){
            $unit_list=model('user_unit')
                ->where('section_id',$section_id)
                ->where('user_id',$user_id)
                ->where('unit_id',$unit_id)
                ->where('compltet_num',2)
                ->select()->toArray();
        }
        show($unit_list,200,'ok');
    }
}
