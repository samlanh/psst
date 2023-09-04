<?php
class Issue_StudentattendanceoneController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStudentAttendanceOne();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'grade'=> '',
						'session_type'=> '',
						'for_semester'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d')
					);
			}
			$this->view->search=$search;
			$rs_rows = $db->getAllAttendence($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","NAME","GROUP","ACADEMIC_YEAR","DEGREE","GRADE","FOR_SEMESTER","SESSION_TYPE","ATTENDANCE_DATE","IS_COMPLETED","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'studentattendanceone','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'stu_name'=>$link,'group_name'=>$link,'academy'=>$link,));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Issue_Model_DbTable_DbStudentAttendanceOne();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(isset($_data['save_new'])){
					 $rs =  $db->addStudentAttendeceOne($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentattendanceone/add");
				}else {
					 $rs =  $db->addStudentAttendeceOne($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentattendanceone");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->group = $db_global->getAllGroupName();
		$this->view->branch_id=$db_global->getAllBranch();
		$this->view->branch_name = $db_global->getAllBranch();
		$this->view->row_year = $db_global->getAllYear();
		$this->view->session = $db_global->getSession();
		$this->view->degree = $db_global->getDegree();
		$this->view->grade = $db_global->getAllGrade();
		$this->view->room = $db_global->getAllRoom();
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Issue_Model_DbTable_DbStudentAttendanceOne();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs = $db->editStudentAttendeceOne($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentattendanceone/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$row= $db->getAttendenceDetailByID($id);
		$this->view->row = 	$row;
		if ($row['isCompleted']!=0){
    		Application_Form_FrmMessage::Sucessfull("Already Completed! ","/issue/studentattendanceone/index");
    	}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->group = $db_global->getAllGroupName();
		
		$this->view->branch_id=$db_global->getAllBranch();
		$this->view->branch_name = $db_global->getAllBranch();
		$this->view->row_year = $db_global->getAllYear();
		$this->view->session = $db_global->getSession();
		$this->view->degree = $db_global->getDegree();
		$this->view->grade = $db_global->getAllGrade();
		$this->view->room = $db_global->getAllRoom();
	}
}

