<?php
/**
 * Created by PhpStorm.
 * User: 岳路川
 * Date: 2020/6/13
 * Time: 9:44
 */
namespace app\index\middleware;
class CheckLogin{
    public function handle($request, \Closure $next)
    {
        $data=json_decode(file_get_contents(('php://input'),true),true);
        $token=$data['user_token'];
        $user_token=!empty($token)?$token:show([],0,'user_token必传');
        $user_info=model('student')->where('token',$user_token)->find();
        if($user_info['token']!=$user_token){
            show([],0,'user_token是无效的');
        }
        if($user_info['expire_time']<time()){
            show([],300,'登录信息已失效');
        }
        return $next($request);
    }
}