<?php
class Issue_IndexController extends Zend_Controller_Action {
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
			$db = new Issue_Model_DbTable_DbCertification();
			$rs_rows= $db->getAllIssueCertification($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","GROUP_CODE","FACULTY_KHNAME","FROM_DATE","TO_DATE","ISSUE_DATE","TYPE","USER","STATUS");
			$link=array(
					'module'=>'issuesetting','controller'=>'index','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link,'dept_kh'=>$link,'program_kh'=>$link));
			
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
		$_db = new Issue_Model_DbTable_DbCertification();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$checkExist = $_db->checkGroupIssueCertificate($_data);
				if(!empty($checkExist)){
					$sms = "RECORD_EXIST";
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/index");
					exit();
				}
				$_db->addIssueCertificate($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/index");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/index/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Issue_Form_FrmIssueCertificate();
		$frm->FrmIssueCertificate(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
  }
public function editAction(){
		$_db = new Issue_Model_DbTable_DbCertification();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="EDIT_SUCCESS";
				$_db->updateIssueCertificate($_data);
				Application_Form_FrmMessage::Sucessfull($sms,"/issue/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row =$_db->getIssueCertificationById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/issue/index");
			exit();
		}
		$this->view->row = $row;
		
		$this->view->rowdetail =$_db->getIssueCetifStudent($id);
		
		$frm = new Issue_Form_FrmIssueCertificate();
		$frm->FrmIssueCertificate($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function getgroupcompleteAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			
			$branch_id = empty($data['branch_id'])?0:$data['branch_id'];
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$group = $_dbgb->getAllCompleteGroupByBranch($branch_id);
			
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	
	function getAllStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$student = $db->getAllPassStudentGroup($data['group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	function getgroupinfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$student = $db->getStudentGroupInfoById($data['group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
}

