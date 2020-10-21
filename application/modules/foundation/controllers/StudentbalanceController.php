<?php
class Foundation_StudentbalanceController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'study_year'=> '',
						'group'=> '',
						'grade_all'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> -1,
						'branch_id'=>''
					);
			}
			$this->view->adv_search=$search;
			$db_student= new Foundation_Model_DbTable_DbStudentBalance();
			$rs_rows = $db_student->getAllStudentBalance($search);
			$list = new Application_Form_Frmtable();
			
				$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_EN","SEX","ACADEMIC_YEAR","GROUP","DEGREE","GRADE","IS_BALANCE","STATUS");
				$link=array(
						'module'=>'foundation','controller'=>'studentbalance','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,
						'stu_name'=>$link,'stu_khname'=>$link,'group_name'=>$link,'academic'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	function addAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
				
				$_db = new Foundation_Model_DbTable_DbStudentBalance();
				$rs_rows = $_db->getAllStudentNotYetPayment($search);
				$this->view->row = $rs_rows;
			}else{
				$search = array(
						'adv_search' => '',
						'study_year'=> '',
						'group'=> '',
						'grade_all'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> -1,
						'branch_id'=>''
					);
			}
			$this->view->adv_search=$search;
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	public function submitdataAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbStudentBalance();
				$_add->addStudentBalance($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentbalance/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	public function editAction(){
		$this->_redirect("/foundation/studentbalance");
	}
	
}