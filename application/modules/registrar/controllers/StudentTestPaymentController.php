<?php
class Registrar_StudenttestpaymentController extends Zend_Controller_Action {
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
    		$db = new Registrar_Model_DbTable_DbStudentTestPayment();
    		    		if($this->getRequest()->isPost()){
    		    			$search=$this->getRequest()->getPost();
    		    		}
    		    		else{
    		    			$search = array(
    		    					'adv_search' => '',
    		    					'degree' => '',
    		    					'branch_id'=>0,
    		    					'user'=>'',
    		    					'start_date'=> date('Y-m-d'),
    		    					'end_date'=>date('Y-m-d'));
    		    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudentTestPayment($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getGernder($rs_rows, BASE_URL );
    		$list = new Application_Form_Frmtable();
    		$collumns = array("SERIAL","RECEIPT_NO","STUDENT_NAME","SEX","PHONE","DEGREE","PRICE","PAID_DATE","USER");
    		$link=array('module'=>'registrar','controller'=>'studenttestpayment','action'=>'edit',);
    		
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('serial'=>$link,'receipt_no'=>$link,'name'=>$link));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	$data = new Registrar_Model_DbTable_DbStudentTestPayment();
    	$db=$this->view->rows_degree=$data->getAllDegree();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction(){
	      if($this->getRequest()->isPost()){
		      	$_data = $this->getRequest()->getPost();
		      	try {
		      		$db = new Registrar_Model_DbTable_DbStudentTestPayment();
		      		$db->addRegister($_data);
		      		if(isset($_data['save_new'])){
		      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
		      		}else{
		      			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . '/studenttestpayment/index');
		      		}
		      	} catch (Exception $e) {
		      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
		      		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		      	}
	      }
	      $db = new Registrar_Model_DbTable_DbStudentTestPayment();
	      $this->view->all_student_test = $db->getAllStudentTested(1);
	      $this->view->degree = $db->getAllDegree();
	      
	      $key = new Application_Model_DbTable_DbKeycode();
	      $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	      
	      $_db = new Application_Form_FrmGlobal();
	      $this->view->header = $_db->getHeaderReceipt();
    }
    
    
    public function editAction(){
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbStudentTestPayment();
    			$db->updateRegister($_data,$id);
//     			if(isset($_data['save_new'])){
//     				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . '/studenttestpayment/index');
//     			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL . '/studenttestpayment/index');
//     			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			echo $e->getMessage();exit();
    			
    		}
    	}
        
    	$db = new Registrar_Model_DbTable_DbStudentTestPayment();
	    $this->view->all_student_test = $db->getAllStudentTested(1);
	    $this->view->degree = $db->getAllDegree();
	    
	    $this->view->row = $db->getStudentTestPaymentById($id);
	    
	    $key = new Application_Model_DbTable_DbKeycode();
	    $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	    
	    $_db = new Application_Form_FrmGlobal();
	    $this->view->header = $_db->getHeaderReceipt();
    }
    
	function getStudentTestInfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbStudentTestPayment();
			$stu_info = $db->getStudentTestInfo($data['stu_test_id']);
			print_r(Zend_Json::encode($stu_info));
			exit();
		}
	}
}