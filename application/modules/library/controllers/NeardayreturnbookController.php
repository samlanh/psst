<?php
class Library_NeardayreturnbookController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
    					'cood_book'	=>	0,
    					'stu_name'	=>	0,
		    			'status_search'	=>	-1,
    					'end_date'=>date('Y-m-d') 
	    		);
    	    }
    	    $db = new Library_Model_DbTable_DbNeardayreturnbook();
    	    $abc = $this->view->row = $db->getReturnBookLate($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
		
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	public function addAction(){
		$this->_redirect('registrar/studentpaymentlate/index');
	}
	
}

