<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\Db;
use think\Model;

class Question extends Controller
{
    //用户的试卷列表
    public function paperList()
    {
        $section_id=$this->request->post('section_id');
        $unit_list_id=$this->request->post('unit_list_id');
        $user_id=$this->request->post('user_id');
        $type=$this->request->post('type');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$section_id){
            show([],0,'section_id必传');
        }
        $paper_list=model('paper')
            ->where('section_id',$section_id)
            ->where('user_id',$user_id)
            ->where('type',$type)
            ->where('unit_list_id',$unit_list_id)
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
//            show([],0,'question_str必传');
            $unit_id=model('paper')->where('id',$paper_id)->value('unit_id');
            if(!$unit_id){
                show([],0,'paper_id不存在');
            }
            $unit_list_id=model('paper')->where('id',$paper_id)->value('unit_list_id');
            if(!$unit_list_id){
                show([],0,'unit_list_id不存在');
            }
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
                ->where('type',3)
                ->update(['is_complete'=>1]);
            $status_res4=model('unit_list_module')
                ->where('unit_list_id',$unit_list_id)
                ->where('type',4)
                ->update(['is_complete'=>1]);
            $where=[];
            $where=[
                'name'=>'score_config',
                'status'=>1
            ];
            $score=db('config')->where($where)->value('value');
            $score1=json_decode($score,true);
            $integral=$score1['complete_score'];

            if($score){
                $user_score=model('student')->where('id',$user_id)->setInc('score',bcmul($integral,100));
            }
            show([],200,'全部正确，已达标');

        }



        $question_arr=explode(',',$question_str);
        $data=[];
        $arr=model('studentErrorquestion')->field('question_id')->where('user_id',$user_id)->select()->toArray();
        if(is_array($question_arr)){
            foreach ($question_arr as $v){
                if(in_array($v,$arr)){
                    continue;
                }
                $data[]=[
                    'question_id'=>$v,
                    'paper_id'=>$paper_id,
                    'user_id'=>$user_id,
                    'create_time'=>time()
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
                ->where('type',3)
                ->update(['is_complete'=>1]);
            $status_res4=model('unit_list_module')
                ->where('unit_list_id',$unit_list_id)
                ->where('type',4)
                ->update(['is_complete'=>1]);
//            var_dump($error_info);exit;
//            $data=array_diff_assoc2_deep($data,$error_info,'question_id');
//            $data=second_array_unique_bykey($data, 'question_id');
        }
        $res=model('studentErrorquestion')->insertAll($data);
        if($res){
            // 录入错题后生成错题本试卷
//            $paper_res=paper_random_data($user_id,$unit_id,$unit_list_id,2);
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
//        $section_id=$this->request->post('section_id',0,'intval');
        $unit_list_id=$this->request->post('unit_list_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$unit_id){
            show([],0,'unit_id必传');
        }
//        if(!$section_id){
//            show([],0,'section_id必传');
//        }
        if(!$unit_list_id){
            show([],0,'unit_list_id必传');
        }

          //判断是否有试卷
        $where='';
        $where=[
            'user_id'=>$user_id,
            'unit_list_id'=>$unit_list_id,
            'unit_id'=>$unit_id
        ];
        $paper_count=model('paper')->where($where)->count();
        if($paper_count==1){
            $paper_id=model('paper')->where($where)->field('id')->find();
            $where='';
            $where=[
                'paper_id'=>$paper_id,
            ];
            $paper_data=model('paper_question')->where($where)->select();
            show($paper_data,200,'ok');
        }

        $question_data=question_random_data(3,2);
        if(!$question_data){
            show([],0,'题库里面的题太少了');
        }

        //随机生成一个试卷
        $paper_res=paper_random_data($user_id,$unit_id,$unit_list_id,1);
        foreach ($question_data as $k=>$v){
            $question_data[$k]['paper_id']=$paper_res;
            $question_data[$k]['question_id']=$v['id'];
            $question_data[$k]['user_id']=$user_id;
            unset($question_data[$k]['id']);
        }

        $paper_question_add=Db::table('think_paper_question')->insertAll($question_data);
//        show($paper_res,200,'ok');
        $paper_question_list=model('paperQuestion')
            ->where('user_id',$user_id)
            ->where('paper_id',$paper_res)
            ->select();
        $count_num=count($paper_question_list);
        $data=[
            'paper_id'=>$paper_res,
            'count_num'=>$count_num,
            'paper_question_list'=>$paper_question_list
        ];
        show($data,200,'ok');
    }
    //答案
    public function paperanswer()
    {

    }
}
