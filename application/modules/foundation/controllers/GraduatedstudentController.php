<?php
class Foundation_GraduatedstudentController extends Zend_Controller_Action {	
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search=array(
					'title'	=>'',
					'branch_id'=>'',
					'study_year' => '',
					'group'	=>'',
					'grade'	=>'',
					'session' => '',
			);
		}
		$db_student= new Foundation_Model_DbTable_DbGraduatedStudent();
		$rs_rows = $db_student->getAllStudentGraduated($search);
		$glClass = new Application_Model_GlobalClass();
		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		$list = new Application_Form_Frmtable();
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$collumns = array("FROM_GROUP","ACADEMIC_YEAR","GRADE","SESSION","TYPE","NOTE","CREATE_DATE","USER","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'graduatedstudent','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('group_code'=>$link,'grade'=>$link,'session'=>$link,'to_group_code'=>$link));
		$this->view-> adv_search = $search;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbGraduatedStudent();
 				$_add->addGraduatedStudent($data);
 				if(!empty($data['save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/graduatedstudent");
 				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Foundation_Model_DbTable_DbGraduatedStudent();		
		$this->view->row = $add =$db->getfromGroup();
		$this->view->rs = $add =$db->gettoGroup();
		
		
		$this->view->academy = $db->getAllYears();
		
		$_db = new Application_Model_DbTable_DbGlobal();		
		$this->view->branch_name = $_db->getAllBranch();
		$rs = $_db->getViewById(9);
		unset($rs[0]);
		$this->view->rstype = $rs;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbGraduatedStudent();
				$db->updateDropStudent($data,$id);
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/graduatedstudent/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		$db= new Foundation_Model_DbTable_DbGraduatedStudent();
		$result = $db->getAllDropById($id);
		$this->view->rs = $result;
		
		$this->view->studentpass = $db->selectStudentPass($result['group_id']);
		$db = new Foundation_Model_DbTable_DbGraduatedStudent();
		$this->view->row = $db->getfromGroup();
		$g_new=$db->getGroupNewAll();
		array_unshift($g_new,array ('id' => -1,'name' => 'បន្ថែមថ្មី'));
		$this->view->g_new=$g_new;
		$this->view->rows = $db->gettoGroup();
		$this->view->academy = $db->getAllYears();
		$this->view->drop_type = $db->getDropType();
		
		//$test = $db->getAllStudentFromGroup(4);
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $_db->getAllFecultyName();
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->rs_session=$db->getSession();
		$room =  $db->getRoom();
		array_unshift($room, array ( 'room_id' => 0, 'room_name' => 'Select Room') );
		$this->view->room = $room;
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
	
	function getAllStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGraduatedStudent();
			$student = $db->getAllStudentFromGroup($data['from_group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
	function getAllStudentUpdateAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGraduatedStudent();
			$student = $db->getAllStudentFromGroupUpdate($data['from_group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}	
	
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGraduatedStudent();
			$student = $db->getGradeByDegree($data['dept_id']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
    function addGroupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbGraduatedStudent();
    		$student = $db->AddNewGroupAjax($data);
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
	
	
}











