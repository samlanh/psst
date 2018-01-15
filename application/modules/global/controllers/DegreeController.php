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
    	$collumns = array("NAME","SHORTCUT","MODIFY_DATE","BY_USER","STATUS");
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
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/global/degree/index");
    			}
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/global/degree/add");
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
    	
    	$subject_exam=new Global_Form_FrmAddSubjectExam();
    	$frm_subject_exam=$subject_exam->FrmAddSubjectExam();
    	Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
    	$this->view->frm_subject_exam = $frm_subject_exam;
    	
    	$parent = new Global_Model_DbTable_DbSubjectExam();
    	$is_parent = $parent->getAllSubjectParent();
    	$sub = $parent->getAllSubjectParent(1);
    	$this->view->rs = $is_parent;
    	$this->view->sub = $sub;
    }
    
    public function editAction(){
    	$db = new Global_Model_DbTable_DbDegree();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try {
    			$_data = $this->getRequest()->getPost();
    			$db->UpdateDegree($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/global/degree/index");
    			//$this->_redirect("");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("Application Error!");
    			echo $e->getMessage();
    		}
    	}
    	
    	$this->view->row = $db->getDeptSubjectById($id);
    	$_db = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_db->getAllSubjectStudy();
    	$this->view->result = $row =$db->getDeptById($id);
    	$frm = new Application_Form_FrmOther();
    	$frm->FrmAddDept($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_dept = $frm;
    	
    	$subject_exam=new Global_Form_FrmAddSubjectExam();
    	$frm_subject_exam=$subject_exam->FrmAddSubjectExam();
    	Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
    	$this->view->frm_subject_exam = $frm_subject_exam;
    	
    	$parent = new Global_Model_DbTable_DbSubjectExam();
    	$is_parent = $parent->getAllSubjectParent();
    	$sub = $parent->getAllSubjectParent(1);
    	$this->view->rs = $is_parent;
    	$this->view->sub = $sub;
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
    function adddegreeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_dbmodel = new Global_Model_DbTable_DbDegree();
    		$result=$_dbmodel->AddDegreeajax($data);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
}

