<?php
class Accounting_ServiceController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/service';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction()
    {
    	try{
	    	$db = new Global_Model_DbTable_DbItemsDetail();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'items_search'=>"",
	    				'status_search' => -1,
	    				'is_onepayment'=>-1,
	    				'auto_payment'=>-1
	    		);
	    	}
	    	$type=2; //Service
	    	$rs_rows= $db->getAllItemsDetail($search,$type);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("SERVICE_NAME","SERVICE_NAME_EN","SHORTCUT","SERVICE_TYPE","ONE_PAYMENT","ORDERING","NOTE","CREATE_DATE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'accounting','controller'=>'service','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('shortcut'=>$link ,'title'=>$link ,'title_en'=>$link,'ordering'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->AddItemsDetail($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms,"/accounting/service");
    			}else{
    				Application_Form_FrmMessage::message($sms);
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}    			
    	}
    	$type=2; //Service
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$d_row = $_dbgb->getAllItems(2);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->rsservice = $d_row;
    }
    public function editAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$db->updateItemsDetail($_data,$id);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=2; //Service
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$d_row = $_dbgb->getAllItems(2);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    }
    public function copyAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
   		 if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->AddItemsDetail($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    				Application_Form_FrmMessage::Sucessfull($sms,"/accounting/service");
    				Application_Form_FrmMessage::message($sms);
    			
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=2; //Service
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	 
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	 
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$d_row = $_dbgb->getAllItems(2);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    }

	function deleteAction(){
		
		$id = $this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbItemsDetail();
		$row = $db->checkService($id);
		if (!empty($row)){
			Application_Form_FrmMessage::Sucessfull("Can not delete this record","/accounting/service",2);
			exit();
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$delete_sms=$tr->translate('CONFIRM_DELETE');
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/accounting/service/deleterecord/id/".$id."'";
		echo"}";
		echo"else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/accounting/service'";
		echo"}
		</script>";
	}
	function deleterecordAction(){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
		
		$id = $this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbItemsDetail();
		try {
				$db->deleteItemDetail($id);
				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/accounting/service");
				exit();
			
		}catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("DELETE_FAIL");
		}
	}
}