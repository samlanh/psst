<?php 
class Issue_DashboardscoreController extends Zend_Controller_Action {
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
}	
