<?php
class Foundation_FacultyController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
    public function indexAction()
    {
    	$db_dept=new Global_Model_DbTable_DbDept();
    	if($this->getRequest()->isPost()){
    		$_data=$this->getRequest()->getPost();
    		$search = array(
    				'title' => $_data['title'],
    				'status' => $_data['status_search']);
    	}
    	else{
    		$search='';
    	}
    	
        $rs_rows = $db_dept->getAllFacultyList($search);
        $glClass = new Application_Model_GlobalClass();
        $rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("NAME","SHORTCUT","MODIFY_DATE","STATUS","BY_USER");
    	$link=array(
    			'module'=>'foundation','controller'=>'faculty','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('en_name'=>$link));
    	$frm = new Global_Form_FrmSearchMajor();
    	$frm = $frm->FrmDepartment();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    	$frm = new Application_Form_FrmOther();
    	$frm =  $frm->FrmAddDept(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->add_dept = $frm;
    }
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$_dbmodel = new Application_Model_DbTable_DbDept();
    			$_dbmodel->AddNewDepartment($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("ការកៃប្រែដោយជោគជ័យ !", "/foundation/faculty/index");
    			}
    			Application_Form_FrmMessage::Sucessfull("ការកៃប្រែដោយជោគជ័យ !", "/foundation/faculty/add");
    			//$this->_redirect("");
    		} catch (Exception $e) {
    			$err =$e->getMessage();
    			Application_Form_FrmMessage::message("Application Error!");
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy();
    	
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    	//$this->_redirect("/foundation/faculty");
    }
    public function editAction(){
    	$_dbmodel = new Application_Model_DbTable_DbDept();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$_dbmodel->UpdateDepartment($_data);
    			Application_Form_FrmMessage::Sucessfull("ការកៃប្រែដោយជោគជ័យ !", "/foundation/faculty/index");
    			//$this->_redirect("");
    		} catch (Exception $e) {
    			$err =$e->getMessage();
    			Application_Form_FrmMessage::message("Application Error!");
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	
    	$this->view->row = $_dbmodel->getDeptSubjectById($id);
    	
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy();
    	
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$_row =$_db->getDeptById($id);
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept($_row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    }
    function addFacultyAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbDept();
    		$faculty_id = $db->AddNewDepartment($data);
    		print_r(Zend_Json::encode($faculty_id));
    		exit();
    		
    	}
    }
  
}

