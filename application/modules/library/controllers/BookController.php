<?php
class Library_BookController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
    					'block_id'	   =>	0,
		    			'status_search'	=>	1
	    		);
    	    }
    	    $rs_rows =$db->getAllBook($search);
    	    $list = new Application_Form_Frmtable();
    	    $collumns = array("BOOK_NAME","AUTHOR_NAME","CATEGORY","BLOCK_NAME","TOTAL","NOT_BORROW","BORROW","BROKEN","CREATE_DATE","USER","STATUS");
    	    $link=array(
    	    		'module'=>'library','controller'=>'book','action'=>'edit',
    	    );
    	    $this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('book_no'=>$link,'title'=>$link));
    	    
//     	    $this->view->book_row=$db->getAllBook($search);
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
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/book/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/book/add");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBook();
    	$cat=$db_cat->getCategoryAll();
    	array_unshift($cat, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->cat=$cat;
    	$block=$db_cat->getBlockAll();
    	array_unshift($block, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->block=$block;
    	
		$frm_major = new Library_Form_FrmBook();
		$frm_search = $frm_major->frmBook();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_book = $frm_search;
		
		$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmCategory();
		$frm_block  = $frm_major->FrmCategory();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		Application_Model_Decorator::removeAllDecorator($frm_block);
		$this->view->frm_cat = $frm_search;
		$this->view->frm_block = $frm_block;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbBook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->editBook($_data,$id);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/book/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/book/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	 
    	$cat=$db->getCategoryAll();
    	array_unshift($cat, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->cat=$cat;
    	$block=$db->getBlockAll();
    	array_unshift($block, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->block=$block;
    	
    	$row=$db->getBookRowById($id);
    	$this->view->row=$row;
    	$this->view->row_detail=$db->getBookRowDetailById($id);;
    	
    	$frm_major = new Library_Form_FrmBook();
    	$frm_search = $frm_major->frmBook($row);
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_book = $frm_search;
    	
    	$frm_major = new Library_Form_FrmCategory();
    	$frm_search = $frm_major->FrmCategory();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_cat = $frm_search;
    }
    
    
	public function copyAction(){
		$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbBook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->addBook($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/book/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/book/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	 
    	$cat=$db->getCategoryAll();
    	array_unshift($cat, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->cat=$cat;
    	$block=$db->getBlockAll();
    	array_unshift($block, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->block=$block;
    	
    	$row=$db->getBookRowById($id);
    	$this->view->row=$row;
    	$this->view->row_detail=$db->getBookRowDetailById($id);;
    	
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
	
	function addBlockAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Library_Model_DbTable_DbCategory();
			$id = $_dbmodel->ajaxAddBlock($_data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

