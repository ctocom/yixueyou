<?php

namespace app\index\controller;


use think\Controller;
use app\index\model\Student as UserModel;
use think\facade\Session;

class Login extends Controller
{
    public function index()
    {
        $account=$this->request->post('account');
        $user_password=$this->request->post('password');
        if(empty($account)){
            show([],0,'账号不能为空');
        }
        if(empty($user_password)){
            show([],0,'密码不能为空');
        }
        $user_info=UserModel::where(['account'=>$account])->find();
        if(empty($user_info)){
            show([],0,'账号或密码错误');
        }
        if($user_info['status']==0)
        {
            show([],0,'您的账号已被禁用');
        }
        if($user_info['password']!=md5($user_password)){
            show([],0,'账号或密码错误');
        }
        //更新token
        $token=createToken($user_info['id']);
        $last_login_ip=request()->ip();
        $last_login_time=time();
        //更新登录信息
        $data = [
            'last_login_ip' => $last_login_ip,
            'last_login_time' => $last_login_time,
            'token'            =>$token,
            'expire_time'             =>strtotime('+1day')
        ];
        $res=UserModel::where(['id'=>$user_info['id']])->update($data);
        $user_info['last_login_time']=$last_login_time;
        $user_info['last_login_ip']=$last_login_ip;
        if($res)
        {
            $user_info=UserModel::where(['account'=>$account])->find();
            $user_info=$user_info->hidden(['password','seconds_password']);
            show($user_info,200,'登录成功');
        }else{
            show($user_info,200,'登录失败');
        }
    }
    //修改学生密码
    public function updatePassword(){
        $user_id=$this->request->post('user_id',0,'intval');
        $old_password=$this->request->post('old_password','','trim');
        $new1_password=$this->request->post('new1_password','','trim');
        $new2_password=$this->request->post('new2_password','','trim');
        $student_info=model('student')->where('id',$user_id)->find();
        if(!$user_id){
            show([],0,'用户id必传');
        }
        if(md5($old_password)!=$student_info['password']){
            show([],0,'旧密码错误');
        }
        if(empty($new1_password) || empty($new2_password)){
            show([],0,'新密码不能为空');
        }
        if(strlen($new1_password)>16 || strlen($new1_password)<6){
            show([],0,'密码长度不能超过16位且不能小于6位');
        }
        if(strlen($new2_password)>16 || strlen($new2_password)<6){
            show([],0,'确认密码长度不能超过16位且不能小于6位');
        }
        if(md5($new1_password)!=md5($new2_password)){
            show([],0,'新密码两次不一致');
        }
        if(md5($new1_password)==md5($old_password)){
            show([],0,'新旧密码不能一样');
        }
        $res=model('student')->where('id',$user_id)->update(['password'=>md5($new2_password)]);
        if($res){
            show([],200,'修改成功');
        }else{
            show([],0,'修改失败');
        }
    }
    //设置二级密码
    public function setSecondPassword()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        $second1_password=$this->request->post('second1_password','','trim');
        $second2_password=$this->request->post('second2_password','','trim');
        if(!$user_id){
            show([],0,'用户id必传');
        }
        if(strlen($second1_password)>16 || strlen($second1_password)<6){
            show([],0,'密码长度不能超过16位且不能小于6位');
        }
        if(strlen($second2_password)>16 || strlen($second2_password)<6){
            show([],0,'确认密码长度不能超过16位且不能小于6位');
        }
        if(md5($second1_password)!=md5($second2_password)){
            show([],0,'新密码两次不一致');
        }
        $res=model('student')->where('id',$user_id)->update(['seconds_password'=>md5($second2_password)]);
        if($res){
            show([],200,'设置成功');
        }else{
            show([],0,'只能设置一次');
        }
    }
    //退出登录
    public function logout(){
        $user_id=$this->request->post('user_id','','intval');
        $res=model('student')->where('id',$user_id)->update(['expire_time'=>time()]);
        if($res){
            show([],200,'已退出');
        }else{
            show([],0,'退出失败');
        }
    }

}
