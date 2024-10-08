<?php
class Issue_DisciplineController extends Zend_Controller_Action {

	const SETTING_INPUT_DISPLINE = 2; // 1=fullListStudentGroup
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStudentdiscipline();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'branch_id' => '',
					'group_name' => '',
					'study_year'=> '',
					'grade'=> '',
					'session'=> '',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d')
				);
			}
			
			$this->view->search=$search;
			$rs_rows = $db->getAllDiscipline($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","GROUP","GRADE","SEMESTER","ATTENDANCE_DATE","amtStuAtt","USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'discipline','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'titleRecord'=>$link,'academy'=>$link,'degree'=>$link,'grade'=>$link,'semester'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Issue_Model_DbTable_DbStudentdiscipline();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(isset($_data['save_new'])){
					$db->addDisciplineStudent($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/discipline/add");
				}else {
					$db->addDisciplineStudent($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/discipline");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$branch = $db_global->getAllBranch();
		$this->view->branch = $branch;
		$this->view->settingInputAttendance = self::SETTING_INPUT_DISPLINE;
		
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Issue_Model_DbTable_DbStudentdiscipline();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				 $_model->updateStudentAttendence($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/discipline");		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$result = $_model->getAttendencetByIDDiscipline($id);
		if (empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issue/discipline");
		}
		$this->view->row=$result;
		if ($result['is_pass'] > 0) {
			Application_Form_FrmMessage::Sucessfull("UNABLE_TO_EDIT_COMPETED_CLASS", "/issue/discipline");
		}
		$condiction = array(
			"attendanceId" => $id
		);
		if (!empty($result)){
			$this->view->allstudentBygroup= $_model->getStudentDisplineDetail($condiction);
			//	$this->view->allstudentBygroup = $_model->getStudentByGroup($result['group_id']);
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		
		$branch = $db_global->getAllBranch();
		$this->view->branch = $branch;
		
	}
	
	function getStudentBygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentdiscipline();
			$data=$db->getStudentByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentdiscipline();
			$data=$db->getStudentInfo($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getsubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentAttendance();
			$result =$db->getSubjectBygroup($data['group']);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}