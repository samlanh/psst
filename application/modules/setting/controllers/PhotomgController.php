<?php
class Setting_PhotomgController extends Zend_Controller_Action {
	const REDIRECT_URL='/setting';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
   
		
		
    	$db = new Setting_Model_DbTable_DbPhotoMg();
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/register/index");
					exit();
				}
				
				$exist = $db->addPhotoMg($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/setting/photomg");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
		$tsub=new Setting_Form_FrmPhotoMg();
		$frm=$tsub->FrmPhotoMg();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	function getRecordListAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Setting_Model_DbTable_DbPhotoMg();
			
			$rows = $db->getAllListRecord($data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
}