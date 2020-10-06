<?php
class Foundation_StudentdropController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search=array(
					'adv_search'	=>'',
					'branch_id'=> '',
					'academic_year'=> '',
					'degree'=> '',
					'grade'=> '',
					'session'=> '',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
				);
			}
			$db_student= new Foundation_Model_DbTable_DbStudentDrop();
			$rs_rows = $db_student->getAllStudentDrop($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_ENGLISH","SEX","ACADEMIC_YEAR","DEGREE","GRADE","GROUP","SESSION","ROOM_NAME","TYPE","STOP_DATE","REASON","USER","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'studentdrop','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'student_kh'=>$link,'academic'=>$link,'stu_id'=>$link,'student_name'=>$link,'sex'=>$link));
	
			$this->view->search = $search;
			$form=new Application_Form_FrmSearchGlobal();
			$forms=$form->FrmSearch();
			Application_Model_Decorator::removeAllDecorator($forms);
			$this->view->form_search=$form;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function addAction(){
		try{
			if($this->getRequest()->isPost()){
				try{
					$_data = $this->getRequest()->getPost();
					$_add = new Foundation_Model_DbTable_DbStudentDrop();
	 				$_add->addStudentDrop($_data);
	 				if(!empty($_data['save_close'])){
	 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentdrop");
	 					exit();
	 				}
	 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentdrop/add");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}
			$db = new Foundation_Model_DbTable_DbStudentDrop();
			$db_global = new Application_Model_DbTable_DbGlobal();
			
			$tsub= new Global_Form_FrmAddClass();
			$frm_student=$tsub->FrmAddGroup();
			Application_Model_Decorator::removeAllDecorator($frm_student);
			$this->view->frm = $frm_student;			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function editAction(){
		try{	
			$id=$this->getRequest()->getParam("id");
			$id = empty($id)?0:$id;
			$db= new Foundation_Model_DbTable_DbStudentDrop();
			$row = $this->view->row = $db->getStudentDropById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/studentdrop/index");
				exit();
			}
			if($this->getRequest()->isPost())
			{
				try{
					$data = $this->getRequest()->getPost();
					$db = new Foundation_Model_DbTable_DbStudentDrop();
					$row=$db->updateStudentDrop($data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/studentdrop/index");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("EDIT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}	
			
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$db = new Foundation_Model_DbTable_DbStudentDrop();
		$this->view->stu_id = $db->getAllStudentNameEdit();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$stu = $db->getAllStudentName();
		$this->view->stuname=$stu;
			
		$db_global = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $db_global->getAllDegreeMent();
		$this->view->group = $db->getAllgroupStudy();
		$this->view->session=$db_global->getSession();
			
		$d_row= $db_global->getAllGradeStudy();
		$this->view->grade_name=$d_row;
			
		$tsub= new Global_Form_FrmAddClass();
		$frm_student=$tsub->FrmAddDrup($row);
		Application_Model_Decorator::removeAllDecorator($frm_student);
		$this->view->frm = $frm_student;
	}

	function getGradeAction(){//may not use
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$grade = $db->getAllGrade($data['dept_id']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	
	function getStudentAction(){//may not use
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentDrop();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
}
