<?php
class Library_BookController extends Zend_Controller_Action {
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
	    	$db = new Library_Model_DbTable_DbBook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	       =>	'',
		    			'parent'	   =>	0,
		    			'status_search'	=>	-1
	    		);
    	    }
    	    $this->view->book_row=$db->getAllBook($search);
	    	//$glClass = new Application_Model_GlobalClass();
			//$rs_rows = $glClass->getGetPayTerm($rs_row, BASE_URL );
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BOOK_ID","BOOK_NAME","AUTHOR_NAME","SERIAL_NO","CATEGORY","QUANTITY",
// 					"UNIT_PRICE","DATE","USER","NOTE");
// 			$link=array(
// 					'module'=>'global','controller'=>'group','action'=>'edit',
// 			);
// 			$this->view->list=$list->getCheckList(0, $collumns, $rs_row,array('group_code'=>$link,'tuitionfee_id'=>$link,'degree'=>$link,'grade'=>$link));
	    	 
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
    			$db = new Library_Model_DbTable_DbBook();
    			$db->addBook($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/book/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/book/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBook();
    	$this->view->cat=$db_cat->getCategoryAll();
    	
		$frm_major = new Library_Form_FrmBook();
		$frm_search = $frm_major->frmBook();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_book = $frm_search;
		
		$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmCategory();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_cat = $frm_search;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbBook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->editBook($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/book/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/book/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$this->view->cat=$db->getCategoryAll();
    	$row=$db->getBookRowById($id);
    	$this->view->row=$row;
    	
    	$frm_major = new Library_Form_FrmBook();
    	$frm_search = $frm_major->frmBook($row);
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_book = $frm_search;
    	
    	$frm_major = new Library_Form_FrmCategory();
    	$frm_search = $frm_major->FrmCategory();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_cat = $frm_search;
    }
    
    function addCategoryAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_dbmodel = new Library_Model_DbTable_DbCategory();
    		$id = $_dbmodel->ajaxAddCategory($_data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
}

