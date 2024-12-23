<?php
class Accounting_TermController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/term';
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
    				'termOption'=>0,
				);
    		}
    		
    		$this->view->search = $search;
    		
			$db = new Global_Model_DbTable_DbTerm();
			$rs_rows = $db->getAllTerm($search);
			$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","ACADEMIC_YEAR","TITLE","PERIOD","PERIOD_TYPE","DEGREE","START_DATE","END_DATE","USER");
    		$link=array(
    				'module'=>'accounting','controller'=>'term','action'=>'edit',
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
    	$db = new Global_Model_DbTable_DbTerm();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addTermStudy($data);
	    		if(isset($data['save_close'])){
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

		$this->view->paymentTermList =  $dbg->getPaytermTypeList();

    }
	public function editAction(){
		$db = new Global_Model_DbTable_DbTerm();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editTermbyID($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$row = $db->getTermById($id);
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
		$this->view->paymentTermList =  $dbg->getPaytermTypeList();
	}
	function gettermstudyAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTerm();
			$option = empty($data['option'])?null:$data['option'];
			$data['study_year'] = empty($data['study_year'])?null:$data['study_year'];
			
			$param = array(
				'branch_id'=>$data['branch_id'],
				'study_year'=>$data['study_year'],
				'option'=>$data['option'],
			);
		
			$rows = $db->getTermStudyInterm($param);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
}
