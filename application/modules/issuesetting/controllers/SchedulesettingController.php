<?php
class Issuesetting_SchedulesettingController extends Zend_Controller_Action {
	const REDIRECT_URL = '/issuesetting/schedulesetting';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
    {
    	try{
	    	$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
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
	    	$rs_rows= $db->getAllScheuleSetting($search);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","TITLE","NOTE","CREATE_DATE","STATUS",);
	    	$link=array(
	    			'module'=>'issuesetting','controller'=>'schedulesetting','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link ,'title'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Issuesetting_Form_FrmScheduleSetting();
    	$frm->FrmAddScheduleSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addScheduleSetting($_data);
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
    	$frm = new Issuesetting_Form_FrmScheduleSetting();
    	$frm->FrmAddScheduleSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$db_glob = new Application_Model_GlobalClass();
    	$this->view->opttime = $db_glob->getHoursStudy();
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->editScheduleSetting($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getScheduleSettingById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$check = $db->getCheckScheduleSettingInSchedule($id);
    	if (!empty($check)){
    		Application_Form_FrmMessage::Sucessfull("UNAVAILABLE_TO_EDIT", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$this->view->detail = $db->getScheduleSettingDetail($id);
    	$frm = new Issuesetting_Form_FrmScheduleSetting();
    	$frm->FrmAddScheduleSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$db_glob = new Application_Model_GlobalClass();
    	$this->view->opttime = $db_glob->getHoursStudy();
    }
    
    public function copyAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addScheduleSetting($_data);
    			Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getScheduleSettingById($id);
    	$this->view->detail = $db->getScheduleSettingDetail($id);
    	
    	$frm = new Issuesetting_Form_FrmScheduleSetting();
    	$frm->FrmAddScheduleSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$db_glob = new Application_Model_GlobalClass();
    	$this->view->opttime = $db_glob->getHoursStudy();
    }
    
    public function getschedulesettingAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    		$scheduldSetting = $db->getScheduleSettingByBranch($data['branch_id']);
    		print_r(Zend_Json::encode($scheduldSetting));
    		exit();
    	}
    }
    
    public function getschedulesettingdetailAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    		$scheduldSetting = $db->getScheduleDetialBySchedule($data);
    		print_r(Zend_Json::encode($scheduldSetting));
    		exit();
    	}
    }
    
    public function getschedulesettingdetaileditAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Issuesetting_Model_DbTable_DbScheduleSetting();
    		$scheduldSetting = $db->getScheduleDetialByScheduleEdit($data);
    		print_r(Zend_Json::encode($scheduldSetting));
    		exit();
    	}
    }
}