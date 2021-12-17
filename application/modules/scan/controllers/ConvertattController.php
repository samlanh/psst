<?php
class Scan_ConvertattController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Scan_Model_DbTable_DbConvertAttendance();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			
			$this->view->search=$search;
			$rs_rows = $db->getAllScanAttendence($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","GROUP","ACADEMIC_YEAR","DEGREE","GRADE","ROOM","SESSION","ATTENDANCE_DATE","STATUS","USER");
			$link=array(
					'module'=>'scan','controller'=>'convertatt','action'=>'add',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'group_name'=>$link,'academy'=>$link,'degree'=>$link,'grade'=>$link,'semester'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllgroupStudy();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$dbConvert = new Scan_Model_DbTable_DbConvertAttendance();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $dbConvert->ConvertScantoAttendece($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/scan/convertatt");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Issue_Model_DbTable_DbStudentAttendance();
		$this->view->group = $db->getAllgroupStudy();
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->branch_id=$db_global->getAllBranch();
		$this->view->branch_name = $db_global->getAllBranch();
		$this->view->row_year = $db_global->getAllYear();
		$this->view->session = $db_global->getSession();
		$this->view->degree = $db_global->getDegree();
		$this->view->grade = $db_global->getAllGrade();
		$this->view->room = $db_global->getAllRoom();
		
		$data['checkescan']=1;
		$this->view->allstudentBygroup = $db->getStudentByGroup($id,$data);
		$this->view->row = $dbConvert->getScanAttendencebyGroup($id);
	}
	
// 	public	function editAction(){
// 		$id=$this->getRequest()->getParam('id');
// 		$id = empty($id)?0:$id;
// 		$_model = new Issue_Model_DbTable_DbStudentAttendance();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			$_data['id']=$id;
// 			try {
// 				$rs =  $_model->updateStudentAttendence($_data);
// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/convertatt");
// 			}catch(Exception $e){
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
// // 		$result = $_model->getAttendencetByID($id);
// // 		if (empty($result)){
// 			//Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/convertatt");
// 			//exit();
// // 		}
// // 		$this->view->row=$result;
// 		$this->view->allstudentBygroup = $_model->getStudentByGroup(7);
// 		$db_global=new Application_Model_DbTable_DbGlobal();
// 		$this->view->branch_id=$db_global->getAllBranch();
// 		$this->view->row_year=$db_global->getAllYear();
// 		$this->view->session=$db_global->getSession();
// 		$this->view->degree=$db_global->getDegree();
// 		$this->view->group = $db_global->getAllgroupStudyNotPass(7);
// 		$this->view->room = $row =$db_global->getAllRoom();
// 		$this->view->grade = $db_global->getAllGrade();
		
// 		$db = new Application_Model_DbTable_DbGlobal();
// // 		$this->view->allstudentBygroup = $db->getAllStudentByGroupForEdit($result['group_id']);
// 		$db = new Issue_Model_DbTable_DbStudentAttendance();
// // 		$data=$db->getStudentByGroup($data['branch_id']);

// 	}
// 	function getSubjectAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$_dbmodel = new Issue_Model_DbTable_DbStudentAttendance();
// 			$data=$_dbmodel->getSujectById($data['parent_id']);
// 			print_r(Zend_Json::encode($data));
// 			exit();
// 		}
// 	}
// 	function getStudentAction(){//May not use
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Issue_Model_DbTable_DbStudentAttendance();
// 			$data=$db->getStudent($data['year'],$data['grade'],$data['session']);
// 			print_r(Zend_Json::encode($data));
// 			exit();
// 		}
// 	}
	function getAllstubygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$data=$db->getAllgroupStu($data['branch_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
// 	function getStudentBygroupAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Issue_Model_DbTable_DbStudentAttendance();
// 			$data=$db->getStudentByGroup($data['group'],$data);
// 			print_r(Zend_Json::encode($data));
// 			exit();
// 		}
// 	}
	
// 	function getsubjectbygroupAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Issue_Model_DbTable_DbStudentAttendance();
// 			$result =$db->getSubjectBygroup($data['group']);
// 			print_r(Zend_Json::encode($result));
// 			exit();
// 		}
// 	}
	
}

