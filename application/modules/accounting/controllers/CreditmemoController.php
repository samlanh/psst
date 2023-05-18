<?php
class Accounting_CreditmemoController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/accounting/creditmemo';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbCreditmemo();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"	=>'',
    					"branch_id"		=>'',
    					"paid_transfer"	=>-1,
    					"status"		=>-1,
    					'start_date'	=> date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
    		
			$rs_rows= $db->getAllCreditmemo($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_CODE","STUDENT_NAME","TOTAL_AMOUNT","TOTAL_AMOUNT_AFTER","START_DATE","END_DATE","NOTE","PAID_TRANSFER","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'creditmemo','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'student_name'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
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
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $_db->getAllBranch();
		
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemoadd();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Accounting_Model_DbTable_DbCreditmemo();				
			try {
				$db->updatcreditMemo($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Accounting_Model_DbTable_DbCreditmemo();
		$row  = $db->getCreditmemobyid($id);
		$this->view->row = $row;
    	$pructis=new Accounting_Form_Frmcreditmemo();
    	$frm = $pructis->Frmcreditmemoadd($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
    
// <?php
// class Accounting_CreditmemoController extends Zend_Controller_Action
// {
// 	const REDIRECT_URL = '/accounting/creditmemo';
	
//     public function init()
//     {
//     	header('content-type: text/html; charset=utf8');
//     	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
//     }

//     public function indexAction()
//     {
//     	try{
//     		$db = new Accounting_Model_DbTable_DbCreditmemo();
//     		if($this->getRequest()->isPost()){
//     			$formdata=$this->getRequest()->getPost();
//     		}
//     		else{
//     			$formdata = array(
//     					"adv_search"=>'',
//     					"paid_status"=>'',
//     					"status"=>-1,
//     					'start_date'=> date('Y-m-d'),
//     					'end_date'=>date('Y-m-d'),
//     			);
//     		}
    		
//     		$this->view->adv_search = $formdata;
    		
// 			$rs_rows= $db->getAllCreditmemo($formdata);//call frome model
//     		$glClass = new Application_Model_GlobalClass();
//     		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
//     		$list = new Application_Form_Frmtable();
//     		$collumns = array("BRANCH","STUDENT_CODE","STUDENT_NAME","TOTAL_AMOUNT","TOTAL_AMOUNT_AFTER","FOR_DATE","NOTE","PAID_STATUS","BY_USER","STATUS");
//     		$link=array(
//     				'module'=>'accounting','controller'=>'creditmemo','action'=>'edit',
//     		);
//     		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'student_name'=>$link));
//     	}catch (Exception $e){
//     		Application_Form_FrmMessage::message("Application Error");
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     	}
// 		$frm = new Registrar_Form_FrmSearchexpense();
//     	$frm = $frm->AdvanceSearch();
//     	Application_Model_Decorator::removeAllDecorator($frm);
//     	$this->view->frm_search = $frm;
//     }
//     public function addAction()
//     {
//     	if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();	
// 			$db = new Accounting_Model_DbTable_DbCreditmemo();				
// 			try {
// 				$db->addCreditmemo($data);
// 				if(!empty($data['save_close'])){
// 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/creditmemo");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 				}				
// 			} catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
//     	$pructis=new Accounting_Form_Frmcreditmemo();
//     	$frm = $pructis->Frmcreditmemo();
//     	Application_Model_Decorator::removeAllDecorator($frm);
//     	$this->view->frm_credit=$frm;
//     }
 
//     public function editAction()
//     {
//     	$id = $this->getRequest()->getParam('id');
//     	if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();	
// 			$data['id'] = $id;
// 			$db = new Accounting_Model_DbTable_DbCreditmemo();				
// 			try {
// 				$db->updatcreditMemo($data);				
// 				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL);		
// 			} catch (Exception $e) {
// 				$this->view->msg = 'EDIT_FAIL';
// 			}
// 		}
		
// 		$id = $this->getRequest()->getParam('id');
// 		$db = new Accounting_Model_DbTable_DbCreditmemo();
// 		$row  = $db->getCreditmemobyid($id);
		
//     	$pructis=new Accounting_Form_Frmcreditmemo();
//     	$frm = $pructis->Frmcreditmemo($row);
//     	Application_Model_Decorator::removeAllDecorator($frm);
//     	$this->view->frm_credit=$frm;

//     	$db = new Application_Model_GlobalClass();
//     	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
//     }
}







