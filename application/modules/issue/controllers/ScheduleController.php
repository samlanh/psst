<?php
class Issue_ScheduleController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		defined('STUDY_DAY_SETTING') || define('STUDY_DAY_SETTING', Setting_Model_DbTable_DbGeneral::geValueByKeyName('studyday_schedule'));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'branch_id' => '',
						'academic_year' => '',
						'group' => '',
						'start_date'=>date("Y-m-d"),
						'end_date' => date("Y-m-d")
						);
			}
			$db = new Issue_Model_DbTable_DbSchedule();
			$rs_rows= $db->getAllScheduleGroup($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDY_YEAR","GROUP","SCHEDULE_SETTING","DATE","USER");
			$link=array(
					'module'=>'issue','controller'=>'schedule','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link,'years'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Issue_Model_DbTable_DbSchedule();
				$db->addScheduleGroup($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/schedule");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/schedule/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAILE");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$frm = new Issue_Form_FrmSchedule();
		$frm->FrmAddSchedule(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
		
		$_db = new Issue_Model_DbTable_DbSchedule();
		$teacher = $_db->getAllTeacher();
		$this->view->teacher = $teacher;
	}
	
	function editAction(){
		$_db= new Issue_Model_DbTable_DbSchedule();
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$_db->updateScheduleGroup($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", "/issue/schedule");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAILE");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row =$_db->getScheduleGroupById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/schedule");
			exit();
		}
		$this->view->row = $row;
		
		$frm = new Issue_Form_FrmSchedule();
		$frm->FrmAddSchedule($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	
	
		$_db = new Issue_Model_DbTable_DbSchedule();
		$teacher = $_db->getAllTeacher();
		$this->view->teacher = $teacher;
	}
	function copyAction(){
		$_db= new Issue_Model_DbTable_DbSchedule();
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$_db->addScheduleGroup($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/schedule");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAILE");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row =$_db->getScheduleGroupById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/schedule");
			exit();
		}
		$this->view->row = $row;
	
		$frm = new Issue_Form_FrmSchedule();
		$frm->FrmAddSchedule($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	
	
		$_db = new Issue_Model_DbTable_DbSchedule();
		$teacher = $_db->getAllTeacher();
		$this->view->teacher = $teacher;
	}
	function checkingAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
	
			$group_id = empty($data['group_id'])?"":$data['group_id'];
			$id = empty($data['id'])?"":$data['id'];
			$arr  = array(
					'group_id'=>$group_id,
					'id'=>$id,
			);
			$_dbmodel = new Issue_Model_DbTable_DbSchedule();
			$result=$_dbmodel->checking($arr);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
    
}

