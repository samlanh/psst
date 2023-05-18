<?php
class Rsvacl_CardmgController extends Zend_Controller_Action {
	const REDIRECT_URL='/rsvacl';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
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
        
			$list = new Application_Form_Frmtable();
			$collumns = array("USING","TITLE","BRANCH","SCHOOL_OPTION","Card Type","Valid Date","NOTE","STATUS");
			$link=array(
					      'module'=>'rsvacl','controller'=>'cardmg','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'branch_name'=>$link,'prefix'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
			}
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllDegreeName();

		$fm = new RsvAcl_Form_FrmCardMg();
		$frm = $fm->FrmCardmg();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
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
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row=$_dbmodel->getCardmgById($id);
		$this->view->rs = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/cardmg/index");
			exit();
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllDegreeName();
		
		$fm = new RsvAcl_Form_FrmCardMg();
		$frm = $fm->FrmCardmg($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	
	=
}

