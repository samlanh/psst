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
						'parent'		=>0,
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
	
	function rptNealydayreturnAction(){
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
						'title'	        =>	'',
						//'cood_book'		=>	0,
						'is_full'	=>	-1,
						'stu_name'	=>	0,
						'parent'		=>0,
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
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
						'title'	        =>	'',
						'stu_name'		=>	0,
						//'status_search'	=>	-1,
						'is_full'	=>	-1,
						'parent'		=>0,
						'start_date'	=>date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->borr_detail= $db->getReturnBookDetail($search);
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
	
	function rptBorrowBookByweekAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						//'cood_book'		=>	0,
						'is_full'	=>	-1,
						'stu_name'	=>	0,
						'parent'		=>0,
// 						'start_date'	=>date('Y-m-d'),
// 						'end_date'		=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->borr_detail= $db->getBorrowDetailByWeek($search);
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
	
	function rptUnavailableAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'cood_book'		=>	0,
						'status_search'	=>	-1,
						'parent'		=>0,
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptLibraryQuery();
			$this->view->book_list= $db->getBookUnavailable($search);
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
	
}
