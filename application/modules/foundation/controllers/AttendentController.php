<?php
class Foundation_AttendentController extends Zend_Controller_Action {
	
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbAttendent();
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
			$rs_rows = $db->getAllAttendent($search);
			$glClass = new Application_Model_GlobalClass();
		    $rs = $glClass->getImgActive($rs_rows, BASE_URL, true);
			//$rs = $glClass->getSession($rs_rows,BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array( "STUDENT_GROUP","STUDY_YEAR","SESSION","SUBJECT","ATTENDENT_DATE","NOTE","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'attendent','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array( 'academic_id'=>$link,'session_id'=>$link,'group_id'=>$link,'subject_id'=>$link));
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_model = new Foundation_Model_DbTable_DbAttendent();
			try {
				$rs =  $_model->addAttendent($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/attendent/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/attendent");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,1);
		$db_subjec=new Global_Model_DbTable_DbStudentScore();
		$this->view->rows_parent=$db_subjec->getParentName();
		$this->view->rows_group=$db_subjec->getGroupAll();
		$db_years=new Registrar_Model_DbTable_DbRegister();
		$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->row_year=$db_homwork->getAllYears();
		$db_session=new Application_Model_DbTable_DbGlobal();
		$this->view->row_sesion=$db_session->getSession();
	}
	function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_model = new Foundation_Model_DbTable_DbAttendent();
			try {
				$_data['id']=$id;
				$rs =  $_model->updateAttendent($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/attendent");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,1);
		$db_subjec=new Global_Model_DbTable_DbStudentScore();
		$this->view->rows_parent=$db_subjec->getParentName();
		$this->view->rows_group=$db_subjec->getGroupAll();
		$db_years=new Registrar_Model_DbTable_DbRegister();
		$db_homwork=new Global_Model_DbTable_DbHomeWorkScore();
		$this->view->row_year=$db_homwork->getAllYears();
		$_model_att = new Foundation_Model_DbTable_DbAttendent();
		$this->view->row_att=$_model_att->getAttendentById($id);
		$this->view->row_att_detial=$_model_att->getAttendentDetail($id);
		$db_session=new Application_Model_DbTable_DbGlobal();
		$this->view->row_sesion=$db_session->getSession();
	}
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Foundation_Model_DbTable_DbAttendent();
			$data=$_dbmodel->getStudent($data['group_id']);
			print_r(Zend_Json::encode($data));
			exit();
		}
	}
}
