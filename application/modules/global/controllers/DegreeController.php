<?php
class Global_DegreeController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
    public function indexAction()
    {
    	$db_dept=new Global_Model_DbTable_DbDegree();
    	if($this->getRequest()->isPost()){
    		$_data=$this->getRequest()->getPost();
    		$search = array(
    				'title' => $_data['title'],
    				'status' => $_data['status_search']);
    	}
    	else{
    		$search='';
    	}
        $rs_rows = $db_dept->getAllDegree($search);
        $glClass = new Application_Model_GlobalClass();
        $rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("NAME","SHORTCUT","MODIFY_DATE","STATUS","BY_USER");
    	$link=array(
    			'module'=>'global','controller'=>'degree','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('en_name'=>$link));
    	
    	$frm = new Global_Form_FrmSearchMajor();
    	$frm = $frm->FrmDepartment();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    }
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbDegree();
    			$db->AddDegree($_data);
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("ការបន្ថែមជោគជ័យ !", "/global/degree/index");
    			}
    			Application_Form_FrmMessage::Sucessfull("ការបន្ថែមជោគជ័យ !", "/global/degree/add");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("Application Error!");
    			echo $e->getMessage();
    		}
    	}
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy();
    	
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    }
    
    public function editAction(){
    	$db = new Global_Model_DbTable_DbDegree();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$db->UpdateDegree($_data);
    			Application_Form_FrmMessage::Sucessfull("ការកៃប្រែដោយជោគជ័យ !", "/global/degree/index");
    			//$this->_redirect("");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("Application Error!");
    			echo $e->getMessage();
    		}
    	}
    	
    	$this->view->row = $db->getDeptSubjectById($id);
    	$_db = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_db->getAllSubjectStudy();
    	$row =$db->getDeptById($id);
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    }
    function addFacultyAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Global_Model_DbTable_DbDegree();
    		$faculty_id = $db->AddNewDepartment($data);
    		print_r(Zend_Json::encode($faculty_id));
    		exit();
    		
    	}
    }
  
}

