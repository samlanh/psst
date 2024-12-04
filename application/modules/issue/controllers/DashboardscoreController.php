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
			
			$rows= $db->getAllGroups($search);
		}else{
			
			$rows = array();
			$search = array(
				'adv_search'=>'',
				'academic_year'=> '',
				'exam_type'=>0,
				'for_semester'=>0,
				'for_month'=>0,
				'degree'=>0,
				'grade'=> 0,
				'subjectId'=> '',
				'criteriaId'=> '',
				'issueScoreStatus'=> '',
				'start_date'=> '',
				'end_date'=>date('Y-m-d')
			);
		}
		$this->view->search = $search;
		$this->view->row = $rows;
		$form=new Application_Form_FrmCombineSearchGlobal();
		$forms=$form->FormSearchTeacherDasboard();
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
				// Applicatiaon_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			 }
		 }
		 exit();
	}	
}


