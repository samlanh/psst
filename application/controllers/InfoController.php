<?php

class InfoController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
    function indexAction(){
    	
    }

    function studentinfoAction(){
    	$this->_helper->layout()->disableLayout();
    	$db= new Home_Model_DbTable_DbStudent();
    	$dbgb= new Application_Model_DbTable_DbGlobal();
    		
    	$id = $this->getRequest()->getParam('id');
    	$student = $db->getStudentById($id);
    		
    	$this->view->rsStudent =$student;
    	$this->view->rsStudy = $db->getAllStudentStudyRecord($id);
    }

	function staffinfoAction(){
    	$this->_helper->layout()->disableLayout();
    }
	
}





