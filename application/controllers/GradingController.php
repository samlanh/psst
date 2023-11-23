<?php

class GradingController extends Zend_Controller_Action
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
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		
		$teacherInfo = $dbExternal->getCurrentTeacherInfo();
		$currentAcademic = empty($teacherInfo['currentAcademic'])?0:$teacherInfo['currentAcademic'];

		$groupId=$this->getRequest()->getParam("id");
		$groupId = empty($groupId)?0:$groupId;
		$this->view->groupId = $groupId;
		$criteriaId=$this->getRequest()->getParam("criteriaId");
		$criteriaId = empty($criteriaId)?'':$criteriaId;
		$this->view->criteriaId = $groupId;
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['externalAuth']=1;
		}
		else{
			
			$search = array(
				'adv_search'=>'',
				'externalAuth'=>1,//for teacher access
				'academic_year'=> $currentAcademic,
				'exam_type'=>-1,
				'for_semester'=>-1,
				'for_month'=>'',
				'degree'=>0,
				'grade'=> 0,
				'group'=> $groupId ,
				'criteriaId'=>$criteriaId,
				'start_date'=> '',
				'end_date'=>date('Y-m-d')
			);
		}
		$this->view->search = $search;
		$db = new Application_Model_DbTable_DbGradingScore();
		$row = $db->getAllGradingScore($search);
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
		$db = new Application_Model_DbTable_DbGradingScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$rs = $db->addScoreGradingByClass($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id,1);
		
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/dashboard");
			exit();
		}
		
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
	
		$this->view-> month = $dbExternal->getAllMonth();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$degreeId = $row['degree_id'];
		$gradingId = $row['gradingId'];
		$result = $dbg->checkEntryScoreSetting($degreeId);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_PERMISSION_TO_ENTRY","/grading/index");
		}
		$array = array(
				'gradingId'=>$gradingId
				);
		$result = $dbExternal->getGradingCriteriaItems($array);

		$this->view->criteria = $result;
	}
	public function editAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		$db = new Application_Model_DbTable_DbGradingScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$rs = $db->UpdateScoreGradingByClass($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/index");				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$gradingRowId=$this->getRequest()->getParam("gradingRowId");
		$gradingRowId = empty($gradingRowId)?0:$gradingRowId;
		
		$resultRecord = $db->getGradingScoreById($gradingRowId);
		if(empty($resultRecord)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/grading/index");
		}
		$this->view->resultRecord = $resultRecord;
		$this->view->gradingRowId = $gradingRowId;
		
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
	
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id,1);
	
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/dashboard");
			exit();
		}
	
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		$this->view-> month = $dbExternal->getAllMonth();
	
		$dbg = new Application_Model_DbTable_DbGlobal();
		$degreeId = $row['degree_id'];
		$gradingId = $row['gradingId'];
		$result = $dbg->checkEntryScoreSetting($degreeId);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_PERMISSION_TO_ENTRY","/grading/index");
		}
		$array = array(
				'gradingId'=>$gradingId
		);
		$result = $dbExternal->getGradingCriteriaItems($array);
		$this->view->criteria = $result;
	}
	function getStudentsingleengryAction(){//single entry by criteria
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGradingScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForGradingScore($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	

}