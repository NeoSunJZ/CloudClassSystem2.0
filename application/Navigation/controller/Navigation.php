<?php
namespace app\Navigation\controller;
use think\Controller;
use think\Session;


class Navigation extends Controller
{
    public function index()
    {
    	if (!session('?ext_user')) {
        header(strtolower("location: ".config("web_root")."/login/login/login"));
        exit();
      }
        if(Session::get('ext_user.Authority')==0){
        	$this->error('您的账户尚未通过审核！请联系您所在班级的学习委员~');
        }
        else
        {
        	return $this->fetch('Navigation');
    	}
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
