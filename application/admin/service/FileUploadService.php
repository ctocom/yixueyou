<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/7
 * Time: 10:00
 */

namespace app\admin\service;


class FileUploadService
{
   public static function upload($fileInfo,$size,$ext,$adress,$file)
   {
       $info = $fileInfo->validate(['size'=>$size,'ext'=>$ext])->move($adress);
       if($info){
           $msg=['code'=>200,'msg'=>'上传成功','data'=>['src'=>'/uploads/'.$file.'/'.$info->getSaveName()]];
       }else{
           $msg=['code'=>0,'msg'=>$fileInfo->getError()];
       }
       return $msg;
   }
    public static function download()
    {
//        $file_dir = "./uploads/20200601/3d618fb01eff93ae3f1db0e921d1bb37.txt";    // 下载文件存放目录
//        return download($file_dir, './uploads/20200601/a.txt', false);
    }
}