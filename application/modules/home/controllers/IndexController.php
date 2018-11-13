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
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		print_r($post);exit();
    	}
      $db = new Allreport_Model_DbTable_DbRptAllStudent();
//       $this->view->rsamountstudent = $db->getAmountStudent();
//       $this->view->rsnewstudent = $db->getAmountNewStudent();
//       $this->view->rsdropstudent = $db->getAmountDropStudent();
//       $this->view->rsteststudent = $db->getAmountStudentTest();
//       $this->view->rsteststuregistered = $db->getAmountStudentTestRegistered();
//       $this->view->rsupdateresult = $db->getAmountStudentUpdateresult();
      
      $_db = new Allreport_Model_DbTable_DbRptIncomeExpense();
      $this->view->totalExpense = $_db->getAmountExpest();
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







