<?php 
class Issue_MonitoringassController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
public function indexAction()
	{
		$db = new Issue_Model_DbTable_DbMonitorAssessment();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{
			 $search = array(
			 			'academic_year'=> "0",
			 			'exam_type'=>0,
			 			'for_semester'=>0,
			 			'for_month'=>0,
			 			'degree'=>0,
			 			'grade'=> 0,
			 			'evaluationStatus'=> 0,
			 			'isLockType'=> 2,
					);
		}
		$this->view->search = $search;
		$rows= $db->getAllGroups($search);
		$this->view->row = $rows;
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
    public function addAction()
	{
		if(empty($row)){
			$this->_redirect("/issue/monitoringass");
		}
		
	}
	
	function approveAssessmentAction(){
		$db = new Issue_Model_DbTable_DbMonitorAssessment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
    		try {
    			$sms="APPROVING_SUCCESS";
    			$db->approvedAssessmentByCheckBox($_data);
    			Application_Form_FrmMessage::Sucessfull($sms, "/issue/monitoringass");
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("UPDATE_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
			
		}
	}
	function viewAssessmentAction(){
		
		$assessmentID=$this->getRequest()->getParam("id");
    	$assessmentID =empty($assessmentID)?0:$assessmentID;
		
		$db = new Issue_Model_DbTable_DbMonitorAssessment();
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
    		try {
    			$sms="APPROVING_SUCCESS";
    			$db->approvedAssessmentAndEditTeacherComment($_data);
    			Application_Form_FrmMessage::Sucessfull($sms, "/issue/monitoringass");
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("UPDATE_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
			
		}
		
		$row = $db->getAssessmentInfo($assessmentID);
		$this->view->rs = $row;
		if(empty($row)){
			$sms="NO_RECORD";
			Application_Form_FrmMessage::Sucessfull($sms, "/issue/monitoringass");
			exit();
		}
		
		$students = $db->getAssessmentDetailList($assessmentID);
		$this->view->students = $students;
		
		$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
	}
}	
