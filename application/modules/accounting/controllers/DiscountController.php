<?php
class Accounting_DiscountController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
 			$db = new Accounting_Model_DbTable_DbDiscount();
 			$rs_rows= $db->getAllDiscount($search);
 			$glClass = new Application_Model_GlobalClass();
 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("DISCOUNT_TYPR","CREATED_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'discount','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('dis_name'=>$link,'doc_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Accounting_Model_DbTable_DbDiscount();
				$_discount = $_dbmodel->addNewDiscount($_data);
				if($_discount==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/accounting/discount");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/accounting/discount/add");
				}
				Application_Form_FrmMessage::message($sms);				
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Accounting_Model_DbTable_DbDiscount();
				$db->updateDiscount($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/discount/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$db = new Accounting_Model_DbTable_DbDiscount();
		$this->view->rs=$db->getDiscountById($id);
	}
// 	function addcompositionAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Accounting_Model_DbTable_DbDiscount();
// 			$id = $db->addDiscounttion($data);
// 			print_r(Zend_Json::encode($id));
// 			exit();
// 		}
// 	}
}