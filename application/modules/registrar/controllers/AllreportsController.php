<?php
class Registrar_AllreportsController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
    public function indexAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' =>'',
    					'study_year' =>'',
    					'service'=>'',
    					'user'=>'',
    					'type'=>1,
    					'branch_id'=>0,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$this->_redirect("/registrar/allreports/rpt-daily");    		
    		if($search['type']==1){
	    		$db = new Registrar_Model_DbTable_DbReportStudentByuser();
	    		$this->view->row = $db->getAllStudentPayment($search);
	    		
	    		$user_type=$db->getUserType();
	    		
	    		if($user_type==1){
		    		$db = new Allreport_Model_DbTable_DbRptOtherIncome();
		    		$this->view->income = $db->getAllOtherIncome($search);
		    		
		    		$db = new Allreport_Model_DbTable_DbRptOtherExpense();
		    		$this->view->expense = $db->getAllOtherExpense($search);
	    		}
    		}else if($search['type']==2){
    			$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    			$data=$this->view->row = $db->getAllStudentPayment($search);
    			
    		}else if($search['type']==3){
    			$_db = new Registrar_Model_DbTable_DbReportStudentByuser();
    			$user_type=$_db->getUserType();
    			
    			if($user_type==1){
    				$db = new Allreport_Model_DbTable_DbRptOtherIncome();
    				$this->view->income = $db->getAllOtherIncome($search);
    			
    				$db = new Allreport_Model_DbTable_DbRptOtherExpense();
    				$this->view->expense = $db->getAllOtherExpense($search);
    			}
    		}
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$this->view->search = $search;
    }
    public function addAction(){
    	//$this->_redirect("/registrar/allreports");
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$data=$this->view->row = $db->getAllService();    	
    }
    function rptreceiptdetailAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptPayment();
    
    	$this->view->rr = $db->getStudentPaymentByid($id);
    	$this->view->row =  $db->getPaymentReciptDetail($id);
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    function updateReceiptAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptPayment();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Allreport_Model_DbTable_DbRptStudentPaymentLate();
    		$db->submitlatePayment($data);
    		$this->_redirect("/registrar/allreports/rptreceiptdetail/id/".$id);
    	}
    	else{
    		$search = array(
    				'adv_search' =>'',
    				'study_year' =>'',
    				'service'=>'',
    				'user'=>'',
    				'type'=>1,
    				'branch_id'=>0,
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    		);
    	}
    	
    	$this->view->rr = $db->getStudentPaymentByid($id);
    	$this->view->row =  $db->getPaymentReciptDetail($id);
    	 
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    function reprintCustomerPaymentAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptPayment();
    
    	$this->view->rr = $db->getCustomerPaymentById($id);
    	$this->view->row =  $db->getCustomerPaymentDeatilById($id);
    	 
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    
    function reprintTestPaymentAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$this->view->row = $db->getStudentTestPaymentById($id);
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    
    function reprintOtherIncomeAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$this->view->row = $db->getOtherIncomeById($id);
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	 
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    
    function reprintClearbalanceAction(){
    	$id=$this->getRequest()->getParam("id");
    	
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$db->voidStudentClearBalance($id);
    	}
    	
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$this->view->row = $db->getClearBalanceById($id);
    	 
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    
    function reprintChangeproductAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$this->view->row = $db->getChangeProductById($id);

    	$this->view->detail = $db->getChangeProductDetailById($id);
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$_db = new Application_Form_FrmGlobal();
    	$this->view->header = $_db->getHeaderReceipt();
    }
    
   
}
