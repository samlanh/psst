<?php
class Allreport_ScoreController extends Zend_Controller_Action
{
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
	}
	function rptScoreAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'room' => 0,
				'exam_type' => -1,
				'for_semester' => -1,
				'group' => '',

				'academic_year' => '',
				'grade' => 0,
				'degree' => 0,
				'session' => 0,
				'for_month' => date('m'),
				'score_result_status' => 0,
			);
		}

		$this->view->search = $search;

		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->studentgroup = $db->getStundetScoreGroup($search);
		$group = $db->getAllgroupStudyNotPass();
		array_unshift($group, array('id' => 0, 'name' => 'ជ្រើសរើស'));
		$this->view->g_all_name = $group;


		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($search['branch_id']) ? 1 : $search['branch_id'];
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->month = $db->getAllMonth();
	}

	function rptScoreResultAction()
	{ //ពិន្ទុសរុបតាមមុខ
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			$isgetId = null;
		} else {
			$isgetId = $id;
			$row = $db->getScoreExamByID($id);
			$search = array(
				'group' => $row['group_id'],
				'study_year' => $row['for_academic_year'],
				'exam_type' => $row['exam_type'],
				'branch_id' => $row['branch_id'],
				'for_month' => $row['for_month'],
				'for_semester' => $row['for_semester'],
				'grade' => '',
				'degree' => '',
				'session' => '',
			);
		}
		$result = $db->getStudentScoreResult($search, $isgetId, 1);
		$this->view->studentScoreResult = $result;

		$this->view->scoreId = $id;

		$this->view->search = $search;

		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($result[0]['branch_id']) ? 1 : $result[0]['branch_id'];
		$this->view->headerScore = $frm->getHeaderReportScore($branch_id);

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branchInfo = $db->getBranchInfo($branch_id);
		$this->view->month = $db->getAllMonth();

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId = empty($result[0]['degree_id']) ? 0 : $result[0]['degree_id'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId, 1);
		$this->view->academicStaffInfo = $dbSetting->getDutyByDegree($dregreeId, 2);
	}
	function rptScoreDetailAction()
	{ //តាមមុខវិជ្ជាលម្អិត
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
	
		$row = $db->getScoreExamByID($id);
		$this->view->scoreResult=$row;

		$this->view->subj = $db->getSubjectScoreGroup($row ['group_id'], null, $row ['exam_type']);

		$param=array(
			'group_id' => $row['group_id'],
			'for_academic_year' => $row['for_academic_year'],
			'degree' => $row['degree'],
			'exam_type' => $row['exam_type'],
			'semesterTotalAverage' => $row['semesterTotalAverage'],
		);

		$this->view->studentgroup  = $db->getStundetScoreDetailGroup($id,$param);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId = empty($row['degree']) ? 0 : $row['degree'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId, 1);
	}

	function rptMonthlytranscriptAction()
	{

		$scoreId = $this->getRequest()->getParam("id");
		$stu_id = $this->getRequest()->getParam("stu_id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();

		$search = array(
			'scoreId' => empty($scoreId) ? 0 : $scoreId
		);
		if (!empty($stu_id)) {
			$search['stu_id'] = $stu_id;
		}

		$result = $db->getAllStudentIdByScoreResult($search, $scoreId, 1);
		$this->view->studentScoreResult = $result;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->month = $db->getAllMonth();
		// 		$this->view->grading =array();// $db->getGradingSystem(@$result[0]['degree_id']);
	}

	function rptSemestertranscriptAction()
	{

		$scoreId = $this->getRequest()->getParam("id");
		$stu_id = $this->getRequest()->getParam("stu_id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();

		$search = array(
			'scoreId' => empty($scoreId) ? 0 : $scoreId
		);
		if (!empty($stu_id)) {
			$search['stu_id'] = $stu_id;
		}

		$result = $db->getAllStudentIdByScoreResult($search, $scoreId, 1);
		$this->view->studentScoreResult = $result;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->month = $db->getAllMonth();
		// 		$this->view->grading =array();// $db->getGradingSystem(@$result[0]['degree_id']);
	}

	function certificateLetterofpraisenewAction()
	{
		$id = $this->getRequest()->getParam("id");
		$stu_id = $this->getRequest()->getParam("stu_id");
		$rank = $this->getRequest()->getParam("rank");
		$score_id = empty($id) ? 0 : $id;
		$stu_id = empty($stu_id) ? 0 : $stu_id;
		$rank = empty($rank) ? 0 : $rank;
		$this->view->rank = $rank;

		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$result = $db->getStudenLetterofpraiseNewById($score_id, $stu_id);
		if (empty($result)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score/rpt-student-letterofpraise");
			exit();
		}
		$this->view->rs = $result;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->certificateSetting = $db->getCertificateSetting(1);
	}
	function monthlyOutstandingStudentAction()
	{
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$id = $this->getRequest()->getParam("id");
		if(!empty($id)){

			$row = $db->getScoreExamByID($id);
			$search = array(
				'group' => $row['group_id'],
				'study_year' => $row['for_academic_year'],
				'exam_type' => $row['exam_type'],
				'branch_id' => $row['branch_id'],
				'for_month' => $row['for_month'],
				'for_semester' => $row['for_semester'],
				'grade' => '',
				'degree' => '',
				'session' => '',
			);
		}
		$this->view->search = $search;

		$studentgroup = $db->getStudentScoreResult($search, $id, 2);
		$this->view->studentgroup = $studentgroup;

		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($studentgroup[0]['branch_id']) ? 1 : $studentgroup[0]['branch_id'];
		$this->view->logoleft = $frm->getLeftLogo($branch_id);

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branchInfo = $db->getBranchInfo($branch_id);
		$this->view->certificateSetting = $db->getCertificateSetting(1);

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId = empty($studentgroup[0]['degree_id']) ? 0 : $studentgroup[0]['degree_id'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId, 1);
	}
	function monthlyOutstandingStudentNophotoAction()
	{
		$id = $this->getRequest()->getParam("id");
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'group_name' => '',
				'study_year' => '',
				'grade_bac' => '',
				'degree_bac' => '',
				'session' => '',
				'for_month' => '',
			);
		}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->studentgroup = $db->getStudentScoreResult($search, $id, 2);

		//$this->view->all_student = $db->getStundetScoreDetailGroup($search, $id, 1);

		$this->view->g_all_name = $db->getAllgroupStudyNotPass();
		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($studentgroup[0]['branch_id']) ? 1 : $studentgroup[0]['branch_id'];
		$this->view->header = $frm->getHeaderReportScore($branch_id);
	}
	public function studentGroupAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => "",
				'group' => "",
				'branch_id' => "",
				'academic_year' => "",
				'grade' => "",
				'session' => "",
				'teacher' => "",
				'room' => 0,
				'degree' => 0,
				'study_status' => -1,
			);
		}
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$rs = $db->getGroupDetailReport($search);
		$this->view->rs = $rs;
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? 1 : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}

	function rptResultbysemesterAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$type = $this->getRequest()->getParam("type");
		$search = array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_semester = $db->getStundetScorebySemester($group_id, $type); //ប្រើតែក្នុងលទ្ធផលប្រចាំខែ និង តារាងកិត្តិយស
		$array_score = array();
		if (empty($result_semester)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->rs_scoresemester = $result_semester;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = $result_semester[0]['branch_id'];
		$this->view->header = $frm->getHeaderReceipt($branch_id);
	}

	function semesterOutstandingStudentAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$semester = $this->getRequest()->getParam("type");
		$search = array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_semester = $db->getStundetScorebySemester($group_id, $semester);
		if (empty($result_semester)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->studentgroup = $result_semester;
	}
	function semesterOutstandingStudentNophotoAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$semester = $this->getRequest()->getParam("type");
		$search = array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_semester = $db->getStundetScorebySemester($group_id, $semester);
		if (empty($result_semester)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->studentgroup = $result_semester;
	}

	function rptResultbyyearAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_year = $db->getStundetScorebyYear($group_id);
		if (empty($result_year)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->studentgroup = $result_year;
	}

	function yearlyOutstandingStudentAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$type = $this->getRequest()->getParam("type");

		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_year = $db->getStundetScorebyYear($group_id);
		if (empty($result_year)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->studentgroup = $result_year;
	}
	function yearlyOutstandingStudentNophotoAction()
	{
		$group_id = $this->getRequest()->getParam("id");
		$type = $this->getRequest()->getParam("type");

		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$result_year = $db->getStundetScorebyYear($group_id);
		if (empty($result_year)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND", "/allreport/score/student-group");
		}
		$this->view->studentgroup = $result_year;
	}


	function rptSemesterEvaluationAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			$this->view->g_name = $search;
		} else {
			$search = array(
				'group_name' => '',
				'study_year' => '',
				'grade_english' => '',
				'degree_english' => '',
				'session' => '',
				'time' => '',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d')
			);
		}
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->studentgroup = $db->getStundentEnglishSemesterScore($search);
		$this->view->g_all_name = $db->getAllgroupStudyNotPass();

		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;
	}

	function rptAssessmenttermAction()
	{
		$id = $this->getRequest()->getParam("id");
		if (empty($id)) {
			$this->_redirect("/allreport/allstudent/student-group");
		}
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'txtsearch' => "",
				'study_type' => 0
			);
		}
		$this->view->search = $search;


		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$row = $db->getStudentGroupScoreEn($id, $search);
		$this->view->rs = $row;

		$db = new Allreport_Model_DbTable_DbRptGroup();
		$rs = $db->getGroupDetailByID($id);
		$this->view->rr = $rs;

		$scorepolicy = $db->checkScorePolicyMoreThanOne($id);
		if (count($scorepolicy) > 1) {
			Application_Form_FrmMessage::Sucessfull("This Group has use score policy type more than one. Please Check Score policy for this group.", "/allreport/allstudent/student-group");
			exit();
		}
		$groupScore = $db->getScoreSettingIdByGroup($id);
		if (empty($groupScore)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/allstudent/student-group");
			exit();
		}
		$db = new Issue_Model_DbTable_DbScoreEng();
		$scoreSetting = $db->getScoreSettingDetail($groupScore['score_setting']);
		$this->view->scoreSetting = $scoreSetting;
	}

	public function rptStudentPassedAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'academic_year' => '',
				'change_id' => '',
			);
		}
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->student_pass = $db->getAllStudentPassed($search);

		$_db = new Allreport_Model_DbTable_DbRptGroupStudentChangeGroup();
		$this->view->change_type = $_db->getChangeType();
		$this->view->all_change_group = $_db->getAllChangeGroup(2); // 2=ឡើងថ្នាក់

		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	public function rptReschedulebygroupAction()
	{
		$group = new Allreport_Model_DbTable_DbRptStudentDrop();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			$rs_rows = $group->getAllReschedulebygroup($search);

		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'academic_year' => '',
				'group' => '',
				'degree' => '',
				'grade' => '',
				'room' => '',
			);
			$rs_rows =array();
		}
		
		$this->view->rs = $rs_rows;
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);


		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->day = $_db->getAllDay();

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$this->view->principalInfo = $dbSetting->getDutyById(1);
	}
	public function rptRescheduleGroupAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'academic_year' => '',
				'subject' => '',
				'group' => '',
				'session' => '',
				'session' => '',
				'subject' => '',
				'teacher' => '',
				'day' => '',
				'start_date' => date("Y-m-d"),
				'end_date' => date("Y-m-d")
			);
		}
		$group = new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $rs_rows = $group->getAllRescheduleGroup($search);
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	
	
	function rptStudentLetterofpraiseAction()
	{
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'degree' => '',
				'academic_year' => '',
				'grade' => '',
				'group' => '',
				'student' => '',
				'language_type' => '',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
		}

		$this->view->row = $db->getStudenLetterofpraise($search);
		$this->view->search = $search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}

	function rptHonorStudentAction()
	{
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'title' => '',
				'branch_id' => '',
				'degree' => '',
				'study_year' => '',
				'grade_all' => '',
				'group' => '',
				'student' => '',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
		}

		$this->view->row = $db->getHonorStudent($search);
		$form = new Registrar_Form_FrmSearchInfor();
		$forms = $form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$this->view->search = $search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	function honorCertificateAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$result = $db->getHonorCertificateById($id);
		if (empty($result)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score/rpt-student-letterofpraise");
			exit();
		}
		$this->view->rs = $result;
	}

	function rptStudentCetificateAction()
	{
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'branch_id' => '',
				'degree' => '',
				'academic_year' => '',
				'grade' => '',
				'group' => '',
				'student' => '',
				'language_type' => '',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
		}

		$this->view->row = $db->getStudenCetificate($search);

		$this->view->search = $search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	function certificateLetterofpraiseAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$result = $db->getStudenLetterofpraiseById($id);
		if (empty($result)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score/rpt-student-letterofpraise");
			exit();
		}
		$this->view->rs = $result;
	}

	function certificateAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$result = $db->getStudenCetificateById($id);
		if (empty($result)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score/rpt-student-cetificate");
			exit();
		}
		$this->view->rs = $result;
	}
	


	function rptStudentEvaluationletterAction()
	{ //

		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
		} else {
			$stu_id = $this->getRequest()->getParam("stu_id");
			$group_id = $this->getRequest()->getParam("group_id");
			$exam_type = $this->getRequest()->getParam("exam_type");
			$for_semester = $this->getRequest()->getParam("for_semester");
			$for_month = $this->getRequest()->getParam("for_month");

			if (empty($stu_id)) {
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score");
				exit();
			} elseif (empty($group_id)) {
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score");
				exit();
			} elseif (empty($exam_type)) {
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score");
				exit();
			} elseif (empty($for_semester)) {
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score");
				exit();
			} elseif (empty($for_month)) {
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/allreport/score");
				exit();
			}
			$data = array(
				'stu_id' => $stu_id,
				'group_id' => $group_id,
				'exam_type' => $exam_type,
				'for_semester' => $for_semester,
				'for_month' => $for_month,
			);
		}
		$this->view->search = $data;
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$rs = $db->getStudentEvaluationBYId($data);
		$this->view->rs = $rs;

		$evaluation = $db->getStudentEvaluation($data);
		$this->view->evaluation = $evaluation;


		$group = $db->getAllGroupOfStudent($data['stu_id']);
		$this->view->group = $group;
		$db = new Issue_Model_DbTable_DbScore();
		$arr = array(
			'group_id' => $data['group_id'],
			'exam_type' => $data['exam_type'],
		);
		$subject = $db->getSubjectScoreByGroup($arr);
		$this->view->subject = $subject;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rating = $db->getRatingValuation();
		$this->view->month = $db->getAllMonth();
	}

	public function rptStudentGroupAction() //to right click show score result or hornor script
	{
		$id = $this->getRequest()->getParam("id");
		if (empty($id)) {
			$this->_redirect("/allreport/score/student-evaluationgroup");
		}
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'txtsearch' => "",
				'study_type' => 0
			);
		}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$row = $db->getStudentGroupReport($id, $search, 1);
		print_r($row);
		$this->view->rs = $row;
		$rs = $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
	}
	public function rptScoreStatisticAction()
	{
		if ($this->getRequest()->isPost()) {

			$search = $this->getRequest()->getPost();

			$db = new Allreport_Model_DbTable_DbRptStudentScore();
			$rs = $db->getScoreStatistic($search);
		} else {
			$rs = array();
			$search = array(
				'branch_id' => "",
				'academic_year' => "",
				'group' => "",
				'grade' => "",
				'degree' => "",
				'exam_type' => 0,
				'for_semester' => 0,
				'for_month' => 0,
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
				'sort_degree' => ''
			);
		}
		$this->view->rs = $rs;
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? 1 : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}

	public function rptSubjectStatisticGraphAction()
	{
		if ($this->getRequest()->isPost()) {

			$search = $this->getRequest()->getPost();

			$db = new Allreport_Model_DbTable_DbRptStudentScore();
			$rs = $db->getScoreSubjectByTeacher($search);
		} else {
			$rs = array();
			$search = array(
				'branch_id' => "",
				'academic_year' => "",
				'group' => "",
				'grade' => "",
				'degree' => "",
				'exam_type' => 0,
				'for_semester' => 0,
				'for_month' => 0,
				'subjectId' => '',
				'teacher' => '',
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
				'sort_degree' => ''
			);
		}
		$this->view->rs = $rs;
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? 1 : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}

	public function rptSubjectStatisticAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			// $db = new Allreport_Model_DbTable_DbRptStudentScore();
			// $rs = $db->getStatisticScoreResult($search);
			// $rsDetail = $db->getSubjectScoreDetail($search);
		} else {
			$rs = array();
			$rsDetail = array();
			$search = array(
				'branch_id' => "",
				'academic_year' => "",
				'group' => "",
				'grade' => "",
				'subjectId' => "",
				'degree' => "",
				'exam_type' => 0,
				'for_semester' => 0,
				'for_month' => 0,
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
				'mention' => '',
				'student_list' => '',
				'subject_list' => ''
			);
		}
		if(!empty($search['student_list'])){
			$db = new Allreport_Model_DbTable_DbRptStudentScore();
			$rs = $db->getStatisticScoreResult($search);
		}else{
			$rs = array();
		}
		if(!empty($search['subject_list'])){
			$db = new Allreport_Model_DbTable_DbRptStudentScore();
			$rsDetail = $db->getSubjectScoreDetail($search);
		}else{
			$rsDetail = array();
		}

		$this->view->rs = $rs;
		$this->view->rsdetail = $rsDetail;
		$this->view->search = $search;

		$branch_id = empty($search['branch_id']) ? 1 : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	function rptScoreResultSemesterAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			$isgetId = null;
		} else {
			$isgetId = $id;
			$row = $db->getScoreExamByID($id);
			$search = array(
				'group' => $row['group_id'],
				'study_year' => $row['for_academic_year'],
				'exam_type' => $row['exam_type'],
				'branch_id' => $row['branch_id'],
				'for_month' => $row['for_month'],
				'for_semester' => $row['for_semester'],
				'grade' => '',
				'degree' => '',
				'session' => '',
			);
		}
		$result = $db->getStudentScoreResult($search, $isgetId, 1);
		$this->view->studentScoreResult = $result;

		$this->view->scoreId = $id;

		$this->view->search = $search;

		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($result[0]['branch_id']) ? 1 : $result[0]['branch_id'];
		$this->view->headerScore = $frm->getHeaderReportScore($branch_id);

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branchInfo = $db->getBranchInfo($branch_id);
		$this->view->month = $db->getAllMonth();

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId = empty($result[0]['degree_id']) ? 0 : $result[0]['degree_id'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId, 1);
		$this->view->academicStaffInfo = $dbSetting->getDutyByDegree($dregreeId, 2);
	}

	function rptScoreResultAnnaulAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
			$isgetId = null;
		} else {
			$isgetId = $id;
			$row = $db->getScoreExamByID($id);
			$search = array(
				'group' => $row['group_id'],
				'study_year' => $row['for_academic_year'],
				'exam_type' => $row['exam_type'],
				'branch_id' => $row['branch_id'],
				'for_month' => $row['for_month'],
				'for_semester' => $row['for_semester'],
				'grade' => '',
				'degree' => '',
				'session' => '',
			);
		}
		$result = $db->getStudentScoreResult($search, $isgetId, 1);
		$this->view->studentScoreResult = $result;

		$this->view->scoreId = $id;

		$this->view->search = $search;

		$form = new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($result[0]['branch_id']) ? 1 : $result[0]['branch_id'];
		$this->view->headerScore = $frm->getHeaderReportScore($branch_id);

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branchInfo = $db->getBranchInfo($branch_id);
		$this->view->month = $db->getAllMonth();

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId = empty($result[0]['degree_id']) ? 0 : $result[0]['degree_id'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId, 1);
		$this->view->academicStaffInfo = $dbSetting->getDutyByDegree($dregreeId, 2);
	}
	function rptAnnaultranscriptAction()
	{

		$scoreId = $this->getRequest()->getParam("id");
		$stu_id = $this->getRequest()->getParam("stu_id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$search = array(
			'scoreId' => empty($scoreId) ? 0 : $scoreId
		);
		if (!empty($stu_id)) {
			$search['stu_id'] = $stu_id;
		}

		$result = $db->getAllStudentIdByScoreResult($search, $scoreId, 1);
		$this->view->studentScoreResult = $result;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->month = $db->getAllMonth();
	}
	function rptAcademictranscriptAction()
	{

		$scoreId = $this->getRequest()->getParam("id");
		$stu_id = $this->getRequest()->getParam("stu_id");
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$search = array(
			'scoreId' => empty($scoreId) ? 0 : $scoreId
		);
		if (!empty($stu_id)) {
			$search['stu_id'] = $stu_id;
		}

		$result = $db->getAllStudentIdByScoreResult($search, $scoreId, 1);
		$this->view->studentScoreResult = $result;

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->month = $db->getAllMonth();
	}
}
