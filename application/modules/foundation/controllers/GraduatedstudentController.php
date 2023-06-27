<?php
class Foundation_GraduatedstudentController extends Zend_Controller_Action {	
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search=array(
				'adv_search'	=>'',
				'branch_id'=>'',
				'academic_year' => '',
				'group'	=>'',
				'degree'	=>'',
				'grade'	=>'',
				'session' => '',
			);
		}
		$db_student= new Foundation_Model_DbTable_DbGraduatedStudent();
		$rs_rows = $db_student->getAllStudentGraduated($search);
		$list = new Application_Form_Frmtable();
		$collumns = array("BRANCH","GROUP","ACADEMIC_YEAR","GRADE","SESSION","TYPE","NOTE","CREATE_DATE","USER","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'graduatedstudent','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('group_code'=>$link,'grade'=>$link,'session'=>$link,'to_group_code'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view-> adv_search = $search;
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbGraduatedStudent();
 				$_add->addGraduatedStudent($data);
//  				if(!empty($data['save_close'])){
//  					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/graduatedstudent");
//  				}
// 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/graduatedstudent/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$tsub= new Foundation_Form_FrmGraduate();
		$frm_student=$tsub->FrmAddGraduate();
		Application_Model_Decorator::removeAllDecorator($frm_student);
		$this->view->frm = $frm_student;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbGraduatedStudent();
				$db->updateGraduateStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/graduatedstudent/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		$db= new Foundation_Model_DbTable_DbGraduatedStudent();
		$result = $db->getAllDropById($id);
		if (empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/foundation/graduatedstudent");
			exit();
		}
		$this->view->rs = $result;
		$this->view->studentpass = $db->selectStudentPass($result['group_id']);
		
		$tsub= new Foundation_Form_FrmGraduate();
		$frm_student=$tsub->FrmAddGraduate($result);
		Application_Model_Decorator::removeAllDecorator($frm_student);
		$this->view->frm = $frm_student;
	}
	function getToGroupAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGraduatedStudent();
			$grade = $db->getGroupStudentChangeGroup1ById($data['to_group'],$data['type']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}

}