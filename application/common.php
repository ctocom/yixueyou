<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件

/**
 * 数据签名认证
 * @param array $data 被认证的数据
 * @return string       签名
 */
use app\admin\service\Wordmaker;
use think\exception\HttpResponseException;
if (!function_exists('sign')) {
    function sign($data)
    {
        // 数据类型检测
        if (!is_array($data)) {
            $data = (array)$data;
        }
        ksort($data); // 排序
        $code = http_build_query($data); // url编码并生成query字符串
        $sign = sha1($code); // 生成签名
        return $sign;
    }
}

/**打印输出
 * @param $arr
 * @author 原点 <467490186@qq.com>
 */
if (!function_exists('p')) {
    function p($arr)
    {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }
}

/**
 * 将返回的数据集转换成树
 * @param array $list 数据集
 * @param string $pk 主键
 * @param string $pid 父节点名称
 * @param string $child 子节点名称
 * @param integer $root 根节点ID
 * @return array          转换后的树
 */
if (!function_exists('list_to_tree')) {
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

/**
 * 获取当前登陆用户uid
 * @return mixed
 * @author 原点 <467490186@qq.com>
 */
if (!function_exists('get_user_id')) {
    function get_user_id()
    {
        return session('user_auth.uid');
    }
}
function http_curl($url, $data = [], $header = [], $ispost = true)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //判断是否加header
    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    //判断是否是POST请求
    if ($ispost) {
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $output = curl_exec($ch);
    curl_close($ch);
    //打印获得的数据
    return $output;
}

/**
 * @param string $msg 待提示的消息
 * @param string $url 跳转地址
 * @param int $time 弹出维持时间（单位秒）
 * @author 原点 <467490186@qq.com>
 */
function alert_error($msg = '', $url = null, $time = 3)
{
    if (is_null($url)) {
        $url = 'parent.location.reload();';
    } else {
        $url = 'parent.location.href=\'' . $url . '\'';
    }
    if (request()->isAjax()) {
        $str = [
            'code' => 0,
            'msg' => $msg,
            'url' => $url,
            'wait' => $time,
        ];
        $response = think\Response::create($str, 'json');
    } else {
        $str = '<script type="text/javascript" src="/layui/layui.js"></script>';
        $str .= '<script>
                    layui.use([\'layer\'],function(){
                       layer.msg("' . $msg . '",{icon:"5",time:' . ($time * 1000) . '},function() {
                         ' . $url . '
                       });
                    })
                </script>';
        $response = think\Response::create($str, 'html');
    }
    throw new HttpResponseException($response);
}

/**
 * json 数据输出
 * @param $data          data数据
 * @param int $code      code
 * @param string $msg    提示信息
 * @param array $param   额外参数
 * @param $httpCode      http状态码
 */
function show($data, $code = 1, $msg = '', $param = [], $httpCode = 200)
{
    $json = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];
    if ($param) {
        $json = array_merge($json, $param);
    }
    $response = json($json, $httpCode);
    throw new HttpResponseException($response);
}

/**
 * 导出Excel文件
 * @param $data 需要导出的数据
 * @param array $header 标题头
 * $header 示例（标题=>数据格式） array(
    'c1-text'=>'string',//text
    'c2-text'=>'@',//text
    'c3-integer'=>'integer',
    'c4-integer'=>'0',
    'c5-price'=>'price',
    'c6-price'=>'#,##0.00',//custom
    'c7-date'=>'date',
    'c8-date'=>'YYYY-MM-DD',
    );
 * @param string $filename 文件名
 */
function download_excel($data, $header = [], $filename = 'output.xlsx')
{
    return tools\Tools::download_excel($data, $header, $filename );
}
//生成token
function createToken($user_id)
{
    $secret='QOFMBNBNFYXZLJGHGHOPOKQWEQEE';
    $token=md5($user_id.date("Y-m-d").$secret);
    return $token;
}
//是否全是中文
function isAllChinese($str)
{
    //新疆等少数民族可能有·
    if (strpos($str, '·')) {
        //将·去掉，看看剩下的是不是都是中文
        $str = str_replace("·", '', $str);
        if (preg_match('/^[\x7f-\xff]+$/', $str)) {
            return true;//全是中文
        } else {
            return false;//不全是中文
        }
    } else {
        if (preg_match('/^[\x7f-\xff]+$/', $str)) {
            return true;//全是中文
        } else {
            return false;//不全是中文
        }
    }
}
//生成word 文件
function WordMake( $content,$absolutePath = "",$isEraseLink = true ){
    $mht = new Wordmaker();
    if ($isEraseLink){
        $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i' , '$1' , $content);   //去掉链接
    }
    $images = array();
    $files = array();
    $matches = array();
//这个算法要求src后的属性值必须使用引号括起来
    if ( preg_match_all('/<img[.\n]*?src\s*?=\s*?[\"\'](.*?)[\"\'](.*?)\/>/i',$content ,$matches ) ){
        $arrPath = $matches[1];
        for ( $i=0;$i<count($arrPath);$i++)
        {
            $path = $arrPath[$i];
            $imgPath = trim( $path );
            if ( $imgPath != "" )
            {
                $files[] = $imgPath;
                if( substr($imgPath,0,7) == 'http://')
                {
//绝对链接，不加前缀
                }
                else
                {
                    $imgPath = $absolutePath.$imgPath;
                }
                $images[] = $imgPath;
            }
        }
    }
    $mht->AddContents("tmp.html",$mht->GetMimeType("tmp.html"),$content);
    for ( $i=0;$i<count($images);$i++)
    {
        $image = $images[$i];
        if ( @fopen($image , 'r') )
        {
            $imgcontent = @file_get_contents( $image );
            if ( $content )
                $mht->AddContents($files[$i],$mht->GetMimeType($image),$imgcontent);
        }
        else
        {
            echo "file:".$image." not exist!<br />";
        }
    }
    return $mht->GetFile();
}
//学生试题生成
function question_random_data($num,$min){
    $model=model('question');
    $where1=[
        'type1'=>1,
        'delete_time'=>0,
    ];
    $where2=[
        'type1'=>2,
        'delete_time'=>0,
    ];
    $where3=[
        'type1'=>3,
        'delete_time'=>0,
    ];
    $fields="";
    $question_type1=$model->where($where1)->select()->shuffle()->toArray();
    $type1_count=count($question_type1);
    if(empty($question_type1) || $type1_count<$min){
        return false;
    }
    $question_type2=$model->where($where2)->select()->shuffle()->toArray();
    $type2_count=count($question_type2);
    if(empty($question_type2) || $type2_count<$min){
        return false;
    }
    $question_type3=$model->where($where3)->select()->shuffle()->toArray();
    $type3_count=count($question_type3);
    if(empty($question_type3) || $type3_count<$min){
        return false;
    }
    $list1 = $model->where($where1)->column('id');
    $rand_list1 = array_rand($list1,$num);//随机抽取3条'
    $type_array1 = array();
    foreach ((array)$rand_list1 as $key) {

        $type_array1[] = $list1[$key];

    }
    $question_type1 = $model->where('id','in',$type_array1)->select()->toArray();

    $list2 = $model->where($where2)->column('id');
    $rand_list2 = array_rand($list2,$num);//随机抽取3条'
    $type_array2 = array();
    foreach ((array)$rand_list2 as $key) {

        $type_array2[] = $list2[$key];

    }
    $question_type2 = $model->where('id','in',$type_array2)->select()->toArray();

    $list3 = $model->where($where3)->column('id');
    $rand_list3 = array_rand($list3,$num);//随机抽取3条'
    $type_array3 = array();
    foreach ((array)$rand_list3 as $key) {

        $type_array3[] = $list3[$key];

    }
    $question_type3 = $model->where('id','in',$type_array3)->select()->toArray();
    $all_question=array_merge($question_type1,$question_type2,$question_type3);
    shuffle($all_question);
    return $all_question;
}
//随机生后试卷信息
function paper_random_data($user_id,$unit_id,$unit_list_id,$type)
{
    $student_info=model('student')->where('id',$user_id)->find();
    $unit_info=model('unit')->where('id',$unit_id)->find();
    $p_name='的练习卷';
    if($type==2){
        $p_name='的错题本';
    }
    $paper_name=$student_info['name'].'的'.$unit_info['name'].$p_name;
    $data['section_id']=$unit_info['section_id'];
    $data['name']=$paper_name;
    $data['score']=100;
    $data['pass_score']=60;
    $data['pass_score']=60;
    $data['unit_id']=$unit_id;
    $data['user_id']=$user_id;
    $data['unit_list_id']=$unit_list_id;
    $data['create_time']=time();
    $data['type']=$type;
    $paper_insert=model('paper')->insertGetId($data);
    if($paper_insert){
        return $paper_insert;
    }else{
        return false;
    }
}
function array_diff_assoc2_deep($Array1, $Array2,$ArrayKey) {
    $RetAll = array();
    foreach ($Array1 as $Key => $Val) {
        $Status = 1;
        foreach ($Array2 as $K=>$V){
            $Status = 1;
            $Str1 = "";
            $Str2 = "";
            foreach ($ArrayKey as $C=>$Va){
                $Str1 .= $Val[$Va];
                $Str2 .= $V[$Va];
            }

            if ($Str1 === $Str2){
                $Status = 2;break;
            }
        }
        if ($Status == 1){
            $RetAll[] = $Val;
        }
    }

    return array_filter($RetAll);
}
function randomFileName()
{
//生成随机文件名
    //定义一个包含大小写字母数字的字符串
    $chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    //把字符串分割成数组
    $newchars=str_split($chars);
    //打乱数组
    shuffle($newchars);
    //从数组中随机取出15个字符
    $chars_key=array_rand($newchars,15);
    //把取出的字符重新组成字符串
    $fnstr='';
    for($i=0;$i<15;$i++){
        $fnstr.=$newchars[$chars_key[$i]];
    }
    $fnstr=$fnstr.time();
    //输出文件名并做MD5加密
    return md5($fnstr);
}
