<?php 
class Foundation_LecturerController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		defined('SHOW_TEACH_DOCUMENT') || define('SHOW_TEACH_DOCUMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('teacher_doc'));
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
						'staff_type' => $_data['staff_type'],
						'teacher_type' => $_data['teacher_type'],
						'branch_id' => $_data['branch_id'],
						'active_type' => $_data['active_type'],
						'status' => $_data['status_search'],
						'department' => $_data['department']
						);
			}else{
				$search = array(
						'title' => '',
						'degree' => '',
						'staff_type' => '',
						'nationality' => '',
						'teacher_type' => -1,
						'branch_id' => '',
						'active_type' => -1,
						'department' => '',
						'status' => -1);
			}
			$rs_rows= $db->getAllTeacher($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","ID_CARD","TEACHER_NAME","NAME_EN","SEX","TYPE",
					"NATIONALITY","DEGREE","TEACHER_TYPE","POSITION","PHONE","EMAIL","NOTE","SCHOOL_OPTION","WORKING_STATUS","STATUS");
			$link=array('module'=>'foundation','controller'=>'lecturer','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('teacher_code'=>$link,'teacher_name_kh'=>$link,'teacher_name_en'=>$link,'branch_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher($search);
		Application_Model_Decorator::removeAllDecorator($frm);
	}
	
	function addAction(){
		
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			if (empty($_data)){
				Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/lecturer");
				exit();
			}
			try {
				$sms="INSERT_SUCCESS";
				$dbmodel = new Global_Model_DbTable_DbTeacher();
				$id = $dbmodel->AddNewStaffGlobal($_data);
				if($id==-1){
					$sms = "RECORD_EXIST";
				}
				$inFrame = empty($_data['inFrame']) ? "" : "true";
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/foundation/lecturer?inFrame='.$inFrame);
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/foundation/lecturer/add?inFrame='.$inFrame);
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$this->view->faculty = $_db->getAllDegreeName();

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optionNation = $_db->getViewByType(21);//Nation
		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
		$this->view->nation = $optionNation;
		
		$row = $_db->getAllDocteacherType(); // degree language
		$this->view->doc = $row;
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
	
	public function editAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");	   
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/lecturer");
					exit();
				}
				$data['id'] = $id;
				$db = new Global_Model_DbTable_DbTeacher();
				$db->updateStaff($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/lecturer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
 		$_db = new Application_Model_DbTable_DbGlobal();
 		$this->view->faculty = $_db->getAllDegreeName();

 		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
 		$optionNation = $_db->getViewByType(21);//Nation
 		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
 		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
 		$this->view->nation = $optionNation;
 		
		$row = $_db->getAllDocteacherType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_DOCUMENT")));
		$this->view->doc_type = $row;
		
		$rows = $_db->getAllDepartment(); // degree language
		array_unshift($rows, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->department_type = $rows;
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		$this->view->row = $_db->getTeacherDocumentById($id);
		
		$row = $_db->getTeacherById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/foundation/lecturer");
			exit();
		}
		$this->view->rs = $row;
		
		$tsub = new Global_Form_FrmTeacher();
		$frm_techer = $tsub->FrmTecher($row);
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_update = $frm_techer;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	
	public function copyAction()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/lecturer");
					exit();
				}
				$data['id'] = $id;
				$db = new Global_Model_DbTable_DbTeacher();
				$idss = $db->AddNewStaffGlobal($data);
				$sms = "COPY_SUCCESS";
				if($idss==-1){
					$sms = "RECORD_EXIST";
				}
				Application_Form_FrmMessage::Sucessfull($sms,"/foundation/lecturer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$this->view->faculty = $_db->getAllDegreeName();

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optionNation = $_db->getViewByType(21);//Nation
		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
		$this->view->nation = $optionNation;
			
		$row = $_db->getAllDocteacherType(); // degree language
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
	function addDoctypeAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Application_Model_DbTable_DbGlobal();
				$row = $db->addDoctecherType($data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	function getdepartmentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTeacher();
			$row = $db->getAllDepartment();
			array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
			array_unshift($row, array ( 'id' => '','name' =>$this->tr->translate("PLEASE_SELECT_DEPARTMENT")));
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
		
		$db= new Foundation_Model_DbTable_DbTeacher();
		$param['id']=$id;
		$this->view->rs = $rs = $db->getTeacherinfoById($param);
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLeftLogo($rs['branch_id']);
	}
	function getTeacherIdAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$code = $db->getTeacherCode($data['branch_id']);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	
	//new create on 24-3-2020
	function getteacherAction(){ 
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
	
			$_db = new RsvAcl_Model_DbTable_DbBranch();
    		$row = $_db->getBranchById($data['branch_id']);//get branch info
    		$schoolOption = $row['schooloptionlist'];
	
			$db = new Application_Model_DbTable_DbGlobal();
			$data["schoolOption"] = $schoolOption;
			$data["branch_id"] = empty($data["branch_id"]) ? 0:$data["branch_id"];
			
			$teacher = $db->getAllTeahcerName($data);
			if(empty($data['hideAddnew'])){
				array_unshift($teacher, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			}
			array_unshift($teacher, array ('id' => 0, 'name' => $this->tr->translate("SELECT_TEACHER")));
			print_r(Zend_Json::encode($teacher));
			exit();
		}
	}
	
	
	function checkUsernameAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTeacher();
			$return=$db->checkUserName($data);
			print_r(Zend_Json::encode($return));
			exit();
		}
	}
}