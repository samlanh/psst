<?php
class Global_DegreeController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/degree';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
    	$type=1; //Degree
        $rs_rows = $db_dept->getAllItems($search,$type);
        $glClass = new Application_Model_GlobalClass();
        $rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    	$list = new Application_Form_Frmtable();
    	$collumns = array("FACULTY_KHNAME","FACULTY_ENNAME","SCHOOL_OPTION","BY_USER","CREATE_DATE","MODIFY_DATE","STATUS");
    	$link=array(
    			'module'=>'global','controller'=>'degree','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'title_en'=>$link,'schoolOption'=>$link));
    	
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
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    			Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	
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
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	$type=1; //Degree
    	$row =$db->getDegreeById($id,$type);
    	$rs =  $db->getDeptSubjectById($id);
    	$this->view->row=$row;
    	$this->view->rowdetail = $rs;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    	
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy(null,$row['schoolOption']);    	 
    }
    function refreshitemsAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$type = empty($data['items_type'])?null:$data['items_type'];
    		$d_row = $_dbgb->getAllItems($type);
    		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		print_r(Zend_Json::encode($d_row));
    		exit();
    	}
    }
	function adddegreeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_dbmodel = new Global_Model_DbTable_DbItems();
    		$type=1; //Degree
    		$result=$_dbmodel->addItemsajax($data,$type);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    function addsubjectajaxAction(){//At callecteral when click client
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
    		$option=$_dbmodel->addSubjectajax($data);
    		$result = array("id"=>$option);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
}