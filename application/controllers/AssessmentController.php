<?php

class AssessmentController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
		}
		else{
			$search = array(
						'adv_search'=>'',
						
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
		
		$db = new Application_Model_DbTable_DbAssessment();
		$row = $db->getAllScoreResult($search);
		$this->view->row = $row;

		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
    public function addAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$db = new Application_Model_DbTable_DbAssessment();
		$dbexnternal = new Application_Model_DbTable_DbExternal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			//check Session
			$checkTeachSesion=  $dbexnternal->checkSessionTeacherExpireBeforeSubmit();
			if(empty($checkTeachSesion)){
				$dbexnternal->reloadPageTecherExpireSession();
				exit();
			}
			try{
				$rs = $db->addStudentAssessment($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/assessment");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id);
		$this->view->row = $row;
		
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/external/dashboard");
		}
		$this->view-> month = $dbExternal->getAllMonth();
		
		$scoreId =$this->getRequest()->getParam("scoreId");
		$scoreId = empty($scoreId)?0:$scoreId;
		$this->view->scoreId = $scoreId; 
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$row = $db->getScoreExamByID($scoreId);
		if(!empty($row)){
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
			$result = $db->getStudentScoreResult($search, $scoreId, 1);
			$this->view->studentScoreResult = $result;
		
			$this->view->scoreId = $scoreId;
			
			$frm = new Application_Form_FrmGlobal();
			$branch_id = empty($result[0]['branch_id']) ? 1 : $result[0]['branch_id'];
			$this->view->header = $frm->getHeaderReceipt($branch_id);
			$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branchInfo = $db->getBranchInfo($branch_id);
			
		}else{
			$this->view->studentScoreResult = array();
			
			$this->view->scoreId = 0;
				
			$frm = new Application_Form_FrmGlobal();
			$branch_id = 0;
			$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
				
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branchInfo = $db->getBranchInfo(0);
		}
	}

	public function editAction()
	{
		$this->_helper->layout()->disableLayout();

		$db = new Application_Model_DbTable_DbAssessment();
		$dbexnternal = new Application_Model_DbTable_DbExternal();
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();

			$checkTeachSesion=  $dbexnternal->checkSessionTeacherExpireBeforeSubmit();
			if(empty($checkTeachSesion)){
				$dbexnternal->reloadPageTecherExpireSession();
				exit();
			}

			try {
				$rs =  $db->updateAssessmentByClass($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/assessment/index");
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$groupId=$this->getRequest()->getParam("groupId");
		$groupId = empty($groupId)?0:$groupId;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($groupId);
		$this->view->row = $row;
		
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/external/dashboard");
		}
		$this->view-> month = $dbExternal->getAllMonth();
		
		$scoreId =$this->getRequest()->getParam("scoreId");
		$scoreId = empty($scoreId)?0:$scoreId;
		$this->view->scoreId = $scoreId; 
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$row = $db->getScoreExamByID($scoreId);
		if(!empty($row)){
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
			$result = $db->getStudentScoreResult($search, $scoreId, 1);
			$this->view->studentScoreResult = $result;
		
			$this->view->scoreId = $scoreId;
			
			$frm = new Application_Form_FrmGlobal();
			$branch_id = empty($result[0]['branch_id']) ? 1 : $result[0]['branch_id'];
			$this->view->header = $frm->getHeaderReceipt($branch_id);
			$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$this->view->branchInfo = $db->getBranchInfo($branch_id);
			
		}

		$db = new Application_Model_DbTable_DbAssessment();	
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$row = $db->getAssessmentByID($id);
		$this->view->rowAss = $row;	
		
		// if(empty($row)){
		// 	Application_Form_FrmMessage::Sucessfull("NO_RECORD","/assessment");
		// }
		// if (empty($row)){
		// 	Application_Form_FrmMessage::Sucessfull("NO_RECORD","/assessment/index");
		// }
		// if ($row['isLock']==1){
		// 	Application_Form_FrmMessage::Sucessfull("RECORD_LOCKED_CAN_NOT_EDIT","/assessment/index");
		// }
		// if ($row['is_pass']==1){
		// 	Application_Form_FrmMessage::Sucessfull("CLASS_COMPLETED_CAN_NOT_EDIT","/assessment/index");
		// }
		// if ($row['status']==0){
		// 	Application_Form_FrmMessage::Sucessfull("SCORE_DEACTIVE_CAN_NOT_EDIT","/assessment/index");
		// }
		
	
		// $dbExternal = new Application_Model_DbTable_DbExternal();
		// $this->view-> month = $dbExternal->getAllMonth();
		
	}
	
	
	function getStudentassessmentAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbAssessment();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			
			
// 			$rs=$db->getStudentForAssessment($data);//format 1
			$rs =$db->getSecondFormatStudentForAssessment($data); // format 2
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getStudentassessmenteditAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbAssessment();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			
			$rs=$db->getSecondFormatStudentForAssessmentEdit($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function checkingDuplicateAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbAssessment();
			$rs=$db->checkingDuplicate($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	
	
}





