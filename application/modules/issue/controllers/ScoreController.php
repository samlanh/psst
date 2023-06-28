<?php
class Issue_ScoreController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbScore();
			$this->view->g_all_name=$db->getGroupSearch();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search'=>'',
						'branch_id' => '',
						'group' => '',
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
			$rs_rows = $db->getAllScore($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","EXAM_TITLE","EXAM_TYPE","FOR_SEMESTER","FOR_MONTH","STUDENT_GROUP","STUDY_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","USER","STATUS");
			//"SCORE_LEVEL",
			$link=array(
					'module'=>'issue','controller'=>'score','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'exam_type'=>$link,'title_score'=>$link,
					'for_semester'=>$link,'for_month'=>$link,'academic_id'=>$link,'degree'=>$link,'group_id'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	public	function addAction(){
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScore();//by subject
			
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/score/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/score");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_branch=$db_global->getAllBranch();
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Issue_Model_DbTable_DbScore();
		
		$this->view->score_id = $id;
		$row = $_model->getScoreById($id);
		if (empty($row)){
			Application_Form_FrmMessage::MessageBacktoOldHistory("NO_RECORD");
			exit();
		}
		if ($row['is_pass']==1){
			Application_Form_FrmMessage::MessageBacktoOldHistory("CLASS_COMPLETED_CAN_NOT_EDIT");
			exit();
		}
		$this->view->score = $row;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				$key = new Application_Model_DbTable_DbKeycode();
				$dbset=$key->getKeyCodeMiniInv(TRUE);
				$dbs = new Issue_Model_DbTable_DbScore();//by subject
				
				if(isset($_data['save_close'])){
					$rs =  $dbs->updateStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/score");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
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
		$this->view->room = $row =$db_global->getAllRoom();		
		
		$db = new Issue_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$data=$db->getStudentByGroup($data['groupId'],$data);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getSubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScore();
			$data=$db->getSubjectByGroup($data['groupId'],null,$data['exam_type']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getChildsubjectAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbScore();
			$data=$db->getChildSubject($data['subject_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}