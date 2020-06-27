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
        $type=$this->request->post('type');
        $where=[
            'type'=>$type,
            'delete_time'=>0
        ];
        $images_data=model('background_images')->where($where)->select()->toArray();
        foreach ($images_data as $k=>$v){
            $images_data[$k]['img_url']=Config::get('domain').$v['img_url'];
        }
        show($images_data,200,'ok');
    }
}
