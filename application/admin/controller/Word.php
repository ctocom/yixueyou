<?php
/**
 *
 * User: Edwin
 * Date: 2019/5/31
 * Email: <467490186@qq.com>
 */

namespace app\admin\controller;

class Word extends Common
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
        $name = iconv("utf-8", "GBK",$data[0]['name']);//转换好生成的word文件名编码
        $fp = fopen('uploads/paper/'.$name.'['.$data[0]['id']."].doc", 'w');//打开生成的文档
        fwrite($fp, $fileContent);//写入包保存文件
        fclose($fp);
        echo $fp;
    }
}