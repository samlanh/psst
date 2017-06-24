<?php
class Library_IndexController extends Zend_Controller_Action {
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
				 
				$db=new Library_Model_DbTable_DbBook();
				$data=$db->getTotalBookEmpty();
				$this->view->rs=$db->getTotalBookEmpty();
				$this->view->rs_notreturn=$db->getBookNotReturn();
				$this->view->rs_borr=$db->getBorrowThisDay();
				$this->view->rs_return=$db->getReturnThisDay();
			}
			 
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function addAction(){
		$this->_redirect('library/');
	}
}

