<?php
class Library_ReturnbookController extends Zend_Controller_Action {
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
	    	$db = new Library_Model_DbTable_DbReturnbook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
		    			'parent'	    =>	0,
    					//'stu_name'	    =>	0,
    					'is_type_bor'	=>0,
		    			'status_search'	=>	-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllReturnBook($search);
	    	$glClass = new Application_Model_GlobalClass();
			//$rs_rows = $glClass->getGetPayTerm($rs_row, BASE_URL );
			$list = new Application_Form_Frmtable();
			$collumns = array("RETURN_NO","IS_TYPE","CODE","NAME","RETURN_DATE","NOTE","USER",
					"STATUS");
			$link=array(
					'module'=>'library','controller'=>'returnbook','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_row,array('return_no'=>$link,'borrow_type'=>$link,'card_id'=>$link,'name'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm_major = new Library_Form_FrmSearchMajor();
    	$frm_search = $frm_major->frmSearchReturn();
    	Application_Model_Decorator::removeAllDecorator($frm_search);
    	$this->view->frm_search = $frm_search;
    	
    	$frm_bor=new Library_Form_FrmSearchMajor();
    	$search=$frm_bor->FrmMajors();
    	Application_Model_Decorator::removeAllDecorator($search);
    	$this->view->frm_bor=$search;
    	
    }
    
    public function addAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db = new Library_Model_DbTable_DbReturnbook();
    			$db->addReturnBook($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/returnbook/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/returnbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbReturnbook();
    	$this->view->stu_id=$db_cat->getAllBorrowId(1);
    	$this->view->stu_name=$db_cat->getAllBorrowId(2);
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	$this->view->borr_no=$db_cat->getReturnBookNo();
     
		$frm_major = new Library_Form_FrmBookreturn();
		$frm_search = $frm_major->frmBook();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_book = $frm_search;
		
		$frm_major = new Library_Form_FrmCategory();
		$frm_search = $frm_major->FrmCategory();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_cat = $frm_search;
    }
    
    public function editAction(){
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		//print_r($_data);exit();
    		$_data['id']=$id;
    		try {
    			$db = new Library_Model_DbTable_DbReturnbook();
    			$db->updateReturnBook($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/returnbook/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/library/returnbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbBorrowbook();
//     	$this->view->stu_id=$db_cat->getAllStudentId(1);
//     	$this->view->stu_name=$db_cat->getAllStudentId(2);
    	$db_cat = new Library_Model_DbTable_DbReturnbook();
    	$this->view->stu_id=$db_cat->getAllBorrId(1);
    	$this->view->stu_name=$db_cat->getAllBorrId();
    	
    	$b=$this->view->book_title=$db_cat->getBookTitle();
    	$this->view->borr_no=$db_cat->getBorrowNo();
    	
    	$db = new Library_Model_DbTable_DbReturnbook();
        $this->view->rs_return=$db->getReturnBookById($id);
    	//$this->view->rs_detail=$db_cat->getReturnDetailById($id);
     
		$frm_major = new Library_Form_FrmBookreturn();
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
    
    function getBookqtyAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbCategory();
    		$gty= $db->getBookQty($data['book_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    
    }
    
    function getReturnBookAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbCategory();
    		$gty= $db->getReturnBook($data['stu_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    
    }
    
    function getReturnBookDetailAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbCategory();
    		$gty= $db->getReturnBookDetail($data['id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    
    }
    
}

