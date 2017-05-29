<?php

class Registrar_IncomeController extends Zend_Controller_Action
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
    		$db = new Registrar_Model_DbTable_DbIncome();
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
    		if($user_type!=1){
    			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/register/index');
    		}
    		
			$rs_rows= $db->getAllIncome($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","INCOME_TITLE","RECEIPT_NO","CURRENCY","TOTAL_INCOME","NOTE","FOR_DATE","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'income','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'title'=>$link,'invoice'=>$link));
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
			$db = new Registrar_Model_DbTable_DbIncome();				
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/income");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$user_type=$_db->getUserType();
		if($user_type!=1){
			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/register/index');
		}
		
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbIncome();				
			try {
				$db->updateIncome($data);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', "/registrar/income");		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$user_type=$_db->getUserType();
		if($user_type!=1){
			Application_Form_FrmMessage::Sucessfull(" You are not Admin !!! ", '/registrar/register/index');
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbIncome();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		
    	$pructis=new Registrar_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	
    }
    
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
	    	$db = new Registrar_Model_DbTable_DbIncome();
	    	$receipt = $db->getReceiptNumber($data['branch_id'],1);
	    	//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
	    	print_r(Zend_Json::encode($receipt));
	    	exit();
    	}
    }
    
    
    
    

}







