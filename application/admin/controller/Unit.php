<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;

class Unit extends Common
{
    public function unitList()
    {
        if($this->request->isAjax())
        {
            $where=[
                'delete_time'=>0
            ];
            $data=model('unit')->where($where)->select();
            show($data,0,'');
        }else{
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
        $res=model('unit')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}