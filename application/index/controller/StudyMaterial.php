<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\facade\Session;
class StudyMaterial extends Controller
{
    public function studyMaterialList(){
        $unit_id=$this->request->post('unit_id',0,'intval');
        //type =1 视频   type=3 录音  type =4 ppt   type=5 课堂笔记
        $type=$this->request->post('type',1,'intval');
        if(!$unit_id){
            show([],0,'unit_id不能为空');
        }
        $where=[];
        $where['unit_id']=$unit_id;
        $where['type']=$type;
        $where['delete_time']=0;
        $material=model('studyMaterial')
            ->where($where)
            ->select();
        foreach ($material as $value){
            $value['create_time']=date('Y-m-d H:i:s', $value['create_time']);
        }
        show($material,200,'ok');
    }
    //完成学习资料
    public function completeMaterial(){
        $unit_list_id=$this->request->post('unit_list_id',0,'intval');
        $user_id=$this->request->post('user_id',0,'intval');
        $student_id=Session::get('student_id');
        if($user_id!=$student_id){
            show([],0,'user_id错误');
        }
        if(!$unit_list_id){
            show([],0,'unit_list_id必传');
        }
        $last_send_time=model('systemNews')->where('from_user',$user_id)->order('id','desc')->value('send_time');
        if($last_send_time>= strtotime('-1year')){
            show([],0,'待审核中，请您耐心等待');
        }
        $student_info=model('student')->field('teacher_id,name')->where('id',$user_id)->find();
        $unit_id=model('unit_list')->where('id',$unit_list_id)->value('unit_id');
        $unit_name=model('unit')->where('id',$unit_id)->value('name');
        //发送消息给老师
        $data['content']='老师，你好！我是“'.$student_info['name'].'”,我已观看完“'.$unit_name.'”的学习内容，请您审核！';
        $data['title']=''.$student_info['name'].'的学习进度';
        $data['from_user']=$user_id;
        //查询到学生的所属老师id
        $data['to_user']=$student_info['teacher_id'];
        $data['is_read']=0;
        $data['send_time']=time();
        $data['type']=2;
        $data['status']=0;
        $news_res=model('systemNews')->insert($data);
        if($news_res){
            show([],200,'请求已提交，等待审核');
        }else{
            show([],0,'请求发送失败，请重新尝试');
        }
    }
    //讲解操作
    public function teachAction(){
        $material_id=$this->request->post('material_id',0,'intval');
        $user_id=$this->request->post('user_id',0,'intval');
        $student_id=Session::get('student_id');
        if($user_id!=$student_id){
            show([],0,'user_id错误');
        }
        if(!$material_id){
            show([],0,'material_id必传');
        }
        $student_info=model('student')->field('teacher_id,name')->where('id',$user_id)->find();
        $material_name=model('study_material')->where('id',$material_id)->value('title');
        //发送消息给老师
        $data['content']='老师，你好！我是“'.$student_info['name'].'”,'.$material_name.'我还不太懂';
        $data['title']=''.$student_info['name'].'的难点';
        $data['from_user']=$user_id;
        //查询到学生的所属老师id
        $data['to_user']=$student_info['teacher_id'];
        $data['is_read']=0;
        $data['send_time']=time();
        $data['type']=1;
        $data['status']=0;
        $news_res=model('systemNews')->insert($data);
        if($news_res){
            show([],200,'申请已发送，会有老师找您沟通');
        }else{
            show([],0,'申请发送失败，请重新尝试');
        }
    }
}
