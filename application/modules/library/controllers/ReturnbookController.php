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
		    			'status_search'	=>	-1,
    					'start_date'	=> date('Y-m-d'),
    					'end_date'		=>date('Y-m-d') 
	    		);
    	    }
    	    $rs_row=$db->getAllReturnBook($search);
	    	$glClass = new Application_Model_GlobalClass();
			$list = new Application_Form_Frmtable();
			$collumns = array("RETURN_NO","NOTE","RETURN_DATE","USER","STATUS");
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
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/library/returnbook/add");
    			
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
    	$db = new Library_Model_DbTable_DbReturnbook();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->updateReturnBook($_data,$id);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/library/returnbook/index");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			echo $e->getMessage();exit();
    		}
    	}
    	
        $this->view->row=$db->getReturnBookById($id);
    	$this->view->row_detail=$db->getReturnDetailById($id);
     
    	$_db = new Library_Model_DbTable_DbBorrowbook();
    	$b=$this->view->book_title=$_db->getBookTitle();
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

