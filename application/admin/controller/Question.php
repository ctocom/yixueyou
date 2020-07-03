<?php
namespace app\admin\controller;
use app\admin\model\Section;
use app\admin\model\Unit;
use app\admin\model\QuestionType;
use app\admin\model\Question as Questions;
use app\admin\model\Course;
class Question extends Common
{
    public function questionList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = Questions::where('title','like',"%".$data['key']."%")
                ->where('delete_time',0)
                ->where('')
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $question_data = [];
            foreach ($list as $key => $val) {
                $question_data[$key] = $val;
                $question_data[$key]['teacher_name']=model('user')->where(['uid'=>$val->teacher_id])->value('name');
                $question_data[$key]['course_name']=model('course')->where(['id'=>$val->course_id])->value('name');
                $question_data[$key]['unit_name']=model('unit')->where(['id'=>$val->unit_id])->value('name');
                $question_data[$key]['section_name']=model('section')->where(['id'=>$val->section_id])->value('name');
            }
            return show($question_data, 0, '', ['count' => $total_list]);
        }else{
            return  $this->fetch();
        }
    }
    public function questionAdd(){
        if($this->request->isAjax()){
           $post_data=$this->request->post();
           $data=[];
           $data['course_id']=intval($post_data['course']);
           $data['section_id']=intval($post_data['section']);
           $data['unit_id']=intval($post_data['unit']);
           $data['analysis']=$post_data['analysis'];
           $data['title']=$post_data['content'];
           $data['type1']=$post_data['type1'];
           $data['radios']=$post_data['question'];
           $answer=$post_data['answer'];
           $answer2=$post_data['answer2'];
           $data['type']=intval($post_data['question_type']);
           if($data['type']==1){
               //选择题
                $data['options']=$answer;

           }else if($data['type']==2){
               //多选题
               $data['options']=implode('|',$answer);
           }else if($data['type']==3){
               //简答题
               $data['keyword']=$answer;
           }else if($data['type']==4){
               //判断题
               $data['answer']=$answer;
           }else if($data['type']==5){
               //填空题题
               $data['keyword']=$answer;
           }
           $data['name']=$data['title'];
           $user = session('user_auth');
           $data['teacher_id']=$user['uid'];
           $data['create_time']=time();
           $res=model('question')->save($data);
           if($res){
               show([],200,'添加成功');
           }else{
               show([],0,'添加失败');
           }
        }else{
            $course_data=Course::getCourseInfo([]);
            $question_type=QuestionType::getQuestionType();
            $this->assign('course_data',$course_data);
            $this->assign('question_type',$question_type);
            return  $this->fetch();
        }
    }
    public function questionEdit()
    {
        $id=$this->request->param('id');
        if($this->request->isAjax()){
            $post_data=$this->request->post();
            $data=[];
            $data['course_id']=intval($post_data['course']);
            $data['section_id']=intval($post_data['section']);
            $data['unit_id']=intval($post_data['unit']);
            $data['analysis']=$post_data['analysis'];
            $data['title']=$post_data['content'];
            $data['type1']=$post_data['type1'];
            $data['radios']=$post_data['question'];
            $answer=$post_data['answer'];
            $answer2=$post_data['answer2'];
            $data['type']=intval($post_data['question_type']);
            if($data['type']==1){
                //选择题
                $data['options']=$answer;

            }else if($data['type']==2){
                //多选题
                $data['options']=implode('|',$answer);
            }else if($data['type']==3){
                //简答题
                $data['keyword']=$answer;
            }else if($data['type']==4){
                //判断题
                $data['answer']=$answer;
            }else if($data['type']==5){
                //填空题题
                $data['keyword']=$answer;
            }
            $data['name']=$data['title'];
            $user = session('user_auth');
            $data['teacher_id']=$user['uid'];
            $data['create_time']=time();
            $res=model('question')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
            }
        }else{
            $course_data=Course::getCourseInfo([]);
            $question_type=QuestionType::getQuestionType();
            $question_data=model('question')->where('id',$id)->find();
            if($question_data['type']==1){
                $question_data['options']=explode('|',$question_data['options']);
            }
            $section_data=model('section')->select();
            $unit_data=model('unit')->select();
            $this->assign('question_data',$question_data);
            $this->assign('course_data',$course_data);
            $this->assign('section_data',$section_data);
            $this->assign('unit_data',$unit_data);
            $this->assign('question_type',$question_type);
            return  $this->fetch();
        }
    }
    //试题删除
    public function questionDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('question')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
    //章节数据
    public function questionSection()
    {
        $course_id=$this->request->post('course_id');
        $section_data=Section::getSectionInfo($course_id);
        show($section_data,200,'ok');
    }
    //知识点数据
    public function questionUnit()
    {
        $section_id=$this->request->post('section_id');
        $unit_data=Unit::getUnitInfo($section_id);
        show($unit_data,200,'ok');
    }
}