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
				$collumns = array("BRANCH_NAME","STUDENT_ID","NAME_KH","NAME_EN","SEX","PHONE","ACADEMIC_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
				$link=array(
						'module'=>'foundation','controller'=>'register','action'=>'edit',
				);
				$link1=array(
						'module'=>'foundation','controller'=>'register','action'=>'view',
				);
				$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('branch_name'=>$link1,'stu_code'=>$link,'name'=>$link,'stu_khname'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
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
						}else{
							Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/register/add");
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
		array_unshift($group, array ( 'id' =>'','name' => $this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
		
		$this->view->row = $db->getDegreeLanguage();
		
		$this->view->year = $db->getAllYear();
		
		$this->view->degree = $rows = $db->getAllFecultyName();
		
		$this->view->room = $row =$db->getAllRoom();
		
		$this->view->province = $row =$db->getProvince();
		
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
		
	}
	public function editAction(){
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
		
		$this->view->degree = $db->getAllFecultyName();
		
		$this->view->province = $db->getProvince();
		
		$this->view->rs =$test =  $db->getStudentById($id);
		//echo $test['group_id'];exit();
		
		$this->view->year = $db->getAllYear();
		$this->view->room = $row =$db->getAllRoom();
		
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
	}
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudent();
			$grade = $db->getAllGrade($data['dept_id']);
			//print_r($grade);exit();
			array_unshift($grade, array ( 'id' => -1, 'name' => 'Add New'));
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
	function submitAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbLanguage();
				$row = $db->addDegreeLanguage($data);
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
			$student_type=$data['student_type'];
			$stu_no = $db->getNewAccountNumber($student_type);
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
						}else{
							Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/register/add");
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
		array_unshift($group, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($group, array ( 'id' =>'','name' => $this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
		
		$this->view->degree = $db->getAllFecultyName();
		
		$this->view->province = $db->getProvince();
		
		$this->view->rs = $db->getStudentById($id);
		
		$this->view->year = $db->getAllYear();
		$this->view->room = $row =$db->getAllRoom();
		
		$row = $db->getStudentById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/foundation/register");
		}
		$rr = $db->getStudyHishotryById($id);
		$this->view->rr = $rr;
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
	}
}