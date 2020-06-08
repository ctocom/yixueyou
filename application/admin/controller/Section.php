<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;

class Section extends Common
{
    public function sectionList()
    {
        if($this->request->isAjax())
        {
            $where=[
                'is_show'=>1,
                'delete_time'=>0
            ];
            $data=model('section')->where($where)->select();
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
    public function delete()
    {

    }
}