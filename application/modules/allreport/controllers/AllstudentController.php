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
		//print_r($id);
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
		//echo $condition;
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
	
	public function rptStudyHistoryAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'stu_id' 		=>'',
					'stu_name' 		=>'',
// 					'title' 		=>'',
// 					'branch_id'		=>0,
// 					'degree'		=>0,
// 					'study_year' 	=>'',
// 					'grade_all' 	=>'',
// 					'session' 		=>'',
// 					'stu_type' 		=>-1,
// 					'start_date'	=> date('Y-m-d'),
// 					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $db->getAllStudyHistory($search);
		//print_r($rs_rows);exit();
		$this->view->search=$search;
		
		$this->view->stu_id = $db->getAllStudentID();
		$this->view->stu_name = $db->getAllStudentName();
		
		
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
					'study_year'	=> "",
					'grade' 		=> "",
					'session' 		=> "",
					'room'=>0,
					'degree'=>0,
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
			);
		}
	
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db= new Allreport_Model_DbTable_DbRptGroupStudentChangeGroup();
		$this->view->rs = $db->getAllStu($search);
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
	
}

