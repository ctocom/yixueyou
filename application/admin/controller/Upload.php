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
}