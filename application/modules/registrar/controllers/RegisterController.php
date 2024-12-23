<?php
class Registrar_RegisterController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/registrar';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	defined('NEW_STU_ID_FROM_TEST') || define('NEW_STU_ID_FROM_TEST', Setting_Model_DbTable_DbGeneral::geValueByKeyName('new_stuid_test'));//0=default,1=show stu_id register to enter
    	defined('SHOW_GROUP_INPAYMENT') || define('SHOW_GROUP_INPAYMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_groupin_payment'));
    	defined('AMOUNT_RECEIPT') || define('AMOUNT_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_print'));
    	defined('SHOW_PIC_INRECEIPT') || define('SHOW_PIC_INRECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_pic_receipt'));
    	defined('PADDINGTOP_RECEIPT') || define('PADDINGTOP_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_paddingtop'));
    	defined('ENABLE_DATE_PAYMENT') || define('ENABLE_DATE_PAYMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('payment_date'));
    }
    public function indexAction(){
		
    	try{
    		$db = new Registrar_Model_DbTable_DbRegister();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    				'adv_search' => '',
    		    	'study_year' => '',
    		    	'degree' => '',
    		    	'time'   =>'', 
    		    	'session'=>'',
    		    	'grade_all'=>'',
    		    	'branch_id'=>0,
    		    	'userId'=>'',
    		    	'start_date'=> date('Y-m-d'),
    		    	'end_date'=>date('Y-m-d')
    			);
    		}
    		$this->view->adv_search=$search;
    		$rs_rows= $db->getAllStudentRegister($search);
			$this->view->row=$rs_rows;
    		
    		// $list = new Application_Form_Frmtable();
    		// $collumns = array("BRANCH","RECEIPT_NO","STUDENT_ID","STUDENT_NAME","SEX","ACADIMIC_YEAR_FEE",
    		// 		"TOTAL_PAYMENT","CREDIT_MEMO","PAID","BALANCE",
    		// 					"PAYMENT_METHOD","CHEQUE_NO","DATE_PAY","USER","STATUS","VOID_BY");
    		
    		// $this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
//     	$db = new Api_Model_DbTable_DbsensokabaApi();
//     	$db->sendMessagetoTeleagrame('419707100','hello from php');
	
//     	$data =array(
//     			'branch_id'=>1,
//     			'studentId'=>17,
//     			'studentType'=>1,
//     			'isCurrent'=>1,
//     			'stopType'=>0,
//     			'grade'=>'',
//     			'isAutopayment'=>'',
//     			'isInititilize'=>1
//     			);
//     	$db = new Application_Model_DbTable_DbGlobal();
//     	$data=$db->getServiceForPaymentRecord($data);
//     	print_r($data);exit();
    }
    public function addAction(){
      if($this->getRequest()->isPost()){
      	$_data = $this->getRequest()->getPost();
      	try{
      		$db = new Registrar_Model_DbTable_DbRegister();
				// // echo $_POST['study_year']['data-term-study'];
				// exit();
      		$db->addRegister($_data);
      		if(!empty($_data['save_close'])){
      			//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/register");
      		}else{
      			//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/register/add");
      		}
      		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/register");
      		//Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
      	}catch (Exception $e) {
      		Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
      		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
      	}
      }
       $_db = new Application_Model_DbTable_DbGlobal();
      
       $this->view->rsbranch = $_db->getAllBranch();
       $this->view->exchange_rate = $_db->getExchangeRate();

       $this->view->rsmaincategory = $_db->getAllItems(null,null,null,0,'','',1,1);
		// print_r($_db->getAllItems(null, null, null, 0, '', '', 1, 1));
	
       $this->view->rs_type = $_db->getAllItems();
	
       $this->view->rsdiscount = $_db->getAllDiscountName();
       $this->view->rs_paymenttype = $_db->getViewById(8,null);
	   
	   $key = new Application_Model_DbTable_DbKeycode();
	   $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	   
	   $this->view->rsBank = $_db->getAllBank();

		$academicType = $_db->getViewById(6,1);

	  $this->view->academicTerm = array_slice($academicType,2,3,true);
	   
	   $frmreceipt = new Application_Form_FrmGlobal();
	   $this->view->officailreceipt = $frmreceipt->getFormatReceipt();
	   
	   $dbclass = new Application_Model_GlobalClass();
	   $this->view->term_option = $dbclass->getAllPayMentTermOption();

	   $session_user=new Zend_Session_Namespace(SYSTEM_SES);
	   $this->view->issupper_user = $session_user->issupper_user;
	   
	//   $data =  array(
	//    		'branch_id'=>1,
	//   		'studentId'=>3,
	//   		'grade'=>'',
	//   		'studentType'=>1,
	//   		'isCurrent'=>1,
	//   		'stopType'=>0,
	//   		'isAutopayment'=>3,
	//    		'isInititilize'=>1);
	//    $db = new Application_Model_DbTable_DbGlobal();
		// $param = array(
		// 	'academicYear'=>2,
		// 	'itemType'=>1
		// );
		// $result = $db->getAllTermbyItemdetail($param);
		// print_r($result);
		// echo "<br />";
		// print_r(array_column($result,'name'));
		// echo "<br />";
		//print_r(array_filter(array_combine(array_keys($result), array_column($result, 'name'))));
		// print_r(array_diff(array_combine(array_keys($result), array_column($result, 'name')), [null]));
		//$result = array_column_keys($items, 'id', 'any_key');
// 	    $result=$db->getServiceForPaymentRecord($data);
// 	    print_r($result);exit();
	// $param = array(
	// 	'branch_id' => 1,
	// 	'isFinished'=>0,
	// 	//'option' => 1,
	// );
	// 	$academicYearList = $db->getAllYearByBranch($param);
	// 	print_r($academicYearList);
	// 	echo "<br />abc<br />";
	// 	print_r(Zend_Json::encode($academicYearList));
    }
    public function addregistraAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Registrar_Model_DbTable_DbRegister();
    			print_r(Zend_Json::encode($db->addRegister($_data)));
    			exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
      		    Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    		}
    	}
    }

    function editAction(){
    	$db = new Registrar_Model_DbTable_DbRegister();
     	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
     		try{
    			$result = $db->updateRegister($_data);
    			print_r(Zend_Json::encode($result));
    			exit();
    			//Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", self::REDIRECT_URL . '/register');
     		} catch (Exception $e) {
     			Application_Form_FrmMessage::message("UPDATE_FAIL");
     			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
     		}
     	}
    }

    function getitemforpaymentrecordAction(){//for payment record
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$data=$db->getServiceForPaymentRecord($data);
    		print_r(Zend_Json::encode($data));
    		exit();
    	}
    }
    
    function getGradeproductAction(){//filter product with current grade fo student 
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$student_id = empty($data['student_id'])?null:$data['student_id'];
    		$data['dept_id'] = empty($data['dept_id'])?null:$data['dept_id'];
    		$is_stutested = empty($data['is_stutested'])?null:$data['is_stutested'];
    		$groupDetailId = empty($data['groupDetailId'])?null:$data['groupDetailId'];
    		$parentCategoryId = empty($data['parentcagetoryId'])?null:$data['parentcagetoryId'];

			$param = array(
				'studentId'=>$student_id,
				'isTestedStudent'=>$is_stutested,
				'groupDetailId'=>$groupDetailId,
				'parentcagetoryId'=>$parentCategoryId
			);

    		$rs = $db->getAllGradeStudyByDegree($param);//$data['dept_id'],$student_id,$is_stutested,$groupDetailId
			
			$data = array(
				'categoryId'=>$data['dept_id'],
				'parentcagetoryId'=>$parentCategoryId,
			);
    		$rsproduct = $db->getProductbyBranch($data);

    		if(!empty($rsproduct) OR !empty($rs)){
    			$rs = array_merge($rs,$rsproduct);
    		}
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
    function getBanlancePriceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$payment = $db->getBalance($data['service_id'],$data['student_id'],$data['type']);
    		print_r(Zend_Json::encode($payment));
    		exit();
    	}
    }
    function getstudentinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$rs = $db->getStudentProfileblog($data['student_id'],$data['data_from'],$data['customer_type']);
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
    
    function getServiceTypeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost(); 
    		$db = new Registrar_Model_DbTable_DbRegister();
    		$service_type = $db->getServiceType($data['service_id']);
    		print_r(Zend_Json::encode($service_type));
    		exit();
    	}
    }
	
	function getReceiptNoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$branch_id = empty($data['branch_id'])?null:$data['branch_id'];
			$receipt = $db->getRecieptNo($branch_id);
			print_r(Zend_Json::encode($receipt));
			exit();
		}
	}
	function getStudenttestinfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$stu_info = $db->getStudentTestInfoRegister($data['student_id']);
			print_r(Zend_Json::encode($stu_info));
			exit();
		}
	}
	function getStartDateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$validate = $db->getStartDateRegister($data['service_id'],$data['stu_id']);
			print_r(Zend_Json::encode($validate));
			exit();
		}
	}
	
	function getallstudenttestAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rows = $db->getAllstudentTest($data);

			$dbUser = new Application_Model_DbTable_DbUsers();
			$permission = $dbUser->getAccessUrl("test","index","add");
			
			if (!empty($permission)){
				array_unshift($rows, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			}
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getliststudenturlAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$branch_id = !empty($data['branch_id'])?$data['branch_id']:null;
			$customer_type = empty($data['customer_type'])?1:$data['customer_type'];
			if ($customer_type==2){
				$rows = $db->getAllCustomer(null,$branch_id);
			}else{
				$dbUser = new Application_Model_DbTable_DbUsers();
				$permission = $dbUser->getAccessUrl("foundation","register","add");
				$rows = $db->getAllListStudent($data);
				if (!empty($permission)){
					if (!empty($data['addNew'])){
						array_unshift($rows, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
					}
				}
			}
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getallstudentcrmAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$branch_id = !empty($data['branch_id'])?$data['branch_id']:null;
			$rows = $db->getAllCrmstudent($branch_id,3);
			
			$dbUser = new Application_Model_DbTable_DbUsers();
			$crmPer = $dbUser->getAccessUrl("home","crm","add");
			if (!empty($crmPer)){
				array_unshift($rows, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			}
			
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getbranchinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Form_FrmGlobal();
			$rows = $db->getHeaderReceipt($data['branch_id']);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getbranchinfodetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rows = $db->getBranchInfo($data['branch_id']);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
	function checkSessionAction(){
		if($this->getRequest()->isPost()){
			$db = new Application_Model_DbTable_DbGlobal();
			$checkses = $db->checkSessionExpire();
			print_r(Zend_Json::encode($checkses));
			exit();
		}
	}
	
	function getstartdateenddateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTerm();
			$rows = $db->getTermById($data['term_study']);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function gettermbyserviceAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$term_result = $db->getAllTermbyItemdetail($data['branch_id'],$data['year'],$data['items_id']);
			print_r(Zend_Json::encode($term_result));
			exit();
		}
	}
	// function getdiscountstudentAction()
	// {
	// 	if($this->getRequest()->isPost()){
	// 		$data = $this->getRequest()->getPost();
	// 		$db = new Application_Model_DbTable_DbGlobal();
	// 		$param =array(
	// 			'Id'=> $data['itemId']
	// 		);
	// 		$result = $db->getItemDetailRow($param);
	// 		$data['degree'] = $result['items_id'];

	// 		$result = $db->getDiscountListbyStudent($data);
	// 		print_r(Zend_Json::encode($result));
	// 		exit();
	// 	}
	// }

}