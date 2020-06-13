<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\facade\Session;

class SignIn extends Controller
{

    public function signIn()
    {
        $user_id=$this->request->post('user_id');
        //获取用户信息
        $user_info=model('student')->where('id',$user_id)->find();
        /**
         * 2.验证今日是否已签到
         * 获取今日凌晨时间戳, 通过查询 sign_in_time 字段来判断
         */
        $signIn = model('student_sign');
        $today_time = strtotime(date('Y-m-d'));
        // 查询条件
        // 查询今日是否已签到
        $exist = $signIn
            ->where('user_id',$user_info['id'])
            ->where('sign_in_time','>=',$today_time)
            ->count();
        if ($exist > 0) {
            show([],0,'今天已经签到过啦!');
        }
        /**
         * 3.开始签到逻辑
         * 先查询昨天的签到记录
         *   如果查到, 则说明是连续签到, 连续签到天数加1
         *   未查到, 连续签到天数为1
         * 根据连续签到天数, 获取相应的积分
         */
        $yesterdayAt = $today_time - 86400;
        // 昨天的签到记录
        $yesterdayRecord = $signIn
            ->where('user_id',$user_info['id'])
            ->where('sign_in_time','EGT',$yesterdayAt)
            ->find();
        // 连续签到天数
        $continuousDays = 1;
        if (!empty($yesterdayRecord)) {
            // 更新连续签到天数
            $continuousDays += $yesterdayRecord['continuous_days'];
        }
        $integral = 0; // 积分数
        $config = $this->getConfig();
        // 通过连续签到天数, 获取相应的积分
        foreach ($config as $day => $integralItem) {
            if ($day > $continuousDays) {
                break;
            }
            $integral = $integralItem;
        }
        $insertData = array(
            'user_id' => $user_info['id'],
            'username' => $user_info['name'],
            'integral' => bcmul($integral,100),
            'continuous_days' => $continuousDays,
            'sign_in_time' => time(),
            'created_time' => time(),
        );
        // 得到了连续签到天数, 和应得的积分, 此处可以添加 额外获取积分的逻辑
        $insertRet = $signIn->insert($insertData);
        if (!$insertRet) {
            show([],0,'签到失败');
        }
        //更新用户积分数
        $res=model('student')->where('id',$user_info['id'])->setInc('score',bcmul($integral,100));
        $msg=sprintf('签到成功, 连续签到%d天, 获得%d积分', $continuousDays, $integral);
        if($res){
            show([],200,$msg);
        }
    }
    /**
     * 签到获取积分规则
     * @return array
     */
    private function getConfig()
    {
        /**
         * 基础积分
         * 如: 第一天5积分, 连续签到每天多5积分, n天及以上每天m积分, 此处n=8, m=40
         */
//        $config = array(
//            1 => 5, // 第一天5积分
//            2 => 10,
//            3 => 15,
//            4 => 20,
//            5 => 25,
//            6 => 30,
//            7 => 35,
//            8 => 40,
//            9 => 45,
//            10 => 50,
//            11 => 55,
//            12 => 60,
//        );

        /**
         * 每天相同签到积分
         */
        $config = array(
            1 => 5, // 每天都是5积分
        );
//        $config = array(
//            1 => 5, // 一天以上5积分
//            3 => 10, // 三天以上10积分
//            5 => 20, // 五天以上20积分
//            10 => 50, // 10天以上50积分
//        );

        return $config;
    }
    /**
     * 积分排行榜
     */
    public function signRankList(){
        $limit=Config::get('score_rank_limit');
        $list=model('student')
            ->where('score','>',0)
            ->Field('score,name')
            ->order('score','desc')
            ->limit($limit)
            ->select();
        foreach ($list as $v){
            $v['score']=bcdiv($v['score'],100);
        }
        show($list,200,'ok');
    }
}
