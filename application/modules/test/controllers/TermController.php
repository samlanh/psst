<?php
class Test_TermController extends Zend_Controller_Action {
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
					'search'=>"",
				);
    		}
    		
    		$this->view->search = $search;
    		
			$db = new Test_Model_DbTable_DbStartdateEnddate();
			$rs_rows = $db->getAlltestTerm($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("START_DATE","END_DATE","NOTE","CREATE_DATE","USER");
    		$link=array(
    				'module'=>'test','controller'=>'term','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('start_date'=>$link,'end_date'=>$link ));
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    public function addAction()
    {	
    	$db = new Test_Model_DbTable_DbStartdateEnddate();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addTerm($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/test/term");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/test/term/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    }
	public function editAction(){
		$db = new Test_Model_DbTable_DbStartdateEnddate();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editTerm($data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/test/term");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$this->view->row = $db->getAlltermforedit();
	}
	function addtermAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$dbc = new Test_Model_DbTable_DbStartdateEnddate();
			$dbc->addTermajax($data);
			
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getallTermtest();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($rs, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));			
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}
