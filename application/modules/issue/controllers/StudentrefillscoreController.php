<?php
class Issue_StudentrefillscoreController extends Zend_Controller_Action {
	
	const SETTING_INPUT_ATTENDANCE = 2; // 1=fullListStudentGroup
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try {
			$db = new Issue_Model_DbTable_DbScoreRefill();
			if ($this->getRequest()->isPost()) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'adv_search' => '',
					'branch_id' => '',
					'group' => '',
					'academic_year' => '',
					'exam_type' => -1,
					'for_semester' => -1,
					'for_month' => '',
					'start_date' => date('Y-m-d'),
					'end_date' => date('Y-m-d')
				);
			}
			$this->view->search = $search;
			$rs_rows = $db->getStudentRefillScore($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_CODE","STUDENT_NAME", "STUDENT_GROUP","EXAM_TITLE", "EXAM_TYPE", "FOR_SEMESTER", "FOR_MONTH", "STATUS");
			
			
			$this->view->list = $list->getCheckList(10, $collumns, $rs_rows, array(
			));
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	public	function addAction()
	{
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset = $key->getKeyCodeMiniInv(TRUE);
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill(); //by subject
			try {
				 $db->addStudentScore($_data);
				if (isset($_data['save_new'])) {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/studentrefillscore/add");
				} else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/issue/studentrefillscore");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->branch_id=$db_global->getAllBranch();
	}
	
	function getscoregroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getScoreResultByGroup($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}

	function getscoreinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getScoreInforByID($data['scoreId']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentscoreAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data = $db->getStudentScoreById($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentrefillAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreRefill();
			$data=$db->getStudentNoScore($data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
}

