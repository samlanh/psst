<?php
class Setting_CertificateController extends Zend_Controller_Action {
	const REDIRECT_URL='/setting';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				$_dbmodel = new Setting_Model_DbTable_DbPickupCard();
				$branch_id= $_dbmodel->addPickupCard($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/pickupcard/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/pickupcard/add");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
			}
		}

		$fm = new Setting_Form_FrmPickupCard();
		$frm = $fm->FrmCertificate();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	// function addAction()
	// {
	// 	if($this->getRequest()->isPost()){//check condition return true click submit button
	// 		$_data = $this->getRequest()->getPost();	
	// 		try {
	// 			$sms = "INSERT_SUCCESS";
	// 			$_dbmodel = new Setting_Model_DbTable_DbPickupCard();
	// 			$branch_id= $_dbmodel->addPickupCard($_data);
	// 			if($branch_id==-1){
	// 				$sms = "RECORD_EXIST";
	// 			}
	// 			if(!empty($_data['save_close'])){
	// 				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/pickupcard/index");
	// 			}else{
	// 				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/pickupcard/add");
	// 			}
	// 		}catch (Exception $e) {
	// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	// 			Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
	// 		}
	// 	}

	// 	$fm = new Setting_Form_FrmPickupCard();
	// 	$frm = $fm->FrmCardmg();
	// 	Application_Model_Decorator::removeAllDecorator($frm);
	// 	$this->view->frm_branch = $frm;
	// }
	// function editAction(){
	// 	$id=$this->getRequest()->getParam("id");
	// 	$_dbmodel = new Setting_Model_DbTable_DbPickupCard();
		
	// 	if($this->getRequest()->isPost()){//check condition return true click submit button
	// 		$_data = $this->getRequest()->getPost();	
	// 		try {
	// 			$sms = "EDIT_SUCCESS";
				
	// 			$branch_id= $_dbmodel->updateCardMG($_data);
	// 			if($branch_id==-1){
	// 				$sms = "RECORD_EXIST";
	// 			}
	// 			if(!empty($_data['save_close'])){
	// 				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/pickupcard/index");
	// 			}
	// 		}catch (Exception $e) {
	// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	// 			Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
	// 		}
	// 	}
	// 	$id = empty($id)?0:$id;
		
	// 	$row=$_dbmodel->getCardmgById($id);
		
	// 	$this->view->rs = $row;
	// 	if (empty($row)){
	// 		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/pickupcard/index");
	// 		exit();
	// 	}
		
	// 	$fm = new Setting_Form_FrmPickupCard();
	// 	$frm = $fm->FrmCardmg($row);
	// 	Application_Model_Decorator::removeAllDecorator($frm);
	// 	$this->view->frm_branch = $frm;
	// }
}