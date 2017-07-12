<?php
class Accounting_TransferController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$db = new Accounting_Model_DbTable_DbTransferstock();
    		$rs_rows= $db->getAllTransfer($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TRANSFER_NUMBER","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","NOTE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'transfer','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('transfer_no'=>$link,'transfer_date'=>$link,'fromlocation'=>$link,'tolocation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
	public function addAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addTransferStock($data);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $db->getAllBranchName();
		$this->view->rsproduct = $db->getallProductName();
	}
	public function editAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->updateTransferStock($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", "/accounting/transfer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$this->view->rs = $db->getTransferById($id);
		$this->view->rsdetail = $db->getTransferByIdDetail($id);
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $db->getAllBranchName();
		$this->view->rsproduct = $db->getallProductName();
	}
}