<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use app\admin\model\Course;
use app\admin\model\Section;
use app\admin\service\FileUploadService;
class Unit extends Common
{
    public function unitList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where=[
                'delete_time'=>0,
            ];
            $list = model('unit')::where('name','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $unit_data = [];
            foreach ($list as $key => $val) {
                $unit_data[$key] = $val;
                $unit_data[$key]['course_name']=model('course')->where('id',$unit_data[$key]['course_id'])->value('name');
                $unit_data[$key]['section_name']=model('section')->where('id',$val['section_id'])->value('name');
            }
            return show($unit_data, 0, '', ['count' => $total_list]);
        }else{
            return $this->fetch();
        }
    }
    public function unitAdd()
    {
        if($this->request->isAjax()){
            $unit_name=$this->request->post('unit_name');
            $unit_icon=$this->request->post('unit_icon');
            $status=$this->request->post('status');
            $course_id=$this->request->post('course_id');
            $section_id=$this->request->post('section_id');
            $unit_order=$this->request->post('unit_order');
            $data['name']=$unit_name;
            $data['icon']=$unit_icon;
            $data['course_id']=$course_id;
            $data['section_id']=$section_id;
            $data['is_show']=$status;
            $data['order']=$unit_order;
            $data['create_time']=time();
            $res=model('unit')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $course_data=Course::getCourseInfo(['delete_time'=>0]);
            $this->assign('course_data',$course_data);
            return $this->fetch();
        }
    }
    //知识点图标上传
    public function unitUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/icon','icon');
        return $msg;
    }
    //章节数据
    public function unitSection()
    {
        $course_id=$this->request->post('course_id');
        $section_data=Section::getSectionInfo($course_id);
        show($section_data,200,'ok');
    }
    public function unitEdit()
    {
        if($this->request->isPost()){
            $unit_name=$this->request->post('unit_name');
            $id=$this->request->post('id');
            $unit_icon=$this->request->post('unit_icon');
            $course_id=$this->request->post('course_id');
            $section_id=$this->request->post('section_id');
            $status=$this->request->post('status');
            $unit_order=$this->request->post('unit_order');
            $data['course_id']=$course_id;
            $data['section_id']=$section_id;
            $data['name']=$unit_name;
            $data['icon']=$unit_icon;
            $data['is_show']=$status;
            $data['order']=$unit_order;
            $data['update_time']=time();
            $res=model('unit')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
            }
        }else{
            $id=$this->request->param('uid');
            $section_data=model('section')->select();
            $unit_data=model('unit')->where('id',$id)->find();
            $course_data=Course::getCourseInfo([]);
            $this->assign('course_data',$course_data);
            $this->assign('section_data',$section_data);
            $this->assign('unit_data',$unit_data);
            return $this->fetch();
        }
    }
    public function unitDelete()
    {
        $id=intval($this->request->post('id'));
        //todo 检测分类下是否有学习资料和试题  有不能删除  需要先删除对应的学习资料和试题
        $res=model('unit')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}