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
        $sub = model('systemNews')
            ->field('to_user,from_user,content,send_time')
            ->where('to_user' ,$to_user)
            ->union('SELECT from_user,to_user,content,send_time FROM think_system_news WHERE to_user = '.$to_user)
            ->buildSql();
        $query = Db::table($sub)
            ->alias('tmp')
            ->group('tmp.to_user')
            ->order('tmp.send_time')
            ->buildSql();
        $info = DB::table($query)
            ->alias('t')
            ->field('t.to_user,name,head,content,send_time')
            ->join('student u', 't.to_user=u.account', 'LEFT')
            ->select();
        var_dump($info);exit;
        if(!empty($system_list)){
            foreach($system_list as $k=>$v){
                $v['from_user_name']=model('user')->where('uid',$v['from_user_id'])->value('name');
            }
        }
        show($system_list,200,'ok');
    }
    //单个好友聊天记录
    public function systemInfo()
    {

    }
}
