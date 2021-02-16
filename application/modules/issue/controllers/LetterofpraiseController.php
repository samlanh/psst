<?php
class Issue_LetterofpraiseController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'branch_id' => '',
					'group' => '',
					'language_type'=>0,
					'status' => -1,
				);
			}
			
			$db = new Issue_Model_DbTable_DbLetterofpraise();
			$rs_rows= $db->getAllIssueCertification($search);
			$list = new Application_Form_Frmtable();
				
			$collumns = array("BRANCH","GROUP_CODE","ACADEMIC_YEAR","GROUP","ISSUE_DATE","TYPE","USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'letterofpraise','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link));
			
			$this->view->search = $search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->frm=$form;
	}
	public function addAction(){
		$_db = new Issue_Model_DbTable_DbLetterofpraise();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$checkExist = $_db->checkGroupIssueLetterpraise($_data);
				if(!empty($checkExist)){
					//$sms = "RECORD_EXIST";
					//Application_Form_FrmMessage::Sucessfull($sms,"/issue/letterofpraise");
					///exit();
				}
				$_db->addIssueLetterpraise($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/letterofpraise");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/letterofpraise/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Issue_Form_FrmIssueLetterofpraise();
		$frm->FrmIssueCertificate(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	public function editAction(){
		$_db = new Issue_Model_DbTable_DbLetterofpraise();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="EDIT_SUCCESS";
				$_db->updateIssueLetterpraise($_data);
				Application_Form_FrmMessage::Sucessfull($sms,"/issue/letterofpraise");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row =$_db->getIssueLetterofpraiseById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/letterofpraise");
			exit();
		}
		$this->view->row = $row;
		
		$this->view->rowdetail =$_db->getIssueLetterofpraiseStudent($id);
		
		$frm = new Issue_Form_FrmIssueLetterofpraise();
		$frm->FrmIssueCertificate($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
}