<?php
class Global_GradeController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/global/grade';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
	    	$type=1; //Degree
	    	$rs_rows= $db->getAllItemsDetail($search,$type);
	    	$glClass = new Application_Model_GlobalClass();
	    	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("GRADE_KH","GRADE_EN","SHORTCUT","ORDERING","DEGREE","CREATE_DATE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'global','controller'=>'grade','action'=>'edit',
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
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::message($sms);
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    			
    	}
    	$type=1; //Degree
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	$d_row = $_dbgb->getAllItems(1);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    	$this->view->subjectlist = $_dbgb->getAllSubjectStudy();
    	$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption($userinfo['branch_list']);
    	
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy();
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
    	$type=1; //Degree
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
    	$d_row = $_dbgb->getAllItems(1);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    	
    }
    
    public function copyAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$db->AddItemsDetail($_data,$id);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=1; //Degree
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }
    function adddegreeAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItemsDetail();
    			$degree = $db->addDegreeByAjax($data);
    			print_r(Zend_Json::encode($degree));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    function addDeptandsubjectAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItemsDetail();
    			$degree = $db->addDegreeByAjax($data);
    			 
    			$_db = new Global_Model_DbTable_DbGroup();
    			$sub_option = $_db->getAllSubjectStudy();
    			 
    			$result = array(
    					"degree"=>$degree,
    					"sub_option"=>$sub_option,
    			);
    			 
    			print_r(Zend_Json::encode($result));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
}