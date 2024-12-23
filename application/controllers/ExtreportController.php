<?php

class ExtreportController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
	public function indexAction()
	{
		$this->_helper->layout()->disableLayout();
	}
    public function rptStudentListAction()
	{
		$this->_helper->layout()->disableLayout();
		$id=$this->getRequest()->getParam("id");
		if(empty($id)){
			$this->_redirect("/external/group");
		}
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
				'txtsearch' => "",
				'study_type'=>0
				);
		}
		
		$db = new Application_Model_DbTable_DbExternal();
		
		$rs = $db->getGroupDetailByIDExternal($id,1);
		if(empty($rs)){
			$this->_redirect("/external/group");
		}
		$this->view->rr = $rs;
		
		$search['group_id']=$id;
		$this->view->search = $search;
		
		$row = $db->getStudentListByGroup($search);
		$this->view->row = $row;
		
		$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
		
	}
	
	function rptScoreListAction(){ 
		$this->_helper->layout()->disableLayout();
    	$gradingID=$this->getRequest()->getParam("id");
    	$gradingID =empty($gradingID)?0:$gradingID;
    	
    	$fullControlID=$this->getRequest()->getParam("fullcontrol");
    	$fullControlID =empty($fullControlID)?0:$fullControlID;

		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getClassSubjectScoreById($gradingID,$fullControlID);
		$this->view->rs = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issuescore/index");
			exit();
		}
		if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("SCORE_DEACTIVE_CAN_NOT_VIEW","/issuescore/index");
			exit();
		}
		$groupId = empty($row['groupId'])?0:$row['groupId'];
		$subjectId = empty($row['subjectId'])?0:$row['subjectId'];
		$examType = empty($row['examType'])?0:$row['examType'];
		$forMonth = empty($row['forMonth'])?0:$row['forMonth'];
		$forSemester = empty($row['forSemester'])?0:$row['forSemester'];
		$arrFilter = array(
			'groupId'=>$groupId,
			'forScoreSubject'=>1,
			'subjectId'=>$subjectId,
			'examType'=>$examType,
			'forMonth'=>$forMonth,
			'forSemester'=>$forSemester,
			'gradingId'=>$gradingID
		);
		$this->view->students = $dbExternal->getStudentGradingScore($arrFilter);
		
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId= empty($row['degreeId'])?0:$row['degreeId'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId,1);
		$this->view->academicStaffInfo = $dbSetting->getDutyByDegree($dregreeId,2);
    }
    function rptGradingListAction(){
    	$this->_helper->layout()->disableLayout();
    	$gradingID=$this->getRequest()->getParam("id");
    	$gradingID =empty($gradingID)?0:$gradingID;
    
		$fullControlID=$this->getRequest()->getParam("fullcontrol");
    	$fullControlID =empty($fullControlID)?0:$fullControlID;

    	$dbG = new Application_Model_DbTable_DbGradingScore();
    	
    	$row = $dbG->getGradingScoreById($gradingID,$fullControlID);
    	$this->view->rs = $row;
    	if (empty($row)){
    		//Application_Form_FrmMessage::Sucessfull("NO_RECORD","/grading/index");
    		//exit();
    	}
    	if ($row['status']==0){
    		//Application_Form_FrmMessage::Sucessfull("SCORE_DEACTIVE_CAN_NOT_VIEW","/grading/index");
    		//exit();
    	}
    	$groupId = empty($row['groupId'])?0:$row['groupId'];
    	$subjectId = empty($row['subjectId'])?0:$row['subjectId'];
    	$examType = empty($row['examType'])?0:$row['examType'];
    	$forMonth = empty($row['forMonth'])?0:$row['forMonth'];
    	$forSemester = empty($row['forSemester'])?0:$row['forSemester'];
    	$criteriaId = empty($row['criteriaId'])?0:$row['criteriaId'];
    
    	$arrFilter = array(
    			'groupId'=>$groupId,
    			'subjectId'=>$subjectId,
    			'examType'=>$examType,
    			'forMonth'=>$forMonth,
    			
    	);
    	
    	$dbExternal = new Application_Model_DbTable_DbExternal();
    	$this->view->students = $dbExternal->getStudentByGroupExternal($arrFilter);
    
    	$gradingId = empty($row['gradingId'])?0:$row['gradingId'];
    	$subjectId = empty($row['subjectId'])?0:$row['subjectId'];
    
    	$arrSearch  = array(
    			'gradingId'=>$gradingId
    			,'subjectId'=>$subjectId
    			,'examType'=>$examType,
    			'criteriaId'=>$criteriaId,
    	);
    	$this->view->criterial = $dbExternal->getGradingCriteriaItems($arrSearch);
    
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	 
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
    }
	
	function rptAssessmentListAction(){
		$this->_helper->layout()->disableLayout();
    	$assessmentID=$this->getRequest()->getParam("id");
    	$assessmentID =empty($assessmentID)?0:$assessmentID;
		
		$fullControlID=$this->getRequest()->getParam("fullcontrol");
    	$fullControlID =empty($fullControlID)?0:$fullControlID;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getClassAssessmentById($assessmentID,$fullControlID);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/assessment/index");
		}
		$this->view->rs = $row;
		
		$groupId = empty($row['groupId'])?0:$row['groupId'];
		$degreeId = empty($row['degreeId'])?0:$row['degreeId'];
		
		$arrFilter = array(
			'groupId'=>$groupId,
		);
		$this->view->students = $dbExternal->getStudentByGroupExternal($arrFilter);
		
		$comments = $dbExternal->getCommentByDegree($degreeId);
		$this->view->comments =$comments;
		
		$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
	}
	
	function rptTeachingScheduleAction(){
		$this->_helper->layout()->disableLayout();
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getDays();
		$this->view->days = $row;
		$row = $dbExternal->getTimeTeachingByTeacher();
		$this->view->timeTeaching = $row;
		
		$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
	}
	function rptGradingtmpListAction(){ 
		$this->_helper->layout()->disableLayout();
    	$groupId=$this->getRequest()->getParam("groupId");
    	$groupId =empty($groupId)?0:$groupId;
    	
    	// $fullControlID=$this->getRequest()->getParam("fullcontrol");
    	// $fullControlID =empty($fullControlID)?0:$fullControlID;

		$dbExternal = new Application_Model_DbTable_DbExternal();
		$data = array(
			'groupId'=>$groupId,
		);
		$row = $dbExternal->getSubjectScoreByGroup($data);
		$this->view->rs = $row;
	
		$arrFilter = array(
			'groupId'=>$groupId,
			// 'forScoreSubject'=>1,
			// 'subjectId'=>$subjectId,
			// 'examType'=>$examType,
			// 'forMonth'=>$forMonth,
			// 'forSemester'=>$forSemester,
		);
		$this->view->students = $dbExternal->getStudentByGroupExternal($arrFilter);
		
    	$gradingId = empty($row['gradingId'])?0:$row['gradingId'];
    	$subjectId = empty($row['subjectId'])?0:$row['subjectId'];
		
		// $arrSearch  = array(
		// 	'gradingId'=>$gradingId
		// 	,'subjectId'=>$subjectId
		// 	,'examType'=>$examType,
		// );
		// $this->view->criterial = $dbExternal->getGradingCriteriaItems($arrSearch);
		
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($row['branchId'])?1:$row['branchId'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);

		$dbSetting = new Setting_Model_DbTable_Dbduty();
		$dregreeId= empty($row['degreeId'])?0:$row['degreeId'];
		$this->view->principalInfo = $dbSetting->getDutyByDegree($dregreeId,1);
		$this->view->academicStaffInfo = $dbSetting->getDutyByDegree($dregreeId,2);

		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
    }
	function rptAttendanceDetailAction(){
		
		$this->_helper->layout()->disableLayout();
		$data=$this->getRequest()->getParams();
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);
		
		$data['groupId'] = empty($data['groupId']) ? 0 : $data['groupId'];
		$arrFilter = array(
			'groupId'=>$data['groupId'],
		);
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$this->view->row = $dbExternal->getStudentByGroupExternal($arrFilter);
		$data['attendanceId'] = empty($data['id']) ? 0 : $data['id'];

		$rs = $dbExternal->getGroupDetailByIDExternal($data['groupId'],1);
		$this->view->rr = $rs;

		$dbReport = new Application_Model_DbTable_DbReport();
		$this->view->scheduleTime = $dbReport->getAttendanceDetailWithClassTeahchingSchedule($data);
		
		
	}

	function rptAttendanceSummaryAction(){
		
		$this->_helper->layout()->disableLayout();
		$data=$this->getRequest()->getParams();
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);

		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$date = new DateTime();
			$endDate = $date->format("Y-m-d");

			$date->modify("-1 month");
			$starDate = $date->format("Y-m-d");
			$search = array(
						'startDate'=>$starDate,
						'endDate'=>$endDate
					);
		}
		$search["groupId"] = $data['groupId'];
		$this->view->search = $search;
		
		$dbExternal = new Application_Model_DbTable_DbReport();
		$this->view->row = $dbExternal->getStudentListByTeachingSubject($search);
	}
}