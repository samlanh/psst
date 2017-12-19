<?php
class Accounting_StartdateEnddateStuController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
					'search'=>"",
    				'stu_id'=>"",
    				'term_id'=>"",
				);
    		}
    		
    		$this->view->search = $search;
			$db = new Accounting_Model_DbTable_DbStartdateEnddateStu();
			$rs_rows = $db->getStartDateEndDate($search);
			$list = new Application_Form_Frmtable();
    		$collumns = array("STUDENT_NAME","PAYMENT_TERM","START_DATE","END_DATE","NOTE","CREATE_DATE","USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'startdateenddatestu','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('stu_name'=>$link,'term'=>$link,'start_date'=>$link,'end_date'=>$link ));
    		$this->view->all_stu = $db->getAllStudent();
    		$this->view->all_paymentterm = $db->getAllpaymentTerm();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    public function addAction()
    {	
    	$db = new Accounting_Model_DbTable_DbStartdateEnddateStu();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addStartdateEnddate($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/startdateenddatestu");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/startdateenddatestu/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/startdateenddatestu/add");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$this->view->all_stu = $db->getAllGerneralOldStudent();
    	$this->view->all_paymentterm = $db->getAllpaymentTerm();
    }
	public function editAction(){
		$db = new Accounting_Model_DbTable_DbStartdateEnddateStu();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editStartdateEnddate($data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/startdateenddatestu");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	
    	$this->view->row = $db->getAllStartDateEndDate();
    	$this->view->all_stu = $db->getAllGerneralOldStudent();
    	$this->view->all_paymentterm = $db->getAllpaymentTerm();
    	
    	
	}
	
}
