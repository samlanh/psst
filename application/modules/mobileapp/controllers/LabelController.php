<?php
class Mobileapp_LabelController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
// 	public function indexAction()
// 	{
// 		try{
// 			$db = new Mobileapp_Model_DbTable_Dbuseraccount();
// 			$rs_rows= $db->getAllLabelList($search=null);//call frome model
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("Key Name","Key Value");
// 			$link=array(
// 					'module'=>'mobileapp','controller'=>'label','action'=>'edit',
// 			);
// 			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('keyValue'=>$link,'keyName'=>$link));
// 		}catch (Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
// 		$this->_helper->flashMessenger->addMessage(array("err_message" => 'unable to comply'));
// 	}
	public	function indexAction(){
		try{
			$db = new Mobileapp_Model_DbTable_Dbuseraccount();
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db ->updateMobileLabel($data);
				Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/mobileapp/Label');
			}
			
			$row = array();
			$row['lbl_smspaymentpaid']= $db->getMobileLabel("lbl_smspaymentpaid");
			$row['lbl_smsatt']= $db->getMobileLabel("lbl_smsatt");
			$row['lbl_smsmistake']= $db->getMobileLabel("lbl_smsmistake");
			$row['lbl_smsscore']= $db->getMobileLabel("lbl_smsscore");
			$row['lbl_smsnews']= $db->getMobileLabel("lbl_smsnews");
			$row['lbl_smsnotification']= $db->getMobileLabel("lbl_smsnotification");
			$row['lbl_paymentnotification']= $db->getMobileLabel("lbl_paymentnotification");
			$row['lbl_schoolphone']= $db->getMobileLabel("lbl_schoolphone");
			$row['mockupAppImage']= $db->geLabelByKeyNamesetting("mockupAppImage");
			$row['qrcodeAppLink']= $db->geLabelByKeyNamesetting("qrcodeAppLink");
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

