<?phpnamespace app\index\controller;use think\facade\Config;use think\Controller;class StudyMaterial extends Controller{    public function studyMaterialList(){        $unit_id=$this->request->post('unit_id',0,'intval');        //type =1 视频   type=3 录音  type =4 ppt   type=5 课堂笔记        $type=$this->request->post('type',1,'intval');        $user_id=$this->request->post('user_id',0,'intval');        if(!$unit_id){            show([],0,'unit_id不能为空');        }        if(!$user_id){            show([],0,'user_id不能为空');        }        $where=[];        $where['unit_id']=$unit_id;        $where['type']=$type;        $where['delete_time']=0;        $material=model('studyMaterial')            ->where($where)            ->select();        $domain=Config::get('domain');        $student_info=model('student')->where('id',$user_id)->find();        $type=1;        if($student_info['type']==1){            $type=0;        }        foreach ($material as $value){            $value['create_time']=date('Y-m-d H:i:s', $value['create_time']);            $value['file_url']=$domain.$value['file_url'];            $value['banner']=$domain.$value['banner'];            $value['button_status']=$type;        }        show($material,200,'ok');    }    //完成学习资料    public function completeMaterial(){        $unit_list_id=$this->request->post('unit_list_id',0,'intval');        $user_id=$this->request->post('user_id',0,'intval');        if(!$unit_list_id){            show([],0,'unit_list_id必传');        }        if(!$user_id){            show([],0,'user_id必传');        }        $student_info=model('student')->field('teacher_id,name,account,id')->where('id',$user_id)->find();        $teacher_info=model('user')->where('uid',$student_info['teacher_id'])->find();        $unit_id=model('unit_list')->where('id',$unit_list_id)->value('unit_id');        $unit_name=model('unit')->where('id',$unit_id)->value('name');        $status=model('systemNews')->where('type',2)->where('unit_id',$unit_id)->where('from_user',$student_info['account'])->order('id','desc')->value('status');        if(!empty($status)){            if($status==1){                show([],0,'审核已通过');            }else if($status==2){                show([],0,'审核已拒绝');            }else if($status==0){                show([],0,'审核中请稍后');            }        }        //发送消息给老师        $data['content']='老师，你好！我是“'.$student_info['name'].'”,我已观看完“'.$unit_name.'”的学习内容，请您审核！';        $data['title']=''.$student_info['name'].'的学习进度';        $data['from_user']=$student_info['account'];        //查询到学生的所属老师id        $data['to_user']=$teacher_info['user'];        $data['to_user_id']=$teacher_info['uid'];        $data['from_user_id']=$student_info['id'];        $data['is_read']=0;        $data['send_time']=time();        $data['type']=2;        $data['unit_id']=$unit_id;        $data['unit_name']=$unit_name;        $data['status']=0;        $news_res=model('systemNews')->insert($data);        if($news_res){            show([],200,'请求已提交，等待审核');        }else{            show([],0,'请求发送失败，请重新尝试');        }    }    public function completeHomework(){        $unit_list_id=$this->request->post('unit_list_id',0,'intval');        $user_id=$this->request->post('user_id',0,'intval');        if(!$unit_list_id){            show([],0,'unit_list_id必传');        }        if(!$user_id){            show([],0,'user_id必传');        }        $student_info=model('student')->field('teacher_id,name,account,id')->where('id',$user_id)->find();        $teacher_info=model('user')->where('uid',$student_info['teacher_id'])->find();        $unit_id=model('unit_list')->where('id',$unit_list_id)->value('unit_id');        $unit_name=model('unit')->where('id',$unit_id)->value('name');        $status=model('systemNews')->where('type',3)->where('unit_id',$unit_id)->where('from_user',$student_info['account'])->order('id','desc')->value('status');        if($status==1){            show([],0,'审核已通过');        }else if($status==2){            show([],0,'审核已拒绝');        }else if($status==0){            show([],0,'审核中请稍后');        }        //发送消息给老师        $data['content']='老师，你好！我是“'.$student_info['name'].'”,我已做完“'.$unit_name.'”的作业，请您审核！';        $data['title']=''.$student_info['name'].'的作业情况';        $data['from_user']=$student_info['account'];        //查询到学生的所属老师id        $data['to_user']=$teacher_info['user'];        $data['to_user_id']=$teacher_info['uid'];        $data['from_user_id']=$student_info['id'];        $data['is_read']=0;        $data['send_time']=time();        $data['type']=3;        $data['unit_id']=$unit_id;        $data['unit_name']=$unit_name;        $data['status']=0;        $news_res=model('systemNews')->insert($data);        if($news_res){            show([],200,'请求已提交，等待审核');        }else{            show([],0,'请求发送失败，请重新尝试');        }    }    //讲解操作    public function teachAction(){        $material_id=$this->request->post('material_id',0,'intval');        $user_id=$this->request->post('user_id',0,'intval');        if(!$material_id){            show([],0,'material_id必传');        }        if(!$user_id){            show([],0,'user_id');        }        $student_info=model('student')->field('teacher_id,name,type,account,id')->where('id',$user_id)->find();        $teacher_info=model('user')            ->where('uid',$student_info['teacher_id'])            ->where('status',1)            ->find();        if(empty($teacher_info)){            show([],0,'该学生老师账号有异常请联系管理员');        }        if($student_info['type']==1){            show([],0,'您没有权限要求讲解');        }        $last_send_time=model('systemNews')->where('type',1)->where('unit_id',$unit_id)->where('material_id',$material_id)->where('from_user',$student_info['account'])->value('send_time');        if($last_send_time>= strtotime('-1year')){            show([],0,'你已提交过讲解请求了');        }        $material_name=model('study_material')            ->where('id',$material_id)            ->where('delete_time',0)            ->value('title');        //发送消息给老师        if(empty($material_name)){            show([],0,'该知识点已被删除');        }        $data['content']='老师，你好！我是“'.$student_info['name'].'”,“'.$material_name.'”这个知识点我还不太懂';        $data['title']=''.$student_info['name'].'的难点';        $data['from_user']=$student_info['account'];        //查询到学生的所属老师id        $data['to_user']=$teacher_info['user'];        $data['is_read']=0;        $data['to_user_id']=$teacher_info['uid'];        $data['from_user_id']=$student_info['id'];        $data['send_time']=time();        $data['type']=1;        $data['material_id']=$material_id;        $data['unit_id']=$material_id;        $data['unit_name']=$material_name;        $data['status']=0;        $news_res=model('systemNews')->insert($data);        if($news_res){            show([],200,'申请已发送，会有老师找您沟通');        }else{            show([],0,'申请发送失败，请重新尝试');        }    }}