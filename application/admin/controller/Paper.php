<?php
namespace app\admin\controller;
class Paper extends Common
{
    public function paperList()
    {
        if($this->request->isAjax()){
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = model('paper')::where('name','like',"%".$data['key']."%")
                ->where('delete_time',0)
                ->paginate($data['limit'], false, ['query' => $data]);
            $total_list=$list->total();
            $paper_data = [];
            foreach ($list as $key => $val) {
                $paper_data[$key] = $val;
            }
            return show($paper_data, 0, '', ['count' => $total_list]);
        }else{

            return $this->fetch();
        }
    }
    public function paperAdd(){
        if($this->request->isAjax()){
            $paper_name=$this->request->post('paper_name','','trim');
            $unit_id=$this->request->post('unit_id','','intval');
            if(empty($paper_name)){
                show([],0,'试卷名称必填');
            }
            $paper_score=$this->request->post('paper_score','');
            if(empty($paper_score)){
                show([],0,'试卷分数必填');
            }
            if(!is_numeric($paper_score)){
                show([],0,'试卷总分必须是数字');
            }
            $pass_score=$this->request->post('pass_score','');
            if(empty($pass_score)){
                show([],0,'试卷及格分必填');
            }
            if(!is_numeric($pass_score)){
                show([],0,'试卷及格分必须是数字');
            }
            $data['name']=$paper_name;
            $data['score']=$paper_score;
            $data['pass_score']=$pass_score;
            $data['create_time']=time();
            $data['unit_id']=$unit_id;
            $res=model('paper')->insert($data);
            if($res){
                show([],200,'添加成功');
            }else{
                show([],0,'添加失败');
            }
        }else{
            $where=[];
            $where['is_show']=1;
            $where['delete_time']=0;
            $unit_data=model('unit')->where($where)->select();
            $this->assign('unit_data',$unit_data);
            return $this->fetch();
        }
    }
    //试卷编辑
    public function paperEdit(){
        $id=$this->request->param('uid');
        if($this->request->isAjax()){
            $paper_name=$this->request->post('paper_name','','trim');
            $unit_id=$this->request->post('unit_id','','intval');
            if(empty($paper_name)){
                show([],0,'试卷名称必填');
            }
            $paper_score=$this->request->post('paper_score','');
            if(empty($paper_score)){
                show([],0,'试卷分数必填');
            }
            if(!is_numeric($paper_score)){
                show([],0,'试卷总分必须是数字');
            }
            $pass_score=$this->request->post('pass_score','');
            if(empty($pass_score)){
                show([],0,'试卷及格分必填');
            }
            if(!is_numeric($pass_score)){
                show([],0,'试卷及格分必须是数字');
            }
            $data['name']=$paper_name;
            $data['score']=$paper_score;
            $data['pass_score']=$pass_score;
            $data['update_time']=time();
            $data['unit_id']=$unit_id;
            $res=model('paper')->where('id',$id)->update($data);
            if($res){
                show([],200,'修改成功');
            }else{
                show([],0,'修改失败');
            }
        }else{
            $where=[];
            $where['is_show']=1;
            $where['delete_time']=0;
            $unit_data=model('unit')->where($where)->select();
            $paper_data=model('paper')->where('id',$id)->find();
            $this->assign('unit_data',$unit_data);
            $this->assign('paper_data',$paper_data);
            return $this->fetch();
        }
    }
    //试卷删除
    public function paperDelete()
    {
        $id=intval($this->request->post('id'));
        $res=model('paper')->where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }

    }
}