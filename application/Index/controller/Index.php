<?php
namespace app\Index\controller;
use think\View;
use think\Request;
use think\Controller;
use think\Db;//数据库
use think\Session;
use think\captcha;//配置验证码

class Index extends Controller
{
    public function index()
    {
    	if (!session('?ext_user')) {
        header(strtolower("location: ".config("web_root")."/login/login/login"));
        exit();
      }
        if(Session::get('ext_user.Authority')==1){
        		$authority="学生";
        	}
        else if(Session::get('ext_user.Authority')==2){
        		$authority="学习委员";
        }
        else{
        	$this->error('您的账户尚未通过审核！请联系您所在班级的学习委员~');
        }
        $this->assign([
            'truename' => Session::get('ext_user.Truename'),
            'department'  => Session::get('ext_user.Department'),
            'classroom'   => Session::get('ext_user.Classroom'),
            'studentid'   => Session::get('ext_user.StudentID'),
            'authority'   => $authority,
        ]);
        return $this->fetch('Index');
    }

    public function logout(){//退出登录
    	\app\common\model\Admin::logout();

      if (!session('?ext_user')) {
        header(strtolower("location: ".config("web_root")."/index/login/login"));
        exit();
      }
      header(strtolower("location:login"));
      return NULL;
    }
}
