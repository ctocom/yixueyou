<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;

class Question extends Controller
{
    //用户的试卷列表
    public function paperList()
    {
        $section_id=$this->request->post('section_id');
        $user_id=$this->request->post('user_id');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$section_id){
            show([],0,'section_id必传');
        }
        $paper_list=model('paper')
            ->where('section_id',$section_id)
            ->where('user_id',$user_id)
            ->select();
        show($paper_list,200,'ok');
    }
    //录入错题
    public function recordErrorQuestion(){
        $paper_id=$this->request->post('paper_id',0,'intval');
        $question_str=$this->request->post('question_str','');
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$paper_id){
            show([],0,'paper_id必传');
        }
        if(empty($question_str)){
            show([],0,'question_str必传');
        }
        $question_arr=explode(',',$question_str);
        $data=[];
        if(is_array($question_arr)){
            foreach ($question_arr as $v){
                $data[]=[
                    'question_id'=>$v,
                    'paper_id'=>$paper_id,
                    'user_id'=>$user_id,
                ];
            }
        }
        //录入错题时检测是否是第二遍，如果是第二遍，直接达标
        $error_info=model('student_errorquestion')
            ->field('question_id,paper_id,user_id')
            ->where('user_id',$user_id)
            ->where('paper_id',$paper_id)
            ->select()->toArray();
        $unit_id=model('paper')->where('id',$paper_id)->value('unit_id');
        if(!$unit_id){
            show([],0,'paper_id不存在');
        }
        $unit_list_id=model('paper')->where('id',$paper_id)->value('unit_list_id');
        if(!$unit_list_id){
            show([],0,'unit_list_id不存在');
        }
        if(!empty($error_info)){
            //改状态为达标
            #查看该试卷是第几遍知识点
            $type=model('unit_list')->where('id',$unit_list_id)->value('type');
            #修改队列状态和知识点状态
            $status_res1=model('unit_user_list')
                ->where('unit_list_id',$unit_list_id)
                ->where('type',$type)
                ->update(['complete_rate'=>100]);
            $status_res2=model('user_unit')
                ->where('unit_id',$unit_id)
                ->update(['complete_num'=>$type]);
            $status_res3=model('unit_list_module')
                ->where('unit_list_id',$unit_list_id)
                ->update(['is_complete'=>1]);
//            $data=array_merge($data,$error_info);
//            $data=second_array_unique_bykey($data,'question_id');
        }
        var_dump($error_info);exit;
        $res=model('studentErrorquestion')->insertAll($data);
        if($res){
            // todo 录入错题后生成错题本试卷
            $paper_res=paper_random_data($user_id,$unit_id,$unit_list_id,2);
            show([],200,'录入成功');
        }else{
            show([],0,'录入失败');
        }
    }
    //用户试卷内的试题
    public function paperQuestion()
    {
        $paper_id=$this->request->post('paper_id',0,'intval');
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$paper_id){
            show([],0,'paper_id必传');
        }
        $paper_question_list=model('paperQuestion')
            ->where('user_id',$user_id)
            ->where('paper_id',$paper_id)
            ->select();
        $count_num=count($paper_question_list);
        $data=[
            'count_num'=>$count_num,
            'paper_question_list'=>$paper_question_list
        ];
        show($data,200,'ok');
    }
    //用户错题试卷列表
    public function userErrorquestionList()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        if (!$user_id){
            show([],0,'user_id必传');
        }
        $where=[
            'user_id'=>$user_id,
            'delete_time'=>0
        ];
        $paper_id_arr=model('student_errorquestion')->where($where)->group('paper_id')->column('paper_id');
        $paper_id_str=implode(',',$paper_id_arr);
        $where2[]=['id','in',$paper_id_str];
        $paper_info=model('paper')
            ->field('id,name,create_time')
            ->where($where2)
            ->select();
        foreach ($paper_info as $v){
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        show($paper_info,200,'ok');

    }
    //按规则生成一个试卷
    public function userPaperAction(){
        $user_id=$this->request->post('user_id',0,'intval');
        $unit_id=$this->request->post('unit_id',0,'intval');
        $section_id=$this->request->post('section_id',0,'intval');
        $unit_list_id=$this->request->post('unit_list_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$unit_id){
            show([],0,'unit_id必传');
        }
        if(!$section_id){
            show([],0,'section_id必传');
        }
        $question_data=question_random_data(3,2);
        if(!$question_data){
            show([],0,'题库里面的题太少了');
        }
        //随机生成一个试卷
        $paper_res=paper_random_data($user_id,$unit_id,$section_id,$unit_list_id,1);
        show($paper_res,200,'ok');
    }
}
