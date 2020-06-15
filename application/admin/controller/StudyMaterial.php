<?php
namespace app\admin\controller;

use app\admin\service\FileUploadService;
use app\admin\model\Unit;
class StudyMaterial extends Common
{
    public function videoList(){
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'unit_id' => $this->request->get('unit_id', '0', 'intval'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where['delete_time']=0;
            if($data['unit_id']){
                $where['unit_id']=$data['unit_id'];
            }$where['type']=1;
            $list = model('studyMaterial')
                ->where('title','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $study_material = [];
            foreach ($list as $key => $val) {
                $study_material[$key] = $val;
                $study_material[$key]['unit_name']=model('unit')->where(['id'=>$val->unit_id])->value('name');
            }
            return show($study_material, 0, '', ['count' => $total_list]);
        }else{
            $unit=Unit::where('delete_time',0)->select();
            $this->assign('unit',$unit);
            return $this->fetch();
        }
    }
    public function videoUpload(){
            $file = request()->file('file');
            $msg=FileUploadService::upload($file,1024*1024*200,'mp4','../public/uploads/study_material/video','study_material/video');
            return $msg;
    }
    public function bannerUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/study_material/banner','study_material/banner');
        return $msg;
    }
    public function videoAdd(){
        if($this->request->isAjax()){
            $unit_id=$this->request->post('unit',0,'intval');
            $title=$this->request->post('title','','trim');
            $introduction=$this->request->post('introduction','','trim');
            $banner=$this->request->post('banner','','trim');
            $video=$this->request->post('video','','trim');
            if(!$unit_id){
                show([],0,'知识点id必传');
            }
            if(empty($title)){
                show([],0,'试题标题必填');
            }
            if(empty($introduction)){
                show([],0,'视频介绍必填');
            }
            if(empty($banner)){
                show([],0,'视频封面必须上传');
            }
            if(empty($video)){
                show([],0,'视频必须上传');
            }
            $data['unit_id']=$unit_id;
            $data['title']=$title;
            $data['introduction']=$introduction;
            $data['banner']=$banner;
            $data['file_url']=$video;
            $data['type']=1;
            $data['create_time']=time();
            $res=model('studyMaterial')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $unit=Unit::where('delete_time',0)->select();
            $this->assign('unit',$unit);
            return $this->fetch();
        }
    }
    public function videoDelete(){

    }
    public function videoUpdate(){

    }

    public function soundList(){
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'unit_id' => $this->request->get('unit_id', '0', 'intval'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where['delete_time']=0;
            if($data['unit_id']){
                $where['unit_id']=$data['unit_id'];
            }
            $where['type']=3;
            $list = model('studyMaterial')
                ->where('title','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $study_material = [];
            foreach ($list as $key => $val) {
                $study_material[$key] = $val;
                $study_material[$key]['unit_name']=model('unit')->where(['id'=>$val->unit_id])->value('name');
            }
            return show($study_material, 0, '', ['count' => $total_list]);
        }else{
            $unit=Unit::where('delete_time',0)->select();
            $this->assign('unit',$unit);
            return $this->fetch();
        }
    }
    public function soundAdd(){
        if($this->request->isAjax()){
            $unit_id=$this->request->post('unit',0,'intval');
            $title=$this->request->post('title','','trim');
            $introduction=$this->request->post('introduction','','trim');
            $banner=$this->request->post('banner','','trim');
            $sound=$this->request->post('sound','','trim');
            if(!$unit_id){
                show([],0,'unit必传');
            }
            if(empty($title)){
                show([],0,'title必传');
            }
            if(empty($introduction)){
                show([],0,'introduction必填');
            }
            if(empty($banner)){
                show([],0,'banner必须上传');
            }
            if(empty($sound)){
                show([],0,'sound必须上传');
            }
            $data['unit_id']=$unit_id;
            $data['title']=$title;
            $data['introduction']=$introduction;
            $data['banner']=$banner;
            $data['file_url']=$sound;
            $data['type']=3;
            $data['create_time']=time();
            $res=model('studyMaterial')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $unit=Unit::where('delete_time',0)->select();
            $this->assign('unit',$unit);
            return $this->fetch();
        }
    }
    public function soundUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*200,'mp3','../public/study_material/mp3','study_material/mp3');
        return $msg;
    }
}