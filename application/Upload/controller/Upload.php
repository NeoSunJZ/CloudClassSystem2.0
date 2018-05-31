<?php
namespace app\Upload\controller;
use think\View;
use think\Request;
use think\Controller;
use think\Db;//数据库
use think\Session;
use think\captcha;//配置验证码

use app\upload\model\HomeworkContent as HomeworkContentModel;
use app\upload\model\Homework as HomeworkModel;
use app\upload\model\UploadHomework as UploadHomeworkModel;

class Upload extends Controller
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

        $where['BelongDepartment'] = Session::get('ext_user.Department');
        $where['BelongClass'] = Session::get('ext_user.Classroom');
        $Homework=Db::view('Homework')
            ->view('HomeworkContent','title','HomeworkContent.id=Homework.id')
            ->where($where)
            ->select();
            $num = count($Homework); 
            for($i=0;$i<$num;$i++)
            {
                $where2['Hid'] = $Homework[$i]["id"];
                $where2['Sid'] = Session::get('ext_user.StudentID');
                $is_upload = UploadHomeworkModel::table('UploadHomework')->where($where2)->find();
                if($is_upload){
                    if($Homework[$i]["Deadline"]>date('Y-m-d H:i:s')){
                        $Homework[$i]["State"]="作业已上交";
                        $Homework[$i]["function"]="<a href=\""."HomeworkInf?Homework_id=".$Homework[$i]["id"]."\" class=\"btn btn-success btn-sm\"><i class=\"fa fa-rotate-left \"></i> 更新</a>";
                    }else{
                        $Homework[$i]["State"]="作业评审中";
                        $Homework[$i]["function"]="<a href=\""."HomeworkInf?Homework_id=".$Homework[$i]["id"]."\" class=\"btn btn-warning btn-sm\"><i class=\"fa fa-chain \"></i> 查看</a>";
                    }
                }
                else{
                    if($Homework[$i]["Deadline"]>date('Y-m-d H:i:s')){
                        $Homework[$i]["State"]="作业未上交";
                        $Homework[$i]["function"]="<a href=\""."HomeworkInf?Homework_id=".$Homework[$i]["id"]."\" class=\"btn btn-success btn-sm\"><i class=\"fa fa-cloud-upload \"></i> 提交</a>";
                    }else{
                        $Homework[$i]["State"]="作业未完成";
                        $Homework[$i]["function"]="<p class=\"btn btn-danger btn-sm\"><i class=\"fa fa-exclamation-triangle \"> 未完成</p>";
                    }
                }
            }
        $this->assign('Homeworklist',$Homework);
        $this->assign('count',count($Homework));
        $this->assign([
            'truename' => Session::get('ext_user.Truename'),
            'department'  => Session::get('ext_user.Department'),
            'classroom'   => Session::get('ext_user.Classroom'),
            'studentid'   => Session::get('ext_user.StudentID'),
        ]);
        return $this->fetch('Upload');
    }

    public function HomeworkInf($Homework_id){
        $where['Homework.id'] = $Homework_id;
        $HomeworkContent=Db::view('Homework')
            ->view('HomeworkContent','title,content,Is_Need_Attachment,Attachment,Is_Need_Example,Example','HomeworkContent.id=Homework.id')
            ->where($where)
            ->find();
            if($HomeworkContent["Is_Need_Attachment"]){
                $Is_Need_Attachment="提供了";
                $this->assign(['Attachment'=> $HomeworkContent["Attachment"]]);
            }
            else{
                $Is_Need_Attachment="未提供";
                $this->assign(['Attachment'=> NULL]);
            }
            
            if($HomeworkContent["Is_Need_Example"]){
                $Is_Need_Example="提供了";
                $this->assign(['Example'=> $HomeworkContent["Example"]]);
            }
            else{
                $Is_Need_Example="未提供";
                $this->assign(['Example'=> NULL]);
            }
        $ver=0;
        $where2['Hid'] = $Homework_id;
        $where2['Sid'] = Session::get('ext_user.StudentID');
        $UploadHomework=UploadHomeworkModel::table('UploadHomework')->where($where2)->find();
        if($UploadHomework){
        $UploadHomeworkContent=$UploadHomework->UploadHomeworkContent;
        foreach ($UploadHomeworkContent as $list){
            if($list["is_end_flag"]==1){
                $ver=$list["Version"];
                }
            }
        $this->assign('UploadHomeworkContent',$UploadHomeworkContent);   
        }else{
            $this->assign('UploadHomeworkContent',NULL);
        }
        $ver++;
        $this->assign([
            'CreateTime'         => $HomeworkContent["CreateTime"],
            'Deadline'           => $HomeworkContent["Deadline"],
            'CreateUser'         => $HomeworkContent["CreateUser"],
            'BelongDepartment'   => $HomeworkContent["BelongDepartment"],
            'BelongSubject'      => $HomeworkContent["BelongSubject"],
            'BelongClass'        => $HomeworkContent["BelongClass"],
            'title'              => $HomeworkContent["title"],
            'content'            => $HomeworkContent["content"],
            'Is_Need_Attachment' => $Is_Need_Attachment,
            'Attachment'         => $HomeworkContent["Attachment"],
            'Is_Need_Example'    => $Is_Need_Example,
            'Homeworkid'         => $Homework_id,
            'ver'                => $ver,
        ]);
        return $this->fetch('HomeworkInf');
    }

    public function arraySequence($array, $field, $sort = 'SORT_ASC')
    {
        /**
        * 二维数组根据字段进行排序
        * @params array $array 需要排序的数组
        * @params string $field 排序的字段
        * @params string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
        */
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
    }

    public function CreateFileName($Homework_id,$title,$ver,$FileNameRule){
        $Cname="";
        $Array_order=explode(" ",$FileNameRule);
            $Array_FileNameTemp[]=array('Order'=>$Array_order[0],'Value'=>Session::get('ext_user.StudentID'));
            $Array_FileNameTemp[]=array('Order'=>$Array_order[1],'Value'=>Session::get('ext_user.Department').$CLASSROOM=Session::get('ext_user.Classroom'));
            $Array_FileNameTemp[]=array('Order'=>$Array_order[2],'Value'=>Session::get('ext_user.Truename'));
            $Array_FileNameTemp[]=array('Order'=>$Array_order[3],'Value'=>$ver);
            $Array_FileNameTemp[]=array('Order'=>$Array_order[4],'Value'=>$title);
            $Array_FileNameTemp[]=array('Order'=>$Array_order[5],'Value'=>$Homework_id);
        $Array_FileName=$this->arraySequence($Array_FileNameTemp,'Order');
        foreach ($Array_FileName as $key => $value) {
            if($value["Order"]!=0){
                $Cname.=$value["Value"].$Array_order[6];
            }
        }
        $Cname = substr($Cname,0,strlen($Cname)-1); 
        //$ID=Session::get('ext_user.StudentID');
        //$NAME=Session::get('ext_user.Truename');
        //$DEPARTMENT=Session::get('ext_user.Department');
        //$CLASSROOM=Session::get('ext_user.Classroom');
        //$VERSION=$ver;
        //$Cname=$Homework_id."_".$ID."_".$DEPARTMENT.$CLASSROOM."_".$NAME."_".$VERSION;
        $I_Cname=iconv('utf-8','gb2312',$Cname);
        return $I_Cname;
    }

    public function CreateFoldername($title,$BelongSubject){
        $DEPARTMENT=Session::get('ext_user.Department');
        $CLASSROOM=Session::get('ext_user.Classroom');
        $Cname=$DEPARTMENT.$CLASSROOM."_".$BelongSubject."_".$title;
        $I_Cname=iconv('utf-8','gb2312',$Cname);
        return $I_Cname;
    }

    public function upload($Homework_id,$ver){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('files');
    if(empty($file)) {    
            $this->error(' 请选择上传文件');
        }
    // 移动到服务器的上传目录 并且使用UsingName规则
    if($file){
        $FileNameRule=HomeworkContentModel::table('HomeworkContent')->where('id',$Homework_id)->value('FileName');
        $title=HomeworkContentModel::table('HomeworkContent')->where('id',$Homework_id)->value('title');
        $Size=HomeworkContentModel::table('HomeworkContent')->where('id',$Homework_id)->value('FileSize');
        $Type=HomeworkContentModel::table('HomeworkContent')->where('id',$Homework_id)->value('FileType');
        $name=$this->CreateFileName($Homework_id,$title,$ver,$FileNameRule);
        $Foldername=$Homework_id;
        $info = $file->validate(['size'=>$Size*1048576,'ext'=>$Type])->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .$Foldername. DS ,$name);
        if($info){
            // 成功上传后 获取上传信息
            $where2['Hid'] = $Homework_id;
            $where2['Sid'] = Session::get('ext_user.StudentID');
            $UploadHomework=UploadHomeworkModel::table('UploadHomework')->where($where2)->find();
            if($UploadHomework){
                $UploadHomeworkContent=$UploadHomework->UploadHomeworkContent()->where('is_end_flag',1)->find();
                if($UploadHomeworkContent){
                    $UploadHomeworkContent->is_end_flag=0;
                    $UploadHomeworkContent->save();
                }else{
                    $this->error("数据库异常，请联系管理员处理！");
                }
                $UploadHomework->UploadHomeworkContent()->save([
                        'UploadTime'=>date("Y-m-d h:i:s"),
                        'Version'=>$ver,
                        'FileLocation'=>iconv('gb2312','utf-8',$info->getSaveName()),
                        'is_end_flag'=>1
                ]);
                $this->success(' 文件更新成功');
            }
            else{
                $NewUploadHomework = new UploadHomeworkModel;
                $NewUploadHomework->Hid    =$Homework_id;
                $NewUploadHomework->Sid    =Session::get('ext_user.StudentID');
                if ($NewUploadHomework->save()) {
                    $UploadHomework=UploadHomeworkModel::table('UploadHomework')->where($where2)->find();
                    $UploadHomework->UploadHomeworkContent()->save([
                        'UploadTime'=>date("Y-m-d h:i:s"),
                        'Version'=>1,
                        'FileLocation'=>iconv('gb2312','utf-8',$info->getSaveName()),
                        'is_end_flag'=>1
                    ]);
                $this->success(' 文件上传成功');
                }
                else{
                    $this->error("数据库写入失败！");
                }
            }
            /*echo $info->getExtension();
            echo $info->getSaveName();
            echo $info->getFilename(); */
        }else{
            // 上传失败获取错误信息
            $this->error($file->getError());
            }   
    }
    }
}