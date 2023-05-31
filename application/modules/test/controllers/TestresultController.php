<?php
class Test_TestresultController extends Zend_Controller_Action
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
    					// 'branch_search'=>'',
    					// 'txtsearch'=>'',
    					// 'degree_search' => '',
    					// 'result_status' => '',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					// 'term_test'=>''
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllStudentTestResult($search);
    		$list = new Application_Form_Frmtable();

    		$collumns = array("SERIAL","STUDENT_NAMEKHMER","TEST_TYPE","TEST_DATE","TEST_TERM","DEGREE","GRADE","RESULT_DATE","SCORE","DEGREE","GRADE","BY");
    		// $link=array(
    		// 		'module'=>'test','controller'=>'term','action'=>'edit',
    		// );
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array( ));

			$frm = new Test_Form_FrmStudentTest();
			$frm->FrmAddStudentTest(null);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->form_search = $frm;
			
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
}