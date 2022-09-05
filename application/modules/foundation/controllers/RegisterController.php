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
						'group'=> '',
						'grade_all'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> -1,
						'branch_id'=>''
					);
			}
			$this->view->adv_search=$search;
			$db_student= new Foundation_Model_DbTable_DbStudent();
			$rs_rows = $db_student->getAllStudent($search);
			$list = new Application_Form_Frmtable();
			
				$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_EN","SEX","PHONE","ACADEMIC_YEAR","GROUP","USER","STATUS");
				$link=array(
						'module'=>'foundation','controller'=>'register','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,
						'stu_name'=>$link,'stu_khname'=>$link,'group_name'=>$link,'academic'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$db = new Application_Model_DbTable_DbGlobal();
		echo $db->getnewStudentId(1,1);
	}
	function addAction(){
		$db = new Foundation_Model_DbTable_DbStudent();
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/register/index");
					exit();
				}
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
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
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
		
		$row = $_db->getAllKnowBy(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->know_by = $row;
		
		$row = $_db->getAllDocumentType(); // degree language
		$this->view->doc = $row;
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		$this->view->province = $db->getProvince();
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudentRegister();
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Foundation_Model_DbTable_DbStudent();
		
// 		$rr = $db->getStudyHishotryById($id);
// 		$this->view->rr = $rr;
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/register/index");
					exit();
				}
				$row=$db->updateStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/register/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$row = $db->getStudentById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/foundation/register");
			exit();
		}
		
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
		
		$row = $_db->getAllKnowBy(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->know_by = $row;
		
		$row = $_db->getAllDocumentType(); // degree language
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT")));
		$this->view->doc_type = $row;
		
		$this->view->province = $db->getProvince();
		
		$student_rs =  $db->getStudentById($id);
		$this->view->rs = $student_rs;
		$this->view->row = $db->getStudentDocumentById($id);
		$this->view->currentFee =  $db->getCurentFeeStudentHistory($id);
		$this->view->currentStudy =  $db->getCurentStudentStudy($id);
		
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudentRegister($student_rs);
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$grade = $_dbgb->getAllGradeStudyByDegree($data['dept_id']);
			if(empty($data['noaddnew'])){
				array_unshift($grade, array ( 'id' => -1, 'name' =>$this->tr->translate("ADD_NEW")));
			}
			array_unshift($grade, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GRADE")));
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
			$degree=empty($data['dept_id'])?0:$data['dept_id'];
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
		
		$_db= new Home_Model_DbTable_DbStudent();
		$this->view->rsStudentRerecord = $_db->getAllStudentStudyRecord($id);
	}
	
	public function copyAction(){
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db= new Foundation_Model_DbTable_DbStudent();
		
		
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/register/index");
					exit();
				}
				$exist = $db->addStudent($data);
				if($exist==-1){
					Application_Form_FrmMessage::Sucessfull("RECORD_EXIST","/foundation/register/index");
					exit();
				}
				
				Application_Form_FrmMessage::Sucessfull("COPY_SUCCESS","/foundation/register/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("COPY_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getStudentById($id);
		$this->view->rs = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/foundation/register");
			exit();
		}
// 		$rr = $db->getStudyHishotryById($id);
// 		$this->view->rr = $rr;
		
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
		
		$row = $_db->getAllKnowBy(); // degree language
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
		$this->view->currentFee =  $db->getCurentFeeStudentHistory($id);
// 		$this->view->year = $db->getAllYear();
// 		$this->view->room = $row =$db->getAllRoom();
		
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
// 		$rr = $db->getStudyHishotryById($id);
// 		$this->view->rr = $rr;
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
		
		$row = $_db->getAllKnowBy(); // degree language
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
				$row = $db->addDocstudentType($data);
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
	function webcamAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Foundation_Model_DbTable_DbStudent();
			$serial = $_dbgb->uploadFile($data);
			print_r($serial);
			exit();
		}
	}
	
	function getstudentlistAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$branch_id = empty($data['branch_id'])?null:$data['branch_id'];
			$rows = $db->getAllStudentList($branch_id,$data);
			
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
	function getStudentBygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			if (!empty($data['edit'])){//for all page edit 
				$data=$db->getAllStudentByGroupForEdit($data['group']);
			}else{
				$data=$db->getStudentByGroup(data);
			}
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	
	function checkstudentcodeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$studentCode = empty($data['student_id'])?"":$data['student_id'];
			$id = empty($data['id'])?"":$data['id'];
			$db = new Foundation_Model_DbTable_DbStudent();
			$id_existing = $db->ifStudentIdExisting($studentCode,$id);
			$return = 0;
			if (!empty($id_existing)){
				$return = 1;
			}
			print_r(Zend_Json::encode($return));
			exit();
		}
	}
	
	function checkstudentduplicateAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$idStu = empty($_data['id'])?0:$_data['id'];
			$id_existing = $db->getStudentExist($_data,$idStu);
			$return = 0;
			if (!empty($id_existing)){
				$return = 1;
			}
			print_r(Zend_Json::encode($return));
			exit();
		}
	}
	
	//new get student study class new create on 24-3-2020
	function getallstudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$data['branch_id'] = !empty($data['branch_id'])?$data['branch_id']:null;
			$rows = $db->getAllStudentStudy(null,$data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getstudentstudyinfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$grade = $db->getStudentStudyInfo($data['study_id']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
}