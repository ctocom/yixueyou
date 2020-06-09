<?php
namespace app\admin\controller;

class UnitList extends Common
{
    public function unitList()
    {
        if($this->request->isAjax())
        {
            $user_id=$this->request->get('user_id','0','intval');
            $type=$this->request->get('type','0','intval');
            $complete_rate=$this->request->get('complete_status','-1');
            $where=[];
            $data=[];
            if($user_id){
                $where['user_id']=$user_id;
            }else if($type){
                $where['type']=$type;
            }else if($complete_rate>=0){
                $where['complete_rate']=$complete_rate;
            }
            $data['limit']=$this->request->get('limit', 10, 'intval');
            $list=model('unit_user_list')
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total=$list->total();
            $unit_data=[];
            foreach ($list as $key => $val) {
                $unit_data[$key]=$val;
            }
            foreach ($unit_data as $key=>$v){
                $unit_id=model('unit_list')->where('id',$v['unit_list_id'])->value('unit_id');
                $unit_data[$key]['user_account']=model('student')->where('id',$v['user_id'])->value('account');
                $unit_data[$key]['unit_name']=model('unit')->where('id',$unit_id)->value('name');
            }
           return show($unit_data,0,'',['count' => $total]);
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