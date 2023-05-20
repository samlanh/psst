<?php
class Scan_StudentsettimeController extends Zend_Controller_Action {
    public function init()
    {    	
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
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status' => -1,
						
						);
			}
 			$db = new Scan_Model_DbTable_DbStudentSetTime();
 			$rs_rows= $db->getAllSetLockTimeStudent($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_EN","SEX","GROUP","USER","FROM_TIME","TO_TIME");
			$link=array(
					'module'=>'scan','controller'=>'studentsettime','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('titleKh'=>$link,'titleEn'=>$link));
			$this->view->search=$search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Scan_Model_DbTable_DbStudentSetTime();
				$_discount = $_dbmodel->addSetLockTimeStudent($_data);
				Application_Form_FrmMessage::Sucessfull($sms,"/scan/studentsettime");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $_db->getAllBranch();
	}
}