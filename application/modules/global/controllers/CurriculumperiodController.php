<?php
class Global_CurriculumperiodController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/curriculumperiod';
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
					'adv_search'=>"",
    				'branch_id'=>"",
    				'academic_year'=>"",
				);
    		}
    		
    		$this->view->search = $search;
    		
			$db = new Global_Model_DbTable_DbCurriculumPeriod();
			$rs_rows = $db->getAllCurriculumPeriod($search);
			$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TITLE","TERM","DEGREE","START_DATE","END_DATE","USER");
    		$link=array(
    				'module'=>'global','controller'=>'curriculumperiod','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('branch_name'=>$link,'academic_year'=>$link,'title'=>$link ));
    		
    		$form=new Application_Form_FrmSearchGlobal();
    		$forms=$form->FrmSearch();
    		Application_Model_Decorator::removeAllDecorator($forms);
    		$this->view->form_search=$form;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    public function addAction()
    {	
    	$db = new Global_Model_DbTable_DbCurriculumPeriod();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addCurriculumPeriod($data);
	    		if(isset($data['save_close'])){
					$data['forDepartment'] = 2;
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$db = new Accounting_Model_DbTable_DbFee();
    	$this->view->year = $db->getAceYear();
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->rsbranch = $dbg->getAllBranch();
		$this->view->faculty = $dbg->getAllDegreeName();

		$rows = $dbg->getAllPaymentTerm($id=null,$hidemonth=1);
		$this->view->term_option =	$rows ;

    }
	public function editAction(){
		$db = new Global_Model_DbTable_DbCurriculumPeriod();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
				$data['forDepartment'] = 2;
	    		$db->editCurriculumPeriod($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$row = $db->getCurriculumById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$this->view->row = $row;

		// $termDetail = $db->getTermDetail($row);
		// $this->view->termDetail = $termDetail;
    	
    	$db = new Accounting_Model_DbTable_DbFee();
    	$this->view->year = $db->getAceYear();
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$this->view->rsbranch = $dbg->getAllBranch();
		$this->view->faculty = $dbg->getAllDegreeName();

		$rows = $dbg->getAllPaymentTerm($id=null,$hidemonth=1);
		$this->view->term_option =	$rows ;
	}
	
}
