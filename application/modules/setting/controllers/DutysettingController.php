<?php
class Setting_DutysettingController extends Zend_Controller_Action {
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
    	$db = new Setting_Model_DbTable_Dbduty();
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
           $rs_rows= $db->getAllDutySetting($search);
           $glClass = new Application_Model_GlobalClass();
			$rs_rowshow = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("NAME_KH","NAME_EN","POSITION_KH","POSITION_EN","CREATE_DATE","STATUS");
			$link=array(
					      'module'=>'setting','controller'=>'dutysetting','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns,
					$rs_rowshow,array('duty_namekh'=>$link,'duty_nameen'=>$link,
							'positionkh'=>$link,'positionen'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Setting_Form_Frmduty();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}	
	function addAction()
	{
		$_dbmodel = new Setting_Model_DbTable_Dbduty();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				$_dbmodel->addDutySetting($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/dutysetting/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/dutysetting/add");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
			}
		}
		
		$fm = new Setting_Form_Frmduty();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new Setting_Model_DbTable_Dbduty();
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			try{
				$db->updateDutySetting($data,$id);
				$sms = "EDIT_SUCCESS";
				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL ."/dutysetting/index");
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
			}
		}
		$row=$db->getDutyById($id);
		$this->view->rs = $row;
		$frm= new Setting_Form_Frmduty();
		$update=$frm->FrmBranch($row);
		$this->view->frm_branch=$update;
		Application_Model_Decorator::removeAllDecorator($update);
	}
    // function checkduplicateAction(){
    // 	if($this->getRequest()->isPost()){
    // 		$data = $this->getRequest()->getPost();
    
    // 		$prefix_code = empty($data['prefix_code'])?"":$data['prefix_code'];
    // 		$id = empty($data['id'])?"":$data['id'];
    // 		$arr  = array(
    // 				'prefix_code'=>$prefix_code,
    // 				'id'=>$id,
    // 		);
    // 		$_dbmodel = new Setting_Model_DbTable_Dbduty();
    // 		$result=$_dbmodel->checkuDuplicatePrefix($arr);
    // 		print_r(Zend_Json::encode($result));
    // 		exit();
    // 	}
    // }
}