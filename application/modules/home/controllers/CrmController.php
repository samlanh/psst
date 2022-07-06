<?php

class Home_CrmController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home/crm';
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
	  		if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    			'advance_search' => "",
	    			'branch_search'  => "",
	    			'ask_for_search' => "",
	    			'know_by_search' => "",
	    			'prev_concern'	 => "",
	    			'status_search'  => -1,
	    			'followup_status'=>-1,
	    			'start_date'     => date('Y-m-d'),
	    			'end_date'       => date('Y-m-d'),
	    		);
	    	}
	    	$this->view->search = $search;
	    	$db = new Home_Model_DbTable_DbCRM();
	    	$rs_rows = $db->getAllCRM($search);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","STUDENT_NAMEKHMER","Last Name","First Name","GENDER","PHONE","ASK_FOR",
	    			"FOLLOWU_STATUS","AMOUNT_STUDENT","IS_STUDENT","DATE",
	    			"STATUS","Amount Contacted","BY_USER");
	    	$link=array(
	    			'module'=>'home','controller'=>'crm','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'kh_name'=>$link,
	    			'first_name'=>$link,'last_name'=>$link,'tel'=>$link
	    			));
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::message("Application Error");
    	}
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$pevconcer = $_dbgb->getViewByType(22);
    	$this->view->prev_concern = $pevconcer;
    	
    	$frm = new Home_Form_FrmCrm();
    	$frm->FrmAddCRM(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try{
    			$db = new Home_Model_DbTable_DbCRM();
    			$row = $db->AddCRM($_data);
    	
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
    			}
    			Application_Form_FrmMessage::message("INSERT_SUCCESS");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$pevconcer = $_dbgb->getViewByType(22);
    	$this->view->prev_concern = $pevconcer;
    	
    	$degree = $_dbgb->getAllItems(1);
    	array_unshift($degree, array ( 'id' => '','name' =>$this->tr->translate("PLEASE_SELECT")));
    	$this->view->degree = $degree;
    	
    	$frm = new Home_Form_FrmCrm();
    	$frm->FrmAddCRM(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$row = $_db->getAllKnowBy(); // degree language
    	array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
    	$this->view->know_by = $row;
    }

    public function editAction()
    {
    	$db = new Home_Model_DbTable_DbCRM();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try{
    			$row = $db->updateCrm($_data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	 
    	$row = $db->getCRMById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL);
    	}
    	
    	$rowdetail = $db->getCRMDetailById($id);
    	$this->view->rowdetail = $rowdetail;
    	
    	$allContact = $db->AllHistoryContact($id);
    	$this->view->history = $allContact;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$pevconcer = $_dbgb->getViewByType(22);
    	$this->view->prev_concern = $pevconcer;
    	
    	$pre = explode(",", $row['prev_concern']);
    	$prevCon="";
    	if (!empty($row['prev_concern'])) foreach ($pre as $a){
    		$title = $db->getPrevTilteByKeyCode($a);
    		if(empty($prevCon)){ 
    			$prevCon = $title;
    		}else{
    			if(!empty($title)){
    				$prevCon = $prevCon.",".$title;
    			}
    		}
    	}
    	$this->view->prevconcern = $prevCon;
    	$frm = new Home_Form_FrmCrm();
    	$frm->FrmAddCRM($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
	public function contactAction(){
		$db = new Home_Model_DbTable_DbCRM();
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$row = $db->addCrmContactHistory($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getCRMById($id);
		if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL."/index");
    	}
		$this->view->row = $row;
		$allContact = $db->AllHistoryContact($id);
		$this->view->history = $allContact;
		$frm = new Home_Form_FrmCrm();
		$frm->FrmAddCRMContactHistory($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_crmhistory = $frm;
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$pevconcer = $_dbgb->getViewByType(34);
		$this->view->feedback_type = $pevconcer;
	}
	
	function getcrmbybranchAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Home_Model_DbTable_DbCRM();
			$group = $db->getAllCrmFilter($data['branch_id']);
			array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("PLEASE_SELECT")));
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
}