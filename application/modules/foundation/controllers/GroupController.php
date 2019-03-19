<?php
class Foundation_GroupController extends Zend_Controller_Action {
	const REDIRECT_URL = '/foundation/group';
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
						'branch_id' => '',
						'study_year' => '',
						'degree'=>'',
						'grade' => '',
						'time' => '',
						'session' =>'',
						'status'=>-1,
						);
			}
			$this->view->adv_search = $search;
			$db = new Foundation_Model_DbTable_DbGroup();
			$rs_rows= $db->getAllGroups($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			
			$collumns = array("BRANCH","GROUP_CODE","YEARS","SEMESTER","DEGREE","GRADE","SESSION","ROOM_NAME","NOTE","PROCESS_TYPE","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'group','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link,'tuitionfee_id'=>$link,'degree'=>$link,'grade'=>$link));
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
			$data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$db= new Foundation_Model_DbTable_DbGroup();
				$group_id= $db->AddNewGroup($data);
				if($group_id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
				}
				Application_Form_FrmMessage::message($sms);
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$_db = new Foundation_Model_DbTable_DbGroup();
		$this->view->row_year=$_db->getAllYears();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		
		$this->view->parent_subject = $_db->getParentSubject();
		$this->view->subject = $_db->getAllSubjectStudy();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($room, array ( 'id' => 0,'name' =>$this->tr->translate("SELECT_ROOM")));
		$this->view->room = $room;
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$d_row= $_dbgb->getAllGradeStudy();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$d_row;
		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();
		
		$tsub= new Global_Form_FrmAddClass();
		$frm_group=$tsub->FrmAddGroup();
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}
	function editAction(){
		$db= new Foundation_Model_DbTable_DbGroup();
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db->updateGroup($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("EDIT_FAIL");
			}
		}
		
		$id=$this->getRequest()->getParam("id");
		
		$row = $group_info = $db->getGroupById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
			exit();
		}
		$this->view->rs = $row;
		if($group_info['is_pass']==1){
			//Application_Form_FrmMessage::Sucessfull("ក្រុមសិក្សាត្រូវបានបញ្ចប់ មិនអាចកែបានទេ !!! ", "/global/group/index");
		}
		
		$this->view->row = $db->getGroupSubjectById($id);
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, Array('id'=> -1 ,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->room =$room;
	
		$_db = new Accounting_Model_DbTable_DbFee();
		$tution = $_db->getFeeById($row['academic_year']);
		$schoolOption = $tution['school_option'];
		
		$_dggroup = new Foundation_Model_DbTable_DbGroup();
		if (!empty($schoolOption)){
			$db = new Foundation_Model_DbTable_DbGroup();
			$teacher = $_dggroup->getAllTeacher($schoolOption);
			array_unshift($teacher, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			array_unshift($teacher, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
			$this->view->teacher =$teacher;
			$this->view->teacher_option = $_dggroup->getAllTeacherOption($schoolOption);
			$this->view->subject = $_dggroup->getAllSubjectStudy(null,$schoolOption);
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$d_row= $_dbgb->getAllGradeStudy();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$d_row;
		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();
		$this->view->statustype = $_dbgb->getViewById(9);
		$tsub= new Global_Form_FrmAddClass();
		$frm_group=$tsub->FrmAddGroup($row);
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}
	function copyAction(){
		$db= new Foundation_Model_DbTable_DbGroup();
		
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$groupExit = $db->checkGroupExits($data);
				if (!empty($groupExit)){
					Application_Form_FrmMessage::Sucessfull("RECORD_EXIST", self::REDIRECT_URL."/index");
				}
				$db->AddNewGroup($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL."/index");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id=$this->getRequest()->getParam("id");
		$row = $db->getGroupById($id);
		$this->view->row = $db->getGroupSubjectById($id);
		$this->view->rs = $row;
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, Array('id'=> -1 ,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->room =$room;

		$_db = new Accounting_Model_DbTable_DbFee();
		$tution = $_db->getFeeById($row['academic_year']);
		$schoolOption = $tution['school_option'];
		
		$_dggroup = new Foundation_Model_DbTable_DbGroup();
		if (!empty($schoolOption)){
			$db = new Foundation_Model_DbTable_DbGroup();
			$teacher = $_dggroup->getAllTeacher($schoolOption);
			array_unshift($teacher, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			array_unshift($teacher, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
			$this->view->teacher =$teacher;
				
			$this->view->teacher_option = $_dggroup->getAllTeacherOption($schoolOption);
			$this->view->subject = $_dggroup->getAllSubjectStudy(null,$schoolOption);
		}
		
		$_db = new Foundation_Model_DbTable_DbGroup();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
// 		$this->view->subject = $_db->getAllSubjectStudy();
		$this->view->row_year=$_db->getAllYears();
		$this->view->parent_subject = $_db->getParentSubject();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$dept = $_dbgb->getAllItems(1);//degree
		
		array_unshift($dept, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->degree = $dept;
		$this->view->dept = $dept;
		
		$d_row= $_dbgb->getAllGradeStudy();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->grade_name=$d_row;
		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();
		
		$tsub= new Global_Form_FrmAddClass();
		$frm_group=$tsub->FrmAddGroup($row);
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}
	function addRoomAction(){
		if($this->getRequest()->isPost()){
			try{
				$data=$this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbGroup();
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
			$db = new Foundation_Model_DbTable_DbGroup();
			$group = $db->getDeptSubjectById($data['dept_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	function addGraddjaxAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItemsDetail();
    			$row = $db->AddGradeByAjax($data);
//     			$db = new Foundation_Model_DbTable_DbGroup();
//     			$row = $db->addGradeAjax($data);
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
    			$db = new Foundation_Model_DbTable_DbGroup();
    			$teacher = $db->addTeacherAjax($data);
    			print_r(Zend_Json::encode($teacher));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    function getteacherAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    		
    		if (!empty($schoolOption)){
	    		$db = new Foundation_Model_DbTable_DbGroup();
	    		$teacher = $db->getAllTeacher($schoolOption);
	    		
	    		array_unshift($teacher, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
	    		array_unshift($teacher, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
	    		print_r(Zend_Json::encode($teacher));
	    		exit();
    		}
    	}
    }
    
    function getsubjectAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    
    		if (!empty($schoolOption)){
    			$db = new Application_Model_DbTable_DbGlobal();
				$subject = $db->getAllSubjectStudy($schoolOption);
				array_unshift($subject, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
				print_r(Zend_Json::encode($subject));
    			exit();
    		}
    	}
    }
    function getroomAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$model = new Application_Model_DbTable_DbGlobal();
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		array_unshift($room, array ( 'id' => 0,'name' =>$this->tr->translate("SELECT_ROOM")));
    		print_r(Zend_Json::encode($room));
    		exit();
    	}
    }
    function getgroupbybranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$forfilter=empty($data['forfilter'])?null:$data['forfilter'];
    		$group = $db->getAllGroupByBranch($data['branch_id'],$forfilter);
    		if(!empty($data['noaddnew'])){
    			array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
    function getgroupbyacademicAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllGroupByAcademic($data['academic_year']);
    		if (empty($data['noaddnew'])){
    			array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
    function getacademicAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllYearByBranch($data['branch_id']);
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_ACADEMIC_YEAR")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
    function getdegreeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    		
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllItems(1,null,$schoolOption);
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_DEGREE")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
}