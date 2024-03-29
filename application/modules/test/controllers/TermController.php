<?php
class Test_TermController extends Zend_Controller_Action {
	const REDIRECT_URL = '/test/term';
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
			$db = new Test_Model_DbTable_DbTerm();
			$rs_rows = $db->getAllTerm($search);
			$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","TITLE","START_DATE","END_DATE","CREATE_DATE","USER","STATUS");
    		$link=array(
    				'module'=>'test','controller'=>'term','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('title'=>$link,'create_date'=>$link,'start_date'=>$link,'end_date'=>$link ));
    		
    		$form=new Application_Form_FrmSearchGlobal();
    		$forms=$form->FrmSearch();
    		Application_Model_Decorator::removeAllDecorator($forms);
    		$this->view->form_search=$form;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
    public function addAction()
    {	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Test_Model_DbTable_DbTerm();
		$isaddterm = $this->getRequest()->getParam('addterm');
    	if($this->getRequest()->isPost()){
	    	try{
				
	    		$data = $this->getRequest()->getPost();
	    		$db->addTermStudy($data);
				if(!empty($isaddterm)){
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}
	    		
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$branch = $dbg->getAllBranch();
    	$this->view->branchopt = $branch;
    }
	public function editAction(){
		$db = new Test_Model_DbTable_DbTerm();
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

		$dbg = new Application_Model_DbTable_DbGlobal();
		$branch = $dbg->getAllBranch();
    	$this->view->branchopt = $branch;
	}
}
