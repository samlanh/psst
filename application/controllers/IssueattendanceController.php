<?php

class IssueattendanceController extends Zend_Controller_Action
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
	
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['externalAuth']=1;
		}
		else{
			$search = array(
						'adv_search'=>'',
						'externalAuth'=>1,//for teacher access
						'exam_type'=>-1,
						'for_semester'=>-1,
						'for_month'=>'',
						'degree'=>0,
						'grade'=> 0,
						'start_date'=> '',
						'end_date'=>date('Y-m-d'));
		}
		$this->view->search = $search;
		$db = new Application_Model_DbTable_DbIssueAttendance();
		$row = $db->getClassScheduleForIssueAttendance($search);
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
		
		$data=$this->getRequest()->getParams();
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);

		$db = new Application_Model_DbTable_DbIssueAttendance();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs = $db->submitAttendance($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issueattendance/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
		$rowInfo = $db->getTeacherScheduleDetailById($data);
		if (empty($rowInfo)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issueattendance/index");
		}
		$checking= $db->checkAvailableTimeInput($rowInfo);
		if (empty($checking)){
			Application_Form_FrmMessage::Sucessfull("TEACHING_CLASS_ALREADY_EXPIRED","/issueattendance/index");
		}
		$this->view->rowInfo = $rowInfo;
		
	}

	public function editAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		
		$data=$this->getRequest()->getParams();
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);

		$db = new Application_Model_DbTable_DbIssueAttendance();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs = $db->updateAttendance($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issueattendance/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		if (empty($data["attendanceId"])){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issueattendance/index");
		}
		
		$rowInfo = $db->getTeacherScheduleDetailById($data);
		if (empty($rowInfo)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issueattendance/index");
		}
		$checking= $db->checkAvailableTimeInput($rowInfo);
		if (empty($checking)){
			Application_Form_FrmMessage::Sucessfull("TEACHING_CLASS_ALREADY_EXPIRED","/issueattendance/index");
		}
		$this->view->rowInfo = $rowInfo;
		
	}
	
	function getStudentAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueAttendance();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForIssueAttendance($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}