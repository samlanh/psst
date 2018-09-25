<?php
class Foundation_RegisterController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> '',
					);
				
			}
			$this->view->adv_search=$search;
			$db_student= new Foundation_Model_DbTable_DbStudent();
			$rs_rows = $db_student->getAllStudent($search);
			$list = new Application_Form_Frmtable();
				$collumns = array("BRANCH_NAME","STUDENT_ID","NAME_KH","NAME_EN","SEX","PHONE","ACADEMIC_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","GROUP","STATUS");
				$link=array(
						'module'=>'foundation','controller'=>'register','action'=>'edit',
				);
				$link1=array(
						'module'=>'foundation','controller'=>'register','action'=>'view',
				);
				$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link1,'stu_code'=>$link,'name'=>$link,'stu_khname'=>$link,'stu_enname'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
// 		$db_student->updategroupstudent();
		
	}
	function addAction(){
		$db = new Foundation_Model_DbTable_DbStudent();
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$id_existing = $db->ifStudentIdExisting($_data['student_id']);
				if(!empty($id_existing)){
					print_r("<script type='text/javascript'>
					    alert('អត្តលេខ សិស្សនេះបានប្រើរួចរាល់ហើយសូមត្រួតពិនិត្យម្តងទៀត!');
					</script>");
				}else{
					$exist = $db->addStudent($_data);
					if($exist==-1){
						Application_Form_FrmMessage::message("RECORD_EXIST");
					}else{
						if(isset($_data['save_close'])){
							Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/register");
							exit();
						}else{
							Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/register/add");
							exit();
						}
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
					}
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$group = $db->getAllgroup();
		array_unshift($group,array('id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		array_unshift($group,array ( 'id' =>'','name' => $this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$row = $_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
		
		$row = $_db->getAllLangLevel(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->lang_level = $row;
		
		$row = $_db->getAllNation(); // Nation language
		array_unshift($row, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0, 'name' => $this->tr->translate("SELECT_NATION")));
		$this->view->nation = $row;
		
		$row = $_db->getAllKnoyBy(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->know_by = $row;
		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		$this->view->year = $db->getAllYear();
		$this->view->degree = $rows = $db->getAllFecultyName();
		$this->view->room = $row =$db->getAllRoom();
		$this->view->province = $row =$db->getProvince();
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudentRegister();
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
		
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Foundation_Model_DbTable_DbStudent();
		$row = $db->getStudentById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/foundation/register");
			exit();
		}
		$rr = $db->getStudyHishotryById($id);
		$this->view->rr = $rr;
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$row=$db->updateStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/register/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$group = $db->getAllgroup();
		array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
		
		$row = $_db->getAllNation(); // Nation language
		array_unshift($row, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0, 'name' => $this->tr->translate("SELECT_NATION")));
		$this->view->nation = $row;
		
		$row = $_db->getAllLangLevel(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->lang_level = $row;
		
		$row = $_db->getAllKnoyBy(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->know_by = $row;
		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		
		$this->view->degree = $db->getAllFecultyName();
		
		$this->view->province = $db->getProvince();
		
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
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$grade = $_dbgb->getAllGradeStudyByDegree($data['dept_id']);
// 			$db = new Foundation_Model_DbTable_DbStudent();
// 			$grade = $db->getAllGrade($data['dept_id']);
// 			//print_r($grade);exit();
			array_unshift($grade, array ( 'id' => -1, 'name' =>$this->tr->translate("ADD_NEW")));
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	function addJobAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_dbmodel = new Global_Model_DbTable_DbOccupation();
				$row = $_dbmodel->addNewOccupationPopup($data);
				$result = array("id"=>$row);
				print_r(Zend_Json::encode($row));
				exit();
				//Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	function getStuNoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbRegister();
			$degree=$data['dept_id'];
			$branch_id=$data['branch_id'];
			$stu_no = $db->getNewAccountNumber($branch_id,$degree);
			print_r(Zend_Json::encode($stu_no));
			exit();
		}
	}
	
	function getgroupinfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$group = $db->getGroupInforByID($data['group_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	
	//view detial student by id
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Foundation_Model_DbTable_DbStudent();
		$this->view->rs = $db->getStudentViewDetailById($id);
	}
	public function copyAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Foundation_Model_DbTable_DbStudent();
		$row = $db->getStudentById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/foundation/register");
			exit();
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
		array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
		
		$row = $_db->getAllNation(); // Nation language
		array_unshift($row, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0, 'name' => $this->tr->translate("SELECT_NATION")));
		$this->view->nation = $row;
		
		$row = $_db->getAllLangLevel(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->lang_level = $row;
		
		$row = $_db->getAllKnoyBy(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->know_by = $row;
		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		
		$this->view->degree = $db->getAllFecultyName();
		
		$this->view->province = $db->getProvince();
		
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
// 		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
// 		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		
		$this->view->degree = $db->getAllFecultyName();
		
		$this->view->province = $db->getProvince();
		
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
	function addLanglevelAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Application_Model_DbTable_DbGlobal();
				$row = $db->addLangLevel($data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	
	function addNationAction(){
		if($this->getRequest()->isPost()){
			try{
				$sms="INSERT_SUCCESS";
				$data = $this->getRequest()->getPost();
				$db = new Application_Model_DbTable_DbGlobal();
				$row = $db->addNationType($data);
// 				if($row==-1){
// 					$sms = "RECORD_EXIST";
// 				}
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	
	function addKnowbyAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Application_Model_DbTable_DbGlobal();
				$row = $db->addKnowBy($data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	
	function addDoctypeAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Application_Model_DbTable_DbGlobal();
				$row = $db->addDocType($data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	function getStudenttypeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$student = $db->getAllNation();
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
	
}