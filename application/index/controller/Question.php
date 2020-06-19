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
        $paper_list=model('user_paper')
            ->where('section_id',$section_id)
            ->where('user_id',$user_id)
            ->select();
        foreach ($paper_list as $v){
            $v['paper_name']=model('paper')->where('id',$v['paper_id'])->value('name');
        }
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
                    'create_time'=>time()
                ];
            }
        }
        $res=model('studentErrorquestion')->insertAll($data);
        if($res){
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
    //统计
    public function studyData()
    {

    }
}
