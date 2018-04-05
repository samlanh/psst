<?php
class Accounting_StartdateenddateController extends Zend_Controller_Action {
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
				);
    		}
    		
    		$this->view->search = $search;
    		
			$db = new Accounting_Model_DbTable_DbStartdateEnddate();
			$rs_rows = $db->getStartDateEndDate($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("START_DATE","END_DATE","NOTE","CREATE_DATE","USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'startdateenddate','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('start_date'=>$link,'end_date'=>$link ));
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    public function addAction()
    {	
    	$db = new Accounting_Model_DbTable_DbStartdateEnddate();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addStartdateEnddate($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/startdateenddate");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/startdateenddate/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    }
	public function editAction(){
		$db = new Accounting_Model_DbTable_DbStartdateEnddate();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editStartdateEnddate($data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/startdateenddate");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	
    	$this->view->row = $db->getAllStartDateEndDate();
    	
    	
	}
	
}
