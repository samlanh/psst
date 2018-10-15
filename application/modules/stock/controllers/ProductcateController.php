<?php
class Stock_ProductcateController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/stock/productcate';
    public function init()
    {    	
     /* Initialize action controller here */
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
//     				'type_search'=>"",
    				'schoolOption_search'=>"",
    				'status_search' => -1
    		);
    	}
    	$type =3; //service category
        $rs_rows = $db_dept->getAllItemsOption($search,$type);
        $glClass = new Application_Model_GlobalClass();
        $rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("TITLE","BY_USER","STATUS");
    	$link=array(
    			'module'=>'stock','controller'=>'productcate','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'schoolOption'=>$link));
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null);
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
    			echo $e->getMessage();
    		}
    	}
    	
    	$_dbgb  = new Application_Model_DbTable_DbGlobal();
//     	$_dbuser = new Application_Model_DbTable_DbUsers();
//     	$userid = $_dbgb->getUserId();
//     	$userinfo = $_dbuser->getUserInfo($userid);
    	$this->view->schoolOption = $_dbgb->getAllSchoolOption();
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
    
    public function editAction(){
    	$db = new Global_Model_DbTable_DbItems();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$db->UpdateDegree($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    			exit();
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("Application Error!");
    			echo $e->getMessage();
    		}
    	}
    	$type =3; //service category
    	$row =$db->getDegreeById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	
    	$_dbgb  = new Application_Model_DbTable_DbGlobal();
//     	$_dbuser = new Application_Model_DbTable_DbUsers();
//     	$userid = $_dbgb->getUserId();
//     	$userinfo = $_dbuser->getUserInfo($userid);
    	$this->view->schoolOption = $_dbgb->getAllSchoolOption();
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    	
    	
    }
    
}

