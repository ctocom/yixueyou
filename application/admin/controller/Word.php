<?php
/**
 *
 * User: Edwin
 * Date: 2019/5/31
 * Email: <467490186@qq.com>
 */

namespace app\admin\controller;

use think\Controller;

class Word extends Controller
{
    public function paperWord()
    {
        //从数据库查这个学生试卷的所有题
        $data=[];
        $paper_id=$this->request->post('paper_id');
        $user_id=$this->request->post('user_id');
        $where=[
            'paper_id'=>1,
            'user_id'=>1
        ];
        $data=model('paper_question')->where($where)->select();
        $this->assign('data',$data);//把获取的数据传递的模板，替换模板里面的变量
        $content = $this->fetch('word');//获取模板内容信息word是模板的名称
        $fileContent = WordMake($content);//生成word内容
        $url='uploads/paper/'.randomFileName().".doc";
//        $name = iconv("utf-8", "GBK",$data[0]['name']);//转换好生成的word文件名编码
        $fp = fopen($url, 'w');//打开生成的文档
        //将试卷路径保存到试卷表
        $res=model('paper')->where('id',1)->update(['paper_url'=>$url]);
        fwrite($fp, $fileContent);//写入包保存文件
        fclose($fp);
        if($res){
            echo "生成成功";
            echo "tp5.abc.com/".$url;
        }else{
            echo "生成失败";
        }
    }
    public function dayin()
    {
        return $this->fetch('dayin');
    }
}