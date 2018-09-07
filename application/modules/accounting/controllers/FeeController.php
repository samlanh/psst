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
	    					'title' => '',
	    					'year' => '',
	    					'branch_id'=>'',
    						'school_option'=>-1,
    						'is_finished_search' => '',
    						'status_search' =>-1,
    					);
    		}
    		$db = new Accounting_Model_DbTable_DbFee();
    		$rs_rows= $db->getAllTuitionFee($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TYPE","School Option","CREATED_DATE","PROCESS_TYPE","STATUS","BY_USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'fee','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch'=>$link,'academic'=>$link,'class'=>$link,'generation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
//     public function headAddRecordTuitionFee($rs,$key){
//     	$result[$key] = array(
//     						'id' 	  	=> $rs['id'],
//     						'branch'	=> $rs['branch'],
//     						'academic'	=> $rs['academic'],
//     						'generation'=> $rs['generation'],	
//     						'class'		=>'',
//     		            	//'session'	=> '',
//     						//'time'		=> $rs['time'],
//     						'month'		=>'',
//     						'quarter'	=>'',
// 			    			'semester'	=>'',
// 			    			'year'		=>'',
//     						'date'		=>$rs['create_date'],
//     						'status'	=>''
//     				);
//     	return $result[$key];
//     }
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
	   			echo $e->getMessage();exit();
    		}
    	}
    	
    	$frm = new Accounting_Form_FrmFee();
    	$frm->FrmTutionfee();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_fee = $frm;
    	
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$d_row= $dbgb->getAllGradeStudy();
//     	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
    	$this->view->grade_name=$d_row;
    	
    	$model = new Application_Model_DbTable_DbGlobal();
    	$this->view->payment_term = $model->getAllPaymentTerm(null,null);
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
		$d_row= $dbgb->getAllGradeStudy();
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
		$this->view->grade_name=$d_row;
		$this->view->all_grade = $dbgb ->getAllGradeStudyOption();
		
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
		$d_row= $dbgb->getAllGradeStudy();
// 		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_GRADE")));
		$this->view->grade_name=$d_row;
		$this->view->all_grade = $dbgb ->getAllGradeStudyOption();
		
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
    
}
