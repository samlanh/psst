<?php
class Issue_StudentrefillscoreController extends Zend_Controller_Action {
	
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
			$rs_rows = array();
			//$rs_rows = $db->getAllAttendence($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","GROUP","ACADEMIC_YEAR","DEGREE","GRADE","SEMESTER","ROOM","SESSION","ATTENDANCE_DATE","USER","STATUS");
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
	public	function addAction()
	{
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset = $key->getKeyCodeMiniInv(TRUE);
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill(); //by subject
			try {
				 $db->addStudentScore($_data);
				if (isset($_data['save_new'])) {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/studentrefillscore/add");
				} else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/studentrefillscore");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->branch_id=$db_global->getAllBranch();
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
			exit();
		}
		$this->view->row=$result;
		
		$settingInputAttendance = self::SETTING_INPUT_ATTENDANCE;
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
		$this->view->settingInputAttendance = $settingInputAttendance;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		
		$this->view->branch_id=$db_global->getAllBranch();
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->allstudentBygroup = $db->getAllStudentByGroupForEdit($result['group_id']);
		
		
			
	}

	function getscoregroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getScoreResultByGroup($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}

	function getscoreinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getScoreInforByID($data['scoreId']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentscoreAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data = $db->getStudentScoreById($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentrefillAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getStudentNoScore($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
}

