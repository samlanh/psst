<?php
class Global_ParttimelistController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/parttimelist';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		
	}
	public function indexAction()
    {
    	try{
	    	$db = new Global_Model_DbTable_DbPartTimeList();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'branch_search'=>"",
	    				'start_date'=>date("Y-m-d"),
	    				'end_date'=>date("Y-m-d"),
	    				'status_search' => -1
	    		);
	    	}
	    	$rs_rows= $db->getAllScoreEntrySetting($search);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","TITLE","DESCRIPTION","CREATE_DATE","STATUS",);
	    	$link=array(
	    			'module'=>'global','controller'=>'parttimelist','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link ,'title'=>$link ,'description'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Global_Form_FrmParttimeList();
    	$frm->FrmAddPartimeList(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Global_Model_DbTable_DbPartTimeList();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addPartTimeList($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllDegreeName();

    	$frm = new Global_Form_FrmParttimeList();
    	$frm->FrmAddPartimeList(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Global_Model_DbTable_DbPartTimeList();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->editPartTimeList($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getPartTimeListById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$this->view->rs = $row;
		
    	$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllDegreeName();

    	$frm = new Global_Form_FrmParttimeList();
    	$frm->FrmAddPartimeList($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    
    public function copyAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Global_Model_DbTable_DbPartTimeList();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->addPartTimeList($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getPartTimeListById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$this->view->rs = $row;
		
    	$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllDegreeName();

    	$frm = new Global_Form_FrmParttimeList();
    	$frm->FrmAddPartimeList($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
	
	function getParttimeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$data['degree'] = empty($data['degree']) ? 0 : $data['degree'];
			$grade = $_dbgb->getAllPartTimeList($data);
			array_unshift($grade, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_PARTTIME_SHIFT")));
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
  
}