<?php
namespace app\admin\controller;
use app\admin\model\Section;
use app\admin\model\Unit;
use app\admin\model\QuestionType;
use app\admin\model\Question as Questions;
class Question extends Common
{
    public function questionList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = Questions::withSearch(['title'], ['title' => $data['key']])
                ->paginate($data['limit'], false, ['query' => $data]);
            $question_data = [];
            foreach ($list as $key => $val) {
                $question_data[$key] = $val;
                $question_data[$key]['teacher_name']=model('user')->where(['uid'=>$val->teacher_id])->value('name');
            }
            return show($question_data, 0, '', ['count' => $list->total()]);
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
           $data['analysis']=$post_data['unit'];
           $data['title']=$post_data['content'];
           $answer=$post_data['answer'];
           $answer2=$post_data['answer2'];
           $data['type']=intval($post_data['question_type']);
           if($data['type']==1){
               //选择题
                $data['options']=$answer;
           }else if($data['type']==2){
               //多选题
               $data['options']=$answer2;
           }else if($data['type']==3){
               //简答题
               $data['keyword']=$answer;
           }else if($data['type']==4){
               //判断题
               $data['answer']=$answer;
           }
           $data['name']=$data['title'];
           $user = session('user_auth');
           $data['teacher_id']=$user['uid'];
           $res=model('question')->save($data);
           if($res){
               show([],200,'添加成功');
           }else{
               show([],0,'添加失败');
           }
        }else{
            $course_data=Course::getCourseInfo();
            $question_type=QuestionType::getQuestionType();
            $this->assign('course_data',$course_data);
            $this->assign('question_type',$question_type);
            return  $this->fetch();
        }
    }
    public function edit()
    {

    }
    public function update()
    {

    }
    public function delete()
    {

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