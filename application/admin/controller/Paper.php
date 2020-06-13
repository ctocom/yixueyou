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
            $list = papers::where('name','like',"%".$data['key']."%")
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