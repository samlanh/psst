<?php
class Test_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/test/index';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    }
    public function indexAction()
    {
    	try{
    		$db = new Test_Model_DbTable_DbStudentTest();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'branch_search'=>'',
    					'txtsearch'=>'',
    					'degree' => '',
    					'result_status' => '',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					'term_test'=>''
    			);
    		}
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllStudentTest($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","SERIAL","STUDENT_ID","NAME_KH","NAME_EN","SEX","NATIONALITY","PHONE","DOB","FROM_SCHOOL","PARENT_NAME","TEL","BY_USER","PRINT_PROFILE");
    		$link=array(
    				'module'=>'test','controller'=>'index','action'=>'edit',
    		);
    		$link1=array(
						'module'=>'test','controller'=>'index','action'=>'profile'
			);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('receipt'=>$link,'kh_name'=>$link,'en_name'=>$link,'Print Profile'=>$link1,'បោះពុម្ពទម្រង់'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$dbcrm = new Home_Model_DbTable_DbCRM();
    	$crm = $dbcrm->getAllCompleteCRM();
    	$this->view->crm = $crm;
    	
//     	$form=new Registrar_Form_FrmSearchInfor();
//     	$form->FrmSearchRegister();
//     	Application_Model_Decorator::removeAllDecorator($form);
//     	$this->view->form_search=$form;
    	
    	$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmAddStudentTest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Test_Model_DbTable_DbStudentTest();	
			try {
				$db->addStudentTest($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}		
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optionNation = $_dbgb->getViewByType(21);//Nation
		array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
		$this->view->nation = $optionNation;
		
		$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmAddStudentTest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$db = new Test_Model_DbTable_DbStudentTest();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		try {
    			
    			$db->updateStudentTest($data,$id);
    			Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL);
    				
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$row  = $db->getStudentTestById($id);
    	$this->view->rs = $row;
    	$this->view->row_detail=$db->getStudentTestDetail($id);
    	if($row['register']==1){
    		Application_Form_FrmMessage::Sucessfull('You can not edit because student already registered !!! ', "/registrar/studenttest");
    	}
    	
    	$this->view->testresult = $db->getAllTestResult($id);
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$optionNation = $_dbgb->getViewByType(21);//Nation
    	array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
    	$this->view->nation = $optionNation;
    	
    	$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmAddStudentTest($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    	
    }
    function profileAction(){
    	$id = $this->getRequest()->getParam('id');
    	$db = new Test_Model_DbTable_DbStudentTest();
    	$this->view->row = $row = $db->getStudentTestProfileById($id);
    	$this->view->row_detail=$db->getStudentTestDetail($id);
    }
    
    function makecrmtestAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Test_Model_DbTable_DbStudentTest();
    		try {
    			
    			$db->createStudentTestFromCrm($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
    			}
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			
    		}
    	}
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$optionNation = $_dbgb->getViewByType(21);//Nation
    	array_unshift($optionNation,array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	array_unshift($optionNation,array ( 'id' =>"",'name' => $tr->translate("PLEASE_SELECT")));
    	$this->view->nation = $optionNation;
    	
    	$id = $this->getRequest()->getParam("id");
    	$dbcrm = new Home_Model_DbTable_DbCRM();
    	$row = $dbcrm->getCRMById($id);
    	$this->view->row = $row;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("This Record Created to Student Test Ready",self::REDIRECT_URL);
    	}
    	$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmAddCRMToTest($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
    function getserialAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$serial = $_dbgb->getTestStudentId($data['branch_id']);
    		print_r(Zend_Json::encode($serial));
    		exit();
    	}
    }
    
    function createtestexamAction(){
    	
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Test_Model_DbTable_DbStudentTest();
    		try {
    			 
    			$db->insertTestExam($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
    			}
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			 
    		}
    	}
    	
    	$id = $this->getRequest()->getParam("id");
    	$db = new Test_Model_DbTable_DbStudentTest();
    	$row  = $db->getStudentTestById($id);
    	$this->view->rs = $row;
    	
    	$test = $this->getRequest()->getParam("test");
    	$result=null;
    	
    	if (!empty($test)){
    		$db = new Test_Model_DbTable_DbStudentTest();
    		$result  = $db->getTestResultById($test);
    	}
    	$this->view->detailscore = $result;
    	
    	$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmEnterResultTest($row,$result);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form = $frm;
    	
    }
    
    function getstudenttestbybranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$_dbgb = new Test_Model_DbTable_DbStudentTest();
    		$serial = $_dbgb->getAllStudentByBranchTested($data['branch_id']);
    		array_unshift($serial,array ( 'id' =>"",'name' => $this->tr->translate("PLEASE_SELECT")));
    		print_r(Zend_Json::encode($serial));
    		exit();
    	}
    }
}