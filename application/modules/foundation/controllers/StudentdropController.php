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
	function addAction(){
		try{
			if($this->getRequest()->isPost()){
				try{
					$_data = $this->getRequest()->getPost();
					$_add = new Foundation_Model_DbTable_DbStudentDrop();
	 				$_add->addStudentDrop($_data);
	 				if(!empty($_data['save_close'])){
	 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentdrop");
	 				}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}
			
			$db = new Foundation_Model_DbTable_DbStudentDrop();
			$this->view->stu_id = $db->getAllStudentID();
			$this->view->stu_name = $db->getAllStudentName();
			$this->view->type = $db->getAllDropType();
		
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	public function editAction(){
		try{	
			$id=$this->getRequest()->getParam("id");
			
			$db= new Foundation_Model_DbTable_DbStudentDrop();
			$row = $this->view->row = $db->getStudentDropById($id);
			
			if($this->getRequest()->isPost())
			{
				try{
					$data = $this->getRequest()->getPost();
					$data["id"]=$id;
					$db = new Foundation_Model_DbTable_DbStudentDrop();
					$row=$db->updateStudentDrop($data);
					
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/studentdrop/index");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("EDIT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}	
			
			$db = new Foundation_Model_DbTable_DbStudentDrop();
			$this->view->stu_id = $db->getAllStudentIDEdit();
			$this->view->stu_name = $db->getAllStudentNameEdit();
			$this->view->type = $db->getAllDropType();
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}

	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$grade = $db->getAllGrade($data['dept_id']);
			//print_r($grade);exit();
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentDrop();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
}
