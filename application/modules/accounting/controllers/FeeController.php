<?php
class Accounting_FeeController extends Zend_Controller_Action {
	public function init()
    {    	
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
    							'txtsearch' => '',
    							'year' => '',
    							'branch_id'=>'',
    					);
    		}
    		$db = new Accounting_Model_DbTable_DbTuitionFee();
    		$rs_rows= $db->getAllTuitionFee($search);
    		
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TYPE","CREATED_DATE","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'fee','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(2, $collumns, $rs_rows , array('branch'=>$link,'academic'=>$link,'class'=>$link,'generation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$this->view->adv_search = $search;
    	
    	$year=$db->getAceYear();
    	array_unshift($year, array('id'=>'','name'=>"Select Year"));
    	$this->view->rows_year=$year;
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	  
    }
    public function headAddRecordTuitionFee($rs,$key){
    	$result[$key] = array(
    						'id' 	  	=> $rs['id'],
    						'branch'	=> $rs['branch'],
    						'academic'	=> $rs['academic'],
    						'generation'=> $rs['generation'],	
    						'class'		=>'',
    		            	//'session'	=> '',
    						//'time'		=> $rs['time'],
    						'month'		=>'',
    						'quarter'	=>'',
			    			'semester'	=>'',
			    			'year'		=>'',
    						'date'		=>$rs['create_date'],
    						'status'	=>''
    				);
    	return $result[$key];
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_model = new Accounting_Model_DbTable_DbTuitionFee();
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
    	
    	$_model = new Application_Model_GlobalClass();
//     	$this->view->all_branch = $_model ->getAllMetionOption();
    	$this->view->all_grade = $_model ->getAllFacultyOption();
//     	$data=$this->view->all_session=$_model->getAllSession();
    	$model = new Application_Model_DbTable_DbGlobal();
    	$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		
		$branch = $model->getAllBranchName();
    	array_unshift($branch, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
    	$this->view->branchopt = $branch;
    	
    	$frm = new Application_Form_FrmOther();
    	$frm =  $frm->FrmAddDept(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->add_dept = $frm;
    	
		$fm = new Global_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
		
    	$db_gr=new Global_Model_DbTable_DbGrade();
		$d_row=$db_gr->getNameGradeAll();
		array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		$this->view->grade_name=$d_row;
		
		$dept = $db_gr->getAllDept();
		array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		$this->view->dept = $dept;
    }
 	
    public function editAction()
	{
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbTuitionFee();
				$rs =  $_model->updateTuitionFee($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/fee");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		 
		$_model = new Application_Model_GlobalClass();
		$this->view->all_grade = $_model ->getAllFacultyOption();
// 		$data=$this->view->all_session=$_model->getAllSession();
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
		 
		$frm = new Application_Form_FrmOther();
		$frm =  $frm->FrmAddDept(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->add_dept = $frm;
		
		$db=new Accounting_Model_DbTable_DbTuitionFee();
		$id=$this->getRequest()->getParam("id");
		$this->view->rs =$db->getFeeById($id);
		$feeid = $db->getFeeById($id);
		if(empty($feeid)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/fee");
		}

		$row=0;$indexterm=1;$key=0;
				$rows = $db->getFeeDetailById($id);
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
			   
			   $db = new Accounting_Model_DbTable_DbTuitionFee();
			   $this->view->branch = $db->getAllBranch();
			   
			   $db_gr=new Global_Model_DbTable_DbGrade();
			   $d_row=$db_gr->getNameGradeAll();
			   array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
			   $this->view->grade_name=$d_row;
			   
			   $dept = $db_gr->getAllDept();
			   array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
			   $this->view->dept = $dept;
			   
	}
	
	function copyAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbTuitionFee();
				$rs =  $_model->addTuitionFee($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/fee");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_model = new Application_Model_GlobalClass();
// 		$this->view->all_metion = $_model ->getAllMetionOption();
		$this->view->all_grade = $_model ->getAllFacultyOption();
// 		$data=$this->view->all_session=$_model->getAllSession();
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);
			
		$frm = new Application_Form_FrmOther();
		$frm =  $frm->FrmAddDept(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->add_dept = $frm;
		
		$db=new Accounting_Model_DbTable_DbTuitionFee();
		$id=$this->getRequest()->getParam("id");
		$this->view->rs =$db->getFeeById($id);
		$feeid = $db->getFeeById($id);
		if(empty($feeid)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/accounting/fee");
		}
		
		$db = new Accounting_Model_DbTable_DbTuitionFee();
		$this->view->branch = $db->getAllBranch();
		
		$row=0;$indexterm=1;$key=0;
		$rows = $db->getFeeDetailById($id);
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
		
		$db_gr=new Global_Model_DbTable_DbGrade();
		$d_row=$db_gr->getNameGradeAll();
		array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		$this->view->grade_name=$d_row;
		
		$dept = $db_gr->getAllDept();
		array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		$this->view->dept = $dept;
	}	
    
}
