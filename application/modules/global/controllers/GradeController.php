<?php
class Global_GradeController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
	    	$db = new Global_Model_DbTable_DbGrade();
	    	if($this->getRequest()->isPost()){
	    		$_data=$this->getRequest()->getPost();
		    	$search = array(
	    				'txtsearch' => $_data['title'],
		    			'title' => $_data['title'],
		    			'status' => $_data['status_search']
	    		);
    	   	}else{
    			$search='';
    	    }
	    	$rs_rows= $db->getAllGrade($search);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("NAME","DEGREE","SHORTCUT","MODIFY_DATE","STATUS");
	    	$link=array(
	    			'module'=>'global','controller'=>'grade','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('major_enname'=>$link ,'major_khname'=>$link,'dept_name'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm_major = new Global_Form_FrmSearchMajor();
    	$frm_search = $frm_major->FrmMajors();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_search = $frm_search;
    }
    
    
    public function addAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Global_Model_DbTable_DbGrade();
    			$db->AddGrade($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/global/grade/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    			
    	}
    	
    	$_model = new Global_Model_DbTable_DbGroup();
    	$this->view->subject = $_model->getAllSubjectStudy();
    	
    	$db = new Global_Model_DbTable_DbGrade();
    	$dept = $db->getAllDept();
    	array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
    	$this->view->dept = $dept;
		
		$this->view->teacher= $db->getTeacher();
		
		$this->view->session= $db->getSession();
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$db = new Global_Model_DbTable_DbGrade();
	    		$db->updateGrade($_data,$id);
	    		Application_Form_FrmMessage::Sucessfull("ការកែប្រែដោយជោគជ័យ", "/global/grade/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	
    	$db= new Global_Model_DbTable_DbGrade();
    	$row=$db->getMajorById($id);
    	$this->view->rs = $row;
    	
    	$db = new Global_Model_DbTable_DbGrade();
    	$dept = $db->getAllDept();
    	array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
    	$this->view->dept = $dept;
		
		//$this->view->teacher= $db->getTeacher();
		
		//$this->view->session= $db->getSession();
		
		//$this->view->teacherbyid = $db->getTeacherBySubjectID($id);
		//print_r($abc);exit();
    }
    
    function addDeptAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbGrade();
    			$degree = $db->addDept($data);
    			print_r(Zend_Json::encode($degree));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
 
}

