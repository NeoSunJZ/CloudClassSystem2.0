<?php
namespace app\Upload\controller;
use think\Request;
use think\Controller;
use think\Db;//数据库
use think\Session;

use app\upload\model\HomeworkContent as HomeworkContentModel;
use app\upload\model\Homework as HomeworkModel;
use app\upload\model\Teacher as TeacherModel;

class UploadAdmin extends Controller
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
                if((ceil(strtotime($Homework[$i]["Deadline"])-strtotime(date("Y-m-d H:i:s")))/86400)>2)
                    {
                        $Homework[$i]["State"]="作业任务运行中";
                        $Homework[$i]["function"]="<a href=\""."HomeworkInf?Homework_id=".$Homework[$i]["id"]."\" class=\"btn btn-success btn-sm\"><i class=\"fa fa-rotate-left \"></i> 状态查询</a>";
                    }
                else if ($Homework[$i]["Deadline"]>date('Y-m-d H:i:s'))
                {
                        $Homework[$i]["State"]="临近截止日期";
                        $Homework[$i]["function"]="<p class=\"btn btn-warning btn-sm\"><i class=\"fa fa-exclamation-triangle \"> 状态查询</p>";
                }
                else
                {
                        $Homework[$i]["State"]="作业已经截止";
                        $Homework[$i]["function"]="<p class=\"btn btn-danger btn-sm\"><i class=\"fa fa-exclamation-triangle \"> 状态查询</p>";
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
        return $this->fetch('UploadAdmin');
    }

    public function newupload(){
        $this->assign([
            'department'  => Session::get('ext_user.Department'),
            'classroom'   => Session::get('ext_user.Classroom'),
            'studentid'   => Session::get('ext_user.StudentID'),
        ]);
        $where['Department'] = Session::get('ext_user.Department');
        $Subject = TeacherModel::table('Teacher')->where($where)->select();
        $this->assign('Subject',$Subject);
        return $this->fetch('NewUpload');
    }
    public function donewupload(Request $request){
        $AttachmentName=NULL;
        $ExampleName=NULL;
        $id=HomeworkModel::table('Homework')->max('id');
        $id++;
        if($request->param('Is_Need_Attachment')==1){
            $file = request()->file('Attachment');
            if($file){
                $Cname=$file->getInfo('name');
                $I_Cname=iconv('utf-8','gb2312',$Cname);
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'Attachment'. DS . $id . DS ,$I_Cname);
                if(!$info){
                    $this->error($file->getError());
                }else{
                    $AttachmentName=$Cname;
                }
            }else{
                $this->error('矮油，没有上传附件呢！');
            }
        }
        if($request->param('Is_Need_Example')==1){
            $file = request()->file('Example');
            if($file){
                $Cname=$file->getInfo('name');
                $I_Cname=iconv('utf-8','gb2312',$Cname);
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'Example'. DS . $id . DS ,$I_Cname);
                if(!$info){
                    $this->error($file->getError());
                }else{
                    $ExampleName=$Cname;
                }
            }else{
                $this->error('矮油，没有上传实例呢！');
            }
        }
        $Homework=new HomeworkModel;
      $Homework->CreateTime=date("Y-m-d h:i:s");
      $Homework->Deadline=$request->param('deadline');
      $Homework->CreateUser=Session::get('ext_user.StudentID');
      $Homework->BelongDepartment=$request->param('department');
      $Homework->BelongSubject=$request->param('Lesson');
      $Homework->BelongClass=$request->param('classroom');
      if ($Homework->save())
      {
        if ($request->param('Unwanted_Student')) {
            $All_Need_Upload=0;
        }else{
            $All_Need_Upload=1;
        }
        $FileName=$request->param('FileNameSID').' '.$request->param('FileNameDC').' '.$request->param('FileNameN').' '.$request->param('FileNameV').' '.$request->param('FileNameT').' '.$request->param('FileNameHID').' '.$request->param('FileNameC');
        $HomeworkContent=new HomeworkContentModel;
        $HomeworkContent->title=$request->param('title');
        $HomeworkContent->content=$request->param('content');
        $HomeworkContent->Is_Need_Attachment=$request->param('Is_Need_Attachment');
        $HomeworkContent->Attachment=$AttachmentName;
        $HomeworkContent->All_Need_Upload=$All_Need_Upload;
        $HomeworkContent->Unwanted_Student=$request->param('Unwanted_Student');
        $HomeworkContent->Is_Need_Example=$request->param('Is_Need_Example');
        $HomeworkContent->Example=$ExampleName;
        $HomeworkContent->FileName=$FileName;
        $HomeworkContent->FileSize=$request->param('limit');
        $HomeworkContent->FileType=$request->param('type');
        $Homework->HomeworkContent()->save($HomeworkContent);
        $this->success('作业发布成功');
      }
      else{
        $this->error($Homework->getError());
      }
    }
    public function donewteacher(Request $request){
        $Teacher=new TeacherModel;
        $Teacher->Name=$request->param('Name');
        $Teacher->Lesson=$request->param('Lesson');
        $Teacher->Email=$request->param('Email');
        $Teacher->Department=$request->param('Department');
    if($Teacher->save()){
        $this->success('新增学科成功！');
    }else{
        $this->error($Teacher->getError());
    }
    }
}