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
			$rs_rows = $db->getAllStudentEvaluation($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","TYPE","FOR_SEMESTER","FOR_MONTH","STUDENT_ID","STUDENT_NAME","STUDENT_GROUP","STUDY_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'studentevaluation','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'for_type'=>$link,'name'=>$link,'stu_code'=>$link,
					'for_semester'=>$link,'for_month'=>$link,'academic_id'=>$link,'group_id'=>$link));
		
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
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		$this->view->room = $row =$db_global->getAllRoom();
		
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				$dbs = new Issue_Model_DbTable_DbStudentEvaluation();//by subject
				if(isset($_data['save_close'])){
					$rs =  $dbs->updateStudentScore($_data,$id);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/studentevaluation");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Issue_Model_DbTable_DbStudentEvaluation();
		$row = $db->getStudentEvaluationById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/studentevaluation");
			exit();
		}
		$this->view->row = $row;
		$this->view->row_detail = $db->getStudentEvaluationDetailById($id);
		$this->view->rating = $db->getAllRating();
		
		if($row['is_pass']==1){
			Application_Form_FrmMessage::Sucessfull("Can not edit because this group is finished !!!","/issue/studentevaluation");
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		$this->view->room = $row =$db_global->getAllRoom();
		
		$db = new Foundation_Model_DbTable_DbScore();
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
