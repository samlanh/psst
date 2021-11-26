<?php
class Scan_EntranceController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1);
			}
 			$db = new Scan_Model_DbTable_DbEntrance();
 			$rs_rows= $db->getAllEntrance($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE_KH","TITLE_EN","CREATE_DATE","USER","STATUS");
			$link=array(
					'module'=>'scan','controller'=>'entrance','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('titleKh'=>$link,'titleEn'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Scan_Model_DbTable_DbEntrance();
				$_discount = $_dbmodel->addEntrance($_data);
				
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/scan/entrance");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/scan/entrance/add");
				}
				Application_Form_FrmMessage::message($sms);				
			}catch(Exception $e){
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
				$db = new Scan_Model_DbTable_DbEntrance();
				$db->addEntrance($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/scan/entrance/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id=empty($id)?0:$id;
		$db = new Scan_Model_DbTable_DbEntrance();
		$this->view->rs=$db->getEntranceById($id);
	}
	
}