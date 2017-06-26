<?php
class Foundation_SuspendserviceController extends Zend_Controller_Action {
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
				$_data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' => $_data['adv_search'],
				);
			}
			else{
				$search=array(
						'txtsearch' =>'',
				);
			}
			
			$db =  new Foundation_Model_DbTable_DbSuspendservice();
			$rs = $db->getStudentSuspendService($search);
				$list = new Application_Form_Frmtable();
				$collumns = array("STUDENT_ID","NAME_KH","NAME_EN","SEX","CREATED_DATE");
				$link=array(
						'module'=>'foundation','controller'=>'suspendservice','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(0, $collumns, $rs,array('suspend_no'=>$link,'code'=>$link,'kh_name'=>$link,'en_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$this->view->rs =$search;
		
	}
public function addAction(){
	if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
		try{
				$db = new Foundation_Model_DbTable_DbSuspendservice();
				$row = $db->addSuspendservice($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/suspendservice");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/suspendservice/add");
				}
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$frm = new Foundation_Form_FrmServicesuspend();
		$frm_servicesuspend=$frm->FrmServiceSuspend();
		Application_Model_Decorator::removeAllDecorator($frm_servicesuspend);
		$this->view->frm_servicesuspend = $frm_servicesuspend;
		 
		$db = new Foundation_Model_DbTable_DbSuspendservice();
		$this->view->stu_id = $db->getAllStudentCode();
		
		 
		$_model = new Application_Model_GlobalClass();
		$this->view->all_service = $_model->getAllServiceItemOption(2);
	
}
public function editAction(){
	$id=$this->getRequest()->getParam("id");
	if($this->getRequest()->isPost())
	{
		$_data = $this->getRequest()->getPost();
		//print_r($_data);exit();
		try{
			$_db = new Foundation_Model_DbTable_DbSuspendservice();
			$row = $_db->editSuspendService($_data);
			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/suspendservice/index");
		}catch(Exception $e){
			Application_Form_FrmMessage::message("EDIT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();exit();
		}
	}
	
	$frm = new Foundation_Form_FrmServicesuspend();
	$frm_servicesuspend=$frm->FrmServiceSuspend();
	Application_Model_Decorator::removeAllDecorator($frm_servicesuspend);
	$this->view->frm_servicesuspend = $frm_servicesuspend;
	
	$_db = new Foundation_Model_DbTable_DbSuspendservice();
	
	$this->view->stu_code = $_db->getAllStudentCode();
	$this->view->stu_name = $_db->getAllStudentName();
	
	$this->view->id = $_db->getStudentSuspendServiceByID($id);
	
	$this->view->service = $_db->getSuspendServiceByID($id);
	
	$_model = new Application_Model_GlobalClass();
	$this->view->all_service = $_model->getAllServiceItemOption(2);
}

function getStudentAction(){
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		$db = new Foundation_Model_DbTable_DbSuspendservice();
		$studentinfo = $db->getAllStudentInfo($data['studentid']);
		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
		print_r(Zend_Json::encode($studentinfo));
		exit();
	}
}

function getStudentIdAction(){
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		$db = new Foundation_Model_DbTable_DbSuspendservice();
		$stu_id = $db->getStudentID($data['study_year']);
		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
		print_r(Zend_Json::encode($stu_id));
		exit();
	}
}

function getStudentNameAction(){
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		$db = new Foundation_Model_DbTable_DbSuspendservice();
		$stu_name = $db->getStudentName($data['study_year']);
		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
		print_r(Zend_Json::encode($stu_name));
		exit();
	}
}



















}