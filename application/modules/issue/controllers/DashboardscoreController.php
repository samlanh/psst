<?php 
class Issue_DashboardscoreController extends Zend_Controller_Action {
	const REDIRECT_URL='/issue/dashboardscore';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction()
	{
		$db = new Issue_Model_DbTable_DbDashboardScore();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$this->view->search = $search;
			$rows= $db->getAllGroups($search);
		}else{
			$rows='';
		}
		
		$this->view->row = $rows;
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
    public function addAction()
	{
		$this->_redirect("/issue/dashboardscore");
	}

	public function deleteAction(){
		$db = new Issue_Model_DbTable_DbDashboardScore();
		 if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			 try{
				$result =	$db->deleteTmpScore(	$_data);
				print_r(Zend_Json::encode($result));
				
			 } catch (Exception $e) {
				 Application_Form_FrmMessage::message("UPDATE_FAIL");
				 Applicatiaon_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			 }
		 }
		 exit();
	}	
}


