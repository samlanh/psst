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
    		
    	$token = $this->getRequest()->getParam('token');
    	$student = $db->getStudentByIdToken($token);
    		
    	$this->view->rsStudent =$student;
    	$id = empty($student['stu_id'])?0:$student['stu_id'];
    	$this->view->rsStudy = $db->getAllStudentStudyRecord($id);
    }

	function staffinfoAction(){
    	$this->_helper->layout()->disableLayout();
    	
    	$token=$this->getRequest()->getParam("token");
    	$db= new Foundation_Model_DbTable_DbTeacher();
    	$param = array
    		(
    		'token'=>$token
    		);
    	
    	$this->view->result = $db->getTeacherinfoById($param);
    	
    }
	
}





