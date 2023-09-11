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
	    	$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","GROUP","AMOUNT_DAY","SESSION_TYPE","PHONE","FROM_DATE",
	    			"TO_DATE","REASON","REQUEST_STATUS");
	    	$link=array(
	    			'module'=>'home','controller'=>'studentpermission','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'studentCode'=>$link,'StudentName'=>$link,'GroupName'=>$link));
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
    		try{
				$_data = $this->getRequest()->getPost();
    			$row = $db->updatePermission($_data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
    		}
    	}
		$rowdetail = $db->getAttendacenDetailById($id);
		$this->view->rowdetail = $rowdetail;
    	if($rowdetail['isCompleted']==1){
    		Application_Form_FrmMessage::Sucessfull("Can't edit",self::REDIRECT_URL);
    	}
		$row = $db->getRequestById($id);
    	$this->view->row = $row;
		$frm = new Home_Form_FrmStudentRequest();
    	$frm->FrmStudentRequest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
     }
	 public function addAction()
	 {
	 }
}