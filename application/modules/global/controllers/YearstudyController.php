<?php
class Global_YearstudyController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
					'title' => $_data['title'],
					'status' => $_data['status_search']
				);
			}
			else{
				$search = array(
					'title' => '',
					'status' => -1
				);
			}
 			$db = new Accounting_Model_DbTable_DbYearStudy();
 			$rs_rows= $db->getAllYearStudy($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("FROM_YEAR","TO_YEAR","CREATED_DATE","BY_USER","STATUS");
			$link=array(
				'module'=>'global','controller'=>'yearstudy','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('fromYear'=>$link,'toYear'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Accounting_Model_DbTable_DbYearStudy();
				$_discount = $_dbmodel->addNewAcademicYear($_data);
				if($_discount==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/yearstudy");
				}
				Application_Form_FrmMessage::Sucessfull($sms,"/global/yearstudy/add");			
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		
		
		$tsub=new Accounting_Form_FrmYearStudy();
		$frm=$tsub->FrmYearStudy();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$db = new Accounting_Model_DbTable_DbYearStudy();

		if($this->getRequest()->isPost()){
			$_dbmodel = new Accounting_Model_DbTable_DbYearStudy();
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				
				$_discount = $_dbmodel->updateAcademicYear($_data);
				if($_discount==-1){
					$sms = "RECORD_EXIST";
				}
				Application_Form_FrmMessage::Sucessfull($sms,"/global/yearstudy");	
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$row=$db->getAcademicYearById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/global/yearstudy");
			exit();
		}
		
		$tsub=new Accounting_Form_FrmYearStudy();
		$frm=$tsub->FrmYearStudy($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
}