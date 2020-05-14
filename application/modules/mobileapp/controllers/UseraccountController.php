<?php
class Mobileapp_UseraccountController extends Zend_Controller_Action {
	
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
						'academic_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> '-1',
					);
				
			}
			$this->view->adv_search=$search;
			$db_student= new Mobileapp_Model_DbTable_Dbuseraccount();
			$rs_rows = $db_student->getAllStudent($search);
			$list = new Application_Form_Frmtable();
				$collumns = array("BRANCH","STUDENT_ID","NAME_KH","NAME_EN","SEX","PHONE","ACADEMIC_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","CHANGE_PASSWORD","STATUS","MOBILE_DEVICE");
				$link=array(
						'module'=>'mobileapp','controller'=>'useraccount','action'=>'edit',
				);
				$link1=array(
						'module'=>'mobileapp','controller'=>'useraccount','action'=>'view',
				);
				$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,
						array(
								'ប្តូរលេខសម្ងាត់'=>$link,
								'CHANGE_PASSWORD'=>$link));//'branch_name'=>$link1,'stu_code'=>$link1,'name'=>$link1,'stu_khname'=>$link1,
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		$this->_redirect("mobileapp/useraccount");
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Mobileapp_Model_DbTable_Dbuseraccount();
		$row = $db->getStudentById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/mobileapp/useraccount");
		}
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$row=$db->updateStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/mobileapp/useraccount");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->rs = $row;
		
}
	
// 	//view detial student by id
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
// 		$db= new Mobileapp_Model_DbTable_Dbuseraccount();
// 		$this->view->rs = $db->getStudentViewDetailById($id);
	}
}