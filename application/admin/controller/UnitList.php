<?php
namespace app\admin\controller;

class UnitList extends Common
{
    public function unitList()
    {
        if($this->request->isAjax())
        {
            $user_account=$this->request->get('user_account','','trim');
            $data=[];
            $where=[];
            if($user_account){
                if(isAllChinese($user_account)){
                    $user_id=model('student')->where('name',$user_account)->value('id');
                    $where=[
                        'user_id'=>$user_id
                    ];
                }else{
                    $user_id=model('student')->where('account',$user_account)->value('id');
                    $where=[
                        'user_id'=>$user_id
                    ];
                }
            }
            $data['limit']=$this->request->get('limit', 10, 'intval');
            $list=model('user_unit')
                ->where($where)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total=$list->total();
            $unit_data=[];
            foreach ($list as $key => $val) {
                $unit_data[$key]=$val;
            }
            foreach ($unit_data as $key=>$v){
                $unit_data[$key]['user_account']=model('student')->where('id',$v['user_id'])->value('account');
                $unit_data[$key]['name']=model('student')->where('id',$v['user_id'])->value('name');
                $unit_data[$key]['unit_name']=model('unit')->where('id',$v['unit_id'])->value('name');
                if($v['complete_num']==1){
                    $unit_data[$key]['complete_num']=33;
                    $unit_data[$key]['type']='1';
                }else if($v['complete_num']==2){
                    $unit_data[$key]['complete_num']=66;
                    $unit_data[$key]['type']='2';
                }else{
                    $unit_data[$key]['complete_num']=100;
                    $unit_data[$key]['type']='3';
                }
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