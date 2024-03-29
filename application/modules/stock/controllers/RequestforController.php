<?php
class Stock_RequestforController extends Zend_Controller_Action {
	protected $tr;
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'title'	=>'',
				);
    		}
			$db = new Stock_Model_DbTable_DbRequestfor();
			$rs_rows = $db->getAllRequestFor($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("TITLE","CREATE_DATE","STATUS","USER");
    		$link=array(
    				'module'=>'stock','controller'=>'requestfor','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('title'=>$link));
    		$this->view->search = $search;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
		}
	}
    public function addAction()
    {
    	$db = new Stock_Model_DbTable_DbRequestfor();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
			$exist = $db->checkCheckRequestFor($_data);
    		try{
				if(empty($exist)){
					$db->addNewRequestFor($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/stock/requestfor/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("Already Exist!", "/stock/requestfor/add", 2);
				}
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
	public function editAction(){
    	$db = new Stock_Model_DbTable_DbRequestfor();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
			$exist = $db->checkCheckRequestFor($_data);
    		try {
				if(empty($exist)){
					$db->updateNewRequestFor($_data,$id);
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/stock/requestfor/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("Already Exist!", "/stock/requestfor/index");
				}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$this->view->row = $db->getRequestforById($id);
    }
}