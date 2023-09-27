<?php

class IssuescoreController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/external';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
	public function indexAction()
	{
		$this->_helper->layout()->disableLayout();
		$id=0;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$teacherInfo = $dbExternal->getCurrentTeacherInfo();
		$currentAcademic = empty($teacherInfo['currentAcademic'])?0:$teacherInfo['currentAcademic'];
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['externalAuth']=1;
		}
		else{
			$search = array(
						'adv_search'=>'',
						'externalAuth'=>1,//for teacher access
						'academic_year'=> $currentAcademic,
						'exam_type'=>-1,
						'for_semester'=>-1,
						'for_month'=>'',
						'degree'=>0,
						'grade'=> 0,
						'start_date'=> '',
						'end_date'=>date('Y-m-d'));
		}
		$this->view->search = $search;
		$db = new Application_Model_DbTable_DbIssueScore();
		$row = $db->getAllSubjectScoreByClass($search);
		$this->view->row = $row;

		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
    public function addAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		$db = new Application_Model_DbTable_DbIssueScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$rs = $db->addSubjectScoreByClass($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issuescore/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issuescore/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id);
		
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/dashboard");
			exit();
		}
		
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
	
		$this->view-> month = $dbExternal->getAllMonth();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$degreeId = $row['degree_id'];
		$result = $dbg->checkEntryScoreSetting($degreeId);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_PERMISSION_TO_ENTRY","/issuescore/index");
		}
// 		$param = array(
// 				'groupId'=>540
// 			,'sortStundent'=>0
// 			,'maxSubjectScore'=>10
// 			,'examType'=>1
// 			,'subjectId'=>5
// 			,'gradingId'=>7
// 			,'keyIndex'=>1
			
// 				);
// 		$rs = $db->getStudentForIssueScore($param);
// 		print_r($rs);
		
	}

	public function editAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);

		$db = new Application_Model_DbTable_DbIssueScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $db->updateSubjectScoreByClass($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issuescore/index");
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$row = $db->getSubjectScoreByID($id);
		$this->view->rs = $row;	
		
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issuescore/index");
		}
		if ($row['isLock']==1){
			Application_Form_FrmMessage::Sucessfull("RECORD_LOCKED_CAN_NOT_EDIT","/issuescore/index");
		}
		if ($row['is_pass']==1){
			Application_Form_FrmMessage::Sucessfull("CLASS_COMPLETED_CAN_NOT_EDIT","/issuescore/index");
		}
		if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("SCORE_DEACTIVE_CAN_NOT_EDIT","/issuescore/index");
		}
		$this->view->student= $db->getStudentSubjectSccoreforEdit($id);
		
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$this->view-> month = $dbExternal->getAllMonth();
	}
	
	function getStudentAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForIssueScore($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function getStudentsingleengryAction(){//single entry by criteria
	
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForGradingScore($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	
	
	
	function getStudenteditAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForIssueScoreEdit($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function calculateAverageAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();
			$rs=$db->calculateAverage($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function checkingDuplicateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();
			$rs=$db->checkingDuplicate($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}