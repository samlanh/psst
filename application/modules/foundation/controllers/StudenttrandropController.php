<?php
class Foundation_StudenttrandropController extends Zend_Controller_Action {
	
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
					'title'	=>'',
					'study_year'=> '',
					'grade_bac'=> '',
					'session'=> '',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d")
				);
			}
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			
			$db_student= new Foundation_Model_DbTable_DbStudentDrop();
			$rs_rows = $db_student->getAllStudentDrop($search);
			$list = new Application_Form_Frmtable();
			if(!empty($rs_rows)){
				} 
				else{
					$result = Application_Model_DbTable_DbGlobal::getResultWarning();
				}
				$collumns = array("STUDENT_ID","STUDENT_NAME","SEX","ACADEMIC_YEAR","GRADE","SESSION","TYPE","REASON","STOP_DATE");
				$link=array(
						'module'=>'foundation','controller'=>'studentdrop','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('code'=>$link,'kh_name'=>$link,'en_name'=>$link));
	
			$this->view->adv_search = $search;
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	public function stutrandropAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Foundation_Model_DbTable_DbStudent();
		$row = $db->getStudentById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/foundation/register");
		}
		$rr = $db->getStudyHishotryById($id);
		$this->view->rr = $rr;
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$row=$db->addStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/register/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$group = $db->getAllgroup();
		$this->view->group = $group;
	
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		$this->view->occupation = $row;
	
		$row = $_db->getAllLangLevel(); // degree language
		$this->view->lang_level = $row;
	
		$row = $_db->getAllKnoyBy(); // degree language
		$this->view->know_by = $row;
	
		$row = $_db->getAllDocumentType(); // degree language
		$this->view->doc_type = $row;
	
	
		$this->view->degree = $db->getAllFecultyName();
	
		$test =  $db->getStudentById($id);
		$this->view->rs = $test;
		$this->view->row = $db->getStudentDocumentById($id);
		//echo $test['group_id'];exit();
	
		$this->view->year = $db->getAllYear();
		$this->view->room = $row =$db->getAllRoom();
	
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudentRegister($test);
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
	}	
}
