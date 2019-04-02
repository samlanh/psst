<?php
class Allreport_LibraryController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	
	public function indexAction(){
	}
	
	function rptBookListAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'cood_book'		=>	0,
						'block_id'		=>  0,
						'status_search'	=>	-1,
						'parent'		=>  0,
						'is_borrow'		=>  -1,
						'is_broken'		=>  -1,
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->book_list= $db->getAllBookList($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptBookdetailByidAction(){
		try{
			$id = $this->getRequest()->getParam("id");
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->book_list = $db->getAllBookDetailById($id);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	
	function rptBookListDetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'cood_book'		=>	0,
						'block_id'		=>  0,
						'status_search'	=>	-1,
						'parent'		=>  0,
						'is_borrow'		=>  -1,
						'is_broken'		=>  -1,
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->book_list = $db->getAllBookListDetail($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptBorrowdetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        => '',
						'is_type_bor'	=> 0,
						'student_name'	=> 0,
						'cood_book'		=> 0,
						'parent'		=> 0,
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
						'is_return'		=> -1,
						'is_broken'		=> -1,
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->borr_detail= $db->getBorrowDetail($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptReturndetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        => '',
						'student_name'	=> 0,
						'is_type_bor'	=> 0,
				        'cood_book'		=> 0,
						'parent'		=> 0,
						'start_date'	=> date('Y-m-d'),
						'end_date'		=> date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->return_detail= $db->getReturnBookDetail($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptPurchasedetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'block_id'		=>	0,
						'cood_book'		=>	0,
						'parent'		=> 0,
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->pur_detail= $db->getPurchaseDetail($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptBrokendetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'cood_book'		=>	0,
						'block_id'	=>	0,
						'parent'		=>0,
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->broken_detail= $db->getBrokenDetail($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
	function rptNealydayreturnAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'title'	        =>	'',
						'cood_book'		=>	0,
						'is_type_bor'	=>  0,
						'student_name'	=>	0,
						'status_search'	=>	-1,
						'end_date'		=>date('Y-m-d')
				);
			}
			$db = new Library_Model_DbTable_DbNeardayreturnbook();
			$abc = $this->view->row = $db->getReturnBookLate($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			echo $e->getMessage();
		}
			
		$this->view->search = $search;
		$frm_major = new Library_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
	}
	
}
