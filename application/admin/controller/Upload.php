<?php
/**
 *
 * User: Edwin
 * Date: 2019/5/31
 * Email: <467490186@qq.com>
 */

namespace app\admin\controller;
use app\admin\service\FileUploadService;

class Upload extends Common
{
    public function index()
    {
        if($this->request->isAjax()){
            $file = request()->file('file');
            $msg=FileUploadService::upload($file,1024*1024*2,'jpg,png,gif,txt','../public/uploads');
            return $msg;
        }else{
            return $this->fetch();
        }
    }
    public function dowload()
    {
        if($this->request->isAjax()){
            $msg=FileUploadService::download();
            return $msg;
        }else{
            return $this->fetch();
        }
    }
    //banner图
    public function bannerUpload()
    {
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/background','background');
        return $msg;
    }
    public function bannerAdd()
    {
        if($this->request->isPost())
        {
            $image=$this->request->post('image');
            $name=$this->request->post('name');
            $data=[
                'img_url'=>$image,
                'name'=>$name,
                'type'=>2,
                'create_time'=>time()
            ];
            $res=model('background_images')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch('');
        }
    }
    //轮播图
    public function repeatUpload()
    {
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/background','background');
        return $msg;
    }
    public function repeatAdd()
    {
        if($this->request->isPost())
        {
            $image=$this->request->post('image');
            $name=$this->request->post('name');
            $data=[
                'img_url'=>$image,
                'name'=>$name,
                'type'=>1,
                'create_time'=>time()
            ];
            $res=model('background_images')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch('');
        }
    }
    //分类背景图
    public function cateUpload()
    {
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/background','background');
        return $msg;
    }
    public function cateAdd()
    {
        if($this->request->isPost())
        {
            $image=$this->request->post('image');
            $name=$this->request->post('name');
            $data=[
                'img_url'=>$image,
                'name'=>$name,
                'type'=>4,
                'create_time'=>time()
            ];
            $res=model('background_images')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch('');
        }
    }
    //循环任务图
    public function listUpload()
    {
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/background','background');
        return $msg;
    }
    public function listAdd()
    {
        if($this->request->isPost())
        {
            $image=$this->request->post('image');
            $name=$this->request->post('name');
            $data=[
                'img_url'=>$image,
                'name'=>$name,
                'type'=>5,
                'create_time'=>time()
            ];
            $res=model('background_images')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch('');
        }
    }
    //首页四大模块图
    public function fourUpload()
    {
        $file = request()->file('file');
        $msg=FileUploadService::upload($file,1024*1024*1,'jpg,png,gif,jpeg','../public/uploads/background','background');
        return $msg;
    }
    public function fourAdd()
    {
        if($this->request->isPost())
        {
            $image=$this->request->post('image');
            $name=$this->request->post('name');
            $data=[
                'img_url'=>$image,
                'name'=>$name,
                'type'=>3,
                'create_time'=>time()
            ];
            $res=model('background_images')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            return $this->fetch('');
        }
    }
    public function imagesList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $where=[
                'delete_time'=>0,
            ];
            $list = model('background_images')::where('name','like',"%".$data['key']."%")
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $images_data = [];
            foreach ($list as $key => $val) {
                $images_data[$key] = $val;
            }
            return show($images_data, 0, '', ['count' => $total_list]);
        }else{
            return $this->fetch();
        }
    }
}