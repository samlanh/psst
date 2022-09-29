<?php
class Issuesetting_SubjectareaController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Issuesetting_Model_DbTable_DbSubjectArea();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status'=> -1,
					);
			}
			$rs = $db->getAllItesmScoreEn($search);
			 
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","TYPE","STATUS");
			$link=array(
					'module'=>'issuesetting','controller'=>'subjectarea','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs,array('title'=>$link,'title_en'=>$link,'subject_titleen'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Issuesetting_Model_DbTable_DbSubjectArea();
				$subject_id = $_dbmodel->addItemsScoreEng($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issuesetting/subjectarea");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issuesetting/subjectarea/add");
				}
				Application_Form_FrmMessage::message($sms);
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$subject_exam=new Issuesetting_Form_FrmSubjectarea();
		$frm_subject_exam=$subject_exam->FrmAddSubjecArea();
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$_dbmodel = new Issuesetting_Model_DbTable_DbSubjectArea();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$subject_id = $_dbmodel->addItemsScoreEng($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issuesetting/subjectarea");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $_dbmodel->getItemsEnByID($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issuesetting/subjectarea");
			exit();
		}
		$subject_exam=new Issuesetting_Form_FrmSubjectarea();
		$frm_subject_exam=$subject_exam->FrmAddSubjecArea($row);
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
}