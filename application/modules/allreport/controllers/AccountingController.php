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
	function rptStudentpaymentAction(){
		try{
			if($this->getRequest()->isPost()){
					$search=$this->getRequest()->getPost();
				}
				else{
					$search = array(
							'title' =>'',
							'branch_id'=>'',
							'study_year' =>'',
							'session'=>'',
							'degree' =>'',
							'grade_all' =>'',
							'session' =>'',
							'user' =>'',
							'session' =>'',
							'start_date'=> date('Y-m-d'),
	                        'end_date'=>date('Y-m-d'),
					);
				}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getStudentPayment($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	function submitlistAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$db->submitPaidDate($data);
			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/allreport/accounting/rpt-studentpayment");
		}
	}
	function  rptPaymentdetailbytypeAction(){
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
						'degree'=>-1,
						'grade_all'=>-1,
						'user'=>-1,
						'session'=>-1,
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row_detail = $db->getStudentPaymentDetail($search,3);
			$this->view->row = $db->getStudentPayment($search);
			
			$this->view->service = $db->getService();
			$this->view->search = $search;
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();exit();
		}
	}
	function  rptPaymentdetailbytypeSumupAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'txtsearch' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service_type'=>0
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getPaymentDetailByTypeSumup($search);
			$this->view->service = $db->getService();
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}
	}
	function rptStudentpaymentdetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'branch_id'=>'',
						'study_year' =>-1,
						'degree'=>-1,
						'grade_all' =>-1,
						'user'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service'=>'',
						'session'=>'',
						'payment_by'=>-1,
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getStudentPaymentDetail($search,1);
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
	function rptPaymentrecieptdetailAction(){
		$db = new Allreport_Model_DbTable_DbRptPayment();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db->updateValidationbyreceipt($data);
				Application_Form_FrmMessage::Sucessfull("UPDAE_SUCCESSS", "/allreport/accounting/rpt-studentpayment");
			}
		$id=$this->getRequest()->getParam("id");
		$this->view->row =  $db->getPaymentReciptDetail($id);
		$this->view->rr = $db->getStudentPaymentByid($id);
	}
	function  rptSuspendserviceAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search=array(
						'title' => '',
						'service'=>'',
						'study_year'=>'',
						'service_type'=>-1,
						'grade_all'=>-1,
						'degree'=>-1,
						'start_date'=>date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbSuspendService();
		$this->view->rs = $db->getStudetnSuspendServiceDetail($search);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	function rptStudentListDetailPart1Action(){
	}
	function rptStudentListDetailPart2Action(){
	}
	function rptStudentListDetailPart3Action(){
	}
	public function rptTuitionFeeAction()
	{
	}
	function rptGepFeeAction(){
	}
	function rptGepListAction(){
	}
	function rptListOfItemAction(){
	}
	public function rptstudentbalanceAction(){
		try{
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' => $data['txtsearch'],
						'start_date'=> $data['from_date'],
                        'end_date'=>$data['to_date'],
						'service'=>$data['service'],
						'branch_id'=>$data['branch_id'],
				);
			}else{
				$search=array(
						'txtsearch' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'service'=>'',
						'branch_id'=>'',
				);;
			}
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$db = new Allreport_Model_DbTable_DbRptStudentBalance();
			$this->view->rs = $db->getAllStudentBalance($search);
			$this->view->search = $search;
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
						'txtsearch' =>'',
						'grade_all' =>'',
						'session' 	=>'',
						'stu_code' 	=>'',
						'stu_name' 	=>'',
						'group'		=>-1,
						'service_type'=>-1,
						'end_date'	=>date('Y-m-d'),
						'service'	=>''
				);;
			}
			$db = new Allreport_Model_DbTable_DbRptStudentNearlyEndService();
			$abc = $this->view->row = $db->getAllStudentNearlyEndService($search);
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function rptstudentpaymentlateAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'txtsearch' =>'',
						'grade_all' =>'',
						'session' 	=>'',
						'stu_code' 	=>'',
						'stu_name' 	=>'',
						'group'=>-1,
						'service_type'=>-1,
						'end_date'	=>date('Y-m-d'),
						'service'	=>'',
				);;
			}
			$db = new Allreport_Model_DbTable_DbRptStudentPaymentLate();
			$abc = $this->view->row = $db->getAllStudentPaymentLate($search);
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function rptIncomeExpenseAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'txtsearch' =>'',
						'start_date'=>date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Allreport_Model_DbTable_DbRptIncomeExpense();
			$this->view->row = $db->getAllexspan($search);
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
    public function rptIncomeExpenseDetailAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptIncomeExpense();
		$this->view->row = $db->getAllexspanByid($id);	
		$this->view->detail = $db->getAllexspandetailByid($id);
	}
	public function rptOtherIncomeAction(){
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
			$db = new Allreport_Model_DbTable_DbRptOtherIncome();
			$abc = $this->view->row = $db->getAllOtherIncome($search);
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$this->view->search = $search;
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
					'year' =>'',
					'grade_all' =>'',
					'branch_id' =>'',
					'degree_bac'=>0,);
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
		//print_r($data);exit();
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
			$_data=$this->getRequest()->getPost();
			$search = array(
					'txtsearch' => $_data['txtsearch'],
					'year' => $_data['year'],
					'branch_id' => $_data['branch_id'],
					'service_type' => $_data['service_type'],
					'service' => $_data['service'],
			);
		}
		else{
			$search=array(
					'txtsearch' =>'',
					'year' =>'',
					'branch_id' =>'',
					'service_type'=>-1, 
					'service' =>-1,
			);
		}
	
		$db = new Allreport_Model_DbTable_DbRptServiceCharge();
		$service= $db->getAllServiceFee($search);
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	
		$model = new Application_Model_DbTable_DbGlobal();
		$row=0;$indexterm=1;$key=0;$rs_rows=array();
		if(!empty($service)){
			foreach ($service as $i => $rs) {
				$rows = $db->getServiceFeebyId($rs['id'],$search['service_type'],$search['service']);
				$fee_row=1;
				if(!empty($rows))foreach($rows as $payment_tran){
					if($payment_tran['payment_term']==1){
						$rs_rows[$key]=$this->headAddRecordServiceFee($rs,$key);
						$term = $model->getAllPaymentTerm($fee_row);
	
						$rs_rows[$key]['service_name'] = $payment_tran['service_name'];
						$rs_rows[$key]['ser_type'] = $payment_tran['ser_type'];
						$rs_rows[$key]['remark'] = $payment_tran['remark'];
						$rs_rows[$key]['monthly'] = $payment_tran['price_fee'];
						$key_old=$key;
						$key++;
					}elseif($payment_tran['payment_term']==2){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['quarter'] = $payment_tran['price_fee'];
							
					}elseif($payment_tran['payment_term']==3){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['semester'] = $payment_tran['price_fee'];
					}
					elseif($payment_tran['payment_term']==4){
						$term = $model->getAllPaymentTerm($payment_tran['payment_term']);
						$rs_rows[$key_old]['year'] = $payment_tran['price_fee'];
					}
				}
			}
		}
		else{
			$rs_rows = array();
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$this->view->rs = $rs_rows;
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptFee();
		$year = $db->getAllYearFee();
		$this->view->row = $year;
	}
	public function headAddRecordServiceFee($rs,$key){
		$result[$key] = array(
				'id' 	  => $rs['id'],
				'academic'=> $rs['academic'],
				'monthly'=>'',
				'quarter'=>'',
				'semester'=>'',
				'year'=>'',
				'date'=>$rs['create_date'],
				'status'=> Application_Model_DbTable_DbGlobal::getAllStatus($rs['status'])
		);
		return $result[$key];
	}
	public function rptTeacherStudentsAction(){
		$db = new Allreport_Model_DbTable_DbRptLecturer();
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'degree' =>-1,
						'grade' =>-1,
						'academic' =>-1,
						'txtsearch'=>"",
						'branch_id'=> '',
						'session'=>'',
				);
			}
			$this->view->search = $search;
			$this->view->row = $db->getAmountStudentByTeacher($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->degree = $db->getAllDegree();
		$this->view->grade= $db->getAllGrade();
		$this->view->academic = $db->getAcademicyear();
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;	
	}
	public function rptInvoiceAction(){
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
				$this->view->all_invoice = $db->getinvoice($search);
				$db = new Registrar_Model_DbTable_DbRegister();
				$this->view->all_student_name = $db->getAllGerneralOldStudentName();
				$this->view->all_student_code = $db->getAllGerneralOldStudent();
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
		$this->view->invoice = $db->getinvoiceByid($id);
		$rs=$this->view->invoice_service = $db->getinvoiceservice($id);
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,null);		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
    					"payment_type"=>-1,
    					"status"=>-1,
    					'paid_status'=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
			$this->view->all_memo= $db->getAllCreditmemo($formdata);
			$this->view->search = $formdata;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
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
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
	
			$db = new Registrar_Model_DbTable_DbRptByType();
			$this->view->row = $db->getAllStudentPaymentByType($search,1);
			$this->view->rs = $db->getAllStudentPaymentByType($search,2);
			
			$this->view->studenttestincome = $db->getTotalStudentTestPayment($search);
			$this->view->otherincome = $db->getTotalOtherIncome($search);
			$this->view->changeproduct = $db->getTotalChangeProduct($search);
			$this->view->customerpayment = $db->getTotalCustomerPayment($search);
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	function  rptPaymentbydegreeAction(){
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
					'degree'=>-1,
					'grade_all'=>-1,
					'user'=>-1,
					'session'=>-1,
				);
			}
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row_detail = $db->getStudentPaymentbyDegree($search,3);
			$this->view->service = $db->getService();
			$this->view->search = $search;
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
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
				//$this->view->void_test = $db->getAllTestVoid($search);
				$this->view->void_chang_product = $db->getAllChangeProductVoid($search);
				$this->view->void_income = $db->getAllIncomeVoid($search);
				$this->view->void_expense = $db->getAllExpenseVoid($search);
			}else if($search['type']==1){
				$this->view->void_stu = $db->getAllStudentVoid($search);
			}else if($search['type']==2){
				//$this->view->void_test = $db->getAllTestVoid($search);
			}else if($search['type']==3){
				$this->view->void_chang_product = $db->getAllChangeProductVoid($search);
			}else if($search['type']==4){
				$this->view->void_income = $db->getAllIncomeVoid($search);
			}else if($search['type']==5){
				$this->view->void_expense = $db->getAllExpenseVoid($search);
			}
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	function rptPaymentByDateAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'branch_id'=>'',
						'study_year' =>'',
						'session'=>'',
						'degree' =>'',
						'grade_all' =>'',
						'session' =>'',
						'user' =>'',
						'session' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getPaymentByDate($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	
}