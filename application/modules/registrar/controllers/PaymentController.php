<?php
class Registrar_PaymentController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/registrar';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction(){
    	try{
    		$db = new Registrar_Model_DbTable_DbPayment();
    		$param = $this->getRequest()->getParams();
			if(isset($param['search'])){
				$search=$param;
				
				$this->view->adv_search=$search;
				$rs_rows= $db->getListStudentForPayment($search);
				$this->view->rs=$rs_rows;
				
				$paginator = Zend_Paginator::factory($rs_rows);
				$paginator->setDefaultItemCountPerPage(35);
				$allItems = $paginator->getTotalItemCount();
				$countPages= $paginator->count();
				$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
				 
				if(isset($p))
				{
					$paginator->setCurrentPageNumber($p);
				} else $paginator->setCurrentPageNumber(1);
				
				$currentPage = $paginator->getCurrentPageNumber();
				 
				$this->view->row  = $paginator;
				$this->view->countItems = $allItems;
				$this->view->countPages = $countPages;
				$this->view->currentPage = $currentPage;
				
				if($currentPage == $countPages)
				{
					$this->view->nextPage = $countPages;
					$this->view->previousPage = $currentPage-1;
				}
				else if($currentPage == 1)
				{
					$this->view->nextPage = $currentPage+1;
					$this->view->previousPage = 1;
				}
				else {
					$this->view->nextPage = $currentPage+1;
					$this->view->previousPage = $currentPage-1;
				}
			
			} else{
				
			}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		
    	$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;

    }
    public function addAction(){
		
		$db = new Registrar_Model_DbTable_DbPayment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db->addRegister($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/payment");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/payment/add");
				}
				//Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$stuId=$this->getRequest()->getParam("id");
		$stuId = empty($stuId)?0:$stuId;
		$rsStudent = $db->getStudentForPaymentById($stuId);
		$this->view->rsStudent = $rsStudent;
		if(empty($rsStudent)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA_ON_THIS","/registrar/payment");
			exit();
		}
		
       $_db = new Application_Model_DbTable_DbGlobal();
       $this->view->exchange_rate = $_db->getExchangeRate();
      
       $this->view->rs_type = $_db->getAllItems();
       $this->view->rsdiscount = $_db->getAllDiscountName();
       $this->view->rs_paymenttype = $_db->getViewById(8,null);
	   
	   $key = new Application_Model_DbTable_DbKeycode();
	   $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	  
	   
	   $frmreceipt = new Application_Form_FrmGlobal();
	   $this->view->officailreceipt = $frmreceipt->getFormatReceipt();
	   
	   $dbclass = new Application_Model_GlobalClass();
	   $this->view->term_option = $dbclass->getAllPayMentTermOption();	   
    }
	public function addregistraAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbPayment();
    			$receipt_no = $db->addRegister($_data);
    			print_r(Zend_Json::encode($receipt_no));
    			exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
	function getReceiptNoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$branch_id = empty($data['branch_id'])?null:$data['branch_id'];
			$receipt = $db->getRecieptNo($branch_id);
			print_r(Zend_Json::encode($receipt));
			exit();
		}
	}
	
	
    
}