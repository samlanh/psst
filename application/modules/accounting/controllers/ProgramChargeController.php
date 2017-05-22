<?php
class Accounting_ProgramChargeController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
    public function indexAction()
    {
    	try{
    		if($this->getRequest()->isPost()){
    			$_data=$this->getRequest()->getPost();
    			
    			$search = array(
    					//'title' => $session_servicetype->txtsearch,
    					'txtsearch' => $_data['title'],
    					'status' => $_data['status_search'],
    					'type' => $_data['type']);
    		}else{
    			$search='';
    		}
    		$db = new Accounting_Model_DbTable_DbProgramCharge();
    		$service= $db->getAllTuitionFee($search);
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$row=0;$indexterm=1;$test = 0;$key=0;
    		if(!empty($service)){
    			foreach ($service as $i => $rs) {
    				$rows = $db->getProgramFeebyId($rs['id']);
    				if(empty($rows)){ continue;}
    				$fee_row=1;
    				if(!empty($rows))foreach($rows as $payment_tran){
    					
    					if($payment_tran['pay_type']==1){
    						$rs_rows[$key]=$this->headAddRecordTuitionFee($rs,$key);
    						//if($rs_rows[$key_old]['id']==$rs_rows[$key]['id']){
    					                                        	
    						//}
    						$term = $model->getAllGEPPrgramPayment($fee_row);
    						//$rs_rows[$key]['program_name'].=$payment_tran['level'];
    						//$rs_rows[$key]['level'] = $payment_tran['level'];
    						$rs_rows[$key]['fee'] = $payment_tran['fee'];
    						$key_old=$key;
    						$key++;
    						//echo 2;
    					}elseif($payment_tran['pay_type']==2){
    						$term = $model->getAllGEPPrgramPayment($payment_tran['pay_type']);
    					//	$rs_rows[$key]['level'] = $payment_tran['level'];
    						$rs_rows[$key_old]['2term'] = $payment_tran['fee'];
    						//echo 3333;
    	
    					}elseif($payment_tran['pay_type']==3){
    						$term = $model->getAllGEPPrgramPayment($payment_tran['pay_type']);
    						//$rs_rows[$key]['level'] = $payment_tran['level'];
    						$rs_rows[$key_old]['3term'] = $payment_tran['fee'];
    					}
    					else{
    						$term = $model->getAllGEPPrgramPayment($payment_tran['pay_type']);
    						//$rs_rows[$key]['level'] = $payment_tran['level'];
    						$rs_rows[$key_old]['full_fee'] = $payment_tran['fee'];
    					}
    				}
    			}
    		}
    		else{
    			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
    			$rs_rows=array();
    		}
    		if(empty($rs_rows)){
    			$rs_rows="";
    		}
    		$pay_term = $model->getAllGEPPrgramPayment();
    		$list = new Application_Form_Frmtable();
    		$collumns = array("PROGRAM_NAME","TYPE","STATUS");
    		$end = @end(array_keys($collumns));
    		$payment_term='';$key=1;//for merch array for collumn
    		    foreach ($pay_term as $value){
    		        $collumns[$end+$key]=$value;
    		    	$key++;
    		    }
    		$link=array(
    				'module'=>'accounting','controller'=>'programcharge','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows, array('cate_name'=>$link,'program_name'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Global_Form_FrmSearchMajor();
    	$frm = $frm->frmSearchServiceChageFee();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    }
    public function headAddRecordTuitionFee($rs,$key){
    	$result[$key] = array(
    			'id' 	  	  	=> $rs['id'],
    			'program_name' 	=> ($rs['service_name']),
    			'cate_name' 	=> $rs['cate_name'],
    			'status'	   => Application_Model_DbTable_DbGlobal::getAllStatus($rs['status'])
    	);
    	return $result[$key];
    }
	public function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbProgramCharge();
				//print_r($_data);exit();
				$rs =  $_model->addProgramCharge($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/programcharge/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Accounting_Form_FrmServicePrice();
		$frm_set_price=$frm->frmAddProgramCharge();
		Application_Model_Decorator::removeAllDecorator($frm_set_price);
		$this->view->frm_set_charge = $frm_set_price;
		$_model = new Application_Model_GlobalClass();
		$this->view->service_options = $_model->getAllServiceItemOption(1);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllGEPPrgramPayment();
		
		$frm=new Application_Form_FrmPopupGlobal();
		$frm_service = $frm->addProgramName(null,2);
		Application_Model_Decorator::removeAllDecorator($frm_service);
		$this->view->frm_service_name=$frm_service;
		
		$frm_ser_category = $frm->addProServiceCategory();
		Application_Model_Decorator::removeAllDecorator($frm_ser_category);
		$this->view->frm_ser_category=$frm_ser_category;
		
		$this->view->rate =$model->getRate();
		
	}
	public function editAction(){
		if($this->getRequest()->isPost()){
			try {
				$_data = $this->getRequest()->getPost();
				$_model = new Accounting_Model_DbTable_DbProgramCharge();
				$rs =  $_model->updateProgramCharge($_data);
				if(!empty($rs))Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/programcharge/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$_model = new Accounting_Model_DbTable_DbProgramCharge();
		$this->view->rs = $_model->getServiceChargeById($id);
		
		$_rs=array();
		
		$db = new Accounting_Model_DbTable_DbProgramCharge();
		$model = new Application_Model_DbTable_DbGlobal();
		$row=0;$key=0;
		
				$rows = $db->getProgramFeebyId($id);
				$fee_row=1;
				if(!empty($rows))foreach($rows as $payment_tran){
					if($payment_tran['pay_type']==1){
						$rs_rows[$key]=array(
								'subject_id'=>$payment_tran['subject_id'],
								'term1'=>$payment_tran['fee'],
								'term2'=>'',
								'term3'=>'',
								'total_hour'=>$payment_tran['total_hour'],
								'note'=>$payment_tran['note']
								);
						
						$rs_rows[$key]['price_'.$payment_tran['pay_type']] = $payment_tran['fee'];
						
						$key_old=$key;
						$key++;
					}elseif($payment_tran['pay_type']==2){
						$rs_rows[$key_old]['term2'] = $payment_tran['fee'];
					}elseif($payment_tran['pay_type']==3){
						$rs_rows[$key_old]['term3'] = $payment_tran['fee'];
					}
				
		}
		
		$this->view->programerfee = $rs_rows;
		
		$_model = new Application_Model_GlobalClass();
		$this->view->service_options = $_model->getAllServiceItemOption(1);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllGEPPrgramPayment();
	}
	public function headAddRecordService($rs,$key){
		$result[$key] = array(
				'id' 	  	  	=> $rs['service_id'],
// 				'cate_name' 	=> $rs['cate_name'],
// 				'service_name' 	=> ($rs['service_name']),
			//	'status'	   => Application_Model_DbTable_DbGlobal::getAllStatus($rs['status'])
		);
		return $result[$key];
	}
}
