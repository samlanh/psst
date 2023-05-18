<?php
class Library_BlockController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
	    	$db = new Library_Model_DbTable_DbBlock();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	       =>	'',
		    			'status_search'	=>	-1
	    		);
    	    }
	    	$rows = $db->getAllBlock($search);
    				$list = new Application_Form_Frmtable();
    				$collumns = array("BLOCK_NAME","REMARK","USER","STATUS");
    				$link=array(
    						'module'=>'library','controller'=>'block','action'=>'edit', 
    				);
    				$this->view->list=$list->getCheckList(0, $collumns, $rows,array('block_name'=>$link,'remark'=>$link,'date'=>$link));
	    	 
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm_major = new Library_Form_FrmSearchMajor();
    	$frm_search = $frm_major->FrmMajors();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_search = $frm_search;
    }
    
    
    public function addAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Library_Model_DbTable_DbBlock();
    			$db->addBlock($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/block/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/block/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    			
    	}
    	
		$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmBlock();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_cat = $frm_search;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbBlock();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->updateBlock($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/block/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/block/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    		 
    	}
    	
    	$row=$db->getBlockByid($id);
    	$frm_major = new Library_Form_FrmCategory();
    	$frm_search = $frm_major->FrmBlock($row);
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_cat = $frm_search;
    }
    
}

