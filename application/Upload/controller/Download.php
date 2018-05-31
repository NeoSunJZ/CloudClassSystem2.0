<?php
namespace app\Upload\controller;
use think\View;
use think\Request;
use think\Controller;
use think\Db;//数据库
use think\Session;
use think\captcha;//配置验证码

use app\upload\model\Homework as HomeworkModel;
use app\upload\model\UploadHomework as UploadHomeworkModel;

class Download extends Controller{
	function download($Hid,$Ver=0,$is_Public=0,$P_name=""){
		if($is_Public==0){
			$where2['Hid'] = $Hid;
        	$where2['Sid'] = Session::get('ext_user.StudentID');
        	$UploadHomework=UploadHomeworkModel::table('UploadHomework')->where($where2)->find();
        	$UploadHomeworkContent=$UploadHomework->UploadHomeworkContent()->where('Version',$Ver)->find();
        	$fname=$UploadHomeworkContent->FileLocation;
        	$fpath=$Hid;
    	}elseif($is_Public==1){//作业Example下载
    		$fname=$P_name;
            $fpath='Example/'.$Hid;
    	}elseif($is_Public==2){//作业Attachment下载
    		$fname=$P_name;
            $fpath='Attachment/'.$Hid;
    	}
        //避免中文文件名出现检测不到文件名的情况，进行转码utf-8->gbk
        $filename=iconv('utf-8', 'gb2312', $fname);
        $path="uploads/".$fpath."/".$filename;
        if(!file_exists($path)){//检测文件是否存在

            $this->error('Emmmmm……文件'.$fname.'不存在，可能已经过期或被移除了哦!');
        }
        $fp=fopen($path,'r');//只读方式打开
        $filesize=filesize($path);//文件大小
        //返回的文件(流形式)
        header("Content-type: application/octet-stream");
        //按照字节大小返回
        header("Accept-Ranges: bytes");
        //返回文件大小
        header("Accept-Length: $filesize");
        //这里客户端的弹出对话框，对应的文件名
        header("Content-Disposition: attachment; filename=".$filename);
        //================重点====================
        ob_clean();
        flush();
        //=================重点===================
        //设置分流
        $buffer=1024;
        //来个文件字节计数器
        $count=0;
        while(!feof($fp)&&($filesize-$count>0)){
            $data=fread($fp,$buffer);
            $count+=$data;//计数
            echo $data;//传数据给浏览器端
        }
        fclose($fp);

    }
}