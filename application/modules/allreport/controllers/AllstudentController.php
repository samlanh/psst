<?php
class Allreport_AllstudentController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
			
	}
	public function idselectedAction(){
		$id=$this->getRequest()->getParam('id');
		$k = 0;
		$condition = '';
		$ids = explode(',', $id);
		foreach ($ids as $id_stu){
			if($k==0){
				$condition .= $id_stu;
				$k=1;
			}else{
				$condition .= ' or stu_id = '.$id_stu;
			}
		}
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $db->getAllStudentSelected($condition);
	}
	
	public function rptAllStudentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'stu_type' 		=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllStudent($search);
		$this->view->search=$search;
	}
	public function rptAllStudentOldAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'stu_type' 		=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllStudent($search);
		$this->view->search=$search;
	}
	
	public function rptAllAmountStudentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'branch_id'=>0,
					'degree'=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllAmountStudent($search);
		$this->view->search=$search;
	}
	
	public function rptStudentStatisticAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getStudentStatistic($search);
// 		print_r($rs_rows);
		$this->view->search=$search;
	}
	
	public function rptStudyHistoryAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
				$search=array(
					'title' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'stu_type' 		=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
				);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $db->getAllStudyHistory($search);
	}
	public function rptStudentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title'  	=>'',
					'study_year'=>'',
					'grade_bac' =>'',
					'session'  	=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'	=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllStudentDetail($search);
		$this->view->search=$search;
	}
	public function rptStudentattendenceAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'group' 		=>'',
					'branch_id'=>0,
					'degree'=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getStudentAttendance($search);
		$this->view->search=$search;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	
	public function rptStudentMistakeAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'group' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->student = $rs_rows = $group->getStudentMistake($search);
		$this->view->search=$search;
		$this->view->datasearch = $search;
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	
	public function rptTotalStudentMistakeAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'group' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->student = $rs_rows = $group->getStudentMistake($search);
		$this->view->search=$search;
		$this->view->datasearch = $search;
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	
	function mistakeCertificateAction(){
		
		$group_id=$this->getRequest()->getParam("id");
		$stu_id=$this->getRequest()->getParam("stu_id");
		
		//echo $stu_id;
		
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'start_date'	=> null,
					'end_date'		=> date('Y-m-d'),
			);
		}
		$this->view->search=$search;
		$this->view->stu_id = $stu_id;
		$this->view->group_id = $group_id;
		
		$db = new Allreport_Model_DbTable_DbMistakeCertificate();
		$this->view->student_info = $db->getStudentInfo($group_id,$stu_id);
		$this->view->student_mistake = $db->getMistakeRecord($search,$group_id,$stu_id);
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	
	public function rptAttendenceAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'group' 		=>'',
					'branch_id'=>0,
					'degree'=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$this->view->datasearch = $search;
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->student = $db->getStudentAttendence($search);
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister(null,"action");
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	public function rptAttendenceHighschoolAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_highschool' 	=>'',
					'session' 		=>'',
					'group' 		=>'',
					'branch_id'=>0,
// 					'degree'=>0,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$this->view->datasearch = $search;
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->student = $db->getStudentAttendenceHighschool($search);
		$this->view->g_all_name=$db->getGroupHighschoolSearch();
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister(null,"action");
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	function getSubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptAllStudent();
			$data=$db->getSubjectByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	public function rptStudentDropAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' =>'',
					'study_year' =>'',
					'grade_bac' =>'',
					'session' =>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $rs_rows = $group->getAllStudentDrop($search);
		$this->view->search=$search;
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
					'study_year'	=> "",
					'grade' 		=> "",
					'session' 		=> "",
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
	}
	public function rptgroupstudentchangegroupAction()
	{
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{
			$search=array(
					'title' => '',
					'study_year' => '',
					'grade_bac' => '',
					'session' => '',
					'change_type' => '',
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db= new Allreport_Model_DbTable_DbRptGroupStudentChangeGroup();
		$this->view->rs = $db->getAllStu($search);
		$this->view->change_type = $db->getChangeType();
		$this->view->search=$search;
	}
	public function rptStudentChangeGroupAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' => '',
					'study_year' => '',
					'grade_bac' => '',
					'session' => '',
			);
		}
	
		$group= new Allreport_Model_DbTable_DbRptStudentChangeGroup();
	
		$this->view->rs = $rs_rows = $group->getAllStudentChangeGroup($search);
		$this->view->search=$search;
	
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function rptStudentGroupAction()
	{
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
		$rs= $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
	}
	function submitlistAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptGroup();
			$db->submitDateList($data);
// 			Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 			$this->_redirect("/allreport/allstudent/student-group");
// 			Application_Form_FrmMessage::redirector("/allreport/allstudent/student-group");
			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/allreport/allstudent/student-group");
		}
	}
	public function rptStudentListAction()
	{
// 		rpt-student-list
		
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
					'study_type'=>1
					);
		}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$row = $db->getStudentGroup($id,$search,1);
		$this->view->rs = $row;
		$rs= $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
	}
	function certifyAction(){
		$id=$this->getRequest()->getParam("id");
		//echo $id;exit();
		$db = new Allreport_Model_DbTable_DbCertify();
		$result = $db->getStudentCertify($id);
		$this->view->rs = $result;
	}
	function rptTranscriptAction(){
		$group_id=$this->getRequest()->getParam("group_id");
		$student_id=$this->getRequest()->getParam("id");
		$search= array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		
		$this->view->semester1= $db->getStundetExamById($group_id,1,$student_id);//for semester1
		$this->view->semester2= $db->getStundetExamById($group_id,2,$student_id);//for semester1
		
		$this->view->rsrankingsemester1 = $db->getRankingSemesterByStudent($group_id,1,$student_id);
		$this->view->rsrankingsemester2 = $db->getRankingSemesterByStudent($group_id,2,$student_id);
		
// 		$array_score = array();
// 		if(!empty($result_semester)){
// 			foreach ($result_semester as $key => $row){
// 				$array_score[$key]['score_average'] = ($row['average']+$row['avg_exam'])/2;
// 			}
// 		}
// 		array_multisort($array_score, SORT_DESC, $result_semester);
		$result = $db->getAcadimicByStudentHeader($group_id,$student_id);
		$this->view->result = $result;
	}
	function academicTranscriptAction(){
	}
	
	public function rptAttListAction()
	{
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
					'txtsearch' 	=> "",
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d',strtotime('+1 month')),
					'teacher' 		=> 0,
					'subject' 		=> 0,
					);
		}
		
		
		//echo $end_date;exit();
		
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$row = $db->getStudentGroup($id,$search,0);
		$this->view->rs = $row;
		
		$rs= $db->getGroupDetailByID($id);
		$this->view->rr = $rs;		
		$this->view->datasearch = $search;		
		$this->view->all_teacher_by_group = $db->getAllTeacherByGroup($id);
		$this->view->all_subject_by_group = $db->getAllSubjectByGroup($id);
		
	}
	
	public function rptRescheduleGroupAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' =>'',
					'study_year' =>'',
					'group' =>'',
					'session' =>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $rs_rows = $group->getAllRescheduleGroup($search);
		$this->view->search=$search;
	}
	
	public function rptReschedulebygroupAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' =>'',
					'study_year' 	=>'',
					'group' =>'',
					'room' 	=>'',
					'grade' =>'',
					'session' =>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $rs_rows = $group->getAllReschedulebygroup($search);
		$this->view->search=$search;
	}
	
}