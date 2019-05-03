<?php
class Issue_TeacherScoreController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbTeacherScore();
			$this->view->g_all_name=$db->getGroupSearch();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title'=>'',
						'branch_id'=>'',
						'group' => '',
						'study_year'=> '',
						'degree'=>0,
						'grade'=> 0,
						'session'=> 0,
						'time'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs = $db->getAllTeacherScore($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","EXAM_TYPE","STUDENT_GROUP","STUDY_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'teacherscore','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array('exam_type'=>$link,'title_score'=>$link,'student_no'=>$link,'student_id'=>$link,'academic_id'=>$link,'degree'=>$link,'group_id'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbTeacherScore();
			try {
				$rs =  $db->addTeacherStudentScore($_data);
				if(isset($_data['save_new'])){
					if ($rs==1){
						Application_Form_FrmMessage::Sucessfull("Can't Input Score. It's already input.","/issue/teacherscore/add");
					}
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/teacherscore/add");
				}else {
					if ($rs==1){
						Application_Form_FrmMessage::Sucessfull("Can't Input Score. It's already input.","/issue/teacherscore");
					}
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/teacherscore");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
	
		$session_t=new Zend_Session_Namespace('authteacher');
		$teacher_id = $session_t->teacher_id;
	
		$result= $db_global->getAllgroupStudy($teacher_id);
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();
			
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$_model = new Issue_Model_DbTable_DbTeacherScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				if(isset($_data['save_close'])){
					$rs =  $_model->updateTeacherStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/teacherscore");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->score_id = $id;
		$this->view->score = $_model->getScoreTeacherById($id);
		$this->view->student= $_model->getStudentSccoreforEditTeacherScore($id);
		$this->view->rows_scor=$_model->getScoreStudentsTeacherscore($id);
		$data=$this->view->rows_detail=$_model->getSubjectByIdTeacherScore($id);
		$this->view->row_g=$_model->getGroupStudentTeacherScore($id);
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result = $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();
	
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function addscoreAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbTeacherScore();
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/teacherscore/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/teacherscore");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		
		$session_t=new Zend_Session_Namespace('authteacher');
		$teacher_id = $session_t->teacher_id;
		
		$result= $db_global->getAllgroupStudy($teacher_id);
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();
			
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editscoreAction(){
		$id=$this->getRequest()->getParam('id');
		$_model = new Issue_Model_DbTable_DbTeacherScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				if(isset($_data['save_close'])){
					$rs =  $_model->updateStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/score");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->score_id = $id;
		$this->view->score = $_model->getScoreById($id);
		$this->view->student= $_model->getStudentSccoreforEdit($id);
		$this->view->rows_scor=$_model->getScoreStudents($id);
		$data=$this->view->rows_detail=$_model->getSubjectById($id);
		$this->view->row_g=$_model->getGroupStudent($id);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result = $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' => 'ជ្រើសរើសក្រុម') );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();		
		
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	
	function getSubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScore();
			$session_t=new Zend_Session_Namespace('authteacher');
			$teacher_id = $session_t->teacher_id;
			$data=$db->getSubjectByGroup($data['group'],$teacher_id);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}
