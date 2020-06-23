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
        $id=$this->request->post('id',0,'intval');
        $res=model('study_material')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }
    }
    public function videoEdit(){

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
    public function soundDelete(){
        $id=$this->request->post('id',0,'intval');
        $res=model('study_material')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }
    }
    public function soundUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*200,'mp3','../public/uploads/study_material/mp3','study_material/mp3');
        return $msg;
    }
    //笔记列表
    public function noticeList(){
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
                'unit_id' => $this->request->get('unit_id', 0, 'intval'),
            ];
            $where['delete_time']=0;
            if(!empty($data['unit_id'])){
                $where['unit_id']=$data['unit_id'];
            }
            $where['type']=5;
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
    public function noticeUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*200,'jpg','../public/uploads/study_material/notice','study_material/notice');
        return $msg;
    }
    public function noticeAdd(){
        if($this->request->isAjax()){
            $unit_id=$this->request->post('unit',0,'intval');
            $title=$this->request->post('title','','trim');
            $introduction=$this->request->post('introduction','','trim');
            $banner=$this->request->post('banner','','trim');
            $notice=$this->request->post('notice','','trim');
            if(!$unit_id){
                show([],0,'知识点id必传');
            }
            if(empty($title)){
                show([],0,'笔记标题必填');
            }
            if(empty($introduction)){
                show([],0,'笔记介绍必填');
            }
            if(empty($banner)){
                show([],0,'笔记封面必须上传');
            }
            if(empty($notice)){
                show([],0,'笔记必须上传');
            }
            $data['unit_id']=$unit_id;
            $data['title']=$title;
            $data['introduction']=$introduction;
            $data['banner']=$banner;
            $data['file_url']=$notice;
            $data['type']=5;
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
    public function noticeDelete(){
        $id=$this->request->post('id',0,'intval');
        $res=model('study_material')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }
    }
    //ppt列表
    public function pptList(){
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
                'unit_id' => $this->request->get('unit_id', 0, 'intval'),
            ];
            $where['delete_time']=0;
            if(!empty($data['unit_id'])){
                $where['unit_id']=$data['unit_id'];
            }
            $where['type']=4;
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
    public function pptAdd(){
        if($this->request->isAjax()){
            $unit_id=$this->request->post('unit',0,'intval');
            $title=$this->request->post('title','','trim');
            $introduction=$this->request->post('introduction','','trim');
            $banner=$this->request->post('banner','','trim');
            $ppt=$this->request->post('ppt','','trim');
            if(!$unit_id){
                show([],0,'知识点id必传');
            }
            if(empty($title)){
                show([],0,'PPT标题必填');
            }
            if(empty($introduction)){
                show([],0,'PPT介绍必填');
            }
            if(empty($banner)){
                show([],0,'PPT封面必须上传');
            }
            if(empty($ppt)){
                show([],0,'PPT必须上传');
            }
            $data['unit_id']=$unit_id;
            $data['title']=$title;
            $data['introduction']=$introduction;
            $data['banner']=$banner;
            $data['file_url']=$ppt;
            $data['type']=4;
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
    public function pptUpload(){
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*200,'jpg','../public/uploads/study_material/ppt','study_material/ppt');
        return $msg;
    }
    public function pptDelete(){
        $id=$this->request->post('id',0,'intval');
        $res=model('study_material')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }
    }
}