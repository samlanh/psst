<?php

class Home_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/home';
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$user = $dbgb->getUserInfo();
    	if ($user['level']!=1){
    		$this->_redirect("/home/index/dashboard");
    	}
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		print_r($post);exit();
    	}
      $db = new Allreport_Model_DbTable_DbRptAllStudent();
      $this->view->rsamountstudent = $db->getAmountStudent();
      $this->view->rsnewstudent = $db->getAmountNewStudent();
      $this->view->rsdropstudent = $db->getAmountDropStudent();
      $this->view->rsteststudent = $db->getAmountStudentTest();
      $this->view->rsteststuregistered = $db->getAmountStudentTestRegistered();
      $this->view->rsupdateresult = $db->getAmountStudentUpdateresult();
      
      $_db = new Allreport_Model_DbTable_DbRptIncomeExpense();
      $this->view->totalExpense = $_db->getAmountExpest();
      $this->view->totalIncome = $_db->getTotalIncome();
      
      $_db = new Home_Model_DbTable_DbDashboard();
      $this->view->studropnew = $_db->getStudentDropNew();
      $this->view->alltypedrop = $_db->countStudentDrop();
      $this->view->stu_suppend = $_db->countStudentDrop(2);
      $this->view->stu_stopped = $_db->countStudentDrop(1);
      
      $this->view->dissetting = $_db->getSettingDiscountNearlyExpire();
      
      $ddgb = new Application_Model_DbTable_DbGlobal();
      $this->view->news = $ddgb->getAllNew(10);
      $notread = $ddgb->getNewNotreadByUser();
      $this->view->notread = empty($notread)?0:$notread;
      
      $db_yeartran = new Allreport_Model_DbTable_DbRptAllStudent();
      $this->view->yearly = $db_yeartran->getAllYearTuitionfee(6);
    }

    public function dashboardAction()
    {
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		print_r($post);exit();
    	}
    	
    	$param = $this->getRequest()->getParams();
    	$_db = new Home_Model_DbTable_DbDashboard();
    	if(isset($param['search'])){
    		$search=$param;
    		
    		$rs_rows = $_db->getSpecailDiscount($search);
    		$paginator = Zend_Paginator::factory($rs_rows);
    		$paginator->setDefaultItemCountPerPage(15);
    		$allItems = $paginator->getTotalItemCount();
    		$countPages= $paginator->count();
    		$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
    		 
    		if(isset($p))
    		{
    			$paginator->setCurrentPageNumber($p);
    		} else $paginator->setCurrentPageNumber(1);
    		
    		$currentPage = $paginator->getCurrentPageNumber();
    		 
    		$this->view->sepcialdiscount  = $paginator;
    		$this->view->countItems = $allItems;
    		$this->view->countPages = $countPages;
    		$this->view->currentPage = $currentPage;
    		
    		if($currentPage == $countPages)
    		{
    			$this->view->nextPage = $countPages;
    			$this->view->previousPage = $currentPage-1;
    		}
    		else if($currentPage == 1)
    		{
    			$this->view->nextPage = $currentPage+1;
    			$this->view->previousPage = 1;
    		}
    		else {
    			$this->view->nextPage = $currentPage+1;
    			$this->view->previousPage = $currentPage-1;
    		}
    	}
    	else{
    		$search = array(
    				'advance_search'=> '',
    				'dis_type'=> '',
    				'status_type' => '',
    				);
    	}
    	$this->view->search=$search;
    	
    	$this->view->studropnew = $_db->getStudentDropNew();
    	$this->view->alltypedrop = $_db->countStudentDrop();
    	$this->view->stu_suppend = $_db->countStudentDrop(2);
    	$this->view->stu_stopped = $_db->countStudentDrop(1);
    	
    	$db = new Allreport_Model_DbTable_DbRptAllStudent();
    	$this->view->rsamountstudent = $db->getAmountStudent();
    	$this->view->rsnewstudent = $db->getAmountNewStudent();
    	$this->view->rsdropstudent = $db->getAmountDropStudent();
    	
    	$ddgb = new Application_Model_DbTable_DbGlobal();
    	$this->view->news = $ddgb->getAllNew(10);
    	$notread = $ddgb->getNewNotreadByUser();
    	$this->view->notread = empty($notread)?0:$notread;
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    
    	
    }
    
    public function viewAction()
    {
       
    }

    public function addAction()
    {
      
    }

    public function editedAction()
    {
       
    }
	
    public function newsAction(){
    	$id = $this->getRequest()->getParam("id");
    	if (empty($id)){
    		$this->_redirect(self::REDIRECT_URL);
    	}
    	try{
	    	$dbgb = new Application_Model_DbTable_DbGlobal();
	    	$news = $dbgb->getDetailNews($id);
	    	if (empty($news)){
	    		$this->_redirect(self::REDIRECT_URL);
	    	}
	    	if ($news['is_read']!=1){
	    		$dbhome = new Home_Model_DbTable_DbStudent();
	    		$dbhome->addReadNews($id);
	    	}
	    	$this->view->detail = $news;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function allnewsAction(){
    	try{
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$news = $dbgb->getAllNew();
    		if (empty($news)){
    			$this->_redirect(self::REDIRECT_URL);
    		}
    		$this->view->row = $news;
    		
    		$paginator = Zend_Paginator::factory($news);
    		$limits=15;
    		$paginator->setDefaultItemCountPerPage($limits);
    		$allItems = $paginator->getTotalItemCount();
    		$countPages= $paginator->count();
    		$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
    		
    		if(isset($p))
    		{
    			$paginator->setCurrentPageNumber($p);
    		} else $paginator->setCurrentPageNumber(1);
    			
    		$currentPage = $paginator->getCurrentPageNumber();
    		
    		$this->view->article  = $paginator;
    		$this->view->countItems = $allItems;
    		$this->view->countPages = $countPages;
    		$this->view->currentPage = $currentPage;
    			
    		if($currentPage == $countPages)
    		{
    			$this->view->nextPage = $countPages;
    			$this->view->previousPage = $currentPage-1;
    		}
    		else if($currentPage == 1)
    		{
    			$this->view->nextPage = $currentPage+1;
    			$this->view->previousPage = 1;
    		}
    		else {
    			$this->view->nextPage = $currentPage+1;
    			$this->view->previousPage = $currentPage-1;
    		}
    		
    		
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }

}







