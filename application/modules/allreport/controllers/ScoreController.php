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
    				'title',
    				'branch_id'=> '',
    				'room'=>0,
    				'exam_type'=>-1,
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
    }
    function rptScoreDetailAction(){//តាមមុខវិជ្ជាលម្អិត
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,1);
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
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreResult($search,$id,1);
    	
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frm = new Application_Form_FrmGlobal();
    	$rs = $db->getStundetScoreDetailGroup($search,$id,1);
    	$branch_id = $rs[0]['branch_id'];
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
    	    
    	$_db = new Global_Model_DbTable_DbGroup();
    	$teacher = $_db->getAllTeacher();
    	$this->view->teacher = $teacher;
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
    	$stu_id =$this->getRequest()->getParam("stu_id");
    	$score_id =$this->getRequest()->getParam("score_id");
    	if (empty($stu_id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
    		exit();
    	}elseif (empty($score_id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
    		exit();
    	}
    	$data = array(
    			'stu_id'=>$stu_id,
    			'score_id'=>$score_id,
    			);
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$rs = $db->getExamByExamIdAndStudent($data);
    	$this->view->rs = $rs;
    	if (empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/score/rpt-score-bac-monthly");
    		exit();
    	}
    	if ($rs['exam_type']==2){
    		$monthlysemesterAverage = $db->getAverageMonthlyForSemester($rs['group_id'], $rs['for_semester'], $rs['student_id']);
    		$this->view->monthlySemester = $monthlysemesterAverage;
    		
    		$semesterAverage = $db->getAverageSemesterFull($rs['group_id'], $rs['for_semester'], $rs['student_id']);
    		$this->view->Semester = $semesterAverage;
    	}
    	$db = new Foundation_Model_DbTable_DbScore();
    	$subject =$db->getSubjectByGroup($rs['group_id'],null,$rs['exam_type']);
    	$this->view->subject = $subject;
    	
    	
    }
}





