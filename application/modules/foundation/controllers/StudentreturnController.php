<?php
class Foundation_StudentreturnController extends Zend_Controller_Action {
	
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
					'type'	=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
				);
			}
			$db_student= new Foundation_Model_DbTable_DbStudentReturn();
			$rs_rows = $db_student->getAllStudentDropReturn($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_ENGLISH","ACADEMIC_YEAR","DEGREE","GRADE","GROUP","RETURN_DEGREE","RETURN_GRADE","RETURN_GROUP","RETURN_STUDY_DATE","USER","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'studentreturn','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'student_kh'=>$link,'academic'=>$link,'stu_id'=>$link,'student_name'=>$link,'sex'=>$link));
	
			$this->view->search = $search;
		}catch(Exception $e){
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
	function addAction(){
		try{
			if($this->getRequest()->isPost()){
				try{
					$_data = $this->getRequest()->getPost();
					$_add = new Foundation_Model_DbTable_DbStudentReturn();
	 				$_add->addStudentDropReturn($_data);
	 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentreturn");
	 					exit();
				}catch(Exception $e){
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					Application_Form_FrmMessage::message("INSERT_FAIL");
				}
			}
			$tsub = new Foundation_Form_FrmStudentReturn();
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
			$db= new Foundation_Model_DbTable_DbStudentReturn();
			
			if($this->getRequest()->isPost())
			{
				try{
					$data = $this->getRequest()->getPost();
					$db->updateStudentDropReturn($data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/studentreturn/index");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("EDIT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}	
			
			$row = $db->getStudentDropReturnById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/studentreturn/index");
				exit();
			}
			if ($row['status']==0){
				Application_Form_FrmMessage::Sucessfull("UNABLE_TO_EDIT_DEACTIVE_RECORD","/foundation/studentreturn/index");
				exit();
			}
			$this->view->row =$row;
			$tsub= new Foundation_Form_FrmStudentReturn();
			$frm_student=$tsub->FrmAddGroup($row);
			Application_Model_Decorator::removeAllDecorator($frm_student);
			$this->view->frm = $frm_student;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			
		}
			
		
	}


	
	
	function getalldropstudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentReturn();
			$data['branch_id'] = !empty($data['branch_id'])?$data['branch_id']:null;
			$rows = $db->getAllStudentDrop($data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getdropinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentReturn();
			$data['drop_id'] = !empty($data['drop_id'])?$data['drop_id']:null;
			$rows = $db->getStudentDropInfo($data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
}
