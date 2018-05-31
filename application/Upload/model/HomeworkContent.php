<?php
namespace app\upload\model;
use think\Model;
class Homeworkcontent extends Model{
	public function Homework()
    {
        return $this->belongsTo('Homework');
    }
}