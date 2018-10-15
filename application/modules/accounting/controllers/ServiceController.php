<?php
class Accounting_ServiceController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	const REDIRECT_URL = '/accounting/service';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
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
	    				'status_search' => -1
	    		);
	    	}
	    	$type=2; //Service
	    	$rs_rows= $db->getAllItemsDetail($search,$type);
	    	$glClass = new Application_Model_GlobalClass();
	    	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("PROGRAM_TITLE","SHORTCUT","ORDERING","SERVICE_TYPE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'accounting','controller'=>'service','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('shortcut'=>$link ,'title'=>$link ,'ordering'=>$link));
	    	
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
    			echo $e->getMessage();
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
    	$this->view->degree = $d_row;
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
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms,"/accounting/service");
    			}else{
    				Application_Form_FrmMessage::message($sms);
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
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
// function submitAction(){
// 	if($this->getRequest()->isPost()){
// 		try{
// 			$data = $this->getRequest()->getPost();
// 			$db = new Accounting_Model_DbTable_DbService();
// 			$row = $db->AddServiceType($data);
// 			$result = array("id"=>$row);
// 			print_r(Zend_Json::encode($row));
// 			exit();
// 		}catch(Exception $e){
// 			Application_Form_FrmMessage::message("INSERT_FAIL");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
// 	}
// }


}