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
            $age=$this->request->post('age',0);
            if(!is_numeric($age)){
                show([],0,'年龄必须为数字');
            }
            $sex=$this->request->post('sex',0);
            $tel=$this->request->post('tel',0);
            if(!is_numeric($tel) || strlen($tel)!=11){
                show([],0,'手机号必须为11位纯数字');
            }
            $type=$this->request->post('type',0);
            if(!$type){
                show([],0,'监管模式必须选择');
            }
            $student_password=$this->request->post('student_password');
            if(strlen($student_password)<6 || strlen($student_password)>16){
                show([],0,'密码最少6位最多16位,数字或英文组成');
            }
            $head=$this->request->post('student_head','');
            $status=$this->request->post('status','');
            $data=[];
            $data['account']=$student_account;
            $data['name']=$student_name;
            $data['head']=$head;
            $data['sex']=$sex;
            $data['age']=$age;
            $data['tel']=$tel;
            $data['password']=md5($student_password);
            $data['score']=0;
            $teacher_info=session('user_auth');
            $data['teacher_id']=$teacher_info['uid'];
            $data['status']=$status;
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
            $where[]=['id','neq',$id];
            $where[]=['account','=',$student_account];
            $student_res=model('student')
                ->where($where)
                ->find();
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
            $age=$this->request->post('age',0);
            if(!is_numeric($age)){
                show([],0,'年龄必须为数字');
            }
            $sex=$this->request->post('sex',0);
            $tel=$this->request->post('tel',0);
            if(!is_numeric($tel) || strlen($tel)!=11){
                show([],0,'手机号必须为11位纯数字');
            }
            $type=$this->request->post('type',0);
            if(!$type){
                show([],0,'监管模式必须选择');
            }
            $data=[];
            $student_password1=$this->request->post('student_password1','');
            $student_password2=$this->request->post('student_password2','');
            if(!empty($student_password1)){
                if(strlen($student_password1) <6 || strlen($student_password1) >16){
                    show([],0,'密码最少6位最多16位,数字英文组成');
                }
                $data['password']=md5($student_password1);
            }
            if(!empty($student_password2)){
                if(strlen($student_password2) <6 || strlen($student_password2) >16){
                    show([],0,'二级密码最少6位最多16位,数字英文组成');
                }
                $data['seconds_password']=md5($student_password2);
            }
            $head=$this->request->post('student_head','');
            $status=$this->request->post('status',1);
            $data['account']=$student_account;
            $data['name']=$student_name;
            $data['head']=$head;
            $data['sex']=$sex;
            $data['age']=$age;
            $data['tel']=$tel;
            $teacher_info=session('user_auth');
            $data['teacher_id']=$teacher_info['uid'];
            $data['status']=$status;
            $data['type']=$type;
            $data['update_time']=time();
//            var_dump($data);exit;
            $res=model('student')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
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