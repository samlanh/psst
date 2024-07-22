<?php
class Global_FamilytypeController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
			$search["type"] = 41;
 			$db = new Global_Model_DbTable_DbStuentType();
 			$rs_rows= $db->getAllViewItemsByType($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("SHORTCUT","KH_NAME","NAME_EN","STATUS");
			$link=array(
					'module'=>'global','controller'=>'familytype','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name_kh'=>$link,'name_en'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_db = new Global_Model_DbTable_DbStuentType();
				$_data["type"] = 41;
				$_db->addViewItemsByType($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/familytype");
						
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		$type = 41;
		if($this->getRequest()->isPost())
		{
			try{
				$_data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbStuentType();
				$_data["type"] = $type;
				$db->updateViewItemsByType($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/familytype/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$db = new Global_Model_DbTable_DbStuentType();
		$arr = array(
			'id' => $id
			,'type' => $type
		);
		$this->view->rs=$db->getViewItemsByTypeInfo($arr);
	}
	
}