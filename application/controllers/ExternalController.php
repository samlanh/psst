<?php

class ExternalController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }

    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
    	$userName = $sessionUserExternal->userName;
    	$userId = $sessionUserExternal->userId;
    	if (!empty($userId)){
    		$this->_redirect("/external/dashboard");
    	}
    	
		
		$session_lang=new Zend_Session_Namespace('lang');
		if (empty($session_lang->lang_id)){
			$session_lang->lang_id =1;
		}
		$this->view->rslang = $session_lang->lang_id;
		
		if($this->getRequest()->isPost())
    	{
			$formdata = $this->getRequest()->getPost();
    		$userName=$formdata['userName'];
    		$passwordKey=$formdata['passwordKey'];
			
			$dbExternal=new Application_Model_DbTable_DbExternal();
			if($dbExternal->userAuthenticateTeacher($userName,$passwordKey)){
				$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
				$teacherInfo = $dbExternal->getTeacherInfo($userName,$passwordKey);

				$sessionUserExternal->userId= $teacherInfo['id'];
				$sessionUserExternal->userName=$userName;
				$sessionUserExternal->pwd=$passwordKey;
				$sessionUserExternal->staffType= $teacherInfo['staff_type'];
				
				$sessionUserExternal->teacher_code= $teacherInfo['teacher_code'];
				$sessionUserExternal->teacher_name_en= $teacherInfo['teacher_name_en'];
				$sessionUserExternal->teacher_name_kh= $teacherInfo['teacher_name_kh'];
				
				
				Application_Form_FrmMessage::redirectUrl("/external/dashboard");
				exit();
			}
			else{
				$this->view->msg = 'ឈ្មោះគណនី និងពាក្យសម្ងាត់​ មិន​ត្រឺម​ត្រូវ​ទេ ';
			}
    	}
    }
	public function logoutAction()
    {
        // action body
        if($this->getRequest()->getParam('value')==1){        	
        	$aut=Zend_Auth::getInstance();
        	$aut->clearIdentity();        	
        	$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
        	if(!empty($sessionUserExternal->userId)){
	        
	        	$sessionUserExternal->unsetAll();       	
	        	Application_Form_FrmMessage::redirectUrl("/external");
	        	exit();
        	}
        } 
    }
	public function dashboardAction()
    {
		$this->_helper->layout()->disableLayout();
		$arrFilter = array();
		$dbExternal=new Application_Model_DbTable_DbExternal();
		$this->view->allClass = $dbExternal->coutingClassByUser($arrFilter);
		$arrFilter['classTypeFilter']=1;
		$this->view->completedClass = $dbExternal->coutingClassByUser($arrFilter);
		$arrFilter['classTypeFilter']=2;
		$this->view->activeClass = $dbExternal->coutingClassByUser($arrFilter);
    }
	public function groupAction()
    {
		$this->_helper->layout()->disableLayout();
		$arrFilter = array();
		$dbExternal=new Application_Model_DbTable_DbExternal();
		$this->view->allClass = $dbExternal->getAllClassByUser($arrFilter);
		
    }
}





