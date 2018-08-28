<?php
class Accounting_ServicechargeController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
    public function indexAction()
    {
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
 		}
    		else{
    			$search=array(
	    				'title' => '',
	    				'year' => '',
	    				'branch_id'=>'',
    					'is_finished_search' => '',
    					'status_search' =>-1,
    			);
    		}
    		
    		$db = new Accounting_Model_DbTable_DbServiceCharge();
    		$service= $db->getAllServiceFee($search);
    		
    		$collumns = array("BRANCH","ACADEMIC_YEAR","CREATED_DATE","PROCESS_TYPE","STATUS","BY_USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'servicecharge','action'=>'edit',
    		);
    		
    		$list = new Application_Form_Frmtable();
    		$this->view->list=$list->getCheckList(10, $collumns, $service, array('academic'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	
    	$this->view->adv_search = $search;
    	
    	$frm = new Accounting_Form_FrmFee();
    	$frm->FrmTutionfee();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_fee = $frm;
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function headAddRecordTuitionFee($rs,$key){
    	$result[$key] = array(
    						'id' 	  => $rs['id'],
    						'date'=>$rs['create_date'],
    						'status'=> $rs['status']
    				);
    	return $result[$key];
    }
	public function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbServiceCharge();
				$_model->addServiceCharge($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/servicecharge/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/servicecharge/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Accounting_Form_FrmFee();
		$frm->FrmTutionfee();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fee = $frm;
		
// 		$frm = new Accounting_Form_Frmservicefee();
// 		$frm->FrmTutionfee();
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_fee = $frm;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm();
		 
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$d_row= $dbgb->getAllGradeStudy(2);
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
		
		$this->view->service_name=$d_row;
		
	}
	public function editAction(){
// 		$id=$this->getRequest()->getParam("id");
// 		if($this->getRequest()->isPost()){
// 			try {
// 				$_data = $this->getRequest()->getPost();
// 				$_data['id']=$id;
// 				$_model = new Accounting_Model_DbTable_DbServiceCharge();
// 				$rs =  $_model->updateServiceCharge($_data);
// 				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/servicecharge/index");
// 			}catch(Exception $e){
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}

// 		$db = new Accounting_Model_DbTable_DbServiceCharge();
// 		$this->view->year = $db->getAllYear();
		
// 		$_model = new Application_Model_GlobalClass();
// 		$this->view->all_metion = $_model ->getAllMetionOption();
// 		$this->view->all_faculty = $_model->getAllServiceItemOption(2);
// // 		$this->view->all_faculty = $_model ->getAllFacultyOption();
// 		$model = new Application_Model_DbTable_DbGlobal();
// 		$this->view->payment_term = $model->getAllPaymentTerm();
		 
// 		$frm = new Application_Form_FrmOther();
// 		$frm =  $frm->FrmAddDept(null);
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->add_dept = $frm;
		
// 		$db=new Accounting_Model_DbTable_DbServiceCharge();
// 		$id=$this->getRequest()->getParam("id");
// 		$this->view->rs =$db->getServiceChargeById($id);
		
// 		$db = new Accounting_Model_DbTable_DbServiceCharge();
// 		$this->view->branch = $db->getAllBranch();
		
// 		$row=0;$indexterm=1;$key=0;

// 				$rows = $db->getServiceFeebyId($id);
// 				$fee_row=1;$rs_rows=array();
// 				if(!empty($rows))foreach($rows as $payment_tran){
					
// 					$key_old=$key;
// 					if($payment_tran['payment_term']==1){
// 						$rs_rows[$key] = array(
// 								'service_id'=>$payment_tran['service_id'],
// 								'monthly'=>$payment_tran['price_fee'],
// 								'quarter'=>'',
// 								'semester'=>'',
// 								'year'=>'',
// 								'note'=>$payment_tran['remark'],
// 						);
// 						//$rs_rows[$key]['quarter'] = $payment_tran['tuition_fee'];
// 						$key_old=$key;
						
// 					}elseif($payment_tran['payment_term']==2){
// 						$rs_rows[$key_old]['quarter'] = $payment_tran['price_fee'];
		
// 					}elseif($payment_tran['payment_term']==3){
// 						$rs_rows[$key_old]['semester'] = $payment_tran['price_fee'];
// 					}
// 					elseif($payment_tran['payment_term']==4){
// 						$rs_rows[$key_old]['year'] = $payment_tran['price_fee'];
// 						$key++;
// 					}
					
// 				}
				
// 			  $this->view->rows =$rs_rows;
			   
// 			   $db_g=new Application_Model_DbTable_DbGlobal();
// 			   $this->view->all_service=$db_g->getAllstudentRequest(2);
// 			   $db = new Accounting_Model_DbTable_DbService();
// 			   $rs= $db->getServiceType(1);
// 			   array_unshift($rs, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
// 			   $this->view->service = $rs;

		$_db = new Accounting_Model_DbTable_DbServiceCharge();
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$rs =  $_db->updateServiceCharge($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/servicecharge");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id=$this->getRequest()->getParam("id");
		$row = $_db->getServiceChargeById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/servicecharge");
		}
		
		$frm = new Accounting_Form_FrmFee();
		$frm->FrmTutionfee($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fee = $frm;
			
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$d_row= $dbgb->getAllGradeStudy(2);
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
		$this->view->service_name=$d_row;
		$this->view->all_service = $dbgb ->getAllGradeStudyOption(2);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		
		$row=0;$indexterm=1;$key=0;
		$rows = $_db->getServiceFeebyId($id);
		$fee_row=1;$rs_rows=array();
		if(!empty($rows))foreach($rows as $payment_tran){
			if($payment_tran['payment_term']==1){
				$rs_rows[$key] = array(
						'class_id'=>$payment_tran['class_id'],
						'session_id'=>$payment_tran['session'],
						'monthly'=>$payment_tran['tuition_fee'],
						'semester'=>'',
						'year'=>'',
						'note'=>$payment_tran['remark'],
						'status'=>$payment_tran['status'],
				);
				$key_old=$key;
				$key++;
			}elseif($payment_tran['payment_term']==4){
				$rs_rows[$key_old]['year'] = $payment_tran['tuition_fee'];
		
			}elseif($payment_tran['payment_term']==2){
				$rs_rows[$key_old]['quarter'] = $payment_tran['tuition_fee'];
			}
			elseif($payment_tran['payment_term']==3){
				$rs_rows[$key_old]['semester'] = $payment_tran['tuition_fee'];
			}
		}
		$this->view->rows =$rs_rows;
	
	}
	
	public function copyAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbServiceCharge();
				$rs =  $_model->addServiceCharge($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/servicecharge/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	
		$db = new Accounting_Model_DbTable_DbServiceCharge();
		$this->view->year = $db->getAllYear();
	
		$_model = new Application_Model_GlobalClass();
		$this->view->all_metion = $_model ->getAllMetionOption();
		$this->view->all_faculty = $_model->getAllServiceItemOption(2);
		$db_g=new Application_Model_DbTable_DbGlobal();
		$this->view->all_service=$db_g->getAllstudentRequest(2);
		// 		$this->view->all_faculty = $_model ->getAllFacultyOption();
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm();
			
		$frm = new Application_Form_FrmOther();
		$frm =  $frm->FrmAddDept(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->add_dept = $frm;
	
		$db=new Accounting_Model_DbTable_DbServiceCharge();
		$id=$this->getRequest()->getParam("id");
		$this->view->rs =$db->getServiceChargeById($id);
	
		$db = new Accounting_Model_DbTable_DbServiceCharge();
		$this->view->branch = $db->getAllBranch();
	
		$row=0;$indexterm=1;$key=0;
	
		$rows = $db->getServiceFeebyId($id);
		$fee_row=1;$rs_rows=array();
		if(!empty($rows))foreach($rows as $payment_tran){
			if($payment_tran['payment_term']==1){
	
				// 						$rs_rows[$key]['monthly']=$payment_tran['price_fee'];
	
				$rs_rows[$key] = array(
						'service_id'=>$payment_tran['service_id'],
						'monthly'=>$payment_tran['price_fee'],
						'quarter'=>'',
						'semester'=>'',
						'year'=>'',
						'note'=>$payment_tran['remark'],
				);
	
				//$rs_rows[$key]['quarter'] = $payment_tran['tuition_fee'];
				$key_old=$key;
				$key++;
			}elseif($payment_tran['payment_term']==2){
				$rs_rows[$key_old]['quarter'] = $payment_tran['price_fee'];
	
			}elseif($payment_tran['payment_term']==3){
				$rs_rows[$key_old]['semester'] = $payment_tran['price_fee'];
			}
			elseif($payment_tran['payment_term']==4){
				$rs_rows[$key_old]['year'] = $payment_tran['price_fee'];
			}
	
		}
		$test = $this->view->rows =$rs_rows;
		// print_r($test);exit();
		$db_g=new Application_Model_DbTable_DbGlobal();
		$this->view->all_service=$db_g->getAllstudentRequest(2);
		
		$db = new Accounting_Model_DbTable_DbService();
		$rs= $db->getServiceType(1);
		array_unshift($rs, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		$this->view->service = $rs;
	}
	
	public function headAddRecordService($rs,$key){
		$result[$key] = array(
				'id' 	  	  	=> $rs['service_id'],
		);
		return $result[$key];
	}
	
	public function addServiceAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbService();
				$rs = $_model->addServicePopup($_data);
				print_r(Zend_Json::encode($rs));
				exit();
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	public function getallfacAction(){
		$db = new Application_Model_GlobalClass();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$rs = $db->getAllServiceItemOption($_data["type"]);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function addAjaxserviceAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Accounting_Model_DbTable_DbService();
				$row = $db->ajaxgetservice($data);
				$result = array("id"=>$row);
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}	
	function refreshserviceAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$dbgb = new Application_Model_DbTable_DbGlobal();
					$d_row= $dbgb->getAllGradeStudy(2);
					array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
					array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
					print_r(Zend_Json::encode($d_row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
}
