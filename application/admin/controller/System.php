<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/3/28
 * Time: 13:20
 */

namespace app\admin\controller;

use app\admin\model\AuthRule;
use app\admin\model\Config;
use app\admin\model\LoginLog;
use app\admin\model\SystemNews;
use think\facade\App;
use think\facade\Cache;
use app\admin\controller\Word;
use think\Db;
class System extends Common
{
    /**
     * 清除缓存
     * @author 原点 <467490186@qq.com>
     */
    public function cleanCache()
    {

        if (!$this->request->isPost()) {
            return $this->fetch();
        } else {
            $data = input();
            if (isset($data['path'])) {
                $file = App::getRuntimePath();
                foreach ($data['path'] as $key => $value) {
                    array_map('unlink', glob($file . $value . '/*.*'));
                    $dirs = (array)glob($file . $value . '/*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*'));
                    }
                    if ($dirs && $data['delete']) {
                        array_map('rmdir', $dirs);
                    }
                }
                $this->success('缓存清空成功');
            } else {
                $this->error('请选择清除的范围');
            }
        }
    }

    /**
     * 登录日志
     * @return mixed
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function loginLog()
    {
        if ($this->request->isAjax()) {
            $data = [
                'starttime' => $this->request->get('starttime', '', 'trim'),
                'endtime' => $this->request->get('endtime', '', 'trim'),
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval')
            ];
            $list = LoginLog::withSearch(['name', 'create_time'], [
                'name' => $data['key'],
                'create_time' => [$data['starttime'], $data['endtime']],
            ])->paginate($data['limit'], false, ['query' => $data]);
            return show($list->items(),0,'',['count' => $list->total()]);
        }
        return $this->fetch();
    }

    /**
     * 下载登录日志（Excel）
     */
    public function downLoginLog()
    {
        $data = [
            'starttime' => $this->request->get('starttime', '', 'trim'),
            'endtime' => $this->request->get('endtime', '', 'trim'),
            'key' => $this->request->get('key', '', 'trim'),
        ];
        $list = LoginLog::withSearch(['name', 'create_time'], [
            'name' => $data['key'],
            'create_time' => [$data['starttime'], $data['endtime']],
        ])->hidden(['id'])->select();
        $header = [
            'UID'=>'integer',
            '账号'=>'string',
            '昵称'=>'string',
            '最后登录IP'=>'string',
            '登陆时间'=>'string'
        ];
        return download_excel($list->toArray(), $header , 'login_log.xlsx');
    }

    /**
     *系统菜单
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menu()
    {
        if ($this->request->isPost()) {
            $list = AuthRule::order('sort desc')->select();
            return show($list,0,'获取成功');
        }
        return $this->fetch();
    }

    /**
     * 菜单编辑
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMenu()
    {
        if ($this->request->isPost()) {
            $data = [
                'name' => $this->request->post('name', '', 'trim'),
                'title' => $this->request->post('title', '', 'trim'),
                'pid' => $this->request->post('pid', 0, 'intval'),
                'status' => $this->request->post('status', 0, 'intval'),
                'menu' => $this->request->post('menu', '', 'trim'),
                'icon' => $this->request->post('icon', '', 'trim'),
                'sort' => $this->request->post('sort', 0, 'intval'),
            ];
            $id = $this->request->post('id', 0, 'intval');
            if ($id) { //编辑
                $res = AuthRule::where('id', '=', $id)->update($data);
            } else { //添加
                $res = AuthRule::create($data);
            }
            if ($res) {
                Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
                $this->success('保存成功', url('/admin/menu'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');
            if ($id) {
                $data = AuthRule::where('id', '=', $id)->find();
                $this->assign('data', $data);
            }
            $menu = AuthRule::where('pid', '=', 0)->order('sort desc')->column('id,title');
            $menu[0] = '顶级菜单';
            ksort($menu);
            $this->assign('menu', $menu);
            return $this->fetch();
        }
    }

    /**
     * 删除菜单
     * @author 原点 <467490186@qq.com>
     * @throws \Exception
     */
    public function deleteMenu()
    {
        $id = $this->request->post('id', 0, 'intval');
        empty($id) && $this->error('参数错误');
        if (AuthRule::where('pid', '=', $id)->count() > 0) {
            $this->error('该菜单存在子菜单,无法删除!');
        }
        $res = AuthRule::where('id', '=', $id)->delete();
        if ($res) {
            Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
            $this->success('删除成功', url('/admin/menu'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 配置管理
     * @return mixed|void
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config()
    {
        if (!$this->request->isPost()) {
            $data = Config::where('name', 'system_config')->find();
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $save = [
                'value' => [
                    'debug' => $this->request->post('debug', 0, 'intval'),
                    'trace' => $this->request->post('trace', 0, 'intval'),
                    'trace_type' => $this->request->post('trace_type', 0, 'intval'),
                ],
                'status' => $this->request->post('status', 0, 'intval')
            ];
            $res = Config::update($save, ['name' => 'system_config']);
            if ($res) {
                cache('config', null);
                $this->success('修改成功', url('/admin/config'));
            } else {
                $this->error('修改失败');
            }
        }

    }

    /**
     * 站点配置
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function siteConfig()
    {
        if (!$this->request->isPost()) {
            $data = Config::where('name', 'site_config')->find();
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $save = [
                'value' => [
                    'title' => $this->request->post('title', '', 'trim'),
                    'name' => $this->request->post('name', '', 'trim'),
                    'copyright' => $this->request->post('copyright', '', 'trim'),
                    'icp' => $this->request->post('icp', '', 'trim')
                ],
            ];
            $res = Config::update($save, ['name' => 'site_config']);
            if ($res) {
                cache('site_config', null);
                $this->success('修改成功', url('/admin/siteConfig'));
            } else {
                $this->error('修改失败');
            }
        }
    }

    /**
     * 公告配置
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function noticeConfig()
    {
        if (!$this->request->isPost()) {
            $data = Config::where('name', 'notice_config')->find();
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $query = Config::where('name', 'notice_config')->find();
            if(!$query){
                $query = new Config();
                $query->name = 'notice_config';
                $query->title = '公告配置';
                $query->status = 1;
            }
            $query->value = [
                'notice' => $this->request->post('notice',''),
                'status' => $this->request->post('status', 0, 'intval')
            ];
            $res = $query->save();
            if ($res) {
                cache('notice_config', null);
                $this->success('修改成功', url('/admin/noticeConfig'));
            } else {
                $this->error('修改失败');
            }
        }
    }

    /**
     * 积分配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function scoreConfig()
    {
        if($this->request->isPost()){
            $save = [
                'value' => [
                    'study_score' => $this->request->post('study_score', 0, 'intval'),
                    'homework_score' => $this->request->post('homework_score', 0, 'intval'),
                    'check_score' => $this->request->post('check_score', 0, 'intval'),
                    'complete_score' => $this->request->post('complete_score', 0, 'intval'),
                    'error_notice_score' => $this->request->post('error_notice_score', 0, 'intval'),
                    'rank_score' => $this->request->post('rank_score', 0, 'intval'),
                    'good_score' => $this->request->post('good_score', 0, 'intval'),
                ],
            ];
            $res = Config::update($save, ['name' => 'score_config']);
            if ($res) {
                cache('score_config', null);
                show([],'200','修改成功');
            } else {
                show([],'0','修改失败');
            }
        }else{
            $data = Config::where('name', 'score_config')->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }
    /**
     * 系统消息
     */
    public function SystemNewsList(){
        if($this->request->isAjax()){
            $where=[];
            $data = [
                'user' =>$this->request->get('user','','trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $teacher=session('user_auth');
            $where['to_user']=$teacher['user'];
            $where['delete_time']=0;
            $list = SystemNews::where('from_user','like',"%".$data['user']."%")
                ->where($where)
                ->order('is_read','asc')
                ->order('id','desc')
                ->paginate($data['limit'], false, ['query' => $data]);
            $total=$list->total();
            $system_data=[];
            foreach ($list as $key => $val) {
                $system_data[$key] = $val;
            }
            foreach ($system_data as $v){
                if($v['is_read']==0){
                    $v['is_read']='未读';
                }else{
                    $v['is_read']='已读';
                }
            }
            return show($system_data, 0, '', ['count' => $total]);
        }else{
            return $this->fetch();
        }
    }
    //查看消息
    public function systemNewsStatus(){
        $id=$this->request->post('id',0,'intval');
        $is_read=SystemNews::where('id',$id)->value('is_read');
        if($is_read){
            show([],0,'您已查看过');
        }
        $res=SystemNews::where('id',$id)->update(['is_read'=>1,'read_time'=>time()]);
        //老师已读  给学生发一条消息
        $system_news=SystemNews::where('id',$id)->find();
        $teacher_info=model('user')->where('uid',$system_news['to_user_id'])->find();
        $student_info=model('student')->where('openid',$system_news['from_user_id'])->find();
        if($system_news['type']==1){
            //讲解消息
            $data['content']='你好！我是'.$teacher_info['name'].','.$system_news['unit_name'].'这条消息我已查看，稍后我会微信联系你';
            $data['title']=$student_info['name'].'的'.$system_news['unit_name'].'的难点';
        }else if($system_news['type']==2){
            //学习进度消息
            $data['content']='你好！我是'.$teacher_info['name'].','.$system_news['unit_name'].'这条消息我已查看，稍后我会审核';
            $data['title']=$student_info['name'].'的'.$system_news['unit_name'].'的学习进度';
        }else{
            //完成作业消息
            $data['content']='你好！我是'.$teacher_info['name'].','.$system_news['unit_name'].'这条消息我已查看，稍后我会审核';
            $data['title']=$student_info['name'].'的'.$system_news['unit_name'].'的作业情况';
        }
        $data['from_user']=$system_news['to_user'];
        //查询到学生的所属老师id
        $data['to_user']=$system_news['from_user'];
        $data['to_user_id']=$system_news['from_user_id'];
        $data['from_user_id']=$system_news['to_user_id'];
        $data['is_read']=0;
        $data['send_time']=time();
        $data['type']=$system_news['type'];
        $data['unit_id']=$system_news['unit_id'];
        $data['unit_name']=$system_news['unit_name'];
        $data['status']=0;
        $news_res=model('systemNews')->insert($data);
        if($news_res && $res){
            show([],200,'ok');
        }else{
            show([],0,'ok');
        }
    }
    public function systemNewsDelete(){
        $id=$this->request->post('id','0','intval');
        $res=SystemNews::where('id',$id)->update(['delete_time'=>time()]);
        if($res){
            show([],200,'删除成功');
        }else{
            show([],0,'删除失败');
        }
    }
    //审核
    public function systemNewsAudit()
    {
        $status=$this->request->post('status',0,'intval');
        $id=$this->request->post('id',0,'intval');
        $where=[
            'id'=>$id,
        ];
        $is_read=Db::table('think_system_news')->where($where)->value('is_read');
        if($is_read==0){
            show([],0,'请查看后再审核');
        }
        Db::startTrans();
        $system_news=Db::table('think_system_news')->where('id',$id)->find();
        $status_data=Db::table('think_system_news')->where($where)->value('status');
        $type=Db::table('think_system_news')->where($where)->value('type');
        if($status_data==$status && $status==1){
            show([],0,'已通过状态不能通过');
        }else if($status_data==$status && $status==2){
            show([],0,'已驳回状态不能驳回');
        }
        if($status==2 && $status_data==1){
            show([],0,'已通过状态不能再次驳回');
        }
        $res=Db::table('think_system_news')->where($where)->update(['status'=>$status]);
        $data['from_user']=$system_news['to_user'];
        //查询到学生的所属老师id
        $data['to_user']=$system_news['from_user'];
        $data['is_read']=0;
        $data['send_time']=time();
        $data['type']=$type;
        $data['to_user_id']=$system_news['from_user_id'];
        $data['from_user_id']=$system_news['to_user_id'];
        $data['unit_id']=$system_news['unit_id'];
        $data['unit_name']=$system_news['unit_name'];
        $data['status']=0;
        $user_id=Db::table('think_student')->where('openid',$system_news['from_user_id'])->value('id');
        $msg='的学习进度';
        $complete_rat=25;
        $type=1;
        if($type==3){
            $complete_rat=50;
            $msg='的作业情况';
            $type=2;
        }
        if($res && $status==1){
            try{
            //审核通过 给学生发送一条信息
            $data['content']='你好！我是'.$system_news['to_user'].',“'.$system_news['unit_name'].'”已审核成功';
            $data['title']=$system_news['from_user'].'的'.$system_news['unit_name'].$msg;
            $news_res=Db::table('think_system_news')->insert($data);
            //审核通过需要改变进度
             $unit_list_id=Db::table('think_unit_list')
                 ->where(['unit_id'=>$system_news['unit_id'],'type'=>1])
                 ->value('id');
            if($type==1){
                $unit_list_status=Db::table('think_unit_user_list')
                    ->insert(['unit_list_id'=>$unit_list_id,'user_id'=>$user_id,'type'=>$type,'complete_rate'=>$complete_rat]);
            }else{
                $unit_list_status=Db::table('think_unit_user_list')
                    ->where(['user_id'=>$user_id,'unit_list_id'=>$unit_list_id])
                    ->update(['complete_rate'=>$complete_rat]);
            }
            $unit_list_module_id=Db::table('think_unit_list_module')
                ->where(['unit_list_id'=>$unit_list_id,'type'=>$type])->value(['id']);
            //通过队列模块id添加一条用户的模块进度
            $unit_user_list_module=Db::table('think_user_unit_list_module')
                ->insert(['user_id'=>$user_id,'unit_list_module_id'=>$unit_list_module_id,'is_complete'=>1]);
            if($news_res && $unit_list_id  && $unit_list_status && $unit_list_module_id && $unit_user_list_module){
//                $paper_action=$this->paperWord($paper_id,$system_news['from_user_id']);
                //完成学习或者作业后加积分
                $score_config=json_decode(model('config')->where('name','score_config')->value('value'),true);
                if($type==1){
                    $check_score_res=model('student')->where('openid',$system_news['from_user_id'])->setInc('score',intval(bcmul($score_config['study_score'],100)));
                }else{
                    $check_score_res=model('student')->where('openid',$system_news['from_user_id'])->setInc('score',intval(bcmul($score_config['homework_score'],100)));
                }
                if($check_score_res){
                    Db::commit();
                    show([],200,'审核通过');
                }else{
                    show([],0,'审核失败');
                }
            }else{
                Db::rollback();
                show([],0,'审核失败');
            }
            } catch (\think\Exception $e){
                Db::rollback();
                show([],$e->getCode(),$e->getMessage());
            }
        }elseif ($res && $status==2){
            //驳回  给学生发一条消息
            $data['content']='你好！我是'.$system_news['to_user'].',“'.$system_news['unit_name'].'”已驳回，请完成后再提交';
            $data['title']=$system_news['from_user'].'的'.$system_news['unit_name'].$msg;
            $news_res=Db::table('think_system_news')->insert($data);
            if($news_res){
                show([],200,'驳回成功');
            }
        }else{
            show([],0,'审核失败');
        }
    }
}