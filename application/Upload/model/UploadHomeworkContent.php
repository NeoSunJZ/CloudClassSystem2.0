<?php
namespace app\upload\model;
use think\Model;
class Uploadhomeworkcontent extends Model{
	public function UploadHomework()
    {
        return $this->belongsTo('UploadHomework');
    }
}