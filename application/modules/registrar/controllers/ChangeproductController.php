<?php
class Registrar_ChangeproductController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbChangeProduct();
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
    		
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$user_type=$_db->getUserType();
    		if($user_type!=1){
    			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/register/index');
    		}
    		
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
    		echo $e->getMessage();
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
			$db = new Registrar_Model_DbTable_DbChangeProduct();				
			try {
				$db->addExpense($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/expense");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();
			}
		}

		$db = new Registrar_Model_DbTable_DbChangeProduct();
    	$test = $this->view->all_product = $db->getAllProduct();
    	
    	$this->view->stu_code = $db->getAllStuCode();
    	$this->view->stu_name = $db->getAllStuName();
    	
    	//print_r($test);exit();
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Registrar_Model_DbTable_DbChangeProduct();				
			try {
				$db->updatExpense($data);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$user_type=$_db->getUserType();
		if($user_type!=1){
			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/expense');
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbChangeProduct();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		$this->view->rows = $db->getexpenseDetailbyid($id);
		
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;

    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    }
    function getProductPriceAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbChangeProduct();
    		$pro_price = $db->getProductPrice($data['pro_id']);
    		print_r(Zend_Json::encode($pro_price));
    		exit();
    	}
    }
}







