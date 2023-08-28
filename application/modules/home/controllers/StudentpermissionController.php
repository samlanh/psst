<?php

class Home_StudentpermissionController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home/studentpermission';
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
	  		if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    			'advance_search' => "",
	    			'branch_search'  => "",
	    			'session_type'   =>'',
	    			'request_status' =>'',
	    			'start_date'     => date('Y-m-d'),
	    			'end_date'       => date('Y-m-d'),
	    		);
	    	}
	    	$this->view->search = $search;
	    	$db = new Home_Model_DbTable_DbStudentRequestPermission();
	    	$rs_rows = $db->getAllStudentRequest($search);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","STUDENT_NAMEKHMER","GROUP","AMOUNT_DAY","SESSION_TYPE","PHONE","FROM_DATE",
	    			"TO_DATE","REASON","REQUEST_STATUS");
	    	$link=array(
	    			'module'=>'home','controller'=>'studentpermission','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'StudentName'=>$link,'GroupName'=>$link));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::message("Application Error");
    	}
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$pevconcer = $_dbgb->getViewByType(22);
    	$this->view->prev_concern = $pevconcer;
    	
    	$frm = new Home_Form_FrmStudentRequest();
    	$frm->FrmStudentRequest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
  
    public function editAction()
    {
    	$db = new Home_Model_DbTable_DbStudentRequestPermission();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try{
    			$row = $db->updatePermission($_data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getRequestById($id);
    	if ($row['requestStatus']!=0){
    		Application_Form_FrmMessage::Sucessfull("Can't edit",self::REDIRECT_URL);
    	}
    	$this->view->row = $row;
		$frm = new Home_Form_FrmStudentRequest();
    	$frm->FrmStudentRequest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
     }

	 public function addAction()
	 {
		 // if($this->getRequest()->isPost()){
		 // 	$_data = $this->getRequest()->getPost();
		 // 	try{
		 // 		$db = new Home_Model_DbTable_DbCRM();
		 // 		$row = $db->AddCRM($_data);
		 
		 // 		if(isset($_data['save_close'])){
		 // 			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
		 // 		}else{
		 // 			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
		 // 		}
		 // 		Application_Form_FrmMessage::message("INSERT_SUCCESS");
		 // 	}catch(Exception $e){
		 // 		Application_Form_FrmMessage::message("INSERT_FAIL");
		 // 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		 // 	}
		 // }
		 // $_dbgb = new Application_Model_DbTable_DbGlobal();
		 // $pevconcer = $_dbgb->getViewByType(22);
		 // $this->view->prev_concern = $pevconcer;
		 
		 // $degree = $_dbgb->getAllItems(1);
		 // array_unshift($degree, array ( 'id' => '','name' =>$this->tr->translate("PLEASE_SELECT")));
		 // $this->view->degree = $degree;
		 
		 // $frm = new Home_Form_FrmCrm();
		 // $frm->FrmAddCRM(null);
		 // Application_Model_Decorator::removeAllDecorator($frm);
		 // $this->view->frm_crm = $frm;
		 
		 // $_db = new Application_Model_DbTable_DbGlobal();
		 // $row = $_db->getAllKnowBy(); // degree language
		 // array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		 // array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		 // $this->view->know_by = $row;
	 }

}