<?php
class Accounting_SuspendserviceController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search=array(
					'adv_search' 	=>'',
					'branch_id' 	=>-1,
					'studentId' 	=>'',
					'start_date' 	=>date("Y-m-d"),
					'end_date' 		=>date("Y-m-d"),
				);
			}
			$db =  new Accounting_Model_DbTable_DbSuspendservice();
			$rs = $db->getStudentSuspendService($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_ENGLISH","CREATE_DATE","USER","STATUS");
			
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array());
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		$this->view->rs =$search;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db = new Accounting_Model_DbTable_DbSuspendservice();
				$row = $db->addSuspendservice($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/accounting/suspendservice/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/accounting/suspendservice/index");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		 
	}
	public function editAction(){
		Application_Form_FrmMessage::redirector('/accounting/suspendservice/index');
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			$_data = $this->getRequest()->getPost();
			try{
				$_db = new Accounting_Model_DbTable_DbSuspendservice();
				$row = $_db->editSuspendService($_data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/suspendservice/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				
			}
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $_db->getAllBranch();
		
		$_db = new Accounting_Model_DbTable_DbSuspendservice();
		$this->view->row = $_db->getSuspendServiceByID($id);
		$this->view->row_detail = $_db->getSuspendServiceDetailByID($id);
	}
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbSuspendservice();
			$studentinfo = $db->getAllStudentInfo($data['studentid']);
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($studentinfo));
			exit();
		}
	}
	
	function getStudentIdAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbSuspendservice();
			$stu_id = $db->getStudentID($data['study_year']);
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($stu_id));
			exit();
		}
	}
	
	function getStudentNameAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbSuspendservice();
			$stu_name = $db->getStudentName($data['study_year']);
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($stu_name));
			exit();
		}
	}
	
	function getserviceAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbSuspendservice();
			$studentinfo = $db->getAllSerivesByIdInsuspend($data['student']);
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($studentinfo));
			exit();
		}
	}
}