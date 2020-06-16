<?php

namespace app\index\controller;

use think\facade\Config;
use think\Controller;
use think\facade\Session;

class Question extends Controller
{
    public function paperList()
    {
        $course_id=$this->request('');
    }
}
