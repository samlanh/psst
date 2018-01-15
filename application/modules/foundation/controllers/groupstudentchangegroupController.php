<?php
class Foundation_groupstudentchangegroupController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search=array(
					'title'	=>'',
					'study_year' => '',
					'grade_bac'	=>'',
					'session' => '',
			);
		}
		$db_student= new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		$rs_rows = $db_student->selectAllStudentChangeGroup($search);
		$glClass = new Application_Model_GlobalClass();
		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		$list = new Application_Form_Frmtable();
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$collumns = array("FROM_GROUP","ACADEMIC_YEAR","GRADE","SESSION","TO_GROUP","ACADEMIC_YEAR","GRADE","SESSION","MOVING_DATE","NOTE","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'groupstudentchangegroup','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('group_code'=>$link,'grade'=>$link,'session'=>$link,'to_group_code'=>$link));

		$this->view->adv_search = $search;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
 				$_add->addGroupStudentChangeGroup($data);
 				if(!empty($data['save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/groupstudentchangegroup");
 				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		
		$this->view->row = $add =$db->getfromGroup();
		$this->view->rs = $add =$db->gettoGroup();
		$g_new=$db->getGroupNewAll();
		array_unshift($g_new,array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->g_new=$g_new;
		
		$this->view->academy = $db->getAllYears();
		$this->view->change_type = $db->getChangeType();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $_db->getAllDegreeName();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->rs_session=$db->getSession();
		
		$room =  $db->getRoom();
		array_unshift($room, array ( 'room_id' => 0, 'room_name' =>$this->tr->translate("SELECT_ROOM")) );
		$this->view->room = $room;
		
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				//$data["id"]=$id;
				//print_r($data);exit();
				$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
				$row=$db->updateStudentChangeGroup($data,$id);
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/groupstudentchangegroup/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		$db= new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		$result = $db->getAllGroupStudentChangeGroupById($id);
		$this->view->rs = $result;
		
		$this->view->studentpass = $db->selectStudentPass($result['from_group']);
		$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		$this->view->row = $db->getfromGroup();
		$g_new=$db->getGroupNewAll();
		array_unshift($g_new,array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->g_new=$g_new;
		$this->view->rows = $db->gettoGroup();
		$this->view->academy = $db->getAllYears();
		$this->view->change_type = $db->getChangeType();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $_db->getAllFecultyName();
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->rs_session=$db->getSession();
		$room =  $db->getRoom();
		array_unshift($room, array ( 'room_id' => 0, 'room_name' =>$this->tr->translate("SELECT_ROOM")) );
		$this->view->room = $room;
	}
	
	
	function getToGroupAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$grade = $db->getGroupStudentChangeGroup1ById($data['to_group'],$data['type']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	function getAllStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$student = $db->getAllStudentFromGroup($data['from_group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
	function getAllStudentUpdateAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$student = $db->getAllStudentFromGroupUpdate($data['from_group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}	
	
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$student = $db->getGradeByDegree($data['dept_id']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
    function addGroupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
    		$student = $db->AddNewGroupAjax($data);
    		print_r(Zend_Json::encode($student));
    		exit();
    	}
    }
	
	
}











