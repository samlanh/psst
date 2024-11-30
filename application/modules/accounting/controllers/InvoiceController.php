<?php
class Accounting_InvoiceController extends Zend_Controller_Action {
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
					'search'=>'',
    				'branch_id'=>'',
					'studentId' => '',
					'group'=>'',
					'degree'=>'',
					'grade'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
    		}
			$db = new Accounting_Model_DbTable_Dbinvoice();
			$rs_rows = $db->getinvoice($search);
			$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_ENGLISH","SEX",
			"ACADEMIC_YEAR","INVOICE_NO","INVOICE_DATE","EXPIRED_DATE","REMARK","TOTAL_AMOUNT","INPUT_DATE","USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'invoice','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch'=>$link,'stu_code'=>$link,'stu_khname'=>$link,'invoice_date'=>$link, ));
			
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view->search=$search;
		$form= new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->frm=$form;
	}
    public function addAction()
    {	
    	$db = new Accounting_Model_DbTable_Dbinvoice();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addinviceaccount($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/invoice");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/invoice/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
		
		
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		$this->view->branch = $model->getAllBranch();
		$this->view->acadimicYear = $model->getAllAcademicYear();
    }
	public function editAction(){
		$db = new Accounting_Model_DbTable_Dbinvoice();
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editinvice($data , $id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/invoice");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$rs = $db->getinvoiceByid($id);
    	if(empty($rs)){
    		Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/invoice");
    	}
    	$this->view->invoice = $rs;
    	$branch_id =  $rs['branch_id'];
    	$rs=$this->view->invoice_service = $db->getinvoiceservice($id);
    	
    	$data['study_year'] = empty($data['study_year'])?null:$data['study_year'];
    	
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		$this->view->branch =  $model->getAllBranch();
		$this->view->acadimicYear = $model->getAllAcademicYear();

		$db = new Global_Model_DbTable_DbTerm();
		$param = array(
			'branch_id'=>$branch_id,
			'study_year'=>$data['study_year'],
			'option'=>1,
		);
		$this->view->rs_term = $db->getTermStudyInterm($param);
		
	}
	function getitemsdetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_Dbinvoice();
			$student_id = empty($data['student_id'])?null:$data['student_id'];
			
			$param= array(
				'itemsType'=>$data['items_type'],
				'studentId'=>$student_id,
			);
			
			$grade = $db->getAllItemDetail($param);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	function getInvoicenumberAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_Dbinvoice();
			$grade = $db->getvCode($data['branch_id']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
}
