<?php
namespace app\Login\model;

class Admin extends \think\Model
{
    public static function login($name, $password)
    {

        $where['StudentID'] = $name;
        $where['Password'] = md5($password);
        //下面创建User模型类来接受返回的模型对象
        $user = Admin::table('user')->where($where)->find();
        if ($user) {
            unset($user["Password"]);//销毁指定的Password变量以确保安全性
            session("ext_user", $user);//对象的其余变量存入Session
            return true;
        }else{
            return false;
        }
    }


  
    
    public static function logout(){
        session("ext_user", NULL);
        return [
            "code" => 0,
            "desc" => "退出成功"
        ]; 
    }

    
} 
 