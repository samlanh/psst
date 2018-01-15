<?php
class Foundation_DisciplineController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbStudentdiscipline();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'group_name' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			
			$this->view->search=$search;
			$rs_rows = $db->getAllDiscipline($search);
			$glClass = new Application_Model_GlobalClass();
			$rs = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array( "GROUP","ACADEMIC_YEAR","DEGREE","GRADE","SEMESTER","ROOM","SESSION","MISTAKE_DATE","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'discipline','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array('group_name'=>$link,'academy'=>$link,'degree'=>$link,'grade'=>$link,'semester'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Foundation_Model_DbTable_DbStudentdiscipline();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(isset($_data['save_new'])){
					 $rs =  $db->addDiscipline($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/discipline/add");
				}else {
					 $rs =  $db->addDiscipline($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/discipline");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->group = $db->getAllgroupStudy();
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year = $db_global->getAllYear();
		$this->view->session = $db_global->getSession();
		$this->view->degree = $db_global->getDegree();
		$this->view->grade = $db_global->getAllGrade();
		$this->view->room = $db_global->getAllRoom();
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$_model = new Foundation_Model_DbTable_DbStudentdiscipline();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				//if(isset($_data['save_close'])){
					$rs =  $_model->updateStudentAttendence($_data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/discipline");
				//} 
		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$result = $_model->getAttendencetByID($id);
		$this->view->row=$result;
		$this->view->allstudentBygroup = $_model->getStudentByGroup($result['group_id']);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		$this->view->group = $db_global->getAllgroupStudyNotPass($result['group_id']);
		$this->view->room = $row =$db_global->getAllRoom();
		$this->view->grade = $db_global->getAllGrade();
		
	}
	
	function getSubjectAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Foundation_Model_DbTable_DbStudentAttendance();
			$data=$_dbmodel->getSujectById($data['parent_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudent($data['year'],$data['grade'],$data['session']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentScore();
			$grade = $db->getAllGrade($data['degree']);
			//print_r($grade);exit();
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	function getStudentBygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentAttendance();
			$data=$db->getStudentByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getsubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentAttendance();
			$result =$db->getSubjectBygroup($data['group']);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	
	
	
	
	
	
	
	
	
	
}

