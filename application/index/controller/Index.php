<?php

namespace app\index\controller;


use app\admin\controller\Common;
use think\facade\Config;

class Index extends Common
{
    public function index()
    {
        return 1;
    }
    public function imagesInfo()
    {
        $type=$this->request->post('type','0');
        if(!$type){
            show([],0,'type必传');
        }
        $where=[
            'type'=>$type,
            'delete_time'=>0
        ];
        $images_data=model('background_images')->where($where)->select()->toArray();
        $images_data=array_column($images_data,'img_url');
        foreach ($images_data as $k=>$v){
            $images_data[$k]=Config::get('domain').$v;
        }
        show($images_data,200,'ok');
    }
}
