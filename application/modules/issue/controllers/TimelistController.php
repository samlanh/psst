<?php
class Issue_TimelistController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbTimeList();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status'=> -1,
					);
			}
			$rs = $db->getAllTimeList($search);
			 
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","VALUE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'timelist','action'=>'edit',
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
				$_dbmodel = new Issue_Model_DbTable_DbTimeList();
				$subject_id = $_dbmodel->addTimeList($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/timelist");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/timelist/add");
				}
				Application_Form_FrmMessage::message($sms);
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$subject_exam=new Issue_Form_FrmItemsTimeList();
		$frm_subject_exam=$subject_exam->FrmAddTimeList();
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$_dbmodel = new Issue_Model_DbTable_DbTimeList();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$subject_id = $_dbmodel->addTimeList($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/timelist");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $_dbmodel->getTimeListByID($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issue/timelist");
			exit();
		}
		$subject_exam=new Issue_Form_FrmItemsTimeList();
		$frm_subject_exam=$subject_exam->FrmAddTimeList($row);
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
	}
}