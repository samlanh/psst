<?php

class Home_IndexController extends Zend_Controller_Action
{
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
      $this->view->rsamountstudent = $db->getAmountStudent();
      $this->view->rsnewstudent = $db->getAmountNewStudent();
      $this->view->rsdropstudent = $db->getAmountDropStudent();
      $this->view->rsteststudent = $db->getAmountStudentTest();
      $this->view->rsteststuregistered = $db->getAmountStudentTestRegistered();
      $this->view->rsupdateresult = $db->getAmountStudentUpdateresult();
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


}







