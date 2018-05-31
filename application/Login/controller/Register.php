<?php
namespace app\login\controller;
use think\View;
use think\Request;
use think\Controller;
use think\Db;//数据库


use app\login\model\User as UserModel;

class Register extends Controller
{
    public function register() 
    {
      $view = new View();
      return $view->fetch('register'); 
    }

    //学号重复性判断
    public function cheak($StudentID){
      $cheak= DB::table('user')
      ->where('StudentID','=',$StudentID)
      ->find();

      if($cheak){
        echo '<span class="error glyphicon glyphicon-exclamation-sign"></span><span class="error"> 该学号已存在，请登录!</span>';
      }
      else
      {
        echo '<span class="success glyphicon glyphicon-ok-circle"></span><span class="success"> 该学号不存在，可注册~</span>';
      }
    }

    public function doregister(Request $request)
    {
      $user=new UserModel;
      $user->StudentID    =$request->param('student_id');
      $user->Password     =md5($request->param('password'));
      $user->Truename     =$request->param('truename');
      $user->Department   =$request->param('Department');
      $user->Grade        =$request->param('Grade');
      $user->Classroom    =$request->param('Classroom');
      $user->Email        =$request->param('email');
      $user->Authority    =0;
      if ($user->save()) {
          $this->success('用户名增加成功');
      }
      else
       {
          $this->error('用户名增加失败');
       }
    }
}