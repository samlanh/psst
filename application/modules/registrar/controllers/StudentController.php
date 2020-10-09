<?php
class Registrar_StudentController extends Zend_Controller_Action {	
	
 public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	function indexAction(){
		try{
			$db = new Registrar_Model_DbTable_DbNewStudent();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => '-1',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$rs_rows= $db->getAllNewStudent($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("YEARS","DEGREE","GRADE","STUDENT_NAME","SEX","TEL","EMAIL","USER","STATUS");
			$link=array(
					'module'=>'registrar','controller'=>'student','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name'=>$link,'degree'=>$link,'tuitionfee_id'=>$link,));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form = new Registrar_Form_FrmSearchexpense();
    	$frm = $form->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
	}
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel = new Registrar_Model_DbTable_DbNewStudent();
    			$_dbmodel->addNewStudent($_data);
    			if(!empty($_data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/student");
    			}else{
    				Application_Form_FrmMessage::message("INSERT_SUCCESS");
    			}
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
		
		$frm_cal = new Registrar_Form_FrmNewStudent();
		$myform = $frm_cal -> FrmAddNewStudent();
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm = $myform;
    }
    public function editAction()
    {
    	$_dbmodel = new Registrar_Model_DbTable_DbNewStudent();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$_dbmodel->addNewStudent($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/registrar/student");
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$id=$this->getRequest()->getParam('id');
    	$row = $_dbmodel->getNewStudentById($id);
    	$this->view->row = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/registrar/student");
			exit();
		}
		$frm_cal = new Registrar_Form_FrmNewStudent();
		$myform = $frm_cal -> FrmAddNewStudent($row);
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm = $myform;
    }
}