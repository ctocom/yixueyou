<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;
use app\admin\model\Student as S;
use app\admin\service\FileUploadService;
class Student extends Common
{
    public function studentList()
    {
        if ($this->request->isAjax()) {
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = S::where('account','like',"%".$data['key']."%")
                ->where('delete_time',0)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total=$list->total();
            $user_data = [];
            foreach ($list as $key => $val) {
                $user_data[$key]=$val;
            }
            foreach ($user_data as $k=>$v){
                $v['score']=$this->changeMultiple($v['score']);
            }
            return show($user_data, 0, '', ['count' => $total]);
        }else{
            return $this->fetch();
        }
    }
    //学生添加
    public function studentAdd()
    {
        if($this->request->isAjax()){
            $student_account=$this->request->post('student_account','');
            if(empty($student_account)){
                show([],0,'账号不能为空');
            }
            if(strlen($student_account)<6 || strlen($student_account)>16){
                show([],0,'账号最少6位最多16位,数字或英文组成');
            }
            if(!preg_match("/^[a-zA-Z0-9]+$/u",$student_account)){
                show([],0,'账号最少6位最多16位,数字或英文组成');

            }
            $student_res=model('student')->where('account',$student_account)->find();
            if($student_res){
                show([],0,'账号已存在');
            }
            $student_name=$this->request->post('student_name','');
            if(empty($student_name)){
                show([],0,'用户名不能为空');
            }
            $is_chinese=isAllChinese($student_name);
            if(!$is_chinese){
                show([],0,'用户名只能是汉字');
            }
            if(strlen($student_name)>9 || strlen($student_name)<6){
                show([],0,'用户名最多三个汉字,最少两个汉字');
            }
            $type=$this->request->post('type',0);
            if(!$type){
                show([],0,'监管模式必须选择');
            }
            $student_password=$this->request->post('student_password','');
            if(empty($student_password)){
                show([],0,'密码不能为空');
            }
            if(strlen($student_password) <6 || strlen($student_password) >16){
                show([],0,'密码最少6位最多16位,数字英文组成');
            }
            $head=$this->request->post('student_head','');
            $data=[];
            $data['account']=$student_account;
            $data['name']=$student_name;
            $data['head']=$head;
            $data['password']=$student_password;
            $data['score']=0;
            $teacher_info=session('user_auth');
            $data['teacher_id']=$teacher_info['uid'];
            $data['status']=1;
            $data['type']=$type;
            $data['create_time']=time();
            $res=model('student')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch();
        }

    }
    //修改学生资料
    public function studentEdit()
    {
        $id=$this->request->param('uid');
        if($this->request->isAjax()){
            $student_account=$this->request->post('student_account','');
            if(empty($student_account)){
                show([],0,'账号不能为空');
            }
            if(strlen($student_account)<6 || strlen($student_account)>16){
                show([],0,'账号最少6位最多16位,数字或英文组成');
            }
            if(!preg_match("/^[a-zA-Z0-9]+$/u",$student_account)){
                show([],0,'账号最少6位最多16位,数字或英文组成');

            }
            $student_res=model('student')->where('account',$student_account)->find();
            if($student_res){
                show([],0,'账号已存在');
            }
            $student_name=$this->request->post('student_name','');
            if(empty($student_name)){
                show([],0,'用户名不能为空');
            }
            $is_chinese=isAllChinese($student_name);
            if(!$is_chinese){
                show([],0,'用户名只能是汉字');
            }
            if(strlen($student_name)>9 || strlen($student_name)<6){
                show([],0,'用户名最多三个汉字,最少两个汉字');
            }
            $type=$this->request->post('type',0);
            if(!$type){
                show([],0,'监管模式必须选择');
            }
            $student_password=$this->request->post('student_password','');
            if(empty($student_password)){
                show([],0,'密码不能为空');
            }
            if(strlen($student_password) <6 || strlen($student_password) >16){
                show([],0,'密码最少6位最多16位,数字英文组成');
            }
            $head=$this->request->post('student_head','');
            $data=[];
            $data['account']=$student_account;
            $data['name']=$student_name;
            $data['head']=$head;
            $data['password']=$student_password;
            $data['score']=0;
            $teacher_info=session('user_auth');
            $data['teacher_id']=$teacher_info['uid'];
            $data['status']=1;
            $data['type']=$type;
            $data['create_time']=time();
            $res=model('student')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $student_data=model('student')->where('id',$id)->find();
            $this->assign('student_data',$student_data);
            return $this->fetch();
        }
    }
    //上传头像
    public function headUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpeg,jpg,gif,png','../public/uploads/student_head','student_head');
        return $msg;
    }
    public function studentDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('student')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}