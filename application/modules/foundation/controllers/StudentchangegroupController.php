<?php
class Foundation_StudentchangegroupController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search=array(
				'branch_id'	=>'',
				'adv_search'	=>'',
				'academic_year'=> '',
				'grade'=> '',
				'session'=> ''
			);
		}
		
		$db_student= new Foundation_Model_DbTable_DbStudentChangeGroup();
		$rs_rows = $db_student->selectAllStudentChangeGroup($search);
		$list = new Application_Form_Frmtable();
		if(!empty($rs_rows)){
		}
		else{
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","Last Name","First Name","SEX","FROM_GROUP","ACADEMIC_YEAR","GRADE","SESSION","TO_GROUP","ACADEMIC_YEAR","GRADE","SESSION","MOVING_DATE","NOTE","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'studentchangegroup','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('code'=>$link,'kh_name'=>$link,'en_name'=>$link));
		$this->view->adv_search=$search;	
			
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbStudentChangeGroup();
 				$_add->addStudentChangeGroup($_data);
 				if(!empty($_data['save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentchangegroup");
 				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_add = new Foundation_Model_DbTable_DbStudentChangeGroup();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch = $_db->getAllBranch();
		$this->view->branch = $branch;
		
		$tsub= new Foundation_Form_FrmStudentChangeGroup();
		$frm=$tsub->FrmAddStudentChangeGroup();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		
		$db= new Foundation_Model_DbTable_DbStudentChangeGroup();
		$row = $db->getAllStudentChangeGroupById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/studentchangegroup/index");
			exit();
		}
		$this->view->rows = $row;
		
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbStudentChangeGroup();
				$row=$db->updateStudentChangeGroup($data);
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/studentchangegroup/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		
		$tsub= new Foundation_Form_FrmStudentChangeGroup();
		$frm=$tsub->FrmAddStudentChangeGroup($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		
	}

	
	
	function getToGroupAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
// 			$db = new Foundation_Model_DbTable_DbStudentChangeGroup();
// 			$grade = $db->getStudentChangeGroup1ById($data['to_group']);
			$db = new Application_Model_DbTable_DbGlobal();
			$grade = $db->getStudentGroupInfoById($data['to_group']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	function getStudentAction(){// May not use
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentChangeGroup();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
}
