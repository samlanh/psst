<?php
class Registrar_StudentservicepaymentController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/registrar';
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		    		if($this->getRequest()->isPost()){
    		    			$search=$this->getRequest()->getPost();
    		    			//print_r($search);exit();
    						
    		    		}
    		    		else{
    		    			$search = array(
    		    					'adv_search' => '',
    		    					'year' => '',
    		    					'user' => '',
    		    					'start_date'=> date('Y-m-d'),
    		    					'end_date'=>date('Y-m-d'));
    		    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudenTServicePayment($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("STUDENT_ID","NAME","SEX","RECEIPT_NO","GRAND_TOTAL","DISCOUNT",
    				          "TOTAL_PAYMENT","MONEY_RECEIVED","BALANCE","RETURN","DATE_PAY","USER");
    		$link=array(
    				'module'=>'registrar','controller'=>'studentservicepayment','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('year'=>$link,'receipt_number'=>$link,'name'=>$link,'service_name'=>$link,'code'=>$link));
    	}catch (Exception $e){
    		//Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	$forms=new Registrar_Form_FrmSearchInfor();
    	$form=$forms->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	$_db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$this->view->year = $year = $_db->getYearService();
    	
    }
    public function addAction()
    {
    if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try {
      		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
      		$exist = $db->addStudentServicePayment($_data);
      		if($exist==-1){
      			Application_Form_FrmMessage::message("RECORD_EXIST");
      		}else{
	      		if(isset($_data['save_new'])){
	      			Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
	      		}else{
	      			Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
	      		}
      		}
      	} catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      		$err =$e->getMessage();
      		Application_Model_DbTable_DbUserLog::writeMessageError($err);
      	}
      }
       $frm = new Registrar_Form_FrmStudentServicePayment();
       $frm_register=$frm->FrmRegistarWU();
       Application_Model_Decorator::removeAllDecorator($frm_register);
       $this->view->frm_register = $frm_register;
       $key = new Application_Model_DbTable_DbKeycode();
       $this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
       $model = new Application_Form_FrmGlobal();
      
       $db = new Application_Model_DbTable_DbGlobal();
       $abc=$this->view->payment_term = $db->getAllPaymentTerm();
       //print_r($abc);exit();
       
       $db = new Registrar_Model_DbTable_DbStudentServicePayment();
       $this->view->rs = $db->getAllStudentCode();
       $this->view->row = $db->getAllStudentName();
       $service = $db->getAllService();
       array_unshift($service, array ( 'id' => -2, 'name' => 'បន្ថែមថ្មី') );
       array_unshift($service, array ( 'id' => -1, 'name' => 'Select Service') );
       $this->view->service = $service;
       
       $servicetype = $db->getAllServiceType();
       $this->view->service_type = $servicetype;
       
//        $_model = new Application_Model_GlobalClass();
//        $this->view->all_service = $_model->getAllServiceItemOption(2); /// for use in add row
       //$session_user=new Zend_Session_Namespace('auth'); $username = $session_user->first_name;
    }
    public function editAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
     		$_data['id']=$id;
    		try {
    			$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    			$db->updateStudentServicePayment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}else{
    				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/studentservicepayment/index');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			echo $err;
    		}
    	}
    	
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$this->view->row=$db->getStudentServicePaymentByID($id);
    	
    	$payment=$db->getStudentServicePaymentDetailByID($id);
//     	print_r($payment);exit();
    	$this->view->detail = $payment;
    	
//     	print_r($payment);exit();
    	
    	$frm = new Registrar_Form_FrmStudentServicePayment();
    	$frm_register=$frm->FrmRegistarWU($payment);
    	Application_Model_Decorator::removeAllDecorator($frm_register);
    	$this->view->frm_register = $frm_register;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
    	
    	$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    	$this->view->rs = $db->getAllStudentCode();
    	$this->view->row_name = $db->getAllStudentName();
    	$service = $db->getAllService();
    	array_unshift($service, array ( 'id' => -1, 'name' => 'Select Service') );
    	$this->view->service = $service;
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$abc=$this->view->payment_term = $db->getAllPaymentTerm();
    	
//     	$_model = new Application_Model_GlobalClass();
//     	$this->view->all_service = $_model->getAllServiceItemOption(2);
    	
    	
    }
    function getGradeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$grade = $db->getAllGrade($data['dept_id']);
    		//print_r($grade);exit();
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($grade));
    		exit();
    	}
    }
    
    function getPriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTerm($data['studentid'],$data['service'],$data['term'],$data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getPriceEditAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$price = $db->getAllpriceByServiceTermEdit($data['service'],$data['term'],$data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($price));
    		exit();
    	}
    }
    
    function getStudentAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$studentinfo = $db->getAllStudentInfo($data['studentid']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($studentinfo));
    		exit();
    	}
    }
    
    
    function getServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getAllService($data['year']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    function getStudentIdAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$year = $db->getStudentID($data['study_year'],$data['type']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($year));
    		exit();
    	}
    }
    
    function addPopupServiceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentServicePayment();
    		$service = $db->addService($data);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($service));
    		exit();
    	}
    }
    
}
