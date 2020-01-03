<?php
class Allreport_AccountingController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction(){
	}
	public function rptAccountRecAction(){
	}
// 	function rptStudentpaymentAction(){
// 		try{
// 			if($this->getRequest()->isPost()){
// 					$search=$this->getRequest()->getPost();
// 				}
// 				else{
// 					$search = array(
// 							'title' =>'',
// 							'branch_id'=>'',
// 							'study_year' =>'',
// 							'session'=>'',
// 							'degree' =>'',
// 							'grade_all' =>'',
// 							'session' =>'',
// 							'user' =>'',
// 							'session' =>'',
// 							'start_date'=> date('Y-m-d'),
// 	                        'end_date'=>date('Y-m-d'),
// 					);
// 				}
// 			$this->view->search = $search;
// 			$db = new Allreport_Model_DbTable_DbRptPayment();
// 			$this->view->row = $db->getStudentPayment($search);
// 		}catch(Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
// 		$form=new Registrar_Form_FrmSearchInfor();
// 		$form->FrmSearchRegister();
// 		Application_Model_Decorator::removeAllDecorator($form);
// 		$this->view->form_search=$form;
// 	}
	function rptSpecaildiscountAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' => "",
    				'dis_type'=>"",
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
    				'status_type' => "",
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getAllSpecailDis($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    	$this->view->rsfooteracc = $frm->getFooterAccount();
	}
	function submitlistAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$db->submitPaidDate($data);
			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/allreport/accounting/rpt-studentpayment");
		}
	}
	function rptPaymentdetailbytypeAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'txtsearch' =>'',
						'branch_id' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service_type'=>'',
						'payment_by'=>-1,
						'study_year'=>-1,
						'item'=>'',
						'service'=>'',
						'group'=>'',
						'degree'=>-1,
						'grade_all'=>-1,
						'user'=>-1,
						'session'=>-1,
						'pay_term'=>'',
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row_detail = $db->getStudentPaymentDetail($search,3);
			
			$this->view->row = $db->getStudentPayment($search);
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$this->view->rsincome = $db->getAllOtherIncome($search);
			
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->rs_type = $_db->getAllItems();
	}
	function rptStudentpaymentdetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'txtsearch' =>'',
						'branch_id' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service_type'=>'',
						'payment_by'=>-1,
						'study_year'=>-1,
						'item'=>'',
						'service'=>'',
						'group'=>'',
						'degree'=>-1,
						'grade_all'=>-1,
						'user'=>-1,
						'session'=>-1,
						'pay_term'=>'',
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row_detail = $db->getStudentPaymentDetail($search,1);
			
			$this->view->row = $db->getStudentPayment($search);
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$this->view->rsincome = $db->getAllOtherIncome($search);
			
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->rs_type = $_db->getAllItems();
	}
	function rptStudentpaymenthistoryAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'branch_id'=>'',
						'study_year' =>-1,
						'session'=>-1,
						'degree'=>-1,
						'grade_all' =>-1,
						'user'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service'=>'',
						'payment_by'=>-1,
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getStudentPaymentDetail($search,2);
			$this->view->rs = $db->getStudentPayment($search);
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}	
	function rptSuspendserviceAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search=array(
					'adv_search' =>'',
					'stu_name' =>'',
					'grade_all' =>'',
					'category' =>'',
					'start_date' =>date("Y-m-d"),
					'end_date' =>date("Y-m-d"),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbSuspendService();
			$this->view->rs = $db->getStudetnSuspendServiceDetail($search);
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->category = $db->getAllItems(2);
	}
	public function rptstudentbalanceAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search' 	=>'',
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
						'grade'			=>'',
						'branch_id'		=>'',
				);
			}
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$db = new Allreport_Model_DbTable_DbRptStudentBalance();
			$this->view->rs = $db->getAllStudentBalance($search);
			$this->view->search = $search;
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
}
	public function rptexpectincomeAction(){
		try{
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' => $data['txtsearch'],
						'start_date'=> $data['from_date'],
						'end_date'=>$data['to_date']
				);
			}else{
				$search=array(
						'txtsearch' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);;
			}
				
			$db = new Allreport_Model_DbTable_DbRptExpectIncome();
			$this->view->rs = $db->getAllExpectIncome($search);
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function rptstudentnearlyendserviceAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search' =>'',
						'branch_id' =>'',
						'study_year'=>'',
						'grade_all' =>'',
						'degree' =>'',
						'group'		=>-1,
						'item'		=>-1,
						'service_type'=>-1,
						'end_date'	=>date('Y-m-d'),
						'service'	=>''
				);;
			}
			$db = new Allreport_Model_DbTable_DbRptStudentNearlyEndService();
			$abc = $this->view->row = $db->getAllStudentNearlyEndService($search);
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view->search = $search;
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->rs_type = $_db->getAllItems();
	}
// 	public function submitlateAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db = new Allreport_Model_DbTable_DbRptStudentPaymentLate();
// 			$db->submitlatePayment($data);
// 			$this->_redirect("/allreport/accounting/rptstudentpaymentlate");
// 		}
// 	}
	
	public function rptExpenseAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'txtsearch' =>'',
						'branch_id' =>'',
						'start_date'=>date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Allreport_Model_DbTable_DbRptIncomeExpense();
			$this->view->row = $db->getAllexspan($search);
			$this->view->search = $search;
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
    public function rptExpensedetailAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptIncomeExpense();
		$row =$db->getAllexspanByid($id);
		$this->view->row = 	$row;
		$this->view->detail = $db->getAllexspandetailByid($id);
		
		$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
		$_db = new Application_Form_FrmGlobal();
		$this->view->header = $_db->getHeaderReceipt($branch_id);
	}
	public function rptOtherIncomeAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'txtsearch' =>'',
						'branch_id'	=>'',
						'cate_income'	=>'',
						'user'	=>'',
						'sortby'=>'',
						'start_date'=>date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$abc = $this->view->row = $db->getAllOtherIncome($search);
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$this->view->search = $search;
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		
	}
	public function rptExpenseBycateAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
					'txtsearch' =>'',
					'branch_id'	=>'',
					'user'	=>'',
					'start_date'=>date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Allreport_Model_DbTable_DbRptOtherExpense();
			$abc = $this->view->row = $db->getAllExpensebycate($search);
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$this->view->search = $search;
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function rptFeeAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'generation'=>'',
					'finished_status'=>-1,
					'txtsearch' =>'',
					'study_year' =>'',
					'grade_all' =>'',
					'branch_id' =>'',
					'degree'=>0,
					'school_option'=>'');
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db = new Allreport_Model_DbTable_DbRptFee();
		$group= new Allreport_Model_DbTable_DbRptFee();
		$rs_rows = $group->getAllTuitionFee($search);
	
		$year = $db->getAllYearFee();
		$this->view->row = $year;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$row=0;$indexterm=1;$key=0;
		if(!empty($rs_rows)){
			foreach ($rs_rows as $i => $rs) {
				$rows = $db->getFeebyOther($rs['id'],$search['grade_all'],$search['degree'],$search);
				$fee_row=1;
				if(!empty($rows))foreach($rows as $payment_tran){
					if($payment_tran['payment_term']==1){
						$rs_rows[$key]=$this->headAddRecordTuitionFee($rs,$key);
						$term = $model->getAllPaymentTerm($fee_row);
						$rs_rows[$key]['degree']=$payment_tran['degree'];
						$rs_rows[$key]['status'] = Application_Model_DbTable_DbGlobal::getAllStatus($payment_tran['status']);
						$rs_rows[$key]['class'] = $payment_tran['class'];
						$rs_rows[$key]['session'] = $payment_tran['session'];
						$rs_rows[$key]['remark'] = $payment_tran['remark'];
						$rs_rows[$key]['monthly'] = $payment_tran['tuition_fee'];
						 
						$key_old=$key;
						$key++;
					}elseif($payment_tran['payment_term']==2){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['quarter'] = $payment_tran['tuition_fee'];
	
					}elseif($payment_tran['payment_term']==3){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['semester'] = $payment_tran['tuition_fee'];
	
					}elseif($payment_tran['payment_term']==4){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['year'] = $payment_tran['tuition_fee'];
					}
				}else{
					if($key==0){
						$rs_rows=array();
					}else{
						 $key_old=$key;
						unset($rs_rows[$key_old]);
					}
				}
			}
		}
		else{
			$rs_rows=array();
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$data=$this->view->rs = $rs_rows;
		$this->view->search = $search;
	}
	public function headAddRecordTuitionFee($rs,$key){
		$result[$key] = array(
				'id' 	  => $rs['id'],
				'branch_id'=>$rs['branch_name'],
				'academic'=> $rs['academic'],
				'generation'=> $rs['generation'],
				'class'=>'',
				'session'=>'',
				'quarter'=>'',
				'semester'=>'',
				'year'=>'',
				'time'=>$rs['time'],
				'date'=>$rs['create_date'],
				'status'=>''
		);
		return $result[$key];
	}
	public function rptServiceChargeAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['generation']='';
		}
		else{
			$search=array(
					'generation'=>'',
					'finished_status'=>-1,
					'txtsearch' =>'',
					'study_year' =>'',
					'grade_all' =>'',
					'branch_id' =>'',
					'degree_bac'=>0,);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister(2);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db = new Allreport_Model_DbTable_DbRptFee();
		$group= new Allreport_Model_DbTable_DbRptFee();
		$rs_rows = $group->getAllTuitionFee($search,2);
		
		$year = $db->getAllYearFee();
		$this->view->row = $year;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$row=0;$indexterm=1;$key=0;
		if(!empty($rs_rows)){
			foreach ($rs_rows as $i => $rs) {
				$rows = $db->getFeebyOther($rs['id'],$search['grade_all'],$search['degree_bac']);
				$fee_row=1;
				if(!empty($rows))foreach($rows as $payment_tran){
					if($payment_tran['payment_term']==1){
						$rs_rows[$key]=$this->headAddRecordTuitionFee($rs,$key);
						$term = $model->getAllPaymentTerm($fee_row);
						$rs_rows[$key]['degree']=$payment_tran['degree'];
						$rs_rows[$key]['status'] = Application_Model_DbTable_DbGlobal::getAllStatus($payment_tran['status']);
						$rs_rows[$key]['class'] = $payment_tran['class'];
						$rs_rows[$key]['session'] = $payment_tran['session'];
						$rs_rows[$key]['remark'] = $payment_tran['remark'];
						$rs_rows[$key]['monthly'] = $payment_tran['tuition_fee'];
							
						$key_old=$key;
						$key++;
					}elseif($payment_tran['payment_term']==2){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['quarter'] = $payment_tran['tuition_fee'];
		
					}elseif($payment_tran['payment_term']==3){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['semester'] = $payment_tran['tuition_fee'];
		
					}elseif($payment_tran['payment_term']==4){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['year'] = $payment_tran['tuition_fee'];
					}
				}else{
					if($key==0){
						$rs_rows=array();
					}else{
						$key_old=$key;
						unset($rs_rows[$key_old]);
					}
				}
			}
		}
		else{
			$rs_rows=array();
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$data=$this->view->rs = $rs_rows;
		$this->view->search = $search;
		
// 		if($this->getRequest()->isPost()){
// 			$_data=$this->getRequest()->getPost();
// 			$search = array(
// 				'txtsearch' => $_data['txtsearch'],
// 				'year' => $_data['year'],
// 				'branch_id' => $_data['branch_id'],
// 				'service_type' => $_data['service_type'],
// 				'service' => $_data['service'],
// 			);
// 		}
// 		else{
// 			$search=array(
// 				'txtsearch' =>'',
// 				'year' =>'',
// 				'branch_id' =>'',
// 				'service_type'=>-1, 
// 				'service' =>-1,
// 			);
// 		}
	
// 		$db = new Allreport_Model_DbTable_DbRptServiceCharge();
// 		$service= $db->getAllServiceFee($search);
		
// 		$form=new Registrar_Form_FrmSearchInfor();
// 		$form->FrmSearchRegister();
// 		Application_Model_Decorator::removeAllDecorator($form);
// 		$this->view->form_search=$form;
	
// 		$model = new Application_Model_DbTable_DbGlobal();
// 		$row=0;$indexterm=1;$key=0;$rs_rows=array();
// 		if(!empty($service)){
// 			foreach ($service as $i => $rs) {
// 				$rows = $db->getServiceFeebyId($rs['id'],$search['service_type'],$search['service']);
// 				$fee_row=1;
// 				if(!empty($rows))foreach($rows as $payment_tran){
// 					if($payment_tran['payment_term']==1){
// 						$rs_rows[$key]=$this->headAddRecordServiceFee($rs,$key);
// 						$term = $model->getAllPaymentTerm($fee_row);
	
// 						$rs_rows[$key]['service_name'] = $payment_tran['service_name'];
// 						$rs_rows[$key]['ser_type'] = $payment_tran['ser_type'];
// 						$rs_rows[$key]['remark'] = $payment_tran['remark'];
// 						$rs_rows[$key]['monthly'] = $payment_tran['price_fee'];
// 						$key_old=$key;
// 						$key++;
// 					}elseif($payment_tran['payment_term']==2){
// 						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
// 						$rs_rows[$key_old]['quarter'] = $payment_tran['price_fee'];
							
// 					}elseif($payment_tran['payment_term']==3){
// 						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
// 						$rs_rows[$key_old]['semester'] = $payment_tran['price_fee'];
// 					}
// 					elseif($payment_tran['payment_term']==4){
// 						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
// 						$rs_rows[$key_old]['year'] = $payment_tran['price_fee'];
// 					}
// 				}
// 			}
// 		}
// 		else{
// 			$rs_rows = array();
// 			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
// 		}
// 		$this->view->rs = $rs_rows;
// 		$this->view->search = $search;
// 		$db = new Allreport_Model_DbTable_DbRptFee();
// 		$year = $db->getAllYearFee();
// 		$this->view->row = $year;
	}
// 	public function headAddRecordServiceFee($rs,$key){
// 		$result[$key] = array(
// 				'id' 	  => $rs['id'],
// 				'academic'=> $rs['academic'],
// 				'monthly'=>'',
// 				'quarter'=>'',
// 				'semester'=>'',
// 				'year'=>'',
// 				'date'=>$rs['create_date'],
// 				'status'=> Application_Model_DbTable_DbGlobal::getAllStatus($rs['status'])
// 		);
// 		return $result[$key];
// 	}
	
	public function rptInvoiceAction(){
		try{
				if($this->getRequest()->isPost()){
					$search = $this->getRequest()->getPost();
				}
				else{
					$search=array(
						'search'=>'',
						'branch_id' => '',
						'student_name' => '',
						'group'=>'',
						'degree'=>'',
						'grade'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
					);
				}
				$db = new Accounting_Model_DbTable_Dbinvoice();
				$this->view->all_invoice = $db->getinvoice($search);
// 				$db = new Registrar_Model_DbTable_DbRegister();
// 				$this->view->all_student_name = $db->getAllGerneralOldStudentName();
// 				$this->view->all_student_code = $db->getAllGerneralOldStudent();

				$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
				$frm = new Application_Form_FrmGlobal();
				$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
				$this->view->rsfooteracc = $frm->getFooterAccount();
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$this->view->search = $search;
		}
    public function rptInvoicedetailAction(){
		$db = new Accounting_Model_DbTable_Dbinvoice();
		$id=$this->getRequest()->getParam('id');		
		if($this->getRequest()->isPost()){
		}		
		$row = $db->getinvoiceByid($id);
		$this->view->invoice = $row;
		$rs=$this->view->invoice_service = $db->getinvoiceservice($id);
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
		$_db = new Application_Form_FrmGlobal();
		$this->view->header = $_db->getHeaderReceipt($branch_id);
	}	
	public function rptCreditmemoAction(){
		try{
    		$db = new Accounting_Model_DbTable_DbCreditmemo();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"branch_id"=>'',
    					"payment_type"=>-1,
    					"status"=>-1,
    					"status_search"=>-1,
    					'paid_transfer'=>-1,
    					'paid_status'=>'',
    					'by_date'=>0,
    					'paid_type'=>0,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$_db = new Accounting_Model_DbTable_DbTransfercredit();
			$this->view->search = $formdata;
			if($formdata['paid_type']==0){
				$this->view->all_memo= $db->getAllCreditmemo($formdata);
				$this->view->all_memo_transfer = $_db->getAllTransfer($formdata);
			}else if($formdata['paid_type']==1){
				$this->view->all_memo= $db->getAllCreditmemo($formdata);
			}else if($formdata['paid_type']==2){
				$this->view->all_memo_transfer= $_db->getAllTransfer($formdata);
			}
				
			$branch_id = empty($formdata['branch_id'])?null:$formdata['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$form = new Registrar_Form_FrmSearchexpense();
    	$frm = $form->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
	}
	public function generateBarcodeAction(){
		$loan_code = $this->getRequest()->getParam('pro_code');
		header('Content-type: image/png');
		$this->_helper->layout()->disableLayout();
		$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
		$rendererOptions = array();
		$renderer = Zend_Barcode::factory(
				'code39', 'image', $barcodeOptions, $rendererOptions
		)->render();
	}
	public function rptStudentNotPaidAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'txtsearch' =>'',
						'group' 	=>'',
						'grade_all' =>'',
						'session' 	=>'',
						'stu_code' 	=>'',
						'stu_name' 	=>'',
						'service'	=>''
				);
			}
			$db = new Allreport_Model_DbTable_DbRptStudentNotPaid();
			$abc = $this->view->row = $db->getAllStudentNotPaid($search);
				
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	function rptStartdateenddatestuAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' =>'',
					'stuname_con' =>'',
					'term' =>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $rs_rows = $group->getStartDateEndDateStu($search);
		$this->view->search=$search;
		$db_glob = new Application_Model_GlobalClass();
	}
	public function rptStudentBepayAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'ordering'=>1,
					'branch_id'=>0,
					'degree'=>0,
					'group'=>'',
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$group= new Allreport_Model_DbTable_DbRptPayment();
		$this->view->rs = $rs_rows = $group->getAllStudentBepay($search);
		$this->view->search=$search;
	}
	
	public function rptStudentBepayServiceAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'group'			=>'',
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptPayment();
		$this->view->rs = $rs_rows = $group->getAllStudentBepayService($search);
		$this->view->search=$search;
	}
	
	public function rptIncomebycateAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' =>'',
					'branch_id' =>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			
			$db = new Registrar_Model_DbTable_DbRptByType();
			$this->view->row = $db->getIncomebyCategory($search);
			
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$this->view->rsincome = $db->getAllOtherIncomebyCate($search);
			
			$search['user']=-1;
			$search['session']=-1;
			$search['group']='';
			$search['degree']=-1;
			$search['grade_all']=-1;
			$search['study_year']=-1;
			
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row_penalty = $db->getStudentPayment($search);
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	
	public function rptIncomestatementAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Registrar_Model_DbTable_DbRptByType();
			$this->view->row = $db->getIncomebyCategory($search);
				
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$this->view->rsincome = $db->getAllOtherIncomebyCate($search);
			
			$db = new Allreport_Model_DbTable_DbRptOtherExpense();
			$abc = $this->view->rsexpense = $db->getAllExpensebycate($search);
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
		
		$search['user']=-1;
		$search['session']=-1;
		$search['group']='';
		$search['degree']=-1;
		$search['grade_all']=-1;
		$search['study_year']=-1;
			
		$db = new Allreport_Model_DbTable_DbRptPayment();
		$this->view->row_penalty = $db->getStudentPayment($search);
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	function rptReceiptVoidAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' 	=>'',
						'branch_id'	=>'',
						'type'		=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'	=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptReceiptVoid();
			
			if($search['type']==-1){
				$this->view->void_stu = $db->getAllStudentVoid($search);
				$this->view->void_chang_product = $db->getAllChangeProductVoid($search);
				$this->view->void_income = $db->getAllIncomeVoid($search);
				$this->view->void_expense = $db->getAllExpenseVoid($search);
				
			}else if($search['type']==1){
				$this->view->void_stu = $db->getAllStudentVoid($search);
			}else if($search['type']==2){
			}else if($search['type']==3){
				$this->view->void_chang_product = $db->getAllChangeProductVoid($search);
			}else if($search['type']==4){
				$this->view->void_income = $db->getAllIncomeVoid($search);
			}else if($search['type']==5){
				$this->view->void_expense = $db->getAllExpenseVoid($search);
			}
			
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
// 	function rptPaymentByDateAction(){
// 		try{
// 			if($this->getRequest()->isPost()){
// 				$search=$this->getRequest()->getPost();
// 			}
// 			else{
// 				$search = array(
// 						'title' =>'',
// 						'branch_id'=>'',
// 						'study_year' =>'',
// 						'session'=>'',
// 						'degree' =>'',
// 						'grade_all' =>'',
// 						'session' =>'',
// 						'user' =>'',
// 						'session' =>'',
// 						'start_date'=> date('Y-m-d'),
// 						'end_date'=>date('Y-m-d'),
// 				);
// 			}
// 			$this->view->search = $search;
// 			$db = new Allreport_Model_DbTable_DbRptPayment();
// 			$this->view->row = $db->getPaymentByDate($search);
// 			$this->view->testPayment = $db->getStudentTestPaymentDate($search);
// 		}catch(Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
// 		$form=new Registrar_Form_FrmSearchInfor();
// 		$form->FrmSearchRegister();
// 		Application_Model_Decorator::removeAllDecorator($form);
// 		$this->view->form_search=$form;
// 	}
	public function rptDailyAction()
	{
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id'     =>0,
						'degree'     =>'',
						'grade_all'  =>'',
						'session'    =>'',
						'all_payment'=>'all',
						'student_payment'=>'',
						'student_test'=>'',
						'income'    =>'',
						'stu_code'  =>'',
						'stu_name'  =>'',
						'expense'   =>'',
						'change_product'=>'',
						'customer_payment'=>'',
						'clear_balance'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'  => date('Y-m-d'),
				);
			}
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
			
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
			$db = new Registrar_Model_DbTable_DbReportStudentByuser();
	
			if(!empty($search['all_payment'])){
				$data1=$this->view->row = $db->getDailyReport($search);
				$_db = new Allreport_Model_DbTable_DbRptOtherIncome();
				$this->view->income = $_db->getAllOtherIncome($search);
						
				$_db1 = new Allreport_Model_DbTable_DbRptOtherExpense();
				$this->view->expense = $_db1->getAllOtherExpense($search);
			}
			if(!empty($search['student_payment'])){
				$data1=$this->view->row = $db->getDailyReport($search);
			}
			if(!empty($search['income'])){
					$_db = new Allreport_Model_DbTable_DbRptOtherIncome();
					$this->view->income = $_db->getAllOtherIncome($search);
			}
			if(!empty($search['expense'])){
					$_db1 = new Allreport_Model_DbTable_DbRptOtherExpense();
					$this->view->expense = $_db1->getAllOtherExpense($search);
			}
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	function rptreceiptdetailAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptPayment();
		$rs = $db->getStudentPaymentByid($id);
		$this->view->rr = $rs;
		$this->view->row =  $db->getPaymentReciptDetail($id);
		 
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		 
		$branch_id=null;
		if(!empty($rs)){
			$branch_id = $rs['branch_id'];
		}
// 		$_db = new Application_Form_FrmGlobal();
// 		$this->view->header = $_db->getHeaderReceipt($branch_id);
		
		$frmreceipt = new Application_Form_FrmGlobal();
		$this->view->officailreceipt = $frmreceipt->getFormatReceipt();
	}
	public function rptIncomediscountAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' =>'',
					'branch_id' =>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Registrar_Model_DbTable_DbRptByType();
			$this->view->row = $db->getIncomeDiscount($search,1);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
	}
	
	public function rptClosingdailyAction()
	{
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id'     =>0,
						'degree'     =>'',
						'grade_all'  =>'',
						'session'    =>'',
						'all_payment'=>'all',
						'student_payment'=>'',
						'student_test'=>'',
						'income'    =>'',
						'stu_code'  =>'',
						'stu_name'  =>'',
						'expense'   =>'',
						'change_product'=>'',
						'customer_payment'=>'',
						'clear_balance'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'  => date('Y-m-d'),
				);
			}
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
				
			$this->view->rsfooteracc = $frm->getFooterAccount();
				
			$db = new Registrar_Model_DbTable_DbReportStudentByuser();
	
			if(!empty($search['all_payment'])){
				$data1=$this->view->row = $db->getDailyReport($search);
				$_db = new Allreport_Model_DbTable_DbRptOtherIncome();
				$this->view->income = $_db->getAllOtherIncome($search);
	
				$_db1 = new Allreport_Model_DbTable_DbRptOtherExpense();
				$this->view->expense = $_db1->getAllOtherExpense($search);
			}
			if(!empty($search['student_payment'])){
				$data1=$this->view->row = $db->getDailyReport($search);
			}
			if(!empty($search['income'])){
				$_db = new Allreport_Model_DbTable_DbRptOtherIncome();
				$this->view->income = $_db->getAllOtherIncome($search);
			}
			if(!empty($search['expense'])){
				$_db1 = new Allreport_Model_DbTable_DbRptOtherExpense();
				$this->view->expense = $_db1->getAllOtherExpense($search);
			}
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	function closingentryAction(){
		try{
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db = new Registrar_Model_DbTable_DbReportStudentByuser();
				$db->submitClosingEngry($data);
				Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/allreport/accounting/rpt-closingdaily");
			}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			
		}
	}
	function reprintOtherIncomeAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new Registrar_Model_DbTable_DbReportStudentByuser();
		$row = $db->getOtherIncomeById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/accounting/rpt-other-income");
			exit();
		}
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
		$_db = new Application_Form_FrmGlobal();
		$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
		$this->view->header = $_db->getHeaderReceipt($branch_id);
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->officailreceipt = $frmpopup->receiptOtherIncome();
	}
}