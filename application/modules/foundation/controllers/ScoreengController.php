<?php
class Foundation_ScoreengController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbScoreEng();
// 			$this->view->g_all_name=$db->getGroupSearch();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title'=>'',
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'degree'=>0,
						'grade'=> 0,
						'session'=> 0,
						'time'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllScore($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","TITLE","DATE","STUDY_YEAR","GROUP","DEGREE","GRADE","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'scoreeng','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'title'=>$link,'for_date'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public	function addAction(){
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScoreEng();
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/scoreeng/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/scoreeng");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Foundation_Model_DbTable_DbScoreEng();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $_model->editStudentScore($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/scoreeng");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$this->view->score_id = $id;
		$row = $_model->getScoreById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/scoreeng");
			exit();
		}
		
		$this->view->score = $row;
		$scoresetting = $_model->getScoreSettingDetail($row['score_setting']);
		$this->view->scoresettingdetail = $scoresetting;
		
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->student = $db->getStudentByGroup($row['group_id']);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
	}
	function getScoresettingAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScoreEng();
			$data=$db->getScoreSettingByBranch($data['branch_id']);
			array_unshift($data, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_SCORE_SETTING")) );
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getScoresettingdetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScoreEng();
			$data=$db->getScoreSettingDetail($data['scoreSetting']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getgroupinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScoreEng();
			$string = empty($data['string'])?null:$data['string'];
			$data=$db->getGroupInforByID($data['group_id'],$string);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}