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
    					'adv_search' 	=> '',
    					'branch_id' 	=> '',
    					'status'		 => -1,
    			);
    		}
    		$rs_rows= $db->getAllTransfer($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_CODE","INFORS_TO","STUDENT_CODE","INFORS_RECEIVE","TOTAL_AMOUNT","REASON_TO","REASON_RESIVE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'transfercredit','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('stu_name'=>$link,'sfwe'=>$link,'stu_idname'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->frm_search=$form;
    	
    }
	 public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Accounting_Model_DbTable_DbCreditmemo();				
			try {
				$sms = "INSERT_SUCCESS";
				$_transfer = $db->addCreditmemo($data);
				if($_transfer==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/accounting/creditmemo");
				}else{
					Application_Form_FrmMessage::message($sms);
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemo();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
    public function editAction()
 	{
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data['id'] = $id;
    		try {
    			$db = new Accounting_Model_DbTable_DbTransfercredit();
    			$_transfer = $db->updatefercreditMemo($data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/accounting/transfercredit");
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	}
    	$id = $this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbTransfercredit();
    	$row  = $db->getTransferbyid($id);
			if(empty($row)){
				Application_Form_FrmMessage::redirectUrl("/accounting/transfercredit");
			}
    	$this->view->row = $row;
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->all_student_name = $db->getAllStudent();
    	
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemo($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
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
    	if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD","/accounting/creditmemo");
    		exit();
    	}
    	$db = new Accounting_Model_DbTable_DbCreditmemo();
    	$row  = $db->getCreditmemobyid($id);
    	$this->view->row = $row;
    
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemotran($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
}