<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use app\admin\model\Course;
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
            $course_data=Course::getCourseInfo(['delete_time'=>0]);
            $section_data=model('section')->where('delete_time',0)->select();
            $this->assign('course_data',$course_data);
            $this->assign('section_data',$section_data);
            return $this->fetch();
        }
    }
    public function edit()
    {

    }
    public function update()
    {

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