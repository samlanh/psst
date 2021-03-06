<?php

class Registrar_InternalincomeController extends Zend_Controller_Action
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
    		$db = new Registrar_Model_DbTable_DbInternalincomeincome();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"currency_type"=>-1,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$user_type=$_db->getUserType();
    		
			$rs_rows= $db->getAllIncome($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("INCOME_TITLE","RECEIVER","RECEIPT_NO","TOTAL_INCOME","NOTE","PAID_DATE","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'internalincome','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('cate_name'=>$link,'title'=>$link,'invoice'=>$link,'payment_method'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
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
			$db = new Registrar_Model_DbTable_DbInternalincomeincome();				
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/internalincome");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/internalincome/add");
				}				
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/internalincome/add");
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
//     	$db = new Registrar_Model_DbTable_DbInternalincomeincome();
//     	$payment_method = $db->getPaymentMethod(8); // 8 = rms_view type
//     	$this->view->payment_method = $payment_method;
    	
//     	$cate_income = $db->getCateIncome();
//     	array_unshift($cate_income, array('id'=>'-1','name'=>$this->tr->translate("ADD_NEW")));
//     	array_unshift($cate_income, array('id'=>'0','name'=>$this->tr->translate("SELECT_CATEGORY")));
//     	$this->view->cate_income = $cate_income;
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Registrar_Model_DbTable_DbInternalincomeincome();				
			try {
				$db->updateIncome($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/internalincome");		
			} catch (Exception $e) {
				$this->view->msg = $this->tr->translate("EDIT_FAIL");
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbInternalincomeincome();
		$row  = $db->getIncomeById($id);
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$user_type_id = $session_user->level;
		$payment_date = date("Y-m-d",strtotime($row['date']));
		$current_date = date("Y-m-d");
		if($user_type_id!=1 AND $current_date>$payment_date){
			Application_Form_FrmMessage::Sucessfull("you data is more then a day.so can not edit",'/registrar/income');
		}
		
		$this->view->rs = $row;
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
	    	$db = new Registrar_Model_DbTable_DbRegister();
	    	$receipt = $db->getRecieptNo();
	    	//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
	    	print_r(Zend_Json::encode($receipt));
	    	exit();
    	}
    }
    function addCateIncomeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbInternalincomeincome();
    		$cate_income = $db->addNewCateIncome($data);
    		print_r(Zend_Json::encode($cate_income));
    		exit();
    	}
    }
}


