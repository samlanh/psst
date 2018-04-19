<?php
class Global_GroupController extends Zend_Controller_Action {
    public function init()
    {    	
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
						'title' => '',
						'study_year' => '',
						'degree'=>'',
						'grade' => '',
						'time' => '',
						'session' =>'',
						'status_search'=>1,
						'start_date'=>date("Y-m-d"),
						'end_date' => date("Y-m-d")
						);
			}
			$db = new Global_Model_DbTable_DbGroup();
			$rs_rows= $db->getAllGroups($search);
			$glClass = new Application_Model_GlobalClass();
			$list = new Application_Form_Frmtable();
			
			$collumns = array("GROUP_CODE","YEARS","SEMESTER","DEGREE","GRADE","SESSION","ROOM_NAME","START_DATE","END_DATE","NOTE","PROCESS_TYPE","STATUS");
			
			$link=array(
					'module'=>'global','controller'=>'group','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('group_code'=>$link,'tuitionfee_id'=>$link,'degree'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$frm = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Global_Model_DbTable_DbGroup();
				$db->AddNewGroup($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/global/group");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/global/group/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();exit();
			}
		}
		
		$_db = new Global_Model_DbTable_DbGroup();
		$this->view->degree = $rows = $_db->getAllFecultyName();
		$this->view->row_year=$_db->getAllYears();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		
		$this->view->parent_subject = $_db->getParentSubject();
		$this->view->subject = $_db->getAllSubjectStudy();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>$this->tr->translate("ADD_NEW")));
		$this->view->teacher = $teacher;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($room, array ( 'id' => 0,'name' =>$this->tr->translate("SELECT_ROOM")));
		$this->view->room = $room;
		
		$db=new Global_Model_DbTable_DbGrade();
		$d_row=$db->getNameGradeAll();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$d_row;
		
		$dept = $db->getAllDept();
		array_unshift($dept, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->dept = $dept;
	}
	function editAction(){
		$db= new Global_Model_DbTable_DbGroup();
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db->updateGroup($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/global/group/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				echo $e->getMessage();
			}
		}
		
		$id=$this->getRequest()->getParam("id");
		
		$this->view->rs = $group_info = $db->getGroupById($id);
		
		if($group_info['is_pass']==1){
			Application_Form_FrmMessage::Sucessfull("ក្រុមសិក្សាត្រូវបានបញ្ចប់ មិនអាចកែបានទេ !!! ", "/global/group/index");
		}
		
		$this->view->row = $db->getGroupSubjectById($id);
		
		$db = new Global_Model_DbTable_DbGroup();
		$this->view->degree = $rows = $db->getAllFecultyName();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, Array('id'=> -1 ,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->room =$room;
	
		
		$_db = new Global_Model_DbTable_DbGroup();
		$this->view->subject = $_db->getAllSubjectStudy();
		$this->view->row_year=$_db->getAllYears();
		
		$db=new Global_Model_DbTable_DbGrade();
		$grade=$db->getNameGradeAll();
		array_unshift($grade, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$grade;
		
		$dept = $db->getAllDept();
		array_unshift($dept, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->dept = $dept;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>$this->tr->translate("ADD_NEW")));
		$this->view->teacher = $teacher;
	}
	function copyAction(){
		$db= new Global_Model_DbTable_DbGroup();
		
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db->AddNewGroup($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/global/group/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id=$this->getRequest()->getParam("id");
		$this->view->rs = $db->getGroupById($id);
		$this->view->row = $db->getGroupSubjectById($id);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, Array('id'=> -1 ,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->room =$room;

		$_db = new Global_Model_DbTable_DbGroup();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		$this->view->subject = $_db->getAllSubjectStudy();
		$this->view->row_year=$_db->getAllYears();
		$this->view->parent_subject = $_db->getParentSubject();
		
		$db = new Global_Model_DbTable_DbGrade();
		$dept = $db->getAllDept();
		array_unshift($dept, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->degree = $dept;
		
		$grade=$db->getNameGradeAll();
		array_unshift($grade, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$grade;
		
		$this->view->teacher_option = $_db->getAllTeacherOption();
		
		$teacher = $_db->getAllTeacher();
		array_unshift($teacher, array('id'=>-1,'name'=>$this->tr->translate("ADD_NEW")));
		$this->view->teacher = $teacher;
	}
	function addRoomAction(){
		if($this->getRequest()->isPost()){
			try{
				$data=$this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbGroup();
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
			$db = new Global_Model_DbTable_DbGroup();
			$group = $db->getDeptSubjectById($data['dept_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	function addGraddjaxAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbGroup();
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
    		array_unshift($group, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_ROOM")));
    		
    		$result = array('group'=>$group,'degree'=>$degree,'room'=>$room);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    function addTeacherPopupAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbGroup();
    			$teacher = $db->addTeacherAjax($data);
    			print_r(Zend_Json::encode($teacher));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
}