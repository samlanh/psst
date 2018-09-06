<?php
class Registrar_StudenttestController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/studenttest';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
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
    		$collumns = array("SERIAL","STUDENT_ID","NAME_KH","NAME_EN","SEX","PHONE","TEST_DATE","DEGREE","GRADE","TEST_TERM","NOTE","BY_USER","STATUS","PRINT_PROFILE");
    		$link=array(
    				'module'=>'registrar','controller'=>'studenttest','action'=>'edit',
    		);
    		$link1=array(
						'module'=>'registrar','controller'=>'studenttest','action'=>'profile'
			);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('receipt'=>$link,'kh_name'=>$link,'en_name'=>$link,'Print Profile'=>$link1,'បោះពុម្ពទម្រង់'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbStudentTest();	
			try {
				$db->addStudentTest($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest/add");
				}		
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $db->getAllDegreeName();
		$this->view->session = $db->getAllSession();
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->serailno= $db->getTestStudentId();
		
		$rs = $db->getallTermtest();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($rs,array('id' => -1,'name'=>$tr->translate("ADD_NEW")));
		$this->view->startdate_enddate = $rs;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbStudentTest();				
			try {
				$db->updateStudentTest($data,$id);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/studenttest");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbStudentTest();
		$this->view->rs = $row  = $db->getStudentTestById($id);
		$this->view->row_detail=$db->getStudentTestDetail($id);
		if($row['register']==1){
			//Application_Form_FrmMessage::Sucessfull('You can not edit because student already registered !!! ', "/registrar/studenttest");
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $db->getAllDegreeName();
		$this->view->session = $db->getAllSession();
		
		$rs = $db->getallTermtest();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($rs, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->startdate_enddate= $rs;
    }
    function profileAction(){
    	$id = $this->getRequest()->getParam('id');
    	$db = new Registrar_Model_DbTable_DbStudentTest();
    	$this->view->row = $row = $db->getStudentTestProfileById($id);
    	$this->view->row_detail=$db->getStudentTestDetail($id);
    }
    
    function makecrmtestAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		try {
    			$db->addStudentTest($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest/add");
    			}
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest/add");
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->degree = $db->getAllDegreeName();
    	$this->view->session = $db->getAllSession();
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->serailno= $db->getTestStudentId();
    	
    	$rs = $db->getallTermtest();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	array_unshift($rs,array('id' => -1,'name'=>$tr->translate("ADD_NEW")));
    	$this->view->startdate_enddate = $rs;
    	
    	
    	$frm = new Registrar_Form_FrmStudentTest();
    	$frm->FrmAddCRMToTest(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
}