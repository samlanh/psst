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
		  $db = new Setting_Model_DbTable_DbPickupCard();
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
			 $rs_rows= $db->getAllBranch($search);
			  $list = new Application_Form_Frmtable();
			  $collumns = array("USING","TITLE","BRANCH","SCHOOL_OPTION","NOTE","STATUS");
			  $link=array(
							'module'=>'setting','controller'=>'pickupcard','action'=>'edit',
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

	public function editAction(){
		
		$db_gs = new Setting_Model_DbTable_DbGeneral();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				//$db_gs->updateCertificatesetting($data);
				//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/setting/certificate");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$fm = new Setting_Form_FrmPickupCard();
		$frm = $fm->FrmCertificate();
		//$this->view->row = $row;
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	
}