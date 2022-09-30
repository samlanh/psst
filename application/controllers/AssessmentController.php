<?php

class AssessmentController extends Zend_Controller_Action
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
		$row = $db->getAllIssueAssessmentByClass($search);
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
		$db = new Application_Model_DbTable_DbAssessment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$rs = $db->addStudentAssessment($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/assessment/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/assessment/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByID($id);
		$this->view->row = $row;
		
		if(empty($row)){
			$this->_redirect("/external/group");
		}
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
	}

	public function editAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);

		$db = new Application_Model_DbTable_DbAssessment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs =  $db->updateAssessmentByClass($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/assessment/index");
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$row = $db->getAssessmentByID($id);
		$this->view->row = $row;	
		
		if(empty($row)){
			$this->_redirect("/assessment/index");
		}
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/assessment/index");
		}
		if ($row['isLock']==1){
			Application_Form_FrmMessage::Sucessfull("RECORD_LOCKED_CAN_NOT_EDIT","/assessment/index");
		}
		if ($row['is_pass']==1){
			Application_Form_FrmMessage::Sucessfull("CLASS_COMPLETED_CAN_NOT_EDIT","/assessment/index");
		}
		if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("SCORE_DEACTIVE_CAN_NOT_EDIT","/assessment/index");
		}
		//$this->view->student= $db->getStudentSubjectSccoreforEdit($id);
		
		
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
	}
	
	
	function getStudentassessmentAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbAssessment();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			
			
			$rs=$db->getStudentForAssessment($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getStudentassessmenteditAction(){
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbAssessment();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			
			$rs=$db->getStudentForAssessmentEdit($data);
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





