<?php
class Foundation_ScoreexcelController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbScoreexcel();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title'=>'',
						'group_name' => '',
						'study_year'=> '',
						'group'=>'',
						'degree'=>0,
						'grade'=> 0,
						'session'=> 0,
						'time'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->search = $search;
			$rs_rows = $db->getAllScore($search);
			$glClass = new Application_Model_GlobalClass();
			$rs = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","EXAM_TYPE","FOR_SEMESTER","FOR_MONTH","STUDENT_GROUP","STUDY_YEAR",
					"DEGREE","GRADE","SESSION","ROOM_NAME","STATUS","STUDENT_SCORE_REPORT","OUTSTANDING_STUDENT");
			$link=array('module'=>'foundation','controller'=>'scoreexcel','action'=>'edit',);
			$result=array('module'=>'allreport','controller'=>'score','action'=>'rpt-score-detail',);
			$result_outstanding=array('module'=>'allreport','controller'=>'score','action'=>'monthly-outstanding-student',);
			
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array(
					'បោះពុម្ព'=>$result,'តារាងកិត្តិយស'=>$result_outstanding,'exam_type'=>$link,'title_score'=>$link,
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
	public function fullResultAction(){
		
	}
	public	function addAction(){
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScoreexcel();//by subject
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/scoreexcel/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/scoreexcel");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();
			
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$_model = new Foundation_Model_DbTable_DbScore();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			try {
				$key = new Application_Model_DbTable_DbKeycode();
				$dbset=$key->getKeyCodeMiniInv(TRUE);
				$dbs = new Foundation_Model_DbTable_DbScoreexcel();//by subject
				$rs =  $dbs->updateStudentScore($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/scoreexcel");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->score_id = $id;
		$this->view->score = $_model->getScoreById($id);
		$this->view->student= $_model->getStudentSccoreforEdit($id);
		$this->view->rows_scor=$_model->getScoreStudents($id);
		$data=$this->view->rows_detail=$_model->getSubjectById($id);
		$this->view->row_g=$_model->getGroupStudent($id);
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
	
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result = $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
		$this->view->room = $row =$db_global->getAllRoom();		
		
		$db = new Foundation_Model_DbTable_DbScore();
		$this->view->month = $db->getAllMonth();
	}
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScore();
			$grade = $db->getAllGrade($data['degree']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScore();
			$data=$db->getStudentByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getSubjectbygroupAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScore();
			$data=$db->getSubjectScoreByGroup($data['group']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getChildsubjectAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbScore();
			$data=$db->getChildSubject($data['subject_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}
