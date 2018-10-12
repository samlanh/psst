<?php
class Rsvacl_CardmgController extends Zend_Controller_Action {
	const REDIRECT_URL='/rsvacl';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
      try{
    	$db = new RsvAcl_Model_DbTable_DbCardmg();
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
           $glClass = new Application_Model_GlobalClass();
			$rs_rowshow = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("USING","TITLE","BRANCH","SCHOOL_OPTION","NOTE","STATUS");
			$link=array(
					      'module'=>'rsvacl','controller'=>'cardmg','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rowshow,array('title'=>$link,'branch_name'=>$link,'prefix'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$fm = new RsvAcl_Form_FrmCardMg();
		$frm = $fm->FrmCardmg();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	function addAction()
	{
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				$_dbmodel = new RsvAcl_Model_DbTable_DbCardmg();
				$branch_id= $_dbmodel->addCardMg($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/cardmg/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/cardmg/add");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				echo $e->getMessage();exit();
			}
		}

		$fm = new RsvAcl_Form_FrmCardMg();
		$frm = $fm->FrmCardmg();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
		$id=$this->getRequest()->getParam("id");
		$_dbmodel = new RsvAcl_Model_DbTable_DbCardmg();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "EDIT_SUCCESS";
				
				$branch_id= $_dbmodel->updateCardMG($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/cardmg/index");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				echo $e->getMessage();exit();
			}
		}
	
		$row=$_dbmodel->getCardmgById($id);
		$this->view->rs = $row;
		
		$fm = new RsvAcl_Form_FrmCardMg();
		$frm = $fm->FrmCardmg($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
		
		
		
	}
	
	function addbranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
		
    		$db = new RsvAcl_Model_DbTable_DbCardmg();
    		$gty= $db->addajaxs($data);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}   
    }
}

