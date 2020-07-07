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
    'updatePassword'=>'index/Login/updatePassword',//修改密码
    'setSecondPassword'=>'index/Login/setSecondPassword',//设置二级密码
    'signIn'=>'index/SignIn/signIn',//用户签到
    'signRankList'=>'index/SignIn/signRankList',//用户签到
    'completeMaterial'=>'index/StudyMaterial/completeMaterial',//完成学习内容
    'completeHomework'=>'index/StudyMaterial/completeHomework',//完成作业
    'teachAction'=>'index/StudyMaterial/teachAction',//讲解某个知识点
    'systemList'=>'index/System/SystemList',//系统消息对话列表
    'systemInfo'=>'index/System/systemInfo',//系统消息对话记录
    'recordErrorQuestion'=>'index/Question/recordErrorQuestion',//录入错题
    'paperQuestion'=>'index/Question/paperQuestion',//试卷内的试题
    'userErrorquestionList'=>'index/Question/userErrorquestionList',//用户错题试卷列表
    'userPaperAction'=>'index/Question/userPaperAction',//用户试卷生成
    'errCount'=>'index/Question/errCount',//错题数
    'userErr'=>'index/Question/userErr',//错题本
    'errorClear'=>'index/Question/errorClear',//错题清零
    'statisticsStudent'=>'index/Question/statisticsStudent',//统计
    'unitListBefore'=>'index/StudentCourse/unitListBefore',//未完成的知识点列表
    'paperQuestionList'=>'index/Question/paperQuestionList',//试卷试题列表
    'userErrorNotice'=>'index/Question/userErrorNotice',//错题打印
    'signList'=>'index/SignIn/signList',//积分详情
])->middleware(app\index\middleware\CheckLogin::class);
//->middleware(app\index\middleware\CheckLogin::class)
//免登录
Route::group('index', [
    'studyMaterialList'=>'index/StudyMaterial/studyMaterialList',//知识点学习资料
    'unitList'=>'index/StudentCourse/unitList', //知识点循环任务
    'logout'=>'index/Login/logout',
    'index'=>'index/index/index',                                //
    'imagesInfo'=>'index/index/imagesInfo',                                //
    'login'=>'index/Login/index',
    'course'=>'index/StudentCourse/index',//课程分类数据
    'section'=>'index/StudentCourse/section', //章节分类数据
    'unit'=>'index/StudentCourse/unit', //知识点分类数据
    'unitListInfo'=>'index/StudentCourse/unitListInfo',//知识点列表信息
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
    'SystemNewsInfo$'=>'admin/System/SystemNewsList',                      //系统信息
    'SystemNewsStatus$'=>'admin/System/SystemNewsStatus',                      //系统信息修改阅读状态
    'SystemNewsDelete$'=>'admin/System/SystemNewsDelete',                      //系统信息删除
    'systemNewsAudit'=>'admin/System/systemNewsAudit',                      //学习状态审核
])->ext('html');
/**
 * 需要权限验证路由
 */
Route::group('admin', [

    //首页
    'index$'=>'admin/Index/index',                                           //首页
    'home'=>'admin/Index/home',
    'statistics'=>'admin/Index/statistics',


    'paperWord'=>'admin/Word/paperWord',                      //生成试卷
    'dayin'=>'admin/Word/dayin',                      //生成试卷

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
    'scoreConfig'=>'admin/System/scoreConfig',                                 //积分配置
    'noticeConfig'=>'admin/System/noticeConfig',                             //公告配置
    //上传管理
    'upload'=>'admin/Upload/index',                                    //上传图片
    'dowload'=>'admin/Upload/dowload',                                    //上传图片
    'bannerUpload'=>'admin/Upload/bannerUpload',                                    //上传图片
    'bannerAdd'=>'admin/Upload/bannerAdd',                                    //上传图片
    'repeatAdd'=>'admin/Upload/repeatAdd',                                    //上传图片
    'cateAdd'=>'admin/Upload/cateAdd',                                    //上传图片
    'listAdd'=>'admin/Upload/listAdd',                                    //上传图片
    'fourAdd'=>'admin/Upload/fourAdd',                                    //上传图片
    'repeatUpload'=>'admin/Upload/repeatUpload',                                    //上传图片
    'cateUpload'=>'admin/Upload/cateUpload',                                    //上传图片
    'listUpload'=>'admin/Upload/listUpload',                                    //上传图片
    'fourUpload'=>'admin/Upload/fourUpload',                                    //上传图片
    'imagesList'=>'admin/Upload/imagesList',                                    //上传图片
    'imagesDelete'=>'admin/Upload/imagesDelete',                                    //上传图片
    'imagesEdit'=>'admin/Upload/imagesEdit',

    'questionList$'=>'admin/Question/questionList',//试题列表
    'questionAdd$'=>'admin/Question/questionAdd',//试题添加
    'questionSection$'=>'admin/Question/questionSection',//章节下拉
    'questionUnit$'=>'admin/Question/questionUnit',//试题知识点下拉
    'questionDelete$'=>'admin/Question/questionDelete',//试题删除
    'questionEdit$'=>'admin/Question/questionEdit',//试题编辑
    'questionPictureUpload'=>'admin/Question/questionPictureUpload',//试题图片上传
    'questionTeachUpload$'=>'admin/Question/questionTeachUpload',//试题讲解资料上传

    'courseList$'=>'admin/Course/courseList',//课目列表
    'courseDelete$'=>'admin/Course/courseDelete',//科目删除
    'courseAdd$'=>'admin/Course/courseAdd',//科目添加
    'courseUpload$'=>'admin/Course/courseUpload',//科目图标上传
    'courseEdit$'=>'admin/Course/courseEdit',//科目图标上传

    'sectionList$'=>'admin/Section/sectionList',//章节列表
    'sectionDelete$'=>'admin/Section/sectionDelete',//章节删除
    'sectionAdd$'=>'admin/Section/sectionAdd',//章节添加
    'sectionUpload$'=>'admin/Section/sectionUpload',//章节图标上传
    'sectionEdit$'=>'admin/Section/sectionEdit',//章节修改

    'unitList$'=>'admin/Unit/unitList',//知识点列表
    'unitDelete$'=>'admin/Unit/unitDelete',//知识点删除
    'unitAdd$'=>'admin/Unit/unitAdd',//知识点添加
    'unitSection$'=>'admin/Unit/unitSection',//知识点添加
    'unitUpload$'=>'admin/Unit/unitUpload',//知识点图标上传
    'unitEdit$'=>'admin/Unit/unitEdit',//知识点修改

    'unitTask$'=>'admin/UnitList/unitList',//知识点列表

    'studentList$'=>'admin/Student/studentList',//学生列表
    'studentDelete$'=>'admin/Student/studentDelete',//学生删除
    'studentAdd$'=>'admin/Student/studentAdd',//学生添加
    'headUpload$'=>'admin/Student/headUpload',//学生头像上传
    'studentEdit$'=>'admin/Student/studentEdit',//学生修改

    'paperList$'=>'admin/Paper/paperList',//试卷列表
//    'paperDelete$'=>'admin/Paper/paperDelete',//试卷删除
//    'paperAdd$'=>'admin/Paper/paperAdd',//试卷添加
//    'paperEdit$'=>'admin/Paper/paperEdit',//试卷修改

    'videoList$'=>'admin/StudyMaterial/videoList',//视频列表
    'videoUpload$'=>'admin/StudyMaterial/videoUpload',//视频上传
    'bannerUpload$'=>'admin/StudyMaterial/bannerUpload',//视频封面图上传
    'videoAdd$'=>'admin/StudyMaterial/videoAdd',//视频添加
    'videoDelete$'=>'admin/StudyMaterial/videoDelete',//视频删除
    'videoEdit'=>'admin/StudyMaterial/videoEdit',//视频编辑

    'soundList'=>'admin/StudyMaterial/soundList',//录音列表
    'soundAdd'=>'admin/StudyMaterial/soundAdd',//录音添加
    'soundUpload'=>'admin/StudyMaterial/soundUpload',//录音上传
    'soundDelete'=>'admin/StudyMaterial/soundDelete',//录音删除
    'soundEdit'=>'admin/StudyMaterial/soundEdit',//录音编辑

    'noticeList'=>'admin/StudyMaterial/noticeList',//笔记列表
    'noticeAdd'=>'admin/StudyMaterial/noticeAdd',//笔记添加
    'noticeUpload'=>'admin/StudyMaterial/noticeUpload',//笔记上传
    'noticeDelete'=>'admin/StudyMaterial/noticeDelete',//笔记删除
    'noticeEdit'=>'admin/StudyMaterial/noticeEdit',//笔记编辑

    'pptList'=>'admin/StudyMaterial/pptList',//ppt列表
    'pptAdd'=>'admin/StudyMaterial/pptAdd',//ppt添加
    'pptUpload'=>'admin/StudyMaterial/pptUpload',//ppt上传
    'pptDelete'=>'admin/StudyMaterial/pptDelete',//ppt删除
    'pptEdit'=>'admin/StudyMaterial/pptEdit',//ppt编辑
])->middleware(app\admin\middleware\CheckAuth::class)->ext('html');
//->middleware(app\admin\middleware\CheckAuth::class)
/**
 * miss路由
 * 没有定义的路由全部使用该路由
 */
Route::miss('Index/Index/index');
return [
];
