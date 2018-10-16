<?php
class Global_LecturerController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Global_Model_DbTable_DbTeacher();
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title'  => $_data['title'],
						'degree' => $_data['degree'],
						'nationality' => $_data['nationality'],
						'branch_id' => $_data['branch_id'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'degree' => '',
						'nationality' => '',
						'branch_id' => '',
						'status' => -1);
			}
			$rs_rows= $db->getAllTeacher($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","ID_NUMBER","TEACHER_NAME","SEX","TEACHER_TYPE","NATIONALITY","DEGREE","POSITION","PHONE","EMAIL","NOTE","STATUS");
			$link=array('module'=>'global','controller'=>'lecturer','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('teacher_code'=>$link,'teacher_name_kh'=>$link,'teacher_name_en'=>$link,'branch_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$dbmodel = new Global_Model_DbTable_DbTeacher();
				$id = $dbmodel->AddNewStaff($_data);
				if($id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/global/lecturer');
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/global/lecturer/add');
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optionNation = $_db->getViewByType(21);//Nation
		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
		$this->view->nation = $optionNation;
		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_DOCUMENT")));
		$this->view->doc_type = $row;
		
		$row = $_db->getAllDepartment(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->department_type = $row;
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
	}
	public function editAction()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");	   
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data['id'] = $id;
				$db = new Global_Model_DbTable_DbTeacher();
				$db->updateStaff($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/lecturer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
 		$_db = new Application_Model_DbTable_DbGlobal();
 		
 		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
 		$optionNation = $_db->getViewByType(21);//Nation
 		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
 		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
 		$this->view->nation = $optionNation;
 		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_DOCUMENT")));
		$this->view->doc_type = $row;
		
		$row = $_db->getAllDepartment(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->department_type = $row;
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		$this->view->row = $_db->getTeacherDocumentById($id);
		
		$row = $_db->getTeacherById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/global/lecturer");
			exit();
		}
		$this->view->rs = $row;
		$tsub = new Global_Form_FrmTeacher();
		$frm_techer = $tsub->FrmTecher($row);
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_update = $frm_techer;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		//print_r($this->view->row); exit();
	}
	
	public function copyAction()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data['id'] = $id;
				$db = new Global_Model_DbTable_DbTeacher();
				$db->AddNewStaff($data);
				Application_Form_FrmMessage::Sucessfull("COPY_SUCCESS","/global/lecturer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
	
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$_db = new Application_Model_DbTable_DbGlobal();
			
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optionNation = $_db->getViewByType(21);//Nation
		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
		$this->view->nation = $optionNation;
			
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_DOCUMENT")));
		$this->view->doc_type = $row;
		
		$row = $_db->getAllDepartment(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->department_type = $row;
	
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		$this->view->row = $_db->getTeacherDocumentById($id);
	
		$row = $_db->getTeacherById($id);
		$this->view->rs = $row;
		$tsub = new Global_Form_FrmTeacher();
		$frm_techer = $tsub->FrmTecher($row);
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_update = $frm_techer;
	
		//print_r($this->view->row); exit();
	}
	function addPositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTeacher();
			$id = $db->addNewPosition($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
	function getdepartmentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTeacher();
			$teacher = $db->getAllDepartment();
			print_r(Zend_Json::encode($teacher));
			exit();
		}
	}
	
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Global_Model_DbTable_DbTeacher();
		$this->view->rs = $db->getViewById($id);
	}
	function getTeacherIdAction(){
		$db = new Global_Model_DbTable_DbTeacher();
		$code = $db->getTeacherCode();
		print_r(Zend_Json::encode($code));
		exit();
	}
	
}

