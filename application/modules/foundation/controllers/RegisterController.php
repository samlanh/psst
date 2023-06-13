<?php
class Foundation_RegisterController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	defined('SHOW_DOCUMENT') || define('SHOW_DOCUMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('doc_display'));
    	defined('STU_EN_REQUIRED') || define('STU_EN_REQUIRED', Setting_Model_DbTable_DbGeneral::geValueByKeyName('name_required'));
		defined('ENTY_STUID') || define('ENTY_STUID', Setting_Model_DbTable_DbGeneral::geValueByKeyName('entry_stuid'));
    	
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
						'groupId'=> '',
						'gradeId'=> '',
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
		//$this->view->currentFee =  $db->getCurentFeeStudentHistory($id);
		$this->view->currentStudy =  $db->getCurentStudentStudy($id);
		
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudentRegister($student_rs);
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
			$db = new Application_Model_DbTable_DbGlobal();
			$degree=empty($data['dept_id'])?0:$data['dept_id'];
			$branch_id=$data['branch_id'];
			$stu_no = $db->getnewStudentId($branch_id,$degree);
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
		$id = (empty($id))?0:$id;
		$db= new Foundation_Model_DbTable_DbStudent();
		$result =  $db->getStudentViewDetailById($id);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/register");
		}
		$this->view->rs =$result;
		$_db= new Home_Model_DbTable_DbStudent();
		$this->view->rsStudentRerecord = $_db->getAllStudentStudyRecord($id);
		$this->view->document =$_db->getStudentDocumentById($id);
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
		
		$this->view->degree = $_db->getAllItems(1,null);
		
		$this->view->province = $db->getProvince();
		
		$test =  $db->getStudentById($id);
		$this->view->rs = $test;
		$this->view->row = $db->getStudentDocumentById($id);
		
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
		$this->view->doc_type = $row;
		
		$this->view->degree = $_db->getAllItems(1,null);
		
		$this->view->province = $db->getProvince();
		
		$test =  $db->getStudentById($id);
		$this->view->rs = $test;
		$this->view->row = $db->getStudentDocumentById($id);
		
		$this->view->year = $_db->getAllAcademicYear();
		$this->view->room = array();
		
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
				$data=$db->getAllStudentByGroupForEdit($data['groupId']);
			}else{
				$data=$db->getStudentByGroupGlobal($data);
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
	function getallstudentdataAction(){
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

	function getallnationAction(){//all get nation use this function
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    
    		$nation = $db->getAllNation();
    		if(!empty($data['addNew'])){
    			array_unshift($nation, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($nation, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_NATION")));
    		print_r(Zend_Json::encode($nation));
    		exit();
    	}
    }
	function getalljobAction(){//all get nation use this function
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    
    		$job = $db->getOccupation();
    		if(!empty($data['addNew'])){
    			array_unshift($job, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($job, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_JOB")));
    		print_r(Zend_Json::encode($job));
    		exit();
    	}
    }


}