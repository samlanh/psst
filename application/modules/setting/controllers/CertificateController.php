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
		try{
		  $db = new Setting_Model_DbTable_DbCertificate();
		   if($this->getRequest()->isPost()){
			  $_data=$this->getRequest()->getPost();
				 $search = array(
					 'adv_search' => $_data['adv_search'],
					 'status' => $_data['status_search']);
		   }else{
				  $search = array(
					'adv_search' => '',
					'status' => -1);   		
				 }
			 $rs_rows= $db->getAllCertificate($search);
			  $list = new Application_Form_Frmtable();
			  $collumns = array("TITLE","BRANCH","SCHOOL_OPTION","CREATE_DATE","STATUS");
			  $link=array(
							'module'=>'setting','controller'=>'certificate','action'=>'edit',
			  );
			  $this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'branch_name'=>$link,'prefix'=>$link));
		  }catch (Exception $e){
			  Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			  Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		  }
		  $fm = new Setting_Form_FrmPickupCard();
		  $frm = $fm->FrmCardmg();
		  Application_Model_Decorator::removeAllDecorator($frm);
		  $this->view->frm_branch = $frm;
	
	  }	
	public function addAction(){
		
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				$_dbmodel = new Setting_Model_DbTable_DbCertificate();
				$branch_id= $_dbmodel->addCertifSetting($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/certificate/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/certificate/add");
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

	public function editAction(){
		
		$id=$this->getRequest()->getParam("id");
		$_dbmodel = new Setting_Model_DbTable_DbCertificate();
		
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try{
				$sms = "EDIT_SUCCESS";
				
				$branch_id= $_dbmodel->updateCertificate($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/certificate/index");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
			}
		}
		$id = empty($id)?0:$id;
		
		$row=$_dbmodel->getCertifById($id);
		
		$this->view->rs = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/pickupcard/index");
			exit();
		}

		$fm = new Setting_Form_FrmPickupCard();
		$frm = $fm->FrmCertificate($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	
}