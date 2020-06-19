<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\db;

class System extends Controller
{
    //系统消息列表
    public function systemList()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        $student_info=model('student')->where('id',$user_id)->find();
        $where=[
            'to_user'=>$student_info['account']
        ];
        $to_user=$student_info['account'];
        $wmsg=model('systemNews')
            ->where('to_user',$to_user)
            ->order('id','desc')
            ->buildSql();
        $system_list = model('systemNews')->table($wmsg.' a')->group('from_user')->select();
        if(!empty($system_list)){
            foreach($system_list as $k=>$v){
                $v['from_user_name']=model('user')->where('uid',$v['from_user_id'])->value('name');
                $v['image']=Config::get('domain')->model('user')->where('uid',$v['head'])->value('head');
            }
        }
        show($system_list,200,'ok');
    }
    //单个好友聊天记录
    public function systemInfo()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        $from_user_id=$this->request->post('from_user_id',0,'intval');
        if(!$from_user_id){
            show([],0,'from_user_id必传');
        }
        $chat_list=model('systemNews')
            ->where('from_user_id',$from_user_id)
            ->select();
        show($chat_list,200,'ok');
    }
}
