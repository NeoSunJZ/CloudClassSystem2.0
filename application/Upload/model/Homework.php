<?php
namespace app\upload\model;
use think\Model;
class Homework extends Model{
	public function HomeworkContent()
	{
		return $this->hasOne('HomeworkContent','id');//内容是一对一对应的
	}

	public function UploadHomework()
    {
        return $this->hasMany('UploadHomework','Hid');//提交信息是一对多对应的
    }
}