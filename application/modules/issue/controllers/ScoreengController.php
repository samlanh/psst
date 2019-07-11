<?php
class Issue_ScoreengController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbScoreEng();
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
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","TITLE","DATE","STUDY_YEAR","GROUP","DEGREE","GRADE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'scoreeng','action'=>'edit',
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
			$db = new Issue_Model_DbTable_DbScoreEng();
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/scoreeng/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/scoreeng");
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
		$_model = new Issue_Model_DbTable_DbScoreEng();
		
		$this->view->score_id = $id;
		$row = $_model->getScoreById($id);
		if (empty($row)){
			Application_Form_FrmMessage::MessageBacktoOldHistory("NO_RECORD");
			exit();
		}
		if ($row['is_pass']==1){
			Application_Form_FrmMessage::MessageBacktoOldHistory("CLASS_COMPLETED_CAN_NOT_EDIT");
			exit();
		}
		$this->view->score = $row;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $_model->editStudentScore($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/scoreeng");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
		$scoresetting = $_model->getScoreSettingDetail($row['score_setting']);
		$this->view->scoresettingdetail = $scoresetting;
		
		$db = new Issue_Model_DbTable_DbScore();
		$this->view->student = $db->getStudentByGroup($row['group_id']);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
	}
	function getScoresettingAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreEng();
			$data=$db->getScoreSettingByBranch($data['branch_id']);
			array_unshift($data, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_SCORE_SETTING")) );
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getScoresettingdetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreEng();
			$data=$db->getScoreSettingDetail($data['scoreSetting']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getgroupinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreEng();
			$string = empty($data['string'])?null:$data['string'];
			$data=$db->getGroupInforByID($data['group_id'],$string);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}