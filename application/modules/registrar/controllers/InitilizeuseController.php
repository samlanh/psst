<?php
class Registrar_InitilizeuseController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
// 		try{
// 			if($this->getRequest()->isPost()){
// 				$search=$this->getRequest()->getPost();
// 			}
// 			else{
// 				$search = array(
// 						'title' => '',
// 						'branch' => '',
// 						'studentId'=>'',
// 						'discountId'=>'',
// 						'status_search' =>-1
// 						);
// 			}
// 			$db = new Accounting_Model_DbTable_DbDiscountSetting();
//   			$rs_rows= $db->getAllDiscountset($search);
        	
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BRANCH","DISCOUNT_OPTION","STUDENT_NAME","TYPE","COURSE_SERVICE_PRODUCT","DISCOUNT_TYPE","DIS_MAX","START_DATE","END_DATE","BY_USER","STATUS");
// 			$link=array(
// 					'module'=>'accounting','controller'=>'discountsetting','action'=>'edit',
// 			);
// 			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link,'disc_name'=>$link,'discountValue'=>$link,
// 					'discountOption'=>$link,'studentName'=>$link,'itemType'=>$link));
// 			}catch (Exception $e){
// 				Application_Form_FrmMessage::message("Application Error");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
			
// 			$this->view->adv_search = $search;
// 			$frm = new Global_Form_FrmSearchMajor();
// 			$frms =$frm->FrmsearchDiscount();
// 			Application_Model_Decorator::removeAllDecorator($frms);
// 			$this->view->form_search = $frms;
			
// 			$model = new Application_Model_DbTable_DbGlobal();
// 			$disc = $model->getAllDiscount();
// 			$this->view->discount = $disc;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Registrar_Model_DbTable_DbInitilizeservice();
				$_discount = $_dbmodel->addInitilizeService($_data);
				//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/initilizeuse/add");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		array_unshift($disc, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->discount = $disc;
		
		$this->view->itemType = $model->getAllItems();
		
		$tsub=new Accounting_Form_FrmDiscount();
		$frm_discount=$tsub->FrmDiscountsetting();
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost())
		{
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
				$data = $this->getRequest()->getPost();
				$db = new Accounting_Model_DbTable_DbDiscountSetting();
				$db->updateDiscountset($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/discountsetting/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Accounting_Model_DbTable_DbDiscountSetting();
		$rows = $db->getDiscountsetById($id);
		if (empty($rows)){
			Application_Form_FrmMessage::Sucessfull("No Record","/accounting/discountsetting");
			exit();
		}
		$this->view->rs= $rows;
		
		$model = new Application_Model_DbTable_DbGlobal();		
		$dis = $model->getAllDiscount();
		$this->view->discount = $dis;
		
		$this->view->itemType = $model->getAllItems();
		
		$tsub=new Accounting_Form_FrmDiscount();
		$frm_discount=$tsub->FrmDiscountsetting($rows);
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;
	}
	function addcompositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$id = $db->addDiscounttionset($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function adddiscountAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$gty= $db->addNewDiscountPopup($data);
			print_r(Zend_Json::encode($gty));
			exit();
		}
	}
	
}

