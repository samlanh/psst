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
						'stu_code' => '',
						'stu_name' => '',
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
    		$collumns = array("STUDENT_ID","STUDENT_NAME","LAST_NAME","FIRST_NAME","SEX","INVOICE_DATE","INVOICE_NUM","INPUT_DATE","REMARK","AMOUNT","USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'invoice','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('stu_code'=>$link,'stu_khname'=>$link,'invoice_date'=>$link, ));
			
			$db = new Registrar_Model_DbTable_DbRegister();
			$this->view->all_student_name = $db->getAllGerneralOldStudentName();
			$this->view->all_student_code = $db->getAllGerneralOldStudent();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->frm = $form;
	}
    public function addAction()
    {	
    	$db = new Accounting_Model_DbTable_Dbinvoice();
		$this->view->vcode= $db-> getvCode();
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
		$db = new Registrar_Model_DbTable_DbRegister();
		$this->view->all_student_name = $db->getAllGerneralOldStudentName();
		$this->view->all_student_code = $db->getAllGerneralOldStudent();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
    }
	public function editAction(){
		$db = new Accounting_Model_DbTable_Dbinvoice();
		$id=$this->getRequest()->getParam('id');
		$this->view->invoice = $db->getinvoiceByid($id);
		$rs=$this->view->invoice_service = $db->getinvoiceservice($id);
		 
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
		$db = new Registrar_Model_DbTable_DbRegister();
// 		$this->view->all_service = $db->getAllService();
		$this->view->all_student_name = $db->getAllGerneralOldStudentName();
		$this->view->all_student_code = $db->getAllGerneralOldStudent();
		$_db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->all_grade =  $_db->getAllMajor();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
	}
	function getitemsdetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$grade = $db->getAllGradeStudy($data['items_type']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
}
