<?php
class Registrar_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","EXPENSE_TITLE","RECEIPT_NO","PAYMENT_METHOD","TOTAL_EXPENSE","NOTE","FOR_DATE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'title'=>$link,'invoice'=>$link));
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
// 		$_db = new Application_Model_DbTable_DbGlobal();
// 		$user_type=$_db->getUserType();
// 		if($user_type!=1){
// 			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/register/index');
// 		}
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    	
    	$_db = new Registrar_Model_DbTable_DbExpense();
    	$cate_expense = $_db->getAllCateExpense(5);
    	array_unshift($cate_expense, array('id'=>-1 , 'name'=>$this->tr->translate("ADD_NEW")));
    	$this->view->cate_expense = $cate_expense;
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Registrar_Model_DbTable_DbExpense();				
			try {
				$db->updatExpense($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
// 		$_db = new Application_Model_DbTable_DbGlobal();
// 		$user_type=$_db->getUserType();
// 		if($user_type!=1){
// 			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/expense');
// 		}
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		
		$session_user=new Zend_Session_Namespace('authstu');
		$user_type_id = $session_user->level;
		$payment_date = date("Y-m-d",strtotime($row['date']));
		$current_date = date("Y-m-d");
		if($user_type_id!=1 AND $current_date>$payment_date){
			Application_Form_FrmMessage::Sucessfull("you data is more then a day.so can not edit",'/registrar/expense');
		}
		
		$this->view->row = $row;
		$this->view->rows = $db->getexpenseDetailbyid($id);
		
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;

    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    	
    	$_db = new Registrar_Model_DbTable_DbExpense();
    	$cate_expense = $_db->getAllCateExpense(5);
    	array_unshift($cate_expense, array('id'=>-1 , 'name'=>$this->tr->translate("ADD_NEW")));
    	$this->view->cate_expense = $cate_expense;
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbIncome();
    		$receipt = $db->getReceiptNumber($data['branch_id'],2);
    		print_r(Zend_Json::encode($receipt));
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