<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use app\admin\service\FileUploadService;

class Course extends Common
{
    public  function courseList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where=[
                'delete_time'=>0,
            ];
            $list = model('course')::where('name','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $course_data = [];
            foreach ($list as $key => $val) {
                $course_data[$key] = $val;
            }
            return show($course_data, 0, '', ['count' => $total_list]);
        }else{
            return $this->fetch();
        }
    }
    public function courseAdd()
    {
        if($this->request->isAjax()){
            $course_name=$this->request->post('course_name');
            $course_icon=$this->request->post('course_icon');
            $status=$this->request->post('status');
            $course_order=$this->request->post('course_order');
            $data['name']=$course_name;
            $data['icon']=$course_icon;
            $data['is_show']=$status;
            $data['order']=$course_order;
            $data['create_time']=time();
            $res=model('course')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch();
        }
    }
    public function courseEdit()
    {
        if($this->request->isPost()){
            $course_name=$this->request->post('course_name');
            $id=$this->request->post('id');
            $course_icon=$this->request->post('course_icon');
            $status=$this->request->post('status');
            $course_order=$this->request->post('course_order');
            $data['name']=$course_name;
            $data['icon']=$course_icon;
            $data['is_show']=$status;
            $data['order']=$course_order;
            $data['update_time']=time();
            $res=model('course')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
            }
        }else{
            $id=$this->request->param('uid');
            $course_data=model('course')->where('id',$id)->find();
            $this->assign('course_data',$course_data);
            return $this->fetch();
        }
    }
    //课程图标上传
    public function courseUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/icon','icon');
        return $msg;
    }
    //课程删除
    public function courseDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('course')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}