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
						'title'=>'',
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'degree'=>0,
						'grade'=> 0,
						'session'=> 0,
						'time'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllScore($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SUBJECT_TITLE","EXAM_TYPE","FOR_SEMESTER","FOR_MONTH","SCORE_LEVEL","STUDENT_GROUP","STUDY_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'studentevaluation','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'exam_type'=>$link,'title_score'=>$link,
					'for_semester'=>$link,'for_month'=>$link,'academic_id'=>$link,'degree'=>$link,'group_id'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbStudentEvaluation();
			try {
				$rs =  $db->addStudentEvaluation($_data);
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
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		$this->view->room = $row =$db_global->getAllRoom();
		
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$_model = new Issue_Model_DbTable_DbStudentEvaluation();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				$key = new Application_Model_DbTable_DbKeycode();
				$dbset=$key->getKeyCodeMiniInv(TRUE);
				$dbs = new Issue_Model_DbTable_DbStudentEvaluation();//by subject
				if(isset($_data['save_close'])){
					$rs =  $dbs->updateStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/studentevaluation");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$this->view->score_id = $id;
		$row = $_model->getScoreById($id);
		$this->view->score = $row;
		$this->view->student= $_model->getStudentSccoreforEdit($id);
		$this->view->rows_scor=$_model->getScoreStudents($id);
		$data=$this->view->rows_detail=$_model->getSubjectById($id);
		$this->view->row_g=$_model->getGroupStudent($id);
		
		$this->view->subjectGroup = $_model->getSubjectByGroup($row['group_id'],null,$row['exam_type']);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
// 		$result = $db_global->getAllgroupStudy();
// 		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
// 		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();		
		
		$db = new Issue_Model_DbTable_DbStudentEvaluation();
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
			$data=$db->getCommentByDegree($data['degree']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}
