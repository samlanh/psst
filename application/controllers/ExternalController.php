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
		
		$zendRequest = new Zend_Controller_Request_Http();
		$userId = $zendRequest->getCookie(TEACHER_AUTH.'userId');
		
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
				$teacherInfo = $dbExternal->getTeacherInfo($userName,$passwordKey);
				setcookie(TEACHER_AUTH.'userId', $teacherInfo['id'], time() + (86400 * 30), '/');// 86400 = 1 day
				setcookie(TEACHER_AUTH.'userName', $userName, time() + (86400 * 30), '/');
				setcookie(TEACHER_AUTH.'pwd', $passwordKey, time() + (86400 * 30), '/');
				setcookie(TEACHER_AUTH.'staffType', $teacherInfo['staff_type'], time() + (86400 * 30), '/');
				setcookie(TEACHER_AUTH.'teacherCode', $teacherInfo['teacher_code'], time() + (86400 * 30), '/');
				
				Application_Form_FrmMessage::redirectUrl("/external/dashboard");
				exit();
			}
			else{
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$this->view->msg = $this->view->msg = $tr->translate('INVALID_LOGIN');;
			}
    	}
    }
	public function logoutAction()
    {
        // action body
        if($this->getRequest()->getParam('value')==1){        	
        	$aut=Zend_Auth::getInstance();
        	$aut->clearIdentity();    

			$sessionUserExternal=new Zend_Session_Namespace(TEACHER_AUTH);
			$sessionUserExternal->unsetAll();
			
			setcookie(TEACHER_AUTH.'userId', null, -1, '/'); 
			setcookie(TEACHER_AUTH.'pwd', null, -1, '/'); 
            setcookie(TEACHER_AUTH.'userName', null, -1, '/'); 		
            setcookie(TEACHER_AUTH.'staffType', null, -1, '/');    
			setcookie(TEACHER_AUTH.'teacherCode', null, -1, '/');  
			
			Application_Form_FrmMessage::redirectUrl("/external");
			exit();
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
		
		
		$search = array(
			'limitedRecord'=>10
		);
		$search['groupBy']=1;
		$search['classTypeFilter']=2;
		$this->view->activeClassList = $dbExternal->getAllClassByUser($search);
		$search['classTypeFilter']=1;
		$this->view->completedClassList = $dbExternal->getAllClassByUser($search);
		
		$arrFilterI = array();
		$date=date("Y-m-d"); 
		$dayofweek = date('w', strtotime($date));
		$currentDay=$dayofweek;
		$nextDay=$dayofweek+1;
		if($dayofweek==0){
			$currentDay=7;
		}
		$arrFilterI['day']=$currentDay;
		$this->view->todaySchedule = $dbExternal->getTeachingSchedule($arrFilterI);
		
		$arrFilterI['day']=$nextDay;
		$this->view->tomorrowSchedule = $dbExternal->getTeachingSchedule($arrFilterI);
		
		$this->view->teacherInfo = $dbExternal->getCurrentTeacherInfo();
		
    }
	public function groupAction()
    {
		$this->_helper->layout()->disableLayout();
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$teacherInfo = $dbExternal->getCurrentTeacherInfo();
		$currentAcademic = empty($teacherInfo['currentAcademic'])?0:$teacherInfo['currentAcademic'];
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
				'adv_search'		=> '',
				'branch_id'			=> '',
				'academic_year'		=> $currentAcademic,
				'degree'			=>'',
				'grade' 			=> '',
				'session' 			=>'',
			);
		}
		$this->view->search = $search;
		
		$dbExternal=new Application_Model_DbTable_DbExternal();
		$this->view->allClass = $dbExternal->getAllClassByUser($search);
			
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
    }
	
	function getsubjectlistAction(){
		$this->_helper->layout()->disableLayout();
		if($this->getRequest()->isPost()){
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$data = $this->getRequest()->getPost();
			$row=$dbExternal->getAllSubjectByGroupExternal($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getsubjectinfoAction(){
		$this->_helper->layout()->disableLayout();
		if($this->getRequest()->isPost()){
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$data = $this->getRequest()->getPost();
			$row=$dbExternal->getSubjectGroupInfoExternal($data);
			
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function printbyAction(){
		$this->_helper->layout()->disableLayout();
		if($this->getRequest()->isPost()){
			$frmGb = new Application_Form_FrmPopupGlobal();
			$data = $this->getRequest()->getPost();
			$row=$frmGb->printByFormatForTeacher();
			
			echo $row;
			exit();
		}
	}
	
	function changepasswordAction(){
		
		$this->_helper->layout()->disableLayout();
		if ($this->getRequest()->isPost()){
			$zendRequest = new Zend_Controller_Request_Http();
			$userId = $zendRequest->getCookie(TEACHER_AUTH.'userId');
			$userName = $zendRequest->getCookie(TEACHER_AUTH.'userName');
			$pwd = $zendRequest->getCookie(TEACHER_AUTH.'pwd');
			$userId = empty($userId)?0:$userId;
			
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$pass_data=$this->getRequest()->getPost();
			if ($pass_data['password'] == $pwd){
				
				try {
					$dbExternal->changePassword($pass_data['new_password']);
					setcookie(TEACHER_AUTH.'pwd', $pass_data['new_password'], time() + (86400 * 30), '/');
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/external");
				} catch (Exception $e) {
					Application_Form_FrmMessage::message("INSERT_FAIL");
				}
			}
			else{
				Application_Form_FrmMessage::Sucessfull("OLD_PASSWORD_INCORRECT","/external/changepassword");
			}
		}
	}
	
	
}





