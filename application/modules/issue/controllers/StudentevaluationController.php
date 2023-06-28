<?php
class Issue_StudentevaluationController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStudentEvaluation();
			$this->view->g_all_name=$db->getGroupSearch();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group' => '',
						'academic_year'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d')
					);
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllGroupStudentEvaluation($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_GROUP","TYPE","FOR_SEMESTER","FOR_MONTH","TEACHER_COMMENT","CREATE_DATE","USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'studentevaluation','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branchName'=>$link,'groupName'=>$link,'forType'=>$link,));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;;
	}
	public	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentEvaluation();
			try {
				$rs = $db->addStudentEvaluation($_data);
				if($rs==-1){
					Application_Form_FrmMessage::message("RECORD_EXIST");
					return 0;
				}
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentevaluation/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/studentevaluation");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Issue_Model_DbTable_DbStudentEvaluation();
		$this->view->rating = $db->getAllRating();
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
		
		$db = new Issue_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$id = $_data['evaluatioId'];
			try {
				$dbs = new Issue_Model_DbTable_DbStudentEvaluation();//by subject
				if(isset($_data['save_close'])){
					 $dbs->updateStudentEvaluation($_data,$id);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/studentevaluation");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Issue_Model_DbTable_DbStudentEvaluation();
		$row = $db->getEvaluationById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/studentevaluation");
			exit();
		}
		$this->view->row = $row;
		$this->view->row_evaluation = $db->getStudentEvaluationById($id);
		$this->view->rating = $db->getAllRating();
		
		if($row['is_pass']==1){
			Application_Form_FrmMessage::Sucessfull("Can not edit because this group is finished !!!","/issue/studentevaluation");
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
		
		$db = new Issue_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	function getStudentbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentEvaluation();
			$data=$db->getStudentByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getCommentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentEvaluation();
			$result=$db->getCommentByDegree($data);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}
