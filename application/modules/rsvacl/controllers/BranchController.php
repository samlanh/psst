<?php
class Rsvacl_BranchController extends Zend_Controller_Action {
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
    	$db = new RsvAcl_Model_DbTable_DbBranch();
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
			$collumns = array("SCHOOL_NAMEKH","SCHOOL_NAMEEN","BRANCH","PARENT_BRANCH","PREFIX_CODE","CODE","ADDRESS","PHONE","PHONE1","BRANCH_FAX","NOTE","STATUS");
			$link=array(
					      'module'=>'rsvacl','controller'=>'branch','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns,
					$rs_rowshow,array('school_namekh'=>$link,'school_nameen'=>$link,
							'parent_name'=>$link,'branch_nameen'=>$link,'prefix'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new RsvAcl_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	function addAction()
	{
		$_dbmodel = new RsvAcl_Model_DbTable_DbBranch();
		$count = $_dbmodel->getAllBranchCount();
		if ($count>=BRANCHES){
			$this->_redirect(self::REDIRECT_URL ."/branch/index");
		}
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				
				$branch_id= $_dbmodel->addbranch($_data);
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/branch/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/branch/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_dbmodel = new RsvAcl_Model_DbTable_DbBranch();
		$this->view->schoolOption = $_dbmodel->getAllSchoolOption();
		
		$fm = new RsvAcl_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new RsvAcl_Model_DbTable_DbBranch();
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			try{
				$db->updateBranch($data,$id);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"),self::REDIRECT_URL."/branch/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_dbmodel = new RsvAcl_Model_DbTable_DbBranch();
		$this->view->schoolOption = $_dbmodel->getAllSchoolOption();
		
		$row=$db->getBranchById($id);
		$this->view->rs = $row;
		$frm= new RsvAcl_Form_Frmbranch();
		$update=$frm->FrmBranch($row);
		$this->view->frm_branch=$update;
		Application_Model_Decorator::removeAllDecorator($update);
	}
	function copyAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new RsvAcl_Model_DbTable_DbBranch();
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			try{
				$branch_id= $db->addbranch($data);
				$sms = "INSERT_SUCCESS";
				if($branch_id==-1){
					$sms = "RECORD_EXIST";
				}
				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/branch/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
	
		$_dbmodel = new RsvAcl_Model_DbTable_DbBranch();
		$this->view->schoolOption = $_dbmodel->getAllSchoolOption();
	
		$row=$db->getBranchById($id);
		$this->view->rs = $row;
		$frm= new RsvAcl_Form_Frmbranch();
		$update=$frm->FrmBranch($row);
		$this->view->frm_branch=$update;
		Application_Model_Decorator::removeAllDecorator($update);
	}
	function addbranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();		
    		$db = new RsvAcl_Model_DbTable_DbBranch();
    		$gty= $db->addajaxs($data);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}   
    }
}