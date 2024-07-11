<?php
class Accounting_UpdatestudentdiscountController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
    {
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
	    				'title' 			=> '',
	    				'academic_year' 	=> '',
	    				'branch_id'			=>'',
    					'type_study'		=>-1,
    					'school_option'		=>-1,
    					'is_finished_search' => '',
    					'status' 			=>-1,
    				);
    		}
    		$db = new Accounting_Model_DbTable_DbDiscountSetting();
    		//$rs_rows= $db->getAllTuitionFee($search);
			$rs_rows= array();
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TYPE_STUDY","IS_MULTY_STUDY","TYPE","AMOUNT_STUDENT","School Option","CREATED_DATE","PROCESS_TYPE","BY_USER","STATUS");
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array());
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}    	
    	$this->view->adv_search = $search;
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $_db->getAllBranch();    	
    	$frm = new Accounting_Form_FrmFee();
    	$frm->FrmTutionfee();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_fee = $frm;
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
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
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				if(isset($_data['save_close'])){
					$this->_redirect('/accounting/updatestudentdiscount');
				}else{
					$this->_redirect('/accounting/updatestudentdiscount/add');
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