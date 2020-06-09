<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;
use app\admin\model\Student as S;

class Student extends Common
{
    public function studentList()
    {
        if ($this->request->isAjax()) {
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = S::where('account','like',"%".$data['key']."%")
                ->where('delete_time',0)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total=$list->total();
            $user_data = [];
            foreach ($list as $key => $val) {
                $user_data[$key]=$val;
            }
            foreach ($user_data as $k=>$v){
                $v['score']=$this->changeMultiple($v['score']);
            }
            return show($user_data, 0, '', ['count' => $total]);
        }else{
            return $this->fetch();
        }
    }
    public function studentDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('student')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}