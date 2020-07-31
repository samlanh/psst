<?php
class Mobileapp_IntroductionController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

	public	function indexAction(){
		try{
			$db = new Mobileapp_Model_DbTable_Dbuseraccount();
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db ->updateMobileIntroduction($data);
				Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/mobileapp/introduction');
			}
			
			$row = array();
			$row['lbl_introduction']= $db->getMobileLabel("lbl_introduction");
			$row['lbl_introduction_i']= $db->getMobileLabel("lbl_introduction_i");
			$row['introduction_image']= $db->getMobileLabel("introduction_image");
			$this->view->row =$row;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function addAction(){
		$this->_redirect('/mobileapp/index');
		
	}
	function editAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db  = new Mobileapp_Model_DbTable_Dbuseraccount();
			$db->updateLabel($data);
			Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS','/mobileapp/Label');
		}
		$key = new Mobileapp_Model_DbTable_Dbuseraccount();
		$id = $this->getRequest()->getParam('id');
		$rs = $key->getLabelVaueById($id);
		$this->view->rs= $rs;
	}
	function getAllSqlTruncateAction(){
		
	}
	
}

