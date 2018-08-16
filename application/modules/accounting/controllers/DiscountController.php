<?php
class Global_DiscountController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
 			$db = new Global_Model_DbTable_DbDiscount();
 			$rs_rows= $db->getAllDocument($search);
 			$glClass = new Application_Model_GlobalClass();
 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("OCCUPATION_KHNAME","CREATED_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'document','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('doc_name'=>$link,'doc_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchDocument();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->frm_search = $frms;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Global_Model_DbTable_DbDiscount();
				$_occupa = $_dbmodel->addNewDocument($_data);
				if($_occupa==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/document");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/global/document/add");
				}
				Application_Form_FrmMessage::message($sms);				
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbDiscount();
				$db->updateDocument($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/document/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbDocument();
		$this->view->rs=$db->getDocumentById($id);
	}
	function addcompositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbDiscount();
			$id = $db->addDocumenttion($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
}

