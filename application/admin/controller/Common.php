<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2017/5/9
 * Time: 13:54
 */

namespace app\admin\controller;

use app\admin\model\Config;
use think\Controller;
use think\Db;
class Common extends Controller
{
    public $uid;             //用户id
    public $group_id;       //用户组

    /**
     * 后台控制器初始化
     */
    protected function initialize()
    {
        $this->uid = $this->request->LoginUid;
        $this->group_id = $this->request->LoginGroupId;
        $this->config();
        $site_config = $this->siteConfig();
        $this->assign('site_config', $site_config);
    }

    /**
     * 动态配置
     * @author 原点 <467490186@qq.com>
     */
    private function config()
    {
        if (cache('config')) {
            $list = cache('config');
        } else {
            $list = Config::where('name', '=', 'system_config')->field('value,status')->find();
            cache('config', $list);
        }
        if ($list['status'] == 1) {
            config('app_debug', $list['value']['debug']);
            config('app_trace', $list['value']['trace']);
            config('trace.type', $list['value']['trace_type'] == 0 ? 'Html' : 'Console');
        }
    }

    /**
     * 站点配置信息
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function siteConfig()
    {
        $site_config = cache('site_config');
        if ($site_config) {
            return $site_config;
        }
        $list = Config::where('name', '=', 'site_config')->field('value')->find();
        cache('site_config', $list);
        return $list;
    }
    public function changeMultiple($num)
    {
       return bcdiv($num,100);
    }
    public function paperWord($paper_id,$user_id)
    {
        //从数据库查这个学生试卷的所有题
        $data=[];
        $where=[
            'paper_id'=>$paper_id,
            'user_id'=>$user_id
        ];
        $data=model('paper_question')->where($where)->select();
        $this->assign('data',$data);//把获取的数据传递的模板，替换模板里面的变量
        $content = $this->fetch('word/word');//获取模板内容信息word是模板的名称
        $fileContent = WordMake($content);//生成word内容
        $url='uploads/paper/'.randomFileName().".doc";
//        $name = iconv("utf-8", "GBK",$data[0]['name']);//转换好生成的word文件名编码
        $fp = fopen($url, 'w');//打开生成的文档
        //将试卷路径保存到试卷表
        $res=model('paper')->where('id',$paper_id)->update(['paper_url'=>$url]);
        fwrite($fp, $fileContent);//写入包保存文件
        fclose($fp);
        if($res && $data){
           return true;
        }else{
            return false;
        }
    }
}