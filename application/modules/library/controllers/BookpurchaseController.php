<?php
class Library_BookpurchaseController extends Zend_Controller_Action {
private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
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
	    	$db = new Library_Model_DbTable_DbPurchasebook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
		    			'status_search'	=>	-1,
    					'start_date'	=> date('Y-m-d'),
    					'end_date'		=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllPurchase($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("PO_NUMBER","NOTE","DATE_ORDER","USER","STATUS");
			$link=array(
					'module'=>'library','controller'=>'bookpurchase','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_row,array('purchase_no'=>$link,'title'=>$link));
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
    			$db = new Library_Model_DbTable_DbPurchasebook();
    			$db->addPurchaseBook($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/bookpurchase/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/bookpurchase/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	
    	$db=new Library_Model_DbTable_DbPurchasebook();
    	$this->view->po_no=$db->getPONo();
    	
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
    	
    	$book_data = new Library_Model_DbTable_DbBook();
    	$cat_data=new Library_Model_DbTable_DbCategory();
    	$c=$book_data->getCategoryAll();
    	array_unshift($c, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->cat=$c;

    	$block=$book_data->getBlockAll();
    	array_unshift($block, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->block=$block;
    	
    	$book=$cat_data->getAllBookOpt();
    	array_unshift($book, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->book=$book;
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$db = new Library_Model_DbTable_DbPurchasebook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->editPurchaseDetail($_data,$id);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/bookpurchase/index");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			echo $e->getMessage();exit();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	
    	$this->view->row=$db->getPurchaseById($id);
    	$this->view->row_detail=$db->getPurchaseDetailById($id);
    	
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
    	 
    	$book_data = new Library_Model_DbTable_DbBook();
    	$cat_data=new Library_Model_DbTable_DbCategory();
    	$c=$book_data->getCategoryAll();
    	array_unshift($c, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->cat=$c;
    	
    	$block=$book_data->getBlockAll();
    	array_unshift($block, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->block=$block;
    	 
    	$book=$cat_data->getAllBookOpt();
    	array_unshift($book, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
    	$this->view->book=$book;
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
    
    function addBookPurchaseAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbCategory();
    		$gty= $db->ajaxAddBook($data);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    
}

