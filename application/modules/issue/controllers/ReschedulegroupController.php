<?php
class Issue_ReschedulegroupController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' => '',
						'branch_id' => '',
						'day' => '',
						'study_year' => '',
						'group' => '',
						'start_date'=>date("Y-m-d"),
						'end_date' => date("Y-m-d")
						);
			}
			$db = new Foundation_Model_DbTable_DbRescheduleGroup();
			$rs_rows= $db->getAllRescheduleGroup($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDY_YEAR","GROUP","DAY","FROM_HOUR","TO_HOUR","SUBJECT","TEACHER","DATE","USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'reschedulegroup','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link,'years'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Foundation_Model_DbTable_DbRescheduleGroup();
				$db->addRescheduleGroup($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAILE");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$_db = new Foundation_Model_DbTable_DbRescheduleGroup();
		//$this->view->degree = $rows = $_db->getAllFecultyName();
		$this->view->row_year=$_db->getAllYears();
		//$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		
		//$this->view->parent_subject = $_db->getParentSubject();
		$this->view->subject = $_db->getAllSubjectStudy();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>'Add New'));
		$this->view->teacher = $teacher;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		array_unshift($room, array ( 'id' => 0,'name' => 'Select Room'));
		$this->view->room = $room;
		$this->view->branch_name = $model->getAllBranch();
		
		$db=new Global_Model_DbTable_DbGrade();
// 		$d_row=$db->getNameGradeAll();
// 		array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->grade_name=$d_row;
		
// 		$dept = $db->getAllDept();
// 		array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->dept = $dept;
		
		$db_glob = new Application_Model_GlobalClass();
		$this->view->optday = $db_glob->getAllDays();
		$this->view->opttime = $db_glob->getHoursStudy();
	}
	
	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Foundation_Model_DbTable_DbRescheduleGroup();
				$db->updateRescheduleGroup($data,$id);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/issue/reschedulegroup");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/issue/reschedulegroup/");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup/");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$_db = new Foundation_Model_DbTable_DbRescheduleGroup();
		$row =$_db->getRescheduleById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/reschedulegroup");
			exit();
		}
		$this->view->row_g = $row;
		
		$this->view->degree = $rows = $_db->getAllFecultyName();
		$this->view->row_year=$_db->getAllYears();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		$this->view->group_code=$_db->getGroupName();
		
		
		$this->view->parent_subject = $_db->getParentSubject();
		$this->view->subject = $_db->getAllSubjectStudy();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>'Add New'));
		$this->view->teacher = $teacher;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		array_unshift($room, array ( 'id' => 0,'name' => 'Select Room'));
		$this->view->room = $room;
		
		$this->view->branch_name = $model->getAllBranch();
		
		$db=new Global_Model_DbTable_DbGrade();
// 		$d_row=$db->getNameGradeAll();
// 		array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->grade_name=$d_row;
		
// 		$dept = $db->getAllDept();
// 		array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->dept = $dept;
		
		$db_glob = new Application_Model_GlobalClass();
		$this->view->optday = $db_glob->getAllDays();
		$this->view->opttime = $db_glob->getHoursStudy();
		
	}
	
	function copyAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Foundation_Model_DbTable_DbRescheduleGroup();
				$db->addRescheduleGroup($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup/");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/reschedulegroup/");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$_db = new Foundation_Model_DbTable_DbRescheduleGroup();
		$row =$_db->getRescheduleById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/reschedulegroup");
			exit();
		}
		$this->view->row_g=$row;
		
		$this->view->degree = $rows = $_db->getAllFecultyName();
		$this->view->row_year=$_db->getAllYears();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		$this->view->group_code=$_db->getGroupName();
		
		
		$this->view->parent_subject = $_db->getParentSubject();
		$this->view->subject = $_db->getAllSubjectStudy();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>'Add New'));
		$this->view->teacher = $teacher;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
		array_unshift($room, array ( 'id' => 0,'name' => 'Select Room'));
		$this->view->room = $room;
		
		$this->view->branch_name = $model->getAllBranch();
		
		$db=new Global_Model_DbTable_DbGrade();
// 		$d_row=$db->getNameGradeAll();
// 		array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->grade_name=$d_row;
		
// 		$dept = $db->getAllDept();
// 		array_unshift($dept, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
// 		$this->view->dept = $dept;
		
		$db_glob = new Application_Model_GlobalClass();
		$this->view->optday = $db_glob->getAllDays();
		$this->view->opttime = $db_glob->getHoursStudy();
	}
	
	function addRoomAction(){
		if($this->getRequest()->isPost()){
			try{
				$data=$this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbRescheduleGroup();
				$room = $db->addNewRoom($data);
				print_r(Zend_Json::encode($room));
				exit();
			}catch (Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
		}
	}
	function getsubjectbydegreeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbRescheduleGroup();
			$group = $db->getDeptSubjectById($data['dept_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	function addGraddjaxAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Foundation_Model_DbTable_DbRescheduleGroup();
    			$row = $db->addGradeAjax($data);
    			print_r(Zend_Json::encode($row));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    function getgroupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbStudent();
    		$group = $db->getAllgroup();
    		$degree = $db->getAllFecultyName();    		
    		array_unshift($group, array ('id' => -1, 'name' => 'Add New'));
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => 0,'name' => 'Select Room'));
    		
    		$result = array('group'=>$group,'degree'=>$degree,'room'=>$room);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    
    function addTeacherPopupAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Foundation_Model_DbTable_DbRescheduleGroup();
    			$teacher = $db->addTeacherAjax($data);
    			print_r(Zend_Json::encode($teacher));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    
    function getsubjectajaxAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Foundation_Model_DbTable_DbRescheduleGroup();
    			$teacher = $db->getSubjectByGroupId($data['group_id']);
    			print_r(Zend_Json::encode($teacher));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    
}

