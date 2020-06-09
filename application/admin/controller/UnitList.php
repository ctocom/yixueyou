<?php
namespace app\admin\controller;

class UnitList extends Common
{
    public function unitList()
    {
        if($this->request->isAjax())
        {
            $user_id=$this->request->post('user_id');
            $type=$this->request->post('type');
            $complete_rate=$this->request->post('complete_status');
            $where=[];
            if($user_id){
                $where['user_id']=$user_id;
            }else if($type){
                $where['type']=$type;
            }else if($complete_rate){
                $where['complete_rate']=$complete_rate;
            }
            $data=model('unit_list')->where($where)->select();
            foreach ($data as $key=>$v){
                $data[$key]['user_name']=model('student')->where('id',$v['user_id'])->value('name');
                $data[$key]['unit_name']=model('unit')->where('id',$v['unit_id'])->value('name');
            }
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