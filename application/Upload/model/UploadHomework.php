<?php
namespace app\upload\model;
use think\Model;
class Uploadhomework extends Model{//此类名必须小写
	public function UploadHomeworkContent()
    {
        return $this->hasMany('UploadHomeworkContent','cid');//hasMany是一对多
    }

    public function Homework()
    {
        return $this->belongsTo('Homework');
    }
}