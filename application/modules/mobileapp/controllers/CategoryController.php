<?php

class Mobileapp_CategoryController extends Zend_Controller_Action
{
	const REDIRECT_URL='/mobileapp/category';
	protected $tr;
    public function init()
    {       
        /* Initialize action controller here */
        header('content-type: text/html; charset=utf8');
        defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
        $this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }

public function indexAction(){
		try{
			$db = new Mobileapp_Model_DbTable_DbCategory();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' 		=> '',
						'status_search' 	=> '-1',
						'start_date' 		=> date("Y-m-d"),
						'end_date' 			=> date("Y-m-d"));
			}
			$rs_rows= $db->getAllArticle($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'category','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link,'branch_name'=>$link,'zone_num'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm1 = new Mobileapp_Form_FrmNews();
	   	$frm=$frm1->FrmAddNews();
	   	Application_Model_Decorator::removeAllDecorator($frm);
	   	$this->view->frm_new = $frm;
	}
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		try{
	   			$_data = $this->getRequest()->getPost();
	   			$db = new Mobileapp_Model_DbTable_DbCategory();
	   			$db->addCategory($_data);
	   			if(!empty($_data['save_new'])){
	   				 Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL."/add");
	   			}else{
	   				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
	   			}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
   	
   		$dbglobal = new Application_Model_DbTable_DbGlobal();
   		$this->view->lang = $dbglobal->getLaguage();
   }
   function editAction(){
	   	$db = new Mobileapp_Model_DbTable_DbCategory();
	   	$id = $this->getRequest()->getParam('id');
	   	$id = empty($id)?0:$id;
  	 	if($this->getRequest()->isPost()){
	   		try{
	   			$_data = $this->getRequest()->getPost();
	   			$db->addCategory($_data);
	   			Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	$row = $db->getCategoryById($id);
	   	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   		exit();
	   	}
	   	$this->view->row = $row;
	   	$this->view->id = $id;
	   	
   	
	   	$dbglobal = new Application_Model_DbTable_DbGlobal();
	   	$this->view->lang = $dbglobal->getLaguage();
   }
   function copyAction(){
	   	$db = new Mobileapp_Model_DbTable_DbCategory();
	   	$id = $this->getRequest()->getParam('id');
	   	if($this->getRequest()->isPost()){
	   		try{
	   			$_data = $this->getRequest()->getPost();
	   			$db->addCategory($_data);
	   			Application_Form_FrmMessage::Sucessfull($this->tr->translate('COPY_SUCCESS'), self::REDIRECT_URL);
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	   			$err = $e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	$row = $db->getCategoryById($id);
	   	$this->view->row = $row;
	   	$this->view->id = $id;
	   	 
	   
	   	$dbglobal = new Application_Model_DbTable_DbGlobal();
	   	$this->view->lang = $dbglobal->getLaguage();
   }
   function deleteAction(){
	   	try{
	   		$request=Zend_Controller_Front::getInstance()->getRequest();
	   		$action=$request->getActionName();
	   		$controller=$request->getControllerName();
	   		$module=$request->getModuleName();
	   
	   		$dbacc = new Application_Model_DbTable_DbUsers();
	   		$rs = $dbacc->getAccessUrl($module,$controller,'delete');
	   		if(!empty($rs)){
	   			$id = $this->getRequest()->getParam('id');
	   			$db = new Mobileapp_Model_DbTable_DbCategory();
	   			if(!empty($id)){
	   				$db->deleteNews($id);
	   				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS",self::REDIRECT_URL);
	   			}
	   		}
	   		Application_Form_FrmMessage::Sucessfull("You no permission to delete",self::REDIRECT_URL);
	   	}catch (Exception $e){
	   		Application_Form_FrmMessage::message("Application Error");
	   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   	}
   }

}







