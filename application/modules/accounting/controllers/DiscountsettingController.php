<?php
class Accounting_DiscountSettingController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
						'branch_id' => $_data['branch_id'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'branch' => '',
						'status' => -1);
			}
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
  			$rs_rows= $db->getAllDiscountset($search);
        	
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","DISCOUNT_TYPR","DIS_MAX","START_DATE","END_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'discountsetting','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('disc_name'=>$link,'dis_max'=>$link,'branch'=>$link));
			}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchDiscount();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->form_search = $frms;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Accounting_Model_DbTable_DbDiscountSetting();
				$_discount = $_dbmodel->addNewDiscountset($_data);
				if($_discount==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/accounting/discountsetting");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/accounting/discountsetting/add");
				}
				Application_Form_FrmMessage::message($sms);				
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		array_unshift($disc, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->discount = $disc;
		
		$tsub=new Accounting_Form_FrmDiscount();
		$frm_discount=$tsub->FrmDiscountsetting();
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
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
		$this->view->rs= $rows;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		array_unshift($disc, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->discount = $disc;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$dis = $model->getAllDiscount();
		$this->view->discount = $dis;
		
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

