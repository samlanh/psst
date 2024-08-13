<?php
class Issue_StudentattendanceController extends Zend_Controller_Action {
	
	const SETTING_INPUT_ATTENDANCE = 2; // 1=fullListStudentGroup
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			
			$this->view->search=$search;
			$rs_rows = $db->getAllAttendence($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","GROUP","GRADE","SEMESTER","ATTENDANCE_DATE","amtStuAtt","USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'studentattendance','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'group_name'=>$link,'academy'=>$link,'degree'=>$link,'grade'=>$link,'semester'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Issue_Model_DbTable_DbStudentAttendance();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $db->addStudentAttendece($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentattendance/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentattendance");
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		
		$this->view->branch_id=$db_global->getAllBranch();
		$this->view->settingInputAttendance = self::SETTING_INPUT_ATTENDANCE;
		
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Issue_Model_DbTable_DbStudentAttendance();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $_model->updateStudentAttendence($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/studentattendance");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$result = $_model->getAttendencetByID($id);
		if (empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/studentattendance");
		}
		$this->view->row=$result;
		if ($result['is_pass'] > 0) {
			Application_Form_FrmMessage::Sucessfull("UNABLE_TO_EDIT_COMPETED_CLASS", "/issue/studentattendance");
		}
		$dbGb=new Application_Model_DbTable_DbGlobal();
		$settingInputAttendance = self::SETTING_INPUT_ATTENDANCE;
		if (!empty($result)){
			if($settingInputAttendance !=1){
				$condiction = array(
					"attendanceId" => $id
				);
				$this->view->attDeatil= $_model->getStudentAttendanceDetail($condiction);
				$condictionSch = array(
					"group" => empty($result["group_id"]) ? 0 : $result["group_id"],
					"attendenceDate" => empty($result["date_attendence"]) ? date("Y-m-d") : date("Y-m-d",strtotime($result["date_attendence"]))
				);
				$this->view->scheduleTime =$_model->getScheduleTimeStudty($condictionSch);
			}
			$this->view->allstudentBygroup = $dbGb->getAllStudentByGroupForEdit($result['group_id']);
		}
		$this->view->settingInputAttendance = $settingInputAttendance;
		$this->view->branch_id=$dbGb->getAllBranch();
		
		
		
			
	}
	function getSubjectAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$_dbmodel->getSujectById($data['parent_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentAction(){//May not use
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudent($data['year'],$data['grade'],$data['session']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}

	
	function getStudentBygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudentByGroup($data['group'],$data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getsubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$result =$db->getSubjectBygroup($data['group']);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	
	function getstudentbygrouphtmlAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudentByGroupHTML($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getGroupscheduletimeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->getScheduleTimeStudty($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getStudentInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudentInfoWithPermissionRequest($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getCheckDuplicateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$data=$db->checkingDuplicateIssueAttendance($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
}

