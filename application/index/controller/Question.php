<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\Db;
use think\Model;

class Question extends Controller
{
    //用户的试题列表
    public function paperQuestionList()
    {
        $paper_id=$this->request->post('paper_id',0);
        $user_id=$this->request->post('user_id',0);
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$paper_id){
            show([],0,'paper_id必传');
        }
        $where=[
            'paper_id'=>$paper_id,
            'user_id'=>$user_id,
        ];
        $paper_data=model('paper_Question')->field('question_id,title,type,radios,unit_id')->where($where)->select();
        $question_data=[
            'paper_data'=>$paper_data,
        ];
        show($question_data,200,'ok');
    }
    //录入错题
    public function recordErrorQuestion(){
        $paper_id=$this->request->post('paper_id',0,'intval');
        $question_arr=$this->request->post('question_str','');
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$paper_id){
            show([],0,'paper_id必传');
        }
        $unit_id=model('paper')->where('id',$paper_id)->value('unit_id');
        if(!$unit_id){
            show([],0,'paper_id参数错误');
        }
        $section_id=model('unit')->where('id',$unit_id)->value('section_id');
        if(empty($question_arr)){
            //没有错误 直接达标
            $unit_list_type=model('paper')->where('id',$paper_id)->value('type');
                if($unit_list_type==2){
                    //检测该知识点第一盏灯是否亮
                    $is_complete_num=model('user_unit')->where('user_id',$user_id)->where('unit_id',$unit_id)->where('complete_num',2)->find();
                    if($is_complete_num){
                        show([],0,'该知识点第二遍已经达标');
                    }
                    $user_unit_res= model('user_unit')->where('unit_id',$unit_id)->where('user_id',$user_id)->update(['complete_num'=>2]);
                }else if($unit_list_type==3){
                    //检测该知识点第一盏灯是否亮
                    $is_complete_num=model('user_unit')->where('user_id',$user_id)->where('unit_id',$unit_id)->where('complete_num',3)->find();
                    if($is_complete_num){
                        show([],0,'该知识点第三遍已经达标');
                    }
                    $user_unit_res= model('user_unit')->where('unit_id',$unit_id)->where('user_id',$user_id)->update(['complete_num'=>3]);
                }else if($unit_list_type==1){
                    $unit_list_id=model('paper')->where('id',$paper_id)->value('unit_list_id');
                    //检测该知识点第一盏灯是否亮
                    $is_complete_num=model('user_unit')->where('user_id',$user_id)->where('unit_id',$unit_id)->where('complete_num',1)->find();
                    if($is_complete_num){
                        show([],0,'该知识点第一遍已经达标');
                    }
                    $unit_user_list_res=model('unit_user_list')
                        ->where('unit_list_id',$unit_list_id)
                        ->where('user_id',$user_id)
                        ->update(['complete_rate'=>100]);
                    //检测达标模块改为完成
                    $module_id1=model('unit_list_module')
                        ->where('unit_list_id',$unit_list_id)
                        ->where('type',3)
                        ->value('id');
                    $module_id2=model('unit_list_module')
                        ->where('unit_list_id',$unit_list_id)
                        ->where('type',4)
                        ->value('id');
                    $unit_module_arr=[
                        ['unit_list_module_id'=>$module_id1,'user_id'=>$user_id,'is_complete'=>1],
                        ['unit_list_module_id'=>$module_id2,'user_id'=>$user_id,'is_complete'=>1],
                    ];
                    model('user_unit_list_module')->insertAll($unit_module_arr);
                    //知识点亮一个灯
                    $user_unit_res= model('user_unit')->insert(['complete_num'=>1,'unit_id'=>$unit_id,'user_id'=>$user_id,'section_id'=>$section_id]);
                }
            //加积分
            //检测完成加积分
            $score_config=json_decode(model('config')->where('name','score_config')->value('value'),true);
            $check_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['check_score'],100)));
            //达标加积分
            $complete_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['complete_score'],100)));
            //满分额外加积分
            $good_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['good_score'],100)));
             show([],200,'全部正确，已达标');
        }else{
            //有错误，判断是否是第一次录错
            $error_info=model('student_errorquestion')
                ->field('question_id,paper_id,user_id')
                ->where('user_id',$user_id)
                ->where('paper_id',$paper_id)
                ->where('delete_time',0)
                ->select()->toArray();
            if(empty($error_info)){
                //第一次录错题
                foreach ($question_arr as $v){
                    $data[]=[
                        'question_id'=>$v,
                        'paper_id'=>$paper_id,
                        'user_id'=>$user_id,
                        'create_time'=>time()
                    ];
                }
                $res=model('studentErrorquestion')->insertAll($data);
                if($res){
                    show([],200,'录入成功');
                }else{
                    show([],0,'录入失败');
                }
            }else{
                //第二次录错题  录完达标
//                $delete_res=model('studentErrorquestion')
//                    ->where('user_id',$user_id)
//                    ->where('paper_id',$paper_id)
//                    ->update(['delete_time'=>time()]);
                if(is_array($question_arr)){
                    $data=[];
                    foreach ($question_arr as $v){
                        $err_res=model('studentErrorquestion')->where('question_id',$v)->find();
                        if(!$err_res){
                            $data[]=[
                                'question_id'=>$v,
                                'paper_id'=>$paper_id,
                                'user_id'=>$user_id,
                                'create_time'=>time()
                            ];
                        }
                    }
                    if(!empty($data)){
                        $res=model('studentErrorquestion')->insertAll($data);
                    }else{
                        $res=true;
                    }
                }else{
                    show([],0,'试题参数错误');
                }
                if($res){
                    $unit_list_type=model('paper')->where('id',$paper_id)->value('type');
                    if(!$unit_list_type){
                        show([],0,'paper_id参数错误');
                    }
                    if($unit_list_type==2){
                        $complete_num=2;
                        //知识点亮一个灯
                        $user_unit_res= model('user_unit')->where('unit_id',$unit_id)->where('user_id',$user_id)->update(['complete_num'=>2]);
                    }else if($unit_list_type==3){
                        $user_unit_res= model('user_unit')->where('unit_id',$unit_id)->where('user_id',$user_id)->update(['complete_num'=>3]);
                    }else if($unit_list_type==1){
                        //知识点亮一个灯
                        $unit_list_id=model('paper')->where('id',$paper_id)->value('unit_list_id');
                        $user_unit_res= model('user_unit')->insert(['complete_num'=>1,'unit_id'=>$unit_id,'user_id'=>$user_id,'section_id'=>$section_id]);
                        //检测达标模块改为完成
                        $module_id1=model('unit_list_module')
                            ->where('unit_list_id',$unit_list_id)
                            ->where('type',3)
                            ->value('id');
                        $module_id2=model('unit_list_module')
                            ->where('unit_list_id',$unit_list_id)
                            ->where('type',4)
                            ->value('id');
                        $unit_module_arr=[
                            ['unit_list_module_id'=>$module_id1,'user_id'=>$user_id,'is_complete'=>1],
                            ['unit_list_module_id'=>$module_id2,'user_id'=>$user_id,'is_complete'=>1],
                        ];
                        model('user_unit_list_module')->insertAll($unit_module_arr);
                        $user_unit_res=model('unit_user_list')
                            ->where('unit_list_id',$unit_list_id)
                            ->where('user_id',$user_id)
                            ->update(['complete_rate'=>100]);
                    }
                    //加积分
                    //检测完成加积分
                    $score_config=json_decode(model('config')->where('name','score_config')->value('value'),true);
                    $check_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['check_score'],100)));
                    //达标加积分
                    $complete_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['complete_score'],100)));
                    show([],200,'录入成功');
                }else{
                    show([],0,'录入失败');
                }
            }
        }
    }
    //答案
    public function paperQuestion()
    {
        $paper_id=$this->request->post('paper_id',0,'intval');
        $user_id=$this->request->post('user_id',0,'intval');
        $seconds_password=$this->request->post('seconds_password','','trim');

        if(!$user_id){
            show([],0,'user_id必传');
        }
        $pass=model('student')
            ->where('id',$user_id)
            ->value('seconds_password');
        if(!$pass){
            show([],100,'没有二级密码，请设置。');
        }
        if(!$paper_id){
            show([],0,'paper_id必传');
        }
        if(!$seconds_password){
            show([],0,'二级密码不能为空哦');
        }
        if($pass!=md5($seconds_password)){
            show([],0,'二级密码错误');
        }
        $paper_question_list=model('paperQuestion')
            ->field('type,analysis,options,answer,keyword,id')
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
        $type=$this->request->post('type',1,'intval');
        $student_info=model('student')->where('id',$user_id)->find();
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(!$unit_id){
            show([],0,'unit_id必传');
        }
        if($unit_list_id){
            if($type!=1){
                show([],0,'type参数错误');
            }
            //第一遍
            //检测是否审核学习和作业
            //判断unit_list_id是否正确
            $list_id_res=model('unit_list')->where('id',$unit_list_id)->find();
            if(!$list_id_res){
                show([],0,'unit_list_id参数错误');
            }
            $is_complete1=model('system_news')->where('unit_list_id',$unit_list_id)->where('from_user_id',$student_info['openid'])->where('type',2)->where('unit_id',$unit_id)->where('delete_time',0)->find();
            $is_complete2=model('system_news')->where('unit_list_id',$unit_list_id)->where('type',3)->where('from_user_id',$student_info['openid'])->where('unit_id',$unit_id)->where('delete_time',0)->find();
            if(!$is_complete1){
                show([],0,'您还没有学习呢');
            }
            if($is_complete1['status']==0){
                show([],0,'您的学习进度没有被审核');
            }
            if(!$is_complete2){
                show([],0,'您还没有完成作业呢');
            }
            if($is_complete2['status']==0){
                show([],0,'您的作业没有被审核');
            }
            //判断是否有试卷
            $where=[
                'user_id'=>$user_id,
                'unit_list_id'=>$unit_list_id,
                'unit_id'=>$unit_id,
            ];
            $paper_count=model('paper')->where($where)->count();
        }else{
            //第二遍
            if($type==2){
                $where=[
                    'user_id'=>$user_id,
                    'type'=>2,
                    'unit_id'=>$unit_id,
                ];
                $paper_count=model('paper')->where($where)->count();
            }else if($type==3){
                //第三遍
                $where=[
                    'user_id'=>$user_id,
                    'type'=>3,
                    'unit_id'=>$unit_id,
                ];
                $paper_count=model('paper')->where($where)->count();
            }
        }
        if($paper_count!=0){
            $paper_id=model('paper')->where($where)->value('id');
            $paper_url=model('paper')->where($where)->value('paper_url');
            $where=[
                'paper_id'=>$paper_id,
            ];
            $paper_data=model('paper_Question')->field('id,title,type,radios,unit_id')->where($where)->select();
            $p_data=[
                'paper_url'=>$paper_url,
                'paper_data'=>$paper_data,
                'paper_id'=>$paper_id
            ];
            show($p_data,200,'ok');
        }
        $question_data=question_random_data(3,2,$unit_id);
        if(!$question_data) {
            show([], 0, '题库里面的题太少了');
        }
        //随机生成一个试卷
        $paper_res=paper_random_data($user_id,$unit_id,$unit_list_id,$type);//试卷id
        $question_data_all=[];
        foreach ($question_data as $k=>$v) {
            $question_data_all[$k]['name']=$v['name'];
            $question_data_all[$k]['type1']=$v['type1'];
            $question_data_all[$k]['type']=$v['type'];
            $question_data_all[$k]['title']=$v['title'];
            $question_data_all[$k]['radios']=$v['radios'];
            $question_data_all[$k]['analysis']=$v['analysis'];
            $question_data_all[$k]['options']=$v['options'];
            $question_data_all[$k]['answer']=$v['answer'];
            $question_data_all[$k]['keyword']=$v['keyword'];
            $question_data_all[$k]['keyword_imp']=$v['keyword_imp'];
            $question_data_all[$k]['course_id']=$v['course_id'];
            $question_data_all[$k]['section_id']=$v['section_id'];
            $question_data_all[$k]['unit_id']=$v['unit_id'];
            $question_data_all[$k]['score']=$v['score'];
            $question_data_all[$k]['create_time']=time();
            $question_data_all[$k]['paper_id']=$paper_res;
            $question_data_all[$k]['user_id']=$user_id;
            $question_data_all[$k]['question_id']=$v['id'];
        }
        $paper_question_add = Db::table('think_paper_question')->insertAll($question_data_all);
        //生成试卷文件
        $this->paperWord($paper_res,$user_id,1);
        //生成答案文件
        $this->paperWord($paper_res,$user_id,2);
        $paper_question_list=model('paperQuestion')
            ->field('id,title,type,radios,unit_id')
            ->where('user_id',$user_id)
            ->where('paper_id',$paper_res)
            ->select();
        $count_num=count($paper_question_list);
        $paper_url=model('paper')->where('id',$paper_res)->value('paper_url');
        $data=[
            'paper_id'=>$paper_res,
            'paper_url'=>$paper_url,
            'count_num'=>$count_num,
            'paper_data'=>$paper_question_list
        ];
        show($data,200,'ok');
    }
    //错题数
    public function errCount()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        if(!$user_id){
            show([],0,'user_id 必填');
        }
        $errCount=Model('student_errorquestion')
            ->where('user_id',$user_id)
            ->where('delete_time',0)
            ->count();
        show($errCount,200,'ok');
    }
    //错题本、历史错题列表
    public function userErr()
    {
        $user_id=$this->request->post('user_id',0,'intval');
        $type=$this->request->post('type',0,'intval');
        $user_err=$this->request->post('user_err',0,'intval');
        $seconds_password=$this->request->post('seconds_password',0,'intval');
        if(!$user_id){
            show([],0,'user_id 必填');
        }
        if(!$type){
            show([],0,'type 必填');
        }
        if(!$user_err){
            show([],0,'user_err 必填');
        }
        if($user_err==1){
            $where=[
                'user_id'=>$user_id,
                'delete_time'=>0
            ];
        }else{
            $where=[
                'user_id'=>$user_id,
//                'delete_time'=>0
            ];
        }

        $question_id=model('student_errorquestion')->field('question_id')->where($where)->select()->toArray();
        if(!$question_id){
            show([],0,'没有错题');
        }
        $arr=[];
        foreach($question_id as $v){
            $arr[]=$v['question_id'];
        }
        if($type==1){
            $err_data=model('question')->field('id,title,type,radios,unit_id')->where('id','in',$arr)->select()->toArray();
        }else{
            $pass=model('student')
                ->where('id',$user_id)
                ->value('seconds_password');
            if($pass!=md5($seconds_password)){
                show([],0,'二级密码错误');
            }
            $err_data=model('question')->field('id,type,unit_id,analysis,options,answer,keyword')->where('id','in',$arr)->select()->toArray();
        }
        show($err_data,200,'ok');


    }
    //错题本、历史错题  答案打印
    public function userErrorNotice()
    {
        $user_id=$this->request->post('user_id');
        $type=$this->request->post('type','0');
        $answer=$this->request->post('answer','0');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if($type==0){
            show([],0,'type必传');
        }
        if($type==1){
            //当前错题
            $history=1;
        }else{
            //历史错题
            $history=2;
        }
        if($answer==0){
            show([],0,'answer必传');
        }
        $res1=$this->errorQuestionWord($user_id,1,$history);//试题
        $res2=$this->errorQuestionWord($user_id,2,$history);//试题答案
        if($res1 && $res2){
            if($answer==1){
                $data=model('student_error_notice')->field('error_paper_url')->where('user_id',$user_id)->find();
            }else{
                $data=model('student_error_notice')->field('error_answer_url')->where('user_id',$user_id)->find();
            }
            show($data,200,'打印成功');
        }else{
            show([],0,'打印失败');
        }
    }
    //错题清零
    public function errorClear(){
        $question_arr=$this->request->post('question_str','');
        $user_id=$this->request->post('user_id','');
        if(!$user_id){
            show([],0,'user_id必传');
        }
        if(empty($question_arr) || !is_array($question_arr)){
            show([],0,'question_arr参数错误');
        }
        $res=model('student_errorquestion')
            ->where('user_id',$user_id)
            ->where('question_id','in',$question_arr)
            ->update(['delete_time'=>time()]);
        if($res){
            // 加积分
            //清完错题加积分
            $score_config=json_decode(model('config')->where('name','score_config')->value('value'),true);
            $check_score_res=model('student')->where('id',$user_id)->setInc('score',intval(bcmul($score_config['error_notice_score'],100)));
            show([],200,'错题已清除');
        }else{
            show([],0,'错题清除失败');
        }
    }
    //统计
    public function statisticsStudent(){
        $user_id=$this->request->post('user_id',0);
        if(!$user_id){
            show([],200,'user_id必传');
        }
        //正确率  历史错误的题数/试卷题数乘以试卷数*100%
        $student_errorquestion_num=model('student_errorquestion')->where('user_id',$user_id)->group('question_id')->select()->toArray();
        $student_errorquestion_num=count($student_errorquestion_num);
        $paper_num=model('paper')->where('user_id',$user_id)->select()->toArray();
        if(empty($paper_num)){
            $paper_num=0;
        }else{
            $paper_num=count($paper_num);
        }
        $question_num=9;
        $paper_num=intval(bcmul($paper_num,$question_num));
        $true_rate=0;
        if($paper_num!=0){
            $true_rate=bcmul(bcdiv($student_errorquestion_num,$paper_num,2),100);
        }
        //学习时长统计
        $study_time=model('student')->where('id',$user_id)->value('study_time');
        //排行榜统根据学习时长排行
        $student_rank=model('student')->order('study_time')->select();
        //学习进度统计
        $unit_num=model('unit')->select()->toArray();
        $unit_num=intval(bcmul(count($unit_num),3));
        $study_complete_num=model('user_unit')->where('user_id',$user_id)->column('complete_num');
        $study_rate=0;
        foreach ($study_complete_num as $v){
            $study_rate+=$v;
        }
        if($unit_num!=0){
            $study_rate=bcmul(bcdiv($study_rate,$unit_num,2),100);
        }else{
            $study_rate=0;
        }
        $data=[
            'true_rate'=>$true_rate,
            'study_rate'=>$study_rate,
//            'study_time'=>$study_time,
            'student_rank'=>$student_rank
        ];
        show($data,200,'ok');
    }
    //生成试题
    public function paperWord($paper_id,$user_id,$type=1)
    {
        //从数据库查这个学生试卷的所有题
        $data=[];
        $where=[
            'paper_id'=>$paper_id,
            'user_id'=>$user_id
        ];
        if($type==1){
            //生成试题
            $name='question';
        }else{
            //生成答案
            $name='question_answer';
        }
        $data=model('paper_question')->where($where)->select()->toArray();
        $this->assign('data',$data);//把获取的数据传递的模板，替换模板里面的变量
        $content = $this->fetch('word/'.$name);//获取模板内容信息word是模板的名称
        $fileContent = WordMake($content);//生成word内容
        $url='uploads/paper/'.randomFileName().".doc";
//        $name = iconv("utf-8", "GBK",$data[0]['name']);//转换好生成的word文件名编码
        $fp = fopen($url, 'w');//打开生成的文档
        //将试卷路径保存到试卷表
        if($type==1){
            //生成试题
            $update=['paper_url'=>$url];
        }else{
            //生成答案
            $update=['answer_url'=>$url];
        }
        $res=model('paper')->where('id',$paper_id)->update($update);
        fwrite($fp, $fileContent);//写入包保存文件
        fclose($fp);
        if($res && $data){
            return true;
        }else{
            return false;
        }
    }
    //生成错题
    public function errorQuestionWord($user_id,$type=1,$history=0)
    {
        //从数据库查这个学生试卷的所有题
        if($history==1){
            //历史错题
            $where=[
                'user_id'=>$user_id,
            ];
        }else if($history==2){
            //当前错题
            $where=[
                'user_id'=>$user_id,
                'delete_time'=>0
            ];
        }
        if($type==1){
            //生成试题
            $name='error_question';
        }else{
            //生成答案
            $name='error_question_answer';
        }
        $question_id_arr=model('student_errorquestion')->where($where)->column('question_id');
        $data=model('paper_question')->where('question_id','in',$question_id_arr)->select()->toArray();
        $this->assign('data',$data);//把获取的数据传递的模板，替换模板里面的变量
        $content = $this->fetch('word/'.$name);//获取模板内容信息word是模板的名称
        $fileContent = WordMake($content);//生成word内容
        $url='uploads/errorquestion/'.randomFileName().".doc";
//        $name = iconv("utf-8", "GBK",$data[0]['name']);//转换好生成的word文件名编码
        $fp = fopen($url, 'w');//打开生成的文档
        //将试卷路径保存到试卷表
        if($type==1){
            //生成试题
            $insert=['user_id'=>$user_id,'error_paper_url'=>$url,'create_time'=>time()];
            $update=['error_paper_url'=>$url,'update_time'=>time()];
        }else{
            //生成答案
            $update=['error_answer_url'=>$url,'update_time'=>time()];
            $insert=['user_id'=>$user_id,'error_answer_url'=>$url,'create_time'=>time()];
        }
        $q_res=model('student_error_notice')->where('id',$user_id)->find();
        if(!$q_res){
            $res=model('student_error_notice')->insert($insert);
        }else{
            $res=model('student_error_notice')->where('id',$user_id)->update($update);
        }
        fwrite($fp, $fileContent);//写入包保存文件
        fclose($fp);
        if($res && $data){
            return true;
        }else{
            return false;
        }
    }
}
