<?php
class Library_BookcategoryController extends Zend_Controller_Action {
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
	    	$db = new Library_Model_DbTable_DbCategory();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	       =>	'',
		    			'parent'	   =>	'',
		    			'status_search'	=>	-1
	    		);
    	    }
	    	$rows = $db->getAllCategoryVandy(0,'','','',$search);
	    	
		    $this->view->cate=$rows;
	    	 
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
    			$db = new Library_Model_DbTable_DbCategory();
    			$db->add($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/bookcategory/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/bookcategory/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    			
    	}
		$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmCategory();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_cat = $frm_search;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbCategory();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->edit($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/bookcategory/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/bookcategory/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	
    	$row=$db->getCategoryById($id);
    	$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmCategory($row);
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_cat = $frm_search;
    }
}

