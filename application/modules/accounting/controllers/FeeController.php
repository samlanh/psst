<?php
class Accounting_FeeController extends Zend_Controller_Action {
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
    		$db = new Accounting_Model_DbTable_DbFee();
    		$rs_rows= $db->getAllTuitionFee($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TYPE_STUDY","IS_MULTY_STUDY","TYPE","School Option","CREATED_DATE","PROCESS_TYPE","BY_USER","STATUS");
    		$link=array(
    			'module'=>'accounting','controller'=>'fee','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch'=>$link,'academic'=>$link,'study_type'=>$link,'is_multistudy'=>$link,'class'=>$link,'generation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}    	
    	$this->view->adv_search = $search;
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branch = $_db->getAllBranch();    	
    	$frm = new Accounting_Form_FrmFee();
    	$frm->FrmSearchTutionfee();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_fee = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_model = new Accounting_Model_DbTable_DbFee();
    		try {
	    		$rs =  $_model->addTuitionFee($_data);
	    		if(isset($_data['save_close'])){
	    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/fee");
	    		}else{
	    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/fee/add");
	    		}
	    			Application_Form_FrmMessage::message("INSERT_SUCCESS");
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
    	$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		
		$rows = $model->getAllPaymentTerm($id=null,$hidemonth=1);
		$this->view->term_option =	$rows ;
    }
 	
    public function editAction()
	{
		$_db = new Accounting_Model_DbTable_DbFee();
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$rs =  $_db->updateTuitionFee($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/fee");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row = $_db->getFeeById($id);
		$this->view->rs = $row;
		$schoolOption = $row['school_option'];
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/fee");
		}
		
		$frm = new Accounting_Form_FrmFee();
		$frm->FrmTutionfee($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fee = $frm;
		 
		$this->view->all_grade = $_db->getAllGradeStudyOption(1,$schoolOption);		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);

		$row=0;$indexterm=1;$key=0;
		$rows = $_db->getFeeDetailById($id);
		$fee_row=1;$rs_rows=array();
		if(!empty($rows))foreach($rows as $payment_tran){
			if($payment_tran['payment_term']==1){
				$rs_rows[$key] = array(
						'class_id'=>$payment_tran['class_id'],
						'session_id'=>$payment_tran['session'],
						'onepayment'=>'',
						'monthly'=>$payment_tran['tuition_fee'],
						'semester'=>'',
						'year'=>'',
						'note'=>$payment_tran['remark'],
						'status'=>$payment_tran['status'],
				);
				$key_old=$key;
				$key++;
			}elseif($payment_tran['payment_term']==2){
				$rs_rows[$key_old]['quarter'] = $payment_tran['tuition_fee'];
			}
			elseif($payment_tran['payment_term']==3){
				$rs_rows[$key_old]['semester'] = $payment_tran['tuition_fee'];
			}elseif($payment_tran['payment_term']==4){
				$rs_rows[$key_old]['year'] = $payment_tran['tuition_fee'];
			}elseif($payment_tran['payment_term']==5){
				$rs_rows[$key_old]['onepayment'] = $payment_tran['tuition_fee'];
			}
		}
	   $this->view->rows =$rs_rows;
			   
	}
	function copyAction(){
		$_db = new Accounting_Model_DbTable_DbFee();
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$rs =  $_db->addTuitionFee($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/fee");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$row = $_db->getFeeById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/fee");
		}
		
		$frm = new Accounting_Form_FrmFee();
		$frm->FrmTutionfee($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fee = $frm;
		 
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$schoolOption = $row['school_option'];
		$this->view->all_grade = $_db->getAllGradeStudyOption(1,$schoolOption);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);

		$row=0;$indexterm=1;$key=0;
		$rows = $_db->getFeeDetailById($id);
		$fee_row=1;$rs_rows=array();
		if(!empty($rows))foreach($rows as $payment_tran){
			if($payment_tran['payment_term']==1){
				$rs_rows[$key] = array(
						'class_id'=>$payment_tran['class_id'],
						'session_id'=>$payment_tran['session'],
						'onepayment'=>'',
						'monthly'=>$payment_tran['tuition_fee'],
						'semester'=>'',
						'year'=>'',
						'note'=>$payment_tran['remark'],
						'status'=>$payment_tran['status'],
				);
				$key_old=$key;
				$key++;
			}elseif($payment_tran['payment_term']==1){
				$rs_rows[$key_old]['monthly'] = $payment_tran['tuition_fee'];
			}
			elseif($payment_tran['payment_term']==2){
				$rs_rows[$key_old]['quarter'] = $payment_tran['tuition_fee'];
			}
			elseif($payment_tran['payment_term']==3){
				$rs_rows[$key_old]['semester'] = $payment_tran['tuition_fee'];
			}elseif($payment_tran['payment_term']==4){
				$rs_rows[$key_old]['year'] = $payment_tran['tuition_fee'];
			}
			elseif($payment_tran['payment_term']==5){
				$rs_rows[$key_old]['onepayment'] = $payment_tran['tuition_fee'];
			}
		}
	   $this->view->rows =$rs_rows;
	}	
	public function getgradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$dbgb = new Accounting_Model_DbTable_DbFee();
			$d_row= $dbgb->getAllGradeStudySchoolOption(1,$data['school_option']);
			array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
			$this->view->grade_name=$d_row;
			print_r(Zend_Json::encode($d_row));
			exit();
		}
	}
	public function getExitsingFeeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$dbgb = new Accounting_Model_DbTable_DbFee();
			$d_row= $dbgb->getCondition($data);
			print_r(Zend_Json::encode($d_row));
			exit();
		}
	}
	function getfeeidAction(){//year for study only
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
			$result = $db->getAllYearByBranch($data);
			if(!empty($data['selectOption'])){
				array_unshift($result, array ( 'id' =>'','name' =>$this->tr->translate("PLEASE_SELECT_STUDENT_FEE")));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}