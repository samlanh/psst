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
		$row = $db->getAllScoreByUser($search);
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
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbIssueScore();//by subject
			try {
				$rs =  $db->addStudentScore($_data);
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
		
		$db_global=new Application_Model_DbTable_DbGlobal();
	
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
				$rs =  $db->addStudentScore($_data);
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
		
		$rs = $db->getScoreByID($id);
		$this->view->rs = $rs;	
		if(empty($rs)){
			$this->_redirect("/issuescore/index");
		}

		$groupID = empty($rs['group_id'])?0:$rs['group_id'];
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByID($groupID);
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
	}
}





