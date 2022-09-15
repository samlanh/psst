<?php

class IssuescoreController extends Zend_Controller_Action
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
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
						'adv_search'=>'',
						
						'academic_year'=> '',
						'exam_type'=>-1,
						'for_semester'=>-1,
						'for_month'=>'',
						'degree'=>0,
						'grade'=> 0,
						'start_date'=> date('Y-m-d'),
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
		$row = $dbExternal->getGroupDetailByID($id);
		$this->view->row = $row;
		
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$gradingId = empty($row['gradingId'])?0:$row['gradingId'];
		$this->view->criterial = $dbExternal->getGradingSystemDetail($gradingId);
		
	
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
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
		
		if(empty($row)){
			$this->_redirect("/issuescore/index");
		}
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
		$gradingId = empty($row['gradingId'])?0:$row['gradingId'];
		$this->view->criterial = $dbExternal->getGradingSystemDetail($gradingId);
		
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
	}
	
	
}





