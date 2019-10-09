<?php
class Placement_SettingController extends Zend_Controller_Action {
	const REDIRECT_URL = '/placement/setting';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
    {
    	try{
	    	$db = new Placement_Model_DbTable_DbSetting();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'adv_search' => "",
	    				'branch_search'=>"",
	    				'start_date'=>date("Y-m-d"),
	    				'end_date'=>date("Y-m-d"),
	    				'status' => ''
	    		);
	    	}
	    	$rs_rows= $db->getAllPlacementTestSetting($search);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","TITLE","NOTE","CREATE_DATE","STATUS",);
	    	$link=array(
	    			'module'=>'placement','controller'=>'setting','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link ,'title'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Placement_Form_FrmSection();
    	$frm->FrmSearch(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form_search = $frm;
    }  
    public function addAction(){
    	$db = new Placement_Model_DbTable_DbSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addPlacementTestSetting($_data);
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
    	$frm = new Placement_Form_FrmPlacementTestSetting();
    	$frm->FrmAddPlacementTestSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Placement_Model_DbTable_DbSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->editPlacementTestSetting($_data);
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
    	
    	$check = $db->checkSettingInUse($id);
    	if (!empty($check)){
    		Application_Form_FrmMessage::messageAlert("UNAVAILABLE_TO_EDIT", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$row = $db->getPlacementTestSettingById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::messageAlert("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$this->view->detail = $db->getPlacementTestSettingDetail($id);
    	$frm = new Placement_Form_FrmPlacementTestSetting();
    	$frm->FrmAddPlacementTestSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    
    public function copyAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Placement_Model_DbTable_DbSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addPlacementTestSetting($_data);
    			Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getPlacementTestSettingById($id);
    	$this->view->detail = $db->getPlacementTestSettingDetail($id);
    	$frm = new Placement_Form_FrmPlacementTestSetting();
    	$frm->FrmAddPlacementTestSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    function allsettingsAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		 
    		$test_type = empty($data['test_type'])?"":$data['test_type'];
    		$arr  = array(
    				'test_type'=>$test_type,
    		);
    		$_dbmodel = new Application_Model_DbTable_DbGlobal();
    		$result=$_dbmodel->getAllSetting($arr);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
}