<?php
class Issuesetting_SettingscoreattController extends Zend_Controller_Action {
	const REDIRECT_URL = '/issuesetting/settingscoreatt';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
    {
    	try{
	    	$db = new Issuesetting_Model_DbTable_DbSettingScoreAtt();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'adv_search' => "",
	    				'branch_id'=>"",
	    				'start_date'=>date("Y-m-d"),
	    				'end_date'=>date("Y-m-d"),
	    				'status' => -1
	    		);
	    	}
	    	$rs_rows= $db->getAllSettingScoreAtt($search);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","DEGREE","TITLE","NOTE","CREATE_DATE","STATUS",);
	    	$link=array(
	    			'module'=>'issuesetting','controller'=>'settingscoreatt','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link ,'title'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
    }  
    public function addAction(){
    	$db = new Issuesetting_Model_DbTable_DbSettingScoreAtt();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addSettingScoreAttendance($_data);
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
    	$frm = new Issuesetting_Form_FrmSettingScoreAtt();
    	$frm->FrmAddScoreSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Issuesetting_Model_DbTable_DbSettingScoreAtt();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->editSettingScoreAttendance($_data);
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
    	$row = $db->getSettingScoreAttById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$this->view->row = $row;
    	$this->view->detail = $db->getSettingScoreAttDetail($id);
    	$frm = new Issuesetting_Form_FrmSettingScoreAtt();
    	$frm->FrmAddScoreSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    
    public function copyAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Issuesetting_Model_DbTable_DbSettingScoreAtt();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addSettingScoreAttendance($_data);
    			Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getSettingScoreAttById($id);
    	$this->view->detail = $db->getSettingScoreAttDetail($id);
    	$frm = new Issuesetting_Form_FrmSettingScoreAtt();
    	$frm->FrmAddScoreSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
	
	public function getsettingscoreAction(){
		
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Issuesetting_Model_DbTable_DbSettingScoreAtt();
    		$scheduldSetting = $db->getSettingScoreAttList($data);
    		print_r(Zend_Json::encode($scheduldSetting));
    		exit();
    	}
    }
}