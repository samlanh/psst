<?php
class Allreport_StudentattendanceController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$date = new DateTime();
			$currentDate =  $date->format("Y-m-d");
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
				$search["currentDate"] = empty($search["start_date"]) ? $currentDate : $search["start_date"];
			}
			else{
				
				$search = array(
						'currentDate' =>$currentDate,
						'academic_year' =>'',
						'degree'=>'',
						'grade'=>'',
						'branch_id'=>0,
				);
				$dbGb = new Application_Model_DbTable_DbGlobal();
				$last = $dbGb->getLatestAcadmicYear();
				if(!empty($last)){
					$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
				}
			}
			$db = new Allreport_Model_DbTable_DbAttendanceReport();
			$allClass = $db->getCountingAllClass($search);
			$allClassIssuedAtt = $db->getCountingAllClassHasIssuedAtt($search);
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$allClass = empty($allClass) ? 0  : $allClass;
			$allClassIssuedAtt = empty($allClassIssuedAtt) ? 0  : $allClassIssuedAtt;
			$notYetClassIssuedAtt = $allClass - $allClassIssuedAtt;
			
			$this->view->allClass = $allClass;
			$this->view->allClassIssuedAtt = $allClassIssuedAtt;
			$this->view->classInfo = array(
				array("label"=>$tr->translate("ALL_CLASS_ISSUED_ATT"),"value"=>$allClassIssuedAtt,"color"=>"#279742"),
				array("label"=>$tr->translate("CLASS_NOT_YET_ISSUED_ATT"),"value"=>$notYetClassIssuedAtt,"color"=>"#c3cfd2"),
				
			);
			
			$countingAttedanceSummary = $db->getCountingAttedanceSummary($search);
			$totalAbsent = empty($countingAttedanceSummary["totalAbsent"]) ? 0  : $countingAttedanceSummary["totalAbsent"];
			$totalPermission = empty($countingAttedanceSummary["totalPermission"]) ? 0  : $countingAttedanceSummary["totalPermission"];
			$totalLate = empty($countingAttedanceSummary["totalLate"]) ? 0  : $countingAttedanceSummary["totalLate"];
			$totalEarlyLate = empty($countingAttedanceSummary["totalEarlyLate"]) ? 0  : $countingAttedanceSummary["totalEarlyLate"];
			$this->view->attendanceSummary = array(
				array("label"=>$tr->translate("ABSENT"),"value"=>$totalAbsent,"color"=>"#db3434"),
				array("label"=>$tr->translate("PERMISSION"),"value"=>$totalPermission,"color"=>"#0fcc6d"),
				array("label"=>$tr->translate("LATE"),"value"=>$totalLate,"color"=>"#ed9c18"),
				array("label"=>$tr->translate("EARLY_LEAVE"),"value"=>$totalEarlyLate,"color"=>"#9b59b6"),
			);
			
			
			$this->view->countingAttedanceSummary = $countingAttedanceSummary;
			$this->view->studentByDegree = $db->getCountingAllStudentByDegree($search);
	
			$branchId = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branchId);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			$this->view->search = $search;
			
			$form = new Application_Form_FrmSearchGlobal();
			$forms = $form->FrmSearch();
			Application_Model_Decorator::removeAllDecorator($forms);
			$this->view->form_search = $form;
		
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
			
		}
	}
	public function rptAbsenteeReportAction(){
		
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'academic_year' => '',
				'grade' => '',
				'session' => '',
				'group' => '',
				'branch_id' => 0,
				'degree' => 0,
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$last = $dbGb->getLatestAcadmicYear();
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
		}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbAttendanceReport();
		$this->view->absenteeRs = $db->getStudentAbsenteeReport($search);
		
		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
	}
	
	
	public function rptAttendenceAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'academic_year' => '',
				'grade' => '',
				'session' => '',
				'group' => '',
				'branch_id' => 0,
				'degree' => 0,
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
		}
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbAttendanceReport();
		$this->view->student = $db->getStudentAttendence($search);

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
	}
	public function rptStudentMistakeAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' => '',
				'academic_year' => '',
				'grade' => '',
				'session' => '',
				'group' => '',
				'branch_id' => 0,
				'degree' => 0,
				'start_date' => date('Y-m-d'),
				'end_date' => date('Y-m-d'),
			);
		}

		$group = new Allreport_Model_DbTable_DbAttendanceReport();
		$this->view->student = $group->getStudentMistake($search);
		$this->view->search = $search;

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$frm = new Application_Form_FrmGlobal();
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
	}
	
	public function rptTotalStudentMistakeAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();

			$group = new Allreport_Model_DbTable_DbAttendanceReport();
			$this->view->student = $rs_rows = $group->getStudentMistake($search);
			$this->view->search = $search;
			$this->view->datasearch = $search;
		} else {
			$search = array(
				'adv_search' => '',
				'academic_year' => '',
				'grade' => '',
				'session' => '',
				'group' => '',
				'branch_id' => 0,
				'degree' => 0,
			);
		}



		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
	}
	
}