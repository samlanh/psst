<?php
class Foundation_GroupController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/foundation/group';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction()
	{
		try {
			if ($this->getRequest()->isPost()) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'adv_search' 	=> '',
					'branch_id' => '',
					'academic_year' => '',
					'degree'	=> '',
					'grade' 	=> '',
					'time' 		=> '',
					'partTimeList' 	=> '',
					'status'	=> -1,
					'teacher' 	=> '',
					'start_date' => date('Y-m-d'),
					'end_date' => date('Y-m-d'),
				);
			}
			$this->view->adv_search = $search;
			$db = new Foundation_Model_DbTable_DbGroup();
			$rs_rows = $db->getAllGroups($search);
			$list = new Application_Form_Frmtable();

			$collumns = array("BRANCH", "GROUP_CODE", "YEARS", "SEMESTER", "SESSION", "ROOM_NAME", "TEACHER_ROOM", "TIME", "NOTE", "PROCESS_TYPE", "STATUS");
			$link = array(
				'module' => 'foundation', 'controller' => 'group', 'action' => 'edit',
			);
			$this->view->list = $list->getCheckList(10, $collumns, $rs_rows, array('branch_name' => $link, 'titleRecord' => $link, 'tuitionfee_id' => $link, 'degree' => $link, 'grade' => $link));
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	function addAction()
	{
		$dbgb = new Application_Model_DbTable_DbGlobal();
		if ($this->getRequest()->isPost()) {
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)) {
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$data = $this->getRequest()->getPost();
			try {
				$sms = "INSERT_SUCCESS";
				$db = new Foundation_Model_DbTable_DbGroup();
				$group_id = $db->AddNewGroup($data);
				if ($group_id == -1) {
					$sms = "RECORD_EXIST";
				}
				if (!empty($data['save_close'])) {
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL . "/index");
				} else {
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL . "/add");
				}
				Application_Form_FrmMessage::message($sms);
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$_db = new Foundation_Model_DbTable_DbGroup();
		$this->view->row_year = $_db->getAllYears();

		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();

		$tsub = new Global_Form_FrmAddClass();
		$frm_group = $tsub->FrmAddGroup();
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}
	function editAction()
	{
		$db = new Foundation_Model_DbTable_DbGroup();
		$id = $this->getRequest()->getParam("id");

		$row = $group_info = $db->getGroupById($id);
		if (empty($row)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL . "/index");
			exit();
		}
		$this->view->rs = $row;
		if ($this->getRequest()->isPost()) {
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)) {
				$dbgb->reloadPageExpireSession();
				exit();
			}
			try {
				$data = $this->getRequest()->getPost();
				$db->updateGroup($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL . "/index");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("EDIT_FAIL");
			}
		}

		if ($group_info['is_pass'] == 1) {
			//Application_Form_FrmMessage::Sucessfull("ក្រុមសិក្សាត្រូវបានបញ្ចប់ មិនអាចកែបានទេ !!! ", "/global/group/index");
		}
		$this->view->row = $db->getGroupSubjectById($id);
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->room = $room;

		$_dbBranch = new RsvAcl_Model_DbTable_DbBranch();
		$rowBr = $_dbBranch->getBranchById($row['branch_id']); //get branch info
		$schoolOption = $rowBr['schooloptionlist'];

		$_dggroup = new Foundation_Model_DbTable_DbGroup();
		if (!empty($schoolOption)) {
			$arrayFilter = array(
				"branch_id" => $row['branch_id'],
				"schoolOption" => $schoolOption
			);
			$teacher = $model->getAllTeahcerName($arrayFilter);
			array_unshift($teacher, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			array_unshift($teacher, array('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
			$this->view->teacher = $teacher;
			$this->view->teacher_option = $model->getAllTeacherOption($schoolOption, $row['branch_id'], $addNew = 1);
			$this->view->subject = $_dggroup->getAllSubjectStudy(null, $schoolOption);
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();

		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();
		$this->view->statustype = $_dbgb->getViewById(9);
		$tsub = new Global_Form_FrmAddClass();
		$frm_group = $tsub->FrmAddGroup($row);
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}
	function copyAction()
	{
		$db = new Foundation_Model_DbTable_DbGroup();
		$id = $this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$row = $db->getGroupById($id);
		if (empty($row)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL . "/index");
			exit();
		}
		if ($this->getRequest()->isPost()) {
			try {
				$data = $this->getRequest()->getPost();
				$groupExit = $db->checkGroupExits($data);
				if (!empty($groupExit)) {
					Application_Form_FrmMessage::Sucessfull("RECORD_EXIST", self::REDIRECT_URL . "/index");
				}
				$db->AddNewGroup($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . "/index");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}

		$this->view->row = $db->getGroupSubjectById($id);
		$this->view->rs = $row;
		$model = new Application_Model_DbTable_DbGlobal();
		$room = $model->getAllRoom();
		array_unshift($room, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->room = $room;

		$_dbBranch = new RsvAcl_Model_DbTable_DbBranch();
		$rowBr = $_dbBranch->getBranchById($row['branch_id']); //get branch info
		$schoolOption = $rowBr['schooloptionlist'];

		$_dggroup = new Foundation_Model_DbTable_DbGroup();
		if (!empty($schoolOption)) {
			$arrayFilter = array(
				"branch_id" => $row['branch_id'],
				"schoolOption" => $schoolOption
			);
			$teacher = $model->getAllTeahcerName($arrayFilter);
			array_unshift($teacher, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			array_unshift($teacher, array('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
			$this->view->teacher = $teacher;
			$this->view->teacher_option = $model->getAllTeacherOption($schoolOption, $row['branch_id'], $addNew = 1);
			$this->view->subject = $_dggroup->getAllSubjectStudy(null, $schoolOption);
		}

		$_db = new Foundation_Model_DbTable_DbGroup();
		$this->view->subjectlist = $_db->getAllSubjectStudy(1);
		$this->view->row_year = $_db->getAllYears();
		$this->view->parent_subject = $_db->getParentSubject();

		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$dept = $_dbgb->getAllItems(1); //degree

		array_unshift($dept, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->degree = $dept;
		$this->view->dept = $dept;
		$param = array(
			'itemsType' => 1
		);
		$d_row = $_dbgb->getAllItemDetail($param);
		array_unshift($d_row, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->grade_name = $d_row;
		$this->view->schooloptionlist =  $_dbgb->getAllSchoolOption();

		$tsub = new Global_Form_FrmAddClass();
		$frm_group = $tsub->FrmAddGroup($row);
		Application_Model_Decorator::removeAllDecorator($frm_group);
		$this->view->frm = $frm_group;
	}

	function getsubjectbydegreeAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroup();
			$group = $db->getDeptSubjectById($data['dept_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}

	function getAcademicyearAction()
	{ //year for study only
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$group = $db->getAllAcademicYear();
			array_unshift($group, array('id' => '', 'name' => $this->tr->translate("SELECT_ACADEMIC_YEAR")));
			//
			/*if (empty($data['noaddnew'])){
    			array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));*/

			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	function getgroupbybranchAction()
	{ //for show with prefix year
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();

			$forfilter = empty($data['forfilter']) ? null : $data['forfilter'];
			$data['academic_year'] = empty($data['academic_year']) ? null : $data['academic_year'];
			$group = $db->getAllGroupByBranch($data['branch_id'], $forfilter, $data);
			if (empty($data['noaddnew'])) {
				array_unshift($group, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			}
			array_unshift($group, array('id' => '', 'name' => $this->tr->translate("SELECT_GROUP")));
			print_r(Zend_Json::encode($group));
			exit();
		}
	}

	function getallgroupAction()
	{ //all get group use this function
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();

			$forfilter = empty($data['forfilter']) ? null : $data['forfilter'];
			$group = $db->getAllGroupName($data, $forfilter);
			if (empty($data['noaddnew'])) {
				array_unshift($group, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
			}
			array_unshift($group, array('id' => '', 'name' => $this->tr->translate("SELECT_GROUP")));
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
}
