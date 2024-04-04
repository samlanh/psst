<?php
class Issue_ScoretmpController extends Zend_Controller_Action {

	const REDIRECT_URL='/issue/scoretmp';
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbScoreTemorary();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'adv_search'=>'',
						'branch_id' => '',
						'group' => '',
						'teacher'=>'' ,
						'subjectId'=>'' ,
						'criteriaId'=>'',

						'academic_year'=> '',
						'exam_type'=>-1,
						'for_semester'=>-1,
						'for_month'=>'',
						'degree'=>0,
						'grade'=> 0,
						'session'=> 0,
						'time'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllScoreTemporary($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDY_YEAR","STUDENT_GROUP","SUBJECT","CRITERIAL_NAME","EXAM_TYPE","FOR_SEMESTER","FOR_MONTH","DATE","TEACHER","AMOUNT_STU_INPUT","STATUS");
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;

	}
	
	public function addAction(){
		$this->_redirect('/issue/scoretmp');
	}
	
	function deleteAction(){
		try{
			$request=Zend_Controller_Front::getInstance()->getRequest();
			$action=$request->getActionName();
			$controller=$request->getControllerName();
			$module=$request->getModuleName();
	
			$dbacc = new Application_Model_DbTable_DbUsers();
			$rs = $dbacc->getAccessUrl($module,$controller,'delete');
			
			if(!empty($rs)){
				$id = $this->getRequest()->getParam('id');
				$db = new Issue_Model_DbTable_DbScoreTemorary();
				if(!empty($id)){
					$db->deleteTmpScore($id);
					Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS",self::REDIRECT_URL);
				}
			}
			Application_Form_FrmMessage::Sucessfull("You no permission to delete",self::REDIRECT_URL);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScoreTemorary();
			$data['gradingTmpId'] = empty($data['gradingTmpId']) ? 0 : $data['gradingTmpId'];
			$row = $db->getScoreTemporaryInfo($data);
			print_r(Zend_Json::encode($row));
			exit();
			
		}else{
			print_r(Zend_Json::encode(false));
			exit();
		}
	}
	function getsubjectlistAction(){
		$this->_helper->layout()->disableLayout();
		if($this->getRequest()->isPost()){
			$dbg = new Application_Model_DbTable_DbGlobal();
			$data = $this->getRequest()->getPost();
			$row=$dbg->getAllSubjectName($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}