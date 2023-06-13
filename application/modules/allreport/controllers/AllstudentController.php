<?php
class Allreport_AllstudentController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
	}
	public function printIdcardAction(){//using
		$id=$this->getRequest()->getParam('id');
		$front_card=$this->getRequest()->getParam('front_card');
		$this->view->front = empty($front_card)?"":$front_card;
		$k = 0;

		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $db->getAllStudentSelected($id);
		$this->view->groupByBranchAndSchool = $db->getAllStudentSelectedBG($id);
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	public function printPickupcardAction(){//using
		$id=$this->getRequest()->getParam('id');
		$k = 0;

		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $db->getAllStudentSelected($id);
		$this->view->groupByBranchAndSchool = $db->getAllStudentSelectedBG($id);
	}
	
	public function rptStudentcardAction(){//using
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 		=>'',
				'branch_id'		=>0,
				'degree'		=>0,
				'academic_year' 	=>'',
				'grade' 	=>'',
				'session' 		=>'',
				'group'			=>'',
				'stu_type'		=>-1,
				'study_type'	=>"",
				'start_date'	=> date('Y-m-d'),
				'end_date'		=> date('Y-m-d'),
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$rs_rows = $group->getAllStudent($search);
		$this->view->rs = $rs_rows;
		$this->view->groupByBranchAndSchool = $group->getAllStudentGroupbyBranchAndSchoolOption($search);
		$this->view->search=$search;
		
		print_r($group->getAllStudentGroupbyBranchAndSchoolOption($search));
		$searchSS=array(
				'branch_id'		=>1,
			);
		$this->view->stu_forOption = $group->getAllStudent($searchSS);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	public function studentGroupAction()
	{
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['issetGroup']=0;
		}
		else{
			$search = array(
					'adv_search' 		=> "",
					'group' 		=> "",
					'branch_id' 	=> "",
					'academic_year'	=> "",
					'grade' 		=> "",
					'school_option' => "",
					'teacher' 		=> "",
					'room'=>0,
					'degree'=>0,
					'study_status'=>-1,
					'issetGroup'=>0,
			);
		}
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$this->view->rs = $db->getGroupDetailReport($search);
		
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->studentUngroup = $group->getGroupBYStudentGrade($search);
		
	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$this->view->search = $search;
	
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
	}
	public function rptStudentStatisticAction(){//using
		$db_yeartran = new Allreport_Model_DbTable_DbRptAllStudent();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'adv_search' 		=>'',
					'academic_year' 	=>'',
					'grade' 	=>'',
					'session' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_status'=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $group->getGroupBYStudentGrade($search);
		$this->view->search=$search;
	
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
	}
	public function rptAllStudentprofileAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'adv_search' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'academic_year' 	=>'',
					'grade' 	=>'',
					'session' 		=>'',
					'group'			=>'',
					'stu_type'=>'',
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$rs_rows = $group->getAllStudentpro($search);
		$this->view->rs = $rs_rows;
		$this->view->search=$search;
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function rptStudentGroupAction()
	{
		$id=$this->getRequest()->getParam("id");
		$id = (empty($id))?0:$id;
		if(empty($id)){
			$this->_redirect("/allreport/allstudent/student-group");
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
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$result = $db->getStudentGroup($id,$search,0);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/allstudent/student-group");
		}
		$this->view->rs = $result;
		$rs = $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->study_type = $db->getViewByType(5,0);
	}
	public function rptStudentListAction()
	{
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$id=$this->getRequest()->getParam("id");
		$id = (empty($id))?0:$id;
		if(empty($id)){
			$this->_redirect("/allreport/allstudent/student-group");
		}
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			// 			$db->submitDateList($search);
			$row = $db->getStudentGroup(null,$search,1);
			$rs=array();
			if(!empty($row[0]['group_id'])){
				$rs = $db->getGroupDetailByID($row[0]['group_id']);
			}
		}
		else{
			$search = array(
					'txtsearch' 	=> "",
					'group' 		=> "",
					'branch_id' 	=> "",
					'academic_year'	=> "",
					'study_type'	=>0
			);
		}
		
		$result = $db->getGroupDetailByID($id);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/allstudent/student-group");
		}
		
		$this->view->search = $search;
		
		$row = $db->getStudentGroup($id,$search,1);
		$this->view->rs = $row;
		$this->view->rr = $rs;
	
		$frm = new Application_Form_FrmGlobal();
		$branchId=(!empty($rs['branch_id']))?$rs['branch_id']:0;
		$this->view->rsheader = $frm->getLetterHeaderReport($branchId,3);
	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->study_type = $db->getViewByType(5,0);
	}
	public function rptCrmAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'advance_search' => "",
						'branch_search' => "",
						'ask_for_search' => "",
						'know_by_search' => "",
						'prev_concern' => "",
						'crm_process'=>-1,
						'followup_status'=>-1,
						'degree' => "",
						'grade' => "",
						'status_search' => -1,
						'school_option' => '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
				
			$db = new Allreport_Model_DbTable_DbStudent();
			$rs_rows = $db->getAllCRM($search);
			$this->view->row = $rs_rows;
			$this->view->search  = $search;
				
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooterFoundation = $frm->getFooterAccount(2);
				
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$pevconcer = $_dbgb->getViewByType(22);
		$this->view->prev_concern = $pevconcer;
	
		$frm = new Home_Form_FrmCrm();
		$frm->FrmAddCRM(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_crm = $frm;
	
		$this->view->rsknowBy = $_dbgb->getAllKnowBy();
	}
	public function rptCrmDetailAction(){
		$id=$this->getRequest()->getParam("id");
		$id = (empty($id))?0:$id;
		$db = new Home_Model_DbTable_DbCRM();
		$row = $db->getCRMById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/allstudent/rpt-crm");
		}
		$this->view->rs = $row;
	
		$rowdetail = $db->getCRMDetailById($id);
		$this->view->rowdetail = $rowdetail;
		$allContact = $db->AllHistoryContact($id);
		$this->view->history = $allContact;
	
		$pre = explode(",", $row['prev_concern']);
		$prevCon="";
		if (!empty($pre)) foreach ($pre as $a){
			if(empty($a)){
				continue;
			}
			$title = $db->getPrevTilteByKeyCode($a);
			if (empty($prevCon)){
				$prevCon = $title;
			}else {
				if (!empty($title)){
					$prevCon = $prevCon." , ".$title;
				}
			}
		}
		$this->view->prevconcern = $prevCon;
		$branch_id = empty($row['branch_search'])?null:$row['branch_search'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooterFoundation = $frm->getFooterAccount(2);
	}
	function rptCrmDailyContactAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'advance_search' => "",
						'branch_search' => "",
						'ask_for_search' => "",
						'crm_list'  => "",
						'status_search' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'feedback_type'  => "",
				);
			}
	
			$db = new Allreport_Model_DbTable_DbStudent();
			$rs_rows = $db->getAllCRMDailyContact($search);
			$this->view->row = $rs_rows;
			$this->view->search  = $search;
	
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooterFoundation = $frm->getFooterAccount(2);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Home_Form_FrmCrm();
		$frm->FrmAddCRM(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_crm = $frm;
	
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$pevconcer = $_dbgb->getViewByType(34);
		$this->view->feedback_type = $pevconcer;
		$followup = $_dbgb->getcrmFollowupStatus();
		unset($followup[-1]);
		$this->view->followup = $followup;
	}
	function rptStudenttestAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'user'=>'',
						'branch_id'=>0,
						'academic_year' =>'',
						'degree' =>'',
						'term_test' =>'',
						'student_option_search' =>'',
						'province_search' =>'',
						'type_exam' => '',
						'result_status' => '',
						'register_status' => '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
	
			$db = new Allreport_Model_DbTable_DbStudent();
			$this->view->search=$search;
			$this->view->row = $db->getAllStudentTest($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooterFoundation = $frm->getFooterAccount(2);
	}
	public function rptStudentNotyetgrAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'adv_search' 	=> '',
					'branch_id'	=> 0,
					'degree'	=> 0,
					'academic_year'=> '',
					'grade' 	=> '',
					'group'		=> '',
					'student_group_status'	=> -1,
			);
		}
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$rs_rows = $group->getAllStudentNotYetGroup($search);
		$this->view->rs = $rs_rows;
	
		$this->view->search=$search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function rptAllStudentOldAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'title' 		=>'',
				'branch_id'		=>0,
				'degree'		=>0,
				'study_year' 	=>'',
				'grade_all' 	=>'',
				'session' 		=>'',
				'stu_type' 		=>-1,
				'start_date'	=> date('Y-m-d'),
				'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllStudent($search);
		$this->view->search=$search;
	}
	public function rptAllAmountStudentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'adv_search' 		=>'',
					'academic_year' 	=>'',
					'grade' 	=>'',
					'session' 		=>'',
					'branch_id'		=>0,
					'group'			=>'',
					'degree'=>0,
					'study_type'=>'',
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs =  $group->getAllAmountStudent($search);
		$this->view->search=$search;
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
	}
	public function rptAttListAction()
	{
		$id=$this->getRequest()->getParam("id");
		$id = (empty($id))?0:$id;
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
					'txtsearch' 	=> "",
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d',strtotime('+1 month')),
					'teacher' 		=> 0,
					'subject' 		=> 0,
					'showsign'		=> 1,
					'group'        =>'',
					'branch_id'        =>'',
			);
		}
		$db = new Allreport_Model_DbTable_DbRptGroup();
		
		$result = $db->getGroupDetailByID($id);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/allstudent/student-group");
		}
		
		$row = $db->getStudentGroup($id,$search,0);
		$this->view->rs = $row;
	
		$this->view->rr = $result;
		$this->view->datasearch = $search;
		$this->view->search = $search;
		$this->view->all_teacher_by_group = $db->getAllTeacherByGroup($id);
		$this->view->all_subject_by_group = $db->getAllSubjectByGroup($id);
	
		$branch_id = empty($result['branch_id'])?null:$result['branch_id'];
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$this->view->search = $search;
	
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	public function rptenglishprogramAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 	=>'',
				'academic_year' =>'',
				'grade' 	    =>'',
				'session' 		=>'',
				'branch_id'		=>0,
				'group'			=>'',
				'degree'=>0,
				'study_type'=>'',
				'pay_status'	=> 0,
				'start_date'	=> date('Y-m-d'),
				'end_date'		=> date('Y-m-d'),
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $group->getAllStudentgep($search);
		$this->view->search=$search;
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		
	}	
	
	public function rptStudyHistoryAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
				$search=array(
					'title' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'stu_type' 		=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
				);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$db= new Allreport_Model_DbTable_DbRptAllStudent();
		//$this->view->rs = $rs_rows = $db->getAllStudyHistory($search);
		$this->view->search =$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	public function rptStudentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title'  	=>'',
					'branch_id'  	=>'',
					'study_year'=>'',
					'group'  	=>'',
					'grade' =>'',
					'session'  	=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'	=> date('Y-m-d'),
			);
		}
		
		$form = new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $group->getAllStudentDetail($search);
		$this->view->search=$search;
		
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsfooteracc = $frm->getFooterAccount();
	}
	
	function getSubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptAllStudent();
			$data=$db->getSubjectByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	public function rptStudentDropAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 	=>'',
				'branch_id' =>'',
				'academic_year'=>'',
				'degree' 	=>'',
				'grade' 	=>'',
				'session' 	=>'',
				'type'		=>'',
				'start_date'=>date("Y-m-d"),
				'end_date'	=>date("Y-m-d")
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $group->getAllStudentDrop($search);
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view-> rsfooter = $frm->getFooterAccount(2);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	public function rptgroupstudentchangegroupAction()
	{
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{
			$search=array(
					'adv_search' => '',
					'branch_id' => '',
					'academic_year' => '',
					'grade_bac' => '',
					'session' => '',
					'change_type' => '',
					'changegroup_id'=>''
			);
		}
		$db= new Allreport_Model_DbTable_DbRptGroupStudentChangeGroup();
		$this->view->rs = $db->getAllStu($search);
		$this->view->change_type = $db->getChangeType();
		$this->view->all_change_group = $db->getAllChangeGroup(1); // 1=ប្តូរក្រុម
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		$this->view->search=$search;
	}
	
	
	function submitlistAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Allreport_Model_DbTable_DbRptGroup();
			$db->UpdateAmountStudent($data);
			$this->_redirect("/allreport/allstudent/student-group");
		}
	}
	
	function certifyEnglishAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbCertify();
		$result = $db->getStudentCertify($id);
		$this->view->rs = $result;
		
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($result['branch_id']);
	}
	function certifyKhmerAction(){
		$id=$this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbCertify();
		$result = $db->getStudentCertify($id);
		$this->view->rs = $result;
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($result['branch_id']);
	}
	function rptTranscriptAction(){//for psis
		$group_id=$this->getRequest()->getParam("group_id");
		$student_id=$this->getRequest()->getParam("id");
		$search= array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->semester1= $db->getStundetExamById($group_id,1,$student_id);//for semester1
		$this->view->semester2= $db->getStundetExamById($group_id,2,$student_id);//for semester1
		$this->view->rsrankingsemester1 = $db->getRankStudentbyGroupSemester($group_id,1,$student_id);
		$this->view->rsrankingsemester2 = $db->getRankStudentbyGroupSemester($group_id,2,$student_id);
		
		$result = $db->getAcadimicByStudentHeader($group_id,$student_id);
		$this->view->result = $result;
	}
	function academicTranscriptAction(){
	}
	
	public function rptCertificateAction()
	{
		$group_id=$this->getRequest()->getParam("group_id");
		$student_id=$this->getRequest()->getParam("id");
		$search= array();
		$db = new Allreport_Model_DbTable_DbRptStudentScore();
		$this->view->rsscore = $db->getSubjectScorebystudentandgroup($group_id,$student_id);
		$result = $db->getAcadimicByStudentHeader($group_id,$student_id);
		$this->view->result = $result;
	}
	
	
	public function rptStudentDocumentAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 		=>'',
				'branch_id'		=>0,
				'degree'		=>0,
				'academic_year' 	=>'',
				'grade' 	=>'',
				'group'			=>'',
				'start_date'	=> date('Y-m-d'),
				'end_date'		=> date('Y-m-d',strtotime("+5 day")),
			);
		}
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		$rs_rows = $group->getStuDocumentNotEnough($search);
		$this->view->rs = $rs_rows;
	
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function suspensionletterAction(){
		$id=$this->getRequest()->getParam("id");
		if (empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/studentdrop/index");exit();
		}
		$group= new Allreport_Model_DbTable_DbRptAllStudent();
		
		$rs_rows = $group->getStudentDropInfo($id);
		$this->view->rs = $rs_rows;
	}
	
	
	public function rptStudentgroupAdjustAction()
	{
		$id=$this->getRequest()->getParam("id");
		if(empty($id)){
			$this->_redirect("/allreport/allstudent/student-group");
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
		$this->view->search = $search;
		$db = new Allreport_Model_DbTable_DbRptGroup();
		$row = $db->getStudentGroup($id,$search,0);
		$this->view->rs = $row;
		$rs = $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->study_type = $db->getViewByType(5,0);
	}
	
	public function printIdcardTrayprintAction(){
		$id=$this->getRequest()->getParam('id');
		$front_card=$this->getRequest()->getParam('front_card');
		$this->view->front = empty($front_card)?"":$front_card;
		$k = 0;

		$db = new Allreport_Model_DbTable_DbRptAllStudent();
		$this->view->rs = $rs_rows = $db->getAllStudentSelected($id);
		$this->view->groupByBranchAndSchool = $db->getAllStudentSelectedBG($id);
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	
	public function rptStudentDropreturnAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 	=>'',
				'branch_id' =>'',
				'academic_year'=>'',
				'degree' 	=>'',
				'grade' 	=>'',
				'session' 	=>'',
				'type'		=>'',
				'start_date'=>date("Y-m-d"),
				'end_date'	=>date("Y-m-d")
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptStudentDrop();
		$this->view->rs = $group->getAllStudentDropReturn($search);
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
}