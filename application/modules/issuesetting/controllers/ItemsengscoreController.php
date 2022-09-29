<?php
class Issuesetting_ItemsengscoreController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Issuesetting_Model_DbTable_DbItemsScoreEng();
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
			$collumns = array("ITEMS_ENG_KH","ITEMS_ENG_EN","NOTE","STATUS");
			$link=array(
					'module'=>'issuesetting','controller'=>'itemsengscore','action'=>'edit',
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
				$_dbmodel = new Issuesetting_Model_DbTable_DbItemsScoreEng();
				$subject_id = $_dbmodel->addItemsScoreEng($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issuesetting/itemsengscore");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issuesetting/itemsengscore/add");
				}
				Application_Form_FrmMessage::message($sms);
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$subject_exam=new Issuesetting_Form_FrmItemsScoreEngExam();
		$frm_subject_exam=$subject_exam->FrmAddItemsScoreExam();
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$_dbmodel = new Issuesetting_Model_DbTable_DbItemsScoreEng();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$subject_id = $_dbmodel->addItemsScoreEng($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issuesetting/itemsengscore");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $_dbmodel->getItemsEnByID($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issuesetting/itemsengscore");
			exit();
		}
		$this->view->row = $row;
		$subject_exam=new Issuesetting_Form_FrmItemsScoreEngExam();
		$frm_subject_exam=$subject_exam->FrmAddItemsScoreExam($row);
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
}