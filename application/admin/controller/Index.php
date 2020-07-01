<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */
namespace app\admin\controller;

use auth\Auth;
use app\admin\model\User;
use app\admin\model\Config;

class Index extends Common
{
    /**
     * 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //获取菜单
        $menuList = (new Auth($this->uid, $this->group_id))->getMenuList();
        $this->assign('menuList', $menuList);
        $info = User::get($this->uid)->hidden(['password']);
        $info['head'] ? : $info['head'] = '/images/face.jpg';
        //查询后台未读系统消息
        $admin_info=session('user_auth');
        $system_num=model('system_news')
            ->where('to_user',$admin_info['user'])
            ->where('is_read',0)
            ->where('delete_time',0)
            ->count();
        $this->assign('system_num', $system_num);
        $this->assign('info', $info);
        //公告
        $notice_config = $this->noticeConfig();
        $this->assign('notice_config', $notice_config);
        return $this->fetch();
    }

    /**
     * layui 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function home()
    {
        return $this->fetch();
    }

    /**
     * 公告配置信息
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function noticeConfig()
    {
        $notice_config = cache('notice_config');
        if ($notice_config) {
            return $notice_config;
        }
        $list = Config::where('name', '=', 'notice_config')->field('value')->find();
        cache('notice_config', $list);
        return $list;
    }
    //统计
    public function statistics()
    {
        $teacher_info=session('user_auth');
        $t_id=$teacher_info['uid'];
        if ($this->request->isAjax()){
            $teacher_id=explode(',',$this->request->post('teacher_id'));
            $type=$this->request->post('type',0);
            if($type==1){
//                $t_id=model('user')->column('uid');
                $teacher_id=$t_id;
            }else{
                $teacher_id=$teacher_id;
            }
            $student_name=model('student')
                ->where('teacher_id','in',$teacher_id)
                ->where('delete_time',0)
                ->column('name');
            $student_info=model('student')
                ->field('name,id')
                ->where('teacher_id','in',$teacher_id)
                ->where('delete_time',0)
                ->select();
            $student_id=model('student')
                ->where('teacher_id','in',$teacher_id)
                ->where('delete_time',0)
                ->column('id');
            //学生正确率
            $student_errorquestion_num=model('student_errorquestion')
                ->where('user_id','in',$student_id)
                ->select()
                ->toArray();
            $new_student_errorquestion=[];
            foreach ($student_errorquestion_num as $k=>$v){
                $new_student_errorquestion[$v['user_id']][]=$v;
            }
            $new_student_question=[];
            foreach ($student_id as $k=>$v){
                if(isset($new_student_errorquestion[$v])){
                    $new_student_question[]=count($new_student_errorquestion[$v]);
                }else{
                    $new_student_question[]=0;
                }
            }
            $paper_num=model('paper')
                ->where('user_id','in',$student_id)
                ->select()
                ->toArray();
            $new_paper_num=[];
            foreach ($paper_num as $k=>$v){
                $new_paper_num[$v['user_id']][]=$v;
            }
            $new_user_paper_num=[];
            foreach ($student_id as $k=>$v){
                if(isset($new_paper_num[$v])){
                    $new_user_paper_num[]=count($new_paper_num[$v])*9;
                }else{
                    $new_user_paper_num[]=0;
                }
            }
            $true_rate=[];
            foreach ($student_id as $k=>$v){
                if(intval($new_user_paper_num[$k])!=0){
                    $true_rate[]=intval(bcsub(100,bcmul(bcdiv(intval($new_student_question[$k]),intval($new_user_paper_num[$k]),2),100)));
                }else{
                    $true_rate[]=0;
                }
            }
            //学习进度统计
            $unit_num=model('unit')->select()->toArray();
            $unit_num=intval(bcmul(count($unit_num),3));
            $study_complete_num=model('user_unit')
                ->where('user_id','in',$student_id)
                ->select()->toArray();
            $new_study_complete_num=[];
            foreach ($study_complete_num as $k=>$v){
                $new_study_complete_num[$v['user_id']][]=$v;
            }
//            print_r($new_study_complete_num);exit;
            $new_study_rate=[];
            foreach ($student_id as $k=>$v){
                if(isset($new_study_complete_num[$v])){
                    $new_study_rate[]=array_sum(array_column($new_study_complete_num[$v],'complete_num'));
                }else{
                    $new_study_rate[]=0;
                }
            }
            $study_rate=[];
            foreach ($student_id as $k=>$v){
                if(isset($new_study_rate[$k])){
                    $study_rate[]=bcmul(bcdiv($new_study_rate[$k],$unit_num,2),100);
                }else{
                    $study_rate[]=0;
                }
            }
            $student=[
                'student_name'=>$student_name,
                'student_info'=>$student_info,
                'true_rate'=>$true_rate,
                'study_rate'=>$study_rate
            ];
            show($student,200,'ok');
        }else{
            if($t_id==1){
                $teacher_list=model('user')->where('uid','>',1)->select()->toArray();
                $this->assign('teacher_list',$teacher_list);
                $this->assign('type',1);
            }else{
                $this->assign('type',2);
            }
            //查出所有的老师
            return $this->fetch();
        }
    }
}
