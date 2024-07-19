<?php
class Accounting_UpdatestudentdiscountController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/updatestudentdiscount';
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try {
			if ($this->getRequest()->isPost()) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'title' => '',
					'academic_year' => '0',
					'branch' => '',
					'studentId' => '',
					'discountId' => '',
					'discountFor' => '0',
					'discountPeriod' => '0',
					'status_search' => -1
				);
			}
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$rs_rows = $db->getAllStudentDiscount($search);

			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH", "ACADEMIC_YEAR", "TITLE",  "DISCOUNT_TYPE", "DIS_MAX","AMOUNT_STU_USED","AMOUNT_STOP_USED", "DISCOUNT_PERIOD", "BY_USER", "CREATE_DATE", "STATUS");
		
			$this->view->list = $list->getCheckList(
				0,
				$collumns,
				$rs_rows,
				array()
			);
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$this->view->adv_search = $search;
		$frm = new Global_Form_FrmSearchMajor();
		$frms = $frm->FrmsearchDiscount();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->form_search = $frms;

		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		$this->view->discount = $disc;
	}
    
	function addAction(){
		$db = new Accounting_Model_DbTable_DbDiscountSetting();
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = $_data;
			//	print_r($search);exit();
				$rs =$db->getSearchStudentbyDiscount($search);
				$this->view->rs = $rs;
			}else{
				$search = array(
					'adv_search' => '',
					'branch_id' => '',
					'academic_year' => '',
					'academicYearEnroll' => '',
					'degree' => '',
					'grade' => '',
					'discountSettengId'=> '',
				);
			}
			$this->view->search=$search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function submitAction(){
		
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$db = new Accounting_Model_DbTable_DbDiscountSetting();
				$db->updateStudentDiscount($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
					//$this->_redirect('/accounting/updatestudentdiscount');
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
					//$this->_redirect('/accounting/updatestudentdiscount/add');
				}
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	function getdiscountAction(){//year for study only
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$result = $db->getDiscountSetting($data);
			if(!empty($data['selectOption'])){
				array_unshift($result, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_DISCOUNT")));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	function getdiscountinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$string = empty($data['string'])?null:$data['string'];
			$data=$db->getDiscountInforByID($data['discountSettengId'],$string);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}