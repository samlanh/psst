<?php
class Accounting_TransfercreditController extends Zend_Controller_Action {
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	$db = new Accounting_Model_DbTable_DbTransfercredit();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'status_search' => -1,
    			);
    		}
    		$rs_rows= $db->getAllTransfer($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","STUDENT_CODE","INFORS_TO","STUDENT_CODE","INFORS_RECEIVE","TOTAL_AMOUNT","REASON_TO","REASON_RESIVE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'transfer','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('branch_namesfs'=>$link,'stu_idsdf'=>$link,'student_me'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->frm_search=$form;
    	
    }
    public function transferAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data['id'] = $id;
    		try {
    			$sms="INSERT_SUCCESS";
    			$db = new Accounting_Model_DbTable_DbTransfercredit();
    			$_transfer = $db->transfercreditMemo($data);
    			if($_transfer==-1){
    				$sms = "RECORD_EXIST";
    			}
    			Application_Form_FrmMessage::Sucessfull($sms, "/accounting/transfercredit");
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	}
    
    	$id = $this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbCreditmemo();
    	$row  = $db->getCreditmemobyid($id);
    	$this->view->row = $row;
    
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemo($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
// 	public function editAction(){
// 		$db = new Accounting_Model_DbTable_DbTransferstock();
// 		if($this->getRequest()->isPost()){
// 			try{
// 				$data = $this->getRequest()->getPost();
// 				$db->updateTransferStock($data);
// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/accounting/transfer");
// 			}catch(Exception $e){
// 				Application_Form_FrmMessage::message("APPLICATION_ERROR");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
// 		$id=$this->getRequest()->getParam("id");
// 		$this->view->rs = $db->getTransferById($id);
// 		$this->view->rsdetail = $db->getTransferByIdDetail($id);
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->rsbranch = $db->getAllBranchName();
// 		$this->view->rsproduct = $db->getallProductName();
// 	}
}