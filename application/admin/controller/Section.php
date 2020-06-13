<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use app\admin\model\Course;
use app\admin\service\FileUploadService;
class Section extends Common
{
    public function sectionList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where=[
                'delete_time'=>0,
            ];
            $list = model('section')::where('name','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $section_data = [];
            foreach ($list as $key => $val) {
                $section_data[$key] = $val;
                $section_data[$key]['course_name']=model('course')->where('id',$section_data[$key]['course_id'])->value('name');
            }
            return show($section_data, 0, '', ['count' => $total_list]);
        }else{
            return $this->fetch();
        }
    }
    public function sectionAdd()
    {
        if($this->request->isAjax()){
            $section_name=$this->request->post('section_name');
            $section_icon=$this->request->post('section_icon');
            $status=$this->request->post('status');
            $course_id=$this->request->post('course_id');
            $section_order=$this->request->post('section_order');
            $data['name']=$section_name;
            $data['icon']=$section_icon;
            $data['course_id']=$course_id;
            $data['is_show']=$status;
            $data['order']=$section_order;
            $data['create_time']=time();
            $res=model('section')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $course_data=Course::getCourseInfo([]);
            $this->assign('course_data',$course_data);
            return $this->fetch();
        }
    }
    //章节图标上传
    public function sectionUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/icon','icon');
        return $msg;
    }
    public function sectionEdit()
    {
        if($this->request->isPost()){
            $section_name=$this->request->post('section_name');
            $id=$this->request->post('id');
            $section_icon=$this->request->post('section_icon');
            $course_id=$this->request->post('course_id');
            $status=$this->request->post('status');
            $section_order=$this->request->post('section_order');
            $data['course_id']=$course_id;
            $data['name']=$section_name;
            $data['icon']=$section_icon;
            $data['is_show']=$status;
            $data['order']=$section_order;
            $data['update_time']=time();
            $res=model('section')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
            }
        }else{
            $id=$this->request->param('uid');
            $section_data=model('section')->where('id',$id)->find();
            $course_data=Course::getCourseInfo([]);
            $this->assign('course_data',$course_data);
            $this->assign('section_data',$section_data);
            return $this->fetch();
        }
    }
    public function sectionDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('section')->where('id',$id)->update(['delete_time'=>time()]);;
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}