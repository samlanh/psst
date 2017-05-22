<?php
class Foundation_StudentscoreController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbStudentScore();
			$this->view->g_all_name=$db->getGroupSearch();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
				$this->view->g_name=$search;
			}
			else{
				$search = array(
						'group_name' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows = $db->getAllHoweWorkScore($search);
			$glClass = new Application_Model_GlobalClass();
			$rs = $glClass->getImgActive($rs_rows, BASE_URL, true);
			//$rs = $glClass->getSession($rs_rows,BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array( "STUDENT_GROUP","STUDY_YEAR","SESSION","SUBJECT","TERM","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'studentscore','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array('student_no'=>$link,'student_id'=>$link,'academic_id'=>$link,'session_id'=>$link,'group_id'=>$link));
	
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
			$db = new Foundation_Model_DbTable_DbStudentScore();
			try {
				if(isset($_data['save_new'])){
					 $rs =  $db->addStudentScore($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentscore/add");
				}else {
					 $rs =  $db->addStudentScore($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentscore");
				}
		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->row_year=$db_homwork->getAllYears();
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		//get subject id
		$this->view->rows_sub=$db_homwork->getSubjectId();
		//print_r($dbs);exit();
		
 
		$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->rows_sub=$db_homwork->getSubjectId();
		$this->view->rows_parent=$db_homwork->getParent();
		 
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['score_id']=$id;
			$_model = new Foundation_Model_DbTable_DbStudentScore();
			try {
				if(isset($_data['save_close'])){
						$rs =  $_model->updateStudentHomworkScore($_data);
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentscore");
					} 
		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,1);
		$db_subjec=new Global_Model_DbTable_DbStudentScore();
		$this->view->rows_parent=$db_subjec->getParentName();
		//$dbs=$this->view->row_years=$db_subjec->getStudyYears();
		$this->view->rows_group=$db_subjec->getGroupAll();
		$db_years=new Registrar_Model_DbTable_DbRegister();
		$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->row_year=$db_homwork->getAllYears();
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		
		//get subject id
		$this->view->rows_sub=$db_homwork->getSubjectId();
		//print_r($dbs);exit();
		
 
		//$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->rows_sub=$db_homwork->getSubjectId();
		$this->view->rows_parent=$db_homwork->getParent();
		//get id fore update 
		$db=new Foundation_Model_DbTable_DbHomeWorkScore();
		$this->view->row_g=$db->getGroupStudent($id);
		$this->view->rows_scor=$db->getScoreStudents($id);
		$data=$this->view->rows_detail=$db->getSubjectById($id);
	}
	
	function getSubjectAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Global_Model_DbTable_DbStudentScore();
			$data=$_dbmodel->getSujectById($data['parent_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentScore();
			$data=$db->getStudent($data['year'],$data['grade'],$data['session']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
	
	
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbStudentScore();
			$grade = $db->getAllGrade($data['degree']);
			//print_r($grade);exit();
			//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	
	
	
	
	
	
	
	
	
}

