<?php
class Accounting_ServicecateController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/servicecate';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
    public function indexAction()
    {
    	$db_dept=new Global_Model_DbTable_DbItems();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'advance_search' => "",
    				'schoolOption_search'=>"",
    				'status_search' => -1
    		);
    	}
       $type =2; //service category
       $rs_rows = $db_dept->getAllItemsOption($search,$type);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("TYPE","SELECT_SERVICE_TYPE","SERVICE_TYPE_EN","PARENT","ORDERING","BY_USER","STATUS");
    	$link=array(
    			'module'=>'accounting','controller'=>'servicecate','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link,'title_en'=>$link,'schoolOption'=>$link));
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null,2);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$sms="INSERT_SUCCESS";
    			$_data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItems();
    			$degree_id= $db->AddDegree($_data);
    			if($degree_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    			Application_Form_FrmMessage::Sucessfull($sms,  self::REDIRECT_URL."/add");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	$_dbgb  = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	$this->view->schoolOption = $_dbgb->getAllSchoolOption();
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null,2);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
    public function editAction(){
    	$db = new Global_Model_DbTable_DbItems();
    	$id= $this->getRequest()->getParam("id");
    	$type =2; //service category
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$db->UpdateDegree($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",'/accounting/servicecate');
    			exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    		}
    	}
    	
    	$row =$db->getDegreeById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	
    	$_dbgb  = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	$this->view->schoolOption = $_dbgb->getAllSchoolOption();
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree($row,2);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }

	function deleteAction(){
		

		$id = $this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbItemsDetail();
		$row = $db->checkServiceCate($id);
		if (!empty($row)){
			Application_Form_FrmMessage::Sucessfull("Can not delete this record","/accounting/servicecate",2);
			exit();
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$delete_sms=$tr->translate('CONFIRM_DELETE');
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/accounting/servicecate/deleterecord/id/".$id."'";
		echo"}";
		echo"else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/accounting/servicecate'";
		echo"}
		</script>";
	}
	function deleterecordAction(){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
		
		$id = $this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbItems();
		try {
				$db->deleteItem($id);
				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/accounting/servicecate");
				exit();
			
		}catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("DELETE_FAIL");
		}
	}
}