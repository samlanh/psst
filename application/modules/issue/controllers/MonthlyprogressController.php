<?php
class Issue_MonthlyprogressController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbMonthlyProgress();
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
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllScore($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_NAME","TYPE","FOR_SEMESTER","FOR_MONTH","STUDY_YEAR","GROUP","DEGREE","GRADE","DATE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'monthlyprogress','action'=>'edit',
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
			$db = new Issue_Model_DbTable_DbMonthlyProgress();
			try {
				$rs =  $db->addMonthlyProgress($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/monthlyprogress/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/monthlyprogress");
				}
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Issue_Form_FrmMonthlyProgress();
		$frm->FrmAddMonthlyProgress(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
		
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Issue_Model_DbTable_DbMonthlyProgress();
		$this->view->id = $id;
		$row = $_model->getMonthlyProgressById($id);
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
				$rs =  $_model->editMonthlyProgress($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/monthlyprogress");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$scoresetting = $_model->getScoreSettingDetail($row['subjectarea_setting']);
		$this->view->subjectareasettingdetail = $scoresetting;
		
		$db = new Issue_Model_DbTable_DbScore();
		$this->view->subject = $db->getSubjectByGroupScore($row['group_id'],null,1);
		
		$frm = new Issue_Form_FrmMonthlyProgress();
		$frm->FrmAddMonthlyProgress($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	}
	function getsubjectareasettingAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbMonthlyProgress();
			$data=$db->getSubjectAreaSettingByBranch($data['branch_id']);
			array_unshift($data, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_SCORE_SETTING")) );
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function subjectareadetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbMonthlyProgress();
			$data=$db->getScoreSettingDetail($data['subject_area']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function getgroupinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbMonthlyProgress();
			$string = empty($data['string'])?null:$data['string'];
			$data=$db->getGroupInforByID($data['group_id'],$string);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	function checkduplicateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
	
			$student_id = empty($data['student_id'])?"":$data['student_id'];
			$group= empty($data['group'])?"":$data['group'];
			
			$exam_type= empty($data['exam_type'])?"":$data['exam_type'];
			$for_semester= empty($data['for_semester'])?"":$data['for_semester'];
			$for_month= empty($data['for_month'])?"":$data['for_month'];
			
			$id = empty($data['id'])?"":$data['id'];
			$arr  = array(
					'student_id'=>$student_id,
					'group'=>$group,
					
					'exam_type'=>$exam_type,
					'for_semester'=>$for_semester,
					'for_month'=>$for_month,
					
					'id'=>$id,
			);
			$_dbmodel = new Issue_Model_DbTable_DbMonthlyProgress();
			$result=$_dbmodel->checkuDuplicate($arr);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}