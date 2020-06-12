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
        $student_info=model('student')->field('teacher_id,name')->where('id',$user_id)->find();
        $unit_id=model('unit_list')->where('id',$unit_list_id)->value('unit_id');
        $unit_name=model('unit')->where('id',$unit_id)->value('name');
        //发送消息给老师
        $data['content']='老师你好，我是'.$student_info['name'].',我已观看完'.$unit_name.'的学习内容';
        $data['title']='学习进度';
        $data['from_user']=$user_id;
        //查询到学生的所属老师id
        $data['to_user']=$student_info['teacher_id'];
        $data['is_read']=0;
        $data['send_time']=time();
        $data['type']=2;
        $data['status']=0;
        $news_res=model('systemNews')->insert($data);
    }
}
