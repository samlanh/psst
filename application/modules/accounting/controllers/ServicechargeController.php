<?php
class Accounting_ServicechargeController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
    					'status' =>-1,
    			);
    		}
    		$db = new Accounting_Model_DbTable_DbServiceCharge();
    		$service= $db->getAllServiceFee($search);
    		$collumns = array("BRANCH","ACADEMIC_YEAR","CREATED_DATE","PROCESS_TYPE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'servicecharge','action'=>'edit',
    		);
    		
    		$list = new Application_Form_Frmtable();
    		$this->view->list = $list->getCheckList(10, $collumns, $service, array('branch'=>$link,'academic'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $_db->getAllBranch();
    	
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
	public function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbServiceCharge();
				$_model->addServiceCharge($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/servicecharge/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Accounting_Form_FrmFee();
		$frm->FrmTutionfee();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fee = $frm;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm();
		 
		$dbgb = new Application_Model_DbTable_DbGlobal();
		
		$param = array(
			'itemsType'=>2
		);
		$d_row= $dbgb->getAllItemDetail($param);
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
		
		$this->view->service_name=$d_row;
	}
	public function editAction(){
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
		$id = empty($id)?0:$id;
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
		$param = array(
			'itemsType'=>2
		);
		$d_row= $dbgb->getAllItemDetail($param);
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
		$_db = new Accounting_Model_DbTable_DbServiceCharge();
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbServiceCharge();
				$rs =  $_model->addServiceCharge($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCESSS","/accounting/servicecharge");
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
		$param = array(
			'itemsType'=>2
		);
		$d_row= $dbgb->getAllItemDetail($param);
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
	
	public function getallfacAction(){
		$db = new Application_Model_GlobalClass();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$rs = $db->getAllServiceItemOption($_data["type"]);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function refreshserviceAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$param = array(
					'itemsType'=>2
				);
					$d_row= $dbgb->getAllItemDetail($param);
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
	
	public function getYearbybranchAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$rs = $db->getAllYearServiceFeeByBranch($_data["branch_id"]);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function getservicefeeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbServiceCharge();
			$service_fee = $db->getServiceFee($data['year'],$data['service'],$data['term'],$data['studentid'],$data['branch_id']);
			print_r(Zend_Json::encode($service_fee));
			exit();
		}
	}
	
}