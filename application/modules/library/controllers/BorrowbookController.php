<?php
class Library_BorrowbookController extends Zend_Controller_Action {
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
	    	$db = new Library_Model_DbTable_DbBorrowbook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
		    			'parent'	    =>	0,
    					'student_name'	=>	0,
    					'is_type_bor'	=>	0,
		    			'status_search'	=>	1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllBorrow($search);
	    	$glClass = new Application_Model_GlobalClass();
			//$rs_rows = $glClass->getGetPayTerm($rs_row, BASE_URL );
			$list = new Application_Form_Frmtable();
			$collumns = array("BORROW_NO","BORROWER","CODE","NAME","PHONE","BORROW_DATE","RETURN_DATE","NOTE","USER",
					"STATUS");
			$link=array(
					'module'=>'library','controller'=>'borrowbook','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_row,array('borrow_no'=>$link,'stu_code'=>$link,'stu_name'=>$link));
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
    			$db = new Library_Model_DbTable_DbBorrowbook();
    			$db->addBorrowBook($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/borrowbook/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/borrowbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db = new Library_Model_DbTable_DbBorrowbook();
    	$this->view->stu_id=$db->getAllStudentId(1);
    	$this->view->stu_name=$db->getAllStudentId(2);
    	$b=$this->view->book_title=$db->getBookTitle();
    	$this->view->borr_no=$db->getBorrowNo();
    	
    	$this->view->book_detail=$db->getBookDetail();
     
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
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Library_Model_DbTable_DbBorrowbook();
    			$db->editBorrowBook($_data,$id);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/borrowbook/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/borrowbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
    	$this->view->stu_id=$db_cat->getAllStudentId(1);
    	$this->view->stu_name=$db_cat->getAllStudentId(2);
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	$this->view->borr_no=$db_cat->getBorrowNo();
    	
    	$this->view->book_detail=$db_cat->getBookDetail();
    	
    	$this->view->row=$db_cat->getBorrowById($id);
    	$this->view->row_detail=$db_cat->getBorrowDetailById($id);
    	 
    	$frm_major = new Library_Form_FrmBook();
    	$frm_search = $frm_major->frmBook();
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
    
    function getBookdetailAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbBorrowbook();
    		$book= $db->getBookDetail($data['book_id'],$data['type']); // type => 1=borrow , 2=return
    		print_r(Zend_Json::encode($book));
    		exit();
    	}
    }
    
}

