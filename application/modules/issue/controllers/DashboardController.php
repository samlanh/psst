<?php 
class Issue_DashboardController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
public function indexAction()
	{
		$db = new Issue_Model_DbTable_DbDashboard();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$this->view->search = $search;
			$rows= $db->getAllGroups($search);

		}else{
			// $search = array(
			// 			'adv_search'=>'',
			// 			'academic_year'=> $currentAcademic,
			// 			'exam_type'=>0,
			// 			'for_semester'=>0,
			// 			'for_month'=>0,
			// 			'degree'=>0,
			// 			'grade'=> 0,
			// 			'start_date'=> '',
			// 			'end_date'=>date('Y-m-d'));
			$rows='';
		}
		
		$this->view->row = $rows;
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
		$this->view->row = $row;
		
		if(empty($row)){
			$this->_redirect("/external/group");
		}
	
		$this->view-> month = $dbExternal->getAllMonth();
		
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
