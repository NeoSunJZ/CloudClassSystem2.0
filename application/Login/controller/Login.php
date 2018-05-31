<?php
namespace app\login\controller;
use think\View;
use think\Controller;
use think\captcha;//配置验证码
use PHPMailer\PHPMailer\PHPMailer;//配置邮箱

class Login extends Controller
{
    public function login() 
    {
      $view = new View();
      return $view->fetch('login'); 
    } 

    public function dologin()
    {
       $view = new View();
       $param = input('post.');
          //为了防止前端校验未生效，这里加入判断
        if(empty($param['name'])){
          $this->error("用户名不能为空");
        }
        if(empty($param['password'])){
          $this->error('密码不能为空');
        }

        // 处理验证码
       if(!captcha_check($param['captcha'])){
          $this->error('验证码错误');
        }

        //登录验证
       $check=\app\Login\model\Admin::login($param['name'], $param['password']);
       if ($check) {
       		header(strtolower("location:". config("web_root") . "Navigation/Navigation/index"));
			exit();
       }
       else
       {
          $this->error('用户名密码错误');
       }
	}
}
