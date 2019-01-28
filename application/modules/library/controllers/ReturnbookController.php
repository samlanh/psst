<?php
class Library_ReturnbookController extends Zend_Controller_Action {
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
	    	$db = new Library_Model_DbTable_DbReturnbook();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
		    	
    	   	}else{
    			$search = array(
	    				'title'	        =>	'',
		    			'parent'	    =>	0,
    					'student_name'	=>	0,
    					'is_type_bor'	=>0,
		    			'status_search'	=>	-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllReturnBook($search);
	    	$glClass = new Application_Model_GlobalClass();
			$list = new Application_Form_Frmtable();
			$collumns = array("RETURN_NO","RETURN_DATE","NOTE","USER","STATUS");
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
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/returnbook/add");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/returnbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$db_cat = new Library_Model_DbTable_DbReturnbook();
    	$this->view->borr_no=$db_cat->getReturnBookNo();
     
    	$_db = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$_db->getBookTitle();
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
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/returnbook/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/returnbook/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
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
    
    function getBookdetailAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Library_Model_DbTable_DbReturnbook();
    		$book = $db->getBookDetail($data['book_id']); // type => 1=borrow , 2=return
    		print_r(Zend_Json::encode($book));
    		exit();
    	}
    }
    
}

