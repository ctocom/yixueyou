<?php

namespace app\index\controller;


use think\Controller;
use app\index\model\Student as UserModel;
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
        $last_login_ip=request()->ip();
        $last_login_time=time();
        //更新登录信息
        $data = [
            'last_login_ip' => $last_login_ip,
            'last_login_time' => $last_login_time,
        ];
        $res=UserModel::where(['id'=>$user_info['id']])->update($data);
        $user_info=$user_info->hidden(['password','seconds_password']);
        $user_info['last_login_time']=$last_login_time;
        $user_info['last_login_ip']=$last_login_ip;
        if($res)
        {
            show($user_info,200,'登录成功');
        }
    }
}
