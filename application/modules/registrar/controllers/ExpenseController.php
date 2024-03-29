<?php
class Registrar_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	defined('ENABLE_DATE_PAYMENT') || define('ENABLE_DATE_PAYMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('payment_date'));
    	 
    }
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"payment_type"=>-1,
    					"status"=>-1,
    					"branch_id"=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","RECEIPT_NO","INVOICE","PAYMENT_METHOD","BANK_NAME","TOTAL_EXPENSE","FOR_DATE","RECEIVER","EXPENSE_TITLE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'bank_name'=>$link,'payment_type'=>$link,'invoice'=>$link,'external_invoice'=>$link,'total_amount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
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
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbExpense();				
			try {
				$db->addExpense($data);
				if(!empty($data['savenew'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/expense/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/expense");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    	
    	$db = new Registrar_Model_DbTable_DbCateExpense();
    	$this->view->parent = $db->getParentCateExpense();
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->officailExpensereceipt = $frmpopup->getExpenseReceipt();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data['id'] = $id;
				$db = new Registrar_Model_DbTable_DbExpense();	
				$db->updatExpense($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/registrar/expense");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		$this->view->rows = $db->getexpenseDetailbyid($id);
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    	
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    	 
    	$db = new Registrar_Model_DbTable_DbCateExpense();
    	$this->view->parent = $db->getParentCateExpense();
    	 
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->officailExpensereceipt = $frmpopup->getExpenseReceipt();
    }
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbIncome();
    		$invoice = $db->getReceiptNumber($data['branch_id'],2);
    		print_r(Zend_Json::encode($invoice));
    		exit();
    	}
    }
    function addCateExpenseAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbExpense();
    		$id = $db->addCateExpense($data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
}