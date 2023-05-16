<?php
class Library_BrokenbookController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	try{
	    	$db = new Library_Model_DbTable_DbBrokenbook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
		    			'status_search'	=>	-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllBroken($search);
	    	$glClass = new Application_Model_GlobalClass();
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BOOK_NO","NOTE","BROKEN_DATE","USER","STATUS");
			$link=array(
					'module'=>'library','controller'=>'brokenbook','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_row,array('broke_no'=>$link,'date_broken'=>$link,'qty'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
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
    			$db = new Library_Model_DbTable_DbBrokenbook();
    			$db->addBrokenBook($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/brokenbook/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/brokenbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	
    	$db = new Library_Model_DbTable_DbBrokenbook();
    	$this->view->bro_no=$db->getBrokenNo();
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbBrokenbook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->editBrokenBook($_data,$id);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/brokenbook/index");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			echo $e->getMessage();
    		}
    	}
    	$this->view->row=$db->getBrokenById($id);
    	$this->view->row_detail=$db->getBrokenDetailById($id);
    	
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$db_cat->getBookTitle();
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
    
    function getBookqtyAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbCategory();
    		$gty= $db->getBookQty($data['book_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    
    }
    
}

