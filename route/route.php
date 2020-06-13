<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//接口路由  需要登录
Route::group('index', [
    'logout'=>'index/Login/logout',
    'course'=>'index/StudentCourse/index',//课程分类数据
    'section'=>'index/StudentCourse/section', //章节分类数据
    'unit'=>'index/StudentCourse/unit', //知识点分类数据
    'unitList'=>'index/StudentCourse/unitList',
    'updatePassword'=>'index/Login/updatePassword',//修改密码
    'setSecondPassword'=>'index/Login/setSecondPassword',//设置二级密码
    'signIn'=>'index/SignIn/signIn',//用户签到
    'signRankList'=>'index/SignIn/signRankList',//用户签到
    'studyMaterialList'=>'index/StudyMaterial/studyMaterialList',//知识点学习资料
    'unitListInfo'=>'index/StudentCourse/unitListInfo',//知识点列表信息
    'completeMaterial'=>'index/StudyMaterial/completeMaterial',//完成学习内容
    'teachAction'=>'index/StudyMaterial/teachAction',//讲解某个知识点
]);

//免登录
Route::group('index', [
    'index'=>'index/index/index',                                //
    'login'=>'index/Login/index',
]);


/**
 * 后台管理路由
 */

/**
 * 免权限验证路由
 */
Route::group('admin', [
    'login$'=>'admin/Login/login',                                         //登录
    'editPassword'=>'admin/User/editPassword',                             //重置密码
    'logout$'=>'admin/Login/logout',                                       //退出
    'check$'=>'admin/User/check',                                          //验证用户是否存在
    'unlock'=>'admin/Login/unlock',                                        //验证用户是否存在
    'verify'=>'admin/Login/verify',                                        //获取验证码
])->ext('html');
/**
 * 需要权限验证路由
 */
Route::group('admin', [

    //首页
    'index$'=>'admin/Index/index',                                           //首页
    'home'=>'admin/Index/home',
    'SystemNewsInfo$'=>'admin/System/SystemNewsList',                      //系统信息
    'SystemNewsStatus$'=>'admin/System/SystemNewsStatus',                      //系统信息修改阅读状态
    'SystemNewsDelete$'=>'admin/System/SystemNewsDelete',                      //系统信息删除

    //用户管理
    'userList$'=>'admin/User/userList',                                      //用户列表
    'userInfo$'=>'admin/User/userInfo',                                      //用户信息
    'edit$'=>'admin/User/edit',                                              //添加/编辑用户
    'delete$'=>'admin/User/delete',                                          //删除用户
    'groupList$'=>'admin/User/groupList',                                    //用户组列表
    'editGroup$'=>'admin/User/editGroup',                                    //添加编辑用户组
    'disableGroup$'=>'admin/User/disableGroup',                              //禁用用户组
    'ruleList$'=>'admin/User/ruleList',                                      //用户组规则列表
    'editRule$'=>'admin/User/editRule',                                      //修改用户组规则

    //系统管理
    'cleanCache$'=>'admin/System/cleanCache',                                //清除缓存
    'log$'=>'admin/System/loginLog',                                         //登录日志
    'downlog$'=>'admin/System/downLoginLog',                                 //下载登录日志
    'menu$'=>'admin/System/menu',                                            //系统菜单
    'editMenu$'=>'admin/System/editMenu',                                    //编辑菜单
    'deleteMenu$'=>'admin/System/deleteMenu',                                //删除菜单
    'config'=>'admin/System/config',                                         //系统配置
    'siteConfig'=>'admin/System/siteConfig',                                 //站点配置
    'noticeConfig'=>'admin/System/noticeConfig',                             //公告配置
    //上传管理
    'upload'=>'admin/Upload/index',                                    //上传图片
    'dowload'=>'admin/Upload/dowload',                                    //上传图片

    'questionList$'=>'admin/Question/questionList',//试题列表
    'questionAdd$'=>'admin/Question/questionAdd',//试题添加
    'questionSection$'=>'admin/Question/questionSection',//章节下拉
    'questionUnit$'=>'admin/Question/questionUnit',//知识点下拉
    'questionDelete$'=>'admin/Question/questionDelete',//知识点下拉

    'courseList$'=>'admin/Course/courseList',//课目列表
    'courseDelete$'=>'admin/Course/courseDelete',//科目删除
    'courseAdd$'=>'admin/Course/courseAdd',//科目添加
    'courseUpload$'=>'admin/Course/courseUpload',//科目图标上传
    'courseEdit$'=>'admin/Course/courseEdit',//科目图标上传

    'sectionList$'=>'admin/Section/sectionList',//章节列表
    'sectionDelete$'=>'admin/Section/sectionDelete',//章节删除
    'sectionAdd$'=>'admin/Section/sectionAdd',//章节删除
    'sectionUpload$'=>'admin/Section/sectionUpload',//章节图标上传
    'sectionUpload$'=>'admin/Section/sectionUpload',//章节图标上传
    'sectionEdit$'=>'admin/Section/sectionEdit',//章节修改

    'unitList$'=>'admin/Unit/unitList',//知识点列表
    'unitDelete$'=>'admin/Unit/unitDelete',//知识点删除

    'unitTask$'=>'admin/UnitList/unitList',//知识点列表

    'studentList$'=>'admin/Student/studentList',//学生列表
    'studentDelete$'=>'admin/Student/studentDelete',//章节删除

    'paperList$'=>'admin/Paper/paperList',//学生列表
    'paperDelete$'=>'admin/Paper/paperDelete',//学生删除

    'videoList$'=>'admin/StudyMaterial/videoList',//视频列表
    'videoUpload$'=>'admin/StudyMaterial/videoUpload',//视频上传
    'bannerUpload$'=>'admin/StudyMaterial/bannerUpload',//视频封面图上传
    'videoAdd$'=>'admin/StudyMaterial/videoAdd',//视频添加

    'soundList'=>'admin/StudyMaterial/soundList',//录音列表
    'soundAdd'=>'admin/StudyMaterial/soundAdd',//录音添加
    'soundUpload'=>'admin/StudyMaterial/soundUpload',//录音上传
])->middleware(app\admin\middleware\CheckAuth::class)->ext('html');
//->middleware(app\admin\middleware\CheckAuth::class)
/**
 * miss路由
 * 没有定义的路由全部使用该路由
 */
Route::miss('Index/Index/index');
return [
];
