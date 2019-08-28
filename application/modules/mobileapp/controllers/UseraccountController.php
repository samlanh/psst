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
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> '',
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
				$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link1,'stu_code'=>$link1,'name'=>$link1,'stu_khname'=>$link1,'ប្តូរលេខសម្ងាត់'=>$link,'CHANGE_PASSWORD'=>$link));
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
				$data["id"]=$id;
				$row=$db->updateStudent($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/mobileapp/useraccount");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
// 		$group = $db->getAllgroup();
// 		array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
// 		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
// 		$this->view->group = $group;
		$this->view->rs = $row;
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		array_unshift($row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($row, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_JOB")));
		$this->view->occupation = $row;
}
	
// 	//view detial student by id
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Mobileapp_Model_DbTable_Dbuseraccount();
		$this->view->rs = $db->getStudentViewDetailById($id);
	}
}