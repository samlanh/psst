<?php
class Registrar_InitilizeuseController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Registrar_Model_DbTable_DbInitilizeservice();
				$_discount = $_dbmodel->addInitilizeService($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/initilizeuse/index");
				}elseif(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/initilizeuse/add");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		array_unshift($disc, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->discount = $disc;
		$this->view->rsdiscount = $model->getAllDiscountName();
		
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

