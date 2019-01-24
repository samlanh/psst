<?php
class Allreport_ScoreController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){	
		
	}
    function rptScoreBacMonthlyAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title'=>'',
    				'branch_id'=> '',
    				'room'=>0,
    				'exam_type'=>-1,
    				'for_semester'=>-1,
    				'group' => '',
    				'study_year'=> '',
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    			);
    	}
    	
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreGroup($search);
    	$group = $db->getAllgroupStudyNotPass();
    	array_unshift($group, array ( 'id' => 0,'name' => 'ជ្រើសរើស'));
    	$this->view->g_all_name=$group;
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id= empty($search['branch_id'])?1:$search['branch_id'];
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    }
    function rptScoreDetailAction(){//តាមមុខវិជ្ជាលម្អិត
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,null,1);
    	}
    	else{
    		$row = $db->getScoreExamByID($id);
    		$search = array(
    				'group' => $row['group_id'],
    				'study_year'=> $row['for_academic_year'],
    				'exam_type'=> $row['exam_type'],
    				'branch_id'=>$row['branch_id'],
    				'for_month'=>$row['for_month'],
    				'for_semester'=>$row['for_semester'],
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    		);
    		$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,1);
    	}
    	$this->view->search=$search;
    	
    	
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    function rptScoreResultAction(){ //ពិន្ទុសរុបតាមមុខ
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$result = $db->getStundetScoreResult($search,null,1);
    		$this->view->studentgroup = $result;
    	}
    	else{
    		$row = $db->getScoreExamByID($id);
    		$search = array(
    				'group' => $row['group_id'],
    				'study_year'=> $row['for_academic_year'],
    				'exam_type'=> $row['exam_type'],
    				'branch_id'=>$row['branch_id'],
    				'for_month'=>$row['for_month'],
    				'for_semester'=>$row['for_semester'],
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    		);
    		$result = $db->getStundetScoreResult($search,$id,1);
    		$this->view->studentgroup = $result;
    		
    	}
    	$this->view->search=$search;
    	
    	
    	
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frm = new Application_Form_FrmGlobal();
//     	$rs = $db->getStundetScoreDetailGroup($search,$id,1);
    	$branch_id = $result[0]['branch_id'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    }
    function monthlyOutstandingStudentAction(){
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreResult($search,$id,2);
    	
    	$this->view->all_student = $db->getStundetScoreDetailGroup($search,$id,1);
    	
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    function monthlyOutstandingStudentNophotoAction(){
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreResult($search,$id,2);
    	 
    	$this->view->all_student = $db->getStundetScoreDetailGroup($search,$id,1);
    	 
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }   
    public function studentGroupAction()
    {
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title' 		=> "",
    				'group' 		=> "",
    				'branch_id' 	=> "",
    				'study_year'	=> "",
    				'grade' 		=> "",
    				'session' 		=> "",
    				'teacher' 		=> "",
    				'room'=>0,
    				'degree'=>0,
    				'study_status'=>-1,
    		);
    	}
    	$db = new Allreport_Model_DbTable_DbRptGroup();
    	$rs= $db->getGroupDetail($search);
    	$this->view->rs = $rs;
    	$form=new Registrar_Form_FrmSearchInfor();
    	$forms=$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($forms);
    	$this->view->form_search=$form;
    	
    	$this->view->search=$search;
    	    
    	$_db = new Global_Model_DbTable_DbGroup();
    	$teacher = $_db->getAllTeacher();
    	$this->view->teacher = $teacher;
    	
    	$branch_id = empty($search['branch_id'])?1:$search['branch_id'];
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    }
    function rptResultbysemesterAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");
    	$search= array();
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester = $db->getStundetScorebySemester($group_id,$type);//ប្រើតែក្នុងលទ្ធផលប្រចាំខែ និង តារាងកិត្តិយស
    	$array_score = array();
    	if(empty($result_semester)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
    	}
    	$this->view->studentgroup = $result_semester;
    	
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = $result_semester[0]['branch_id'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    }
    
    function semesterOutstandingStudentAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$semester=$this->getRequest()->getParam("type");
    	$search= array();
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester = $db->getStundetScorebySemester($group_id,$semester);    	
    	if(empty($result_semester)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
    	}
    	$this->view->studentgroup = $result_semester;
    }
    function semesterOutstandingStudentNophotoAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$semester=$this->getRequest()->getParam("type");
    	$search= array();
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester = $db->getStundetScorebySemester($group_id,$semester);
    	if(empty($result_semester)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
    	}
    	$this->view->studentgroup = $result_semester;
    }
    
    function rptResultbyyearAction(){
    	$group_id=$this->getRequest()->getParam("id");
//     	$type=$this->getRequest()->getParam("type");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_year = $db->getStundetScorebyYear($group_id);
       if(empty($result_year)){
       		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
       }
       $this->view->studentgroup = $result_year;
    }
    
    function yearlyOutstandingStudentAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");
    
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_year = $db->getStundetScorebyYear($group_id);
    	if(empty($result_year)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
    	}
    	$this->view->studentgroup = $result_year;
    }
    function yearlyOutstandingStudentNophotoAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");
    
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_year = $db->getStundetScorebyYear($group_id);
    	if(empty($result_year)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/score/student-group");
    	}
    	$this->view->studentgroup = $result_year;
    }
    
    function rptScoreGepAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->g_name=$search;
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_english'=> '',
    				'degree_english'=>'',
    				'session'=> '',
    				'time'=> '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    	}
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundentEnglishMonthlyScore($search);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    
    function rptSemesterEvaluationAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->g_name=$search;
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_english'=> '',
    				'degree_english'=>'',
    				'session'=> '',
    				'time'=> '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    	}
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundentEnglishSemesterScore($search);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    function veiwAction(){
    	
    }
    
    function rptScoreListMonthlyAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title',
    				'room'=>0,
    				'group_name' => 0,
    				'study_year'=> 0,
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    		);
    	}
    	 
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreList($search);
    	 
    	$group = $db->getAllgroupStudyNotPass();
    	array_unshift($group, array ( 'id' => 0,'name' => 'ជ្រើសរើស'));
    	 
    	$this->view->g_all_name=$group;
    	$this->view->month = $db->getAllMonth();
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    function rptSubjectScoredetailAction(){//for kentridge
    	
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title',
    				'room'=>0,
    				'group_name' => 0,
    				'study_year'=> 0,
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    		);
    	}
    	
    	$this->view->search=$search;
    	$stu_id=$this->getRequest()->getParam("id");
    	$group_id=$this->getRequest()->getParam("group");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
//     	$this->view->studentgroup = $db->getStundetScoreList($search);
    	$this->view->scoreByStudent = $db->getStundetScoreDetail($stu_id,$group_id);
    	$this->view->studentinfo = $db->getStundetInfo($stu_id,$group_id);
    	
    }
    
    function rptMonthlyScoreStudentAction(){
    	
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    	}else{
	    	$stu_id =$this->getRequest()->getParam("stu_id");
	    	$group_id =$this->getRequest()->getParam("group_id");
	    	$exam_type =$this->getRequest()->getParam("exam_type");
	    	$for_semester =$this->getRequest()->getParam("for_semester");
	    	$for_month =$this->getRequest()->getParam("for_month");
	    	
	    	if (empty($stu_id)){
	    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
	    		exit();
	    	}elseif (empty($group_id)){
	    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
	    		exit();
	    	}elseif (empty($exam_type)){
	    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
	    		exit();
	    	}elseif (empty($for_semester)){
	    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
	    		exit();
	    	}elseif (empty($for_month)){
	    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
	    		exit();
	    	}
	    	$data = array(
	    			'stu_id'=>$stu_id,
	    			'group_id'=>$group_id,
	    			'exam_type'=>$exam_type,
	    			'for_semester'=>$for_semester,
	    			'for_month'=>$for_month,
	    			);
    	}
    	$this->view->search = $data;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$rs = $db->getExamByExamIdAndStudent($data);
    	$this->view->rs = $rs;
//     	if (empty($rs)){
//     		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
//     		exit();
//     	}
    	if ($rs['exam_type']==2){
    		$monthlysemesterAverage = $db->getAverageMonthlyForSemester($rs['group_id'], $rs['for_semester'], $rs['student_id']);
    		$this->view->monthlySemester = $monthlysemesterAverage;
    		
    		$semesterAverage = $db->getAverageSemesterFull($rs['group_id'], $rs['for_semester'], $rs['student_id']);
    		$this->view->Semester = $semesterAverage;
    	}
    	
    	$group = $db->getAllGroupOfStudent($data['stu_id']);
    	$this->view->group = $group;
    	$db = new Foundation_Model_DbTable_DbScore();
    	$subject =$db->getSubjectByGroup($data['group_id'],null,$data['exam_type']);
    	$this->view->subject = $subject;
    	$this->view-> month = $db->getAllMonth();
    }
    
    function rptAssessmenttermAction(){
    	$id=$this->getRequest()->getParam("id");
    	if(empty($id)){
    		$this->_redirect("/allreport/allstudent/student-group");
    	}
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'txtsearch' => "",
    				'study_type'=>1);
    	}
    	$this->view->search = $search;
    	
    	$db = new Allreport_Model_DbTable_DbRptGroup();
    	$row = $db->getStudentGroup($id,$search,1);
    	$this->view->rs = $row;
    	$rs = $db->getGroupDetailByID($id);
    	$this->view->rr = $rs;
    	
    	$scorepolicy = $db->checkScorePolicyMoreThanOne($id);
    	if (count($scorepolicy)>1){
    		Application_Form_FrmMessage::Sucessfull("This Group has use score policy type more than one. Please Check Score policy for this group.", "/allreport/allstudent/student-group");
    		exit();
    	}
    	$groupScore = $db->getScoreSettingIdByGroup($id);
    	if (empty($groupScore)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/allstudent/student-group");
    		exit();
    	}
    	$db = new Foundation_Model_DbTable_DbScoreEng();
    	$scoreSetting=$db->getScoreSettingDetail($groupScore['score_setting']);
    	$this->view->scoreSetting = $scoreSetting;
    	
    }
	
	public function rptStudentPassedAction()
    {
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}else{
    		$search=array(
    				'title' 		=> '',
    				'branch_id' 	=> '',
    				'study_year' 	=> '',
    				'change_id' 	=> '',
    		);
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$forms=$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($forms);
    	$this->view->form_search=$form;
    
    	$db= new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->student_pass = $db->getAllStudentPassed($search);
    	//$this->view->student_fail= $db->getAllStudentFailed($search);
    	//print_r($this->view->student_fail);exit();
    	
    	$_db= new Allreport_Model_DbTable_DbRptGroupStudentChangeGroup();
    	$this->view->change_type = $_db->getChangeType();
    	$this->view->all_change_group = $_db->getAllChangeGroup(2); // 2=ឡើងថ្នាក់
    	
    	$this->view->search=$search;
    
    	$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    }
}





