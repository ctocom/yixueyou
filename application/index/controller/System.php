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
                $v['image']=Config::get('domain').model('user')->where('uid',$v['id'])->value('head');
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
        $student_openid=model('student')->where('id',$user_id)->value('openid');
        $student_info=model('student')->where('id',$user_id)->find();
        $where1=['to_user_id'=>$student_info['openid'],'from_user_id'=>$from_user_id];
        $where2=['to_user_id'=>$from_user_id,'from_user_id'=>$student_info['openid']];
        $chat_list1=model('systemNews')
            ->where($where1)
            ->order('send_time')
            ->select()
            ->toArray();
        $chat_list2=model('systemNews')
            ->where($where2)
            ->order('send_time')
            ->select()
            ->toArray();
        $chat_list=array_merge($chat_list1,$chat_list2);
        $a=array_column($chat_list,'send_time');
        array_multisort($a,SORT_ASC,$chat_list);
        $domain=Config::get('domain');
        foreach ($chat_list as $k=>$v){
            $chat_list[$k]['send_time']=date('Y-m-d H:i:s',$chat_list[$k]['send_time']);
            if($student_openid==$v['from_user_id']){
                //发送者是学生
                $student=model('student')->where('openid',$v['from_user_id'])->find();
                $chat_list[$k]['head']=$domain.$student['head'];
                $chat_list[$k]['user_name']=$student['name'];
            }else{
                //发送者是老师
                $teacher=model('user')->where('uid',$v['from_user_id'])->find();
                $chat_list[$k]['head']=$domain.$teacher['head'];
                $chat_list[$k]['user_name']=$teacher['name'];
            }
        }
        show($chat_list,200,'ok');
    }
}
