<?php
class Registrar_CustomerController extends Zend_Controller_Action {	
	
 public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	function indexAction(){
		try{
			$db = new Registrar_Model_DbTable_DbCustomer();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => '-1',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$rs_rows= $db->getAllCustomer($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("CUSTOMER_NAME","SEX","TEL","EMAIL","USER","STATUS");
			$link=array(
					'module'=>'registrar','controller'=>'customer','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('stu_khname'=>$link,'sex'=>$link,'tel'=>$link,));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form = new Registrar_Form_FrmSearchexpense();
    	$frm = $form->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
	}
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel = new Registrar_Model_DbTable_DbCustomer();
    			$_dbmodel->addCustomer($_data);
    			if(!empty($_data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/customer");
    			}else{
    				Application_Form_FrmMessage::message("INSERT_SUCCESS");
    			}
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
		
		$frm_cal = new Registrar_Form_FrmCustomer();
		$myform = $frm_cal -> FrmAddCustomer();
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm = $myform;
    }
    public function editAction()
    {
    	$_dbmodel = new Registrar_Model_DbTable_DbCustomer();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel->addCustomer($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/registrar/customer");
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$id=$this->getRequest()->getParam('id');
    	$row = $_dbmodel->getCustomerById($id);
    	$this->view->row = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/registrar/customer");
			exit();
		}
		$frm_cal = new Registrar_Form_FrmCustomer();
		$myform = $frm_cal -> FrmAddCustomer($row);
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm = $myform;
    }
}