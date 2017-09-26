<?php
class Home_SearchstudentinfoController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$db_student= new Home_Model_DbTable_DbStudent();
			$rs_rows = $db_student->getAllStudent($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","STUDENT_ID","STUDENT_NAME","SEX","PHONE","ACADEMIC_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
			$link=array(
					'module'=>'home','controller'=>'searchstudentinfo','action'=>'student-detail',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'name'=>$link,'stu_khname'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	public function studentDetailAction(){
		$db= new Home_Model_DbTable_DbStudent();
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id'=>'',
						'payment_by'=>-1,
						'grade_all' =>-1,
						'user'=>-1,
						'adv_search' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
		    $id= $this->getRequest()->getParam('id');
		    
			$this->view->adv_search=$search;
			$this->view->rs =$db->getStudentById($id);
			$rs=$this->view->row = $db->getStudentPaymentDetail($id);
			$this->view->service=$db->getStudentServiceUsing($id,$search, 1);
				
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
}

