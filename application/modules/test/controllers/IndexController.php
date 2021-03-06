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
    		
    		$testCondiction = TEST_CONDICTION;
    		$collumns = array("SERIAL","STUDENT_NAMEKHMER","Last Name","First Name","SEX","PHONE","DOB","PARENT_NAME","CONTACT_NO","GENERAL_KNOWLEDGE_RESULT","FOREIGN_LANGUAGE_RESULT","BY_USER","PRINT_PROFILE");
    		
    		if ($testCondiction!==2){
    			$collumns = array("BRANCH","SERIAL","STUDENT_NAMEKHMER","Last Name","First Name","SEX","NATIONALITY","PHONE","DOB","FROM_SCHOOL","PARENT_NAME","CONTACT_NO","GENERAL_KNOWLEDGE_RESULT","FOREIGN_LANGUAGE_RESULT","BY_USER","PRINT_PROFILE");
    			
    		}
    		$link=array(
    				'module'=>'test','controller'=>'index','action'=>'edit',
    		);
    		$link1=array(
						'module'=>'test','controller'=>'index','action'=>'profile'
			);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'serial'=>$link,'stu_khname'=>$link,'Print Profile'=>$link1,'បោះពុម្ពទម្រង់'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$dbcrm = new Home_Model_DbTable_DbCRM();
    	$crm = $dbcrm->getAllCompleteCRM();
    	$this->view->crm = $crm;
    	
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
					exit();
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
					exit();
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
    			$db->updateStudentTest($data);
    			Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL);
    			exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$row  = $db->getStudentTestById($id);
    	$this->view->rs = $row; 
    	$this->view->row_detail=$db->getStudentTestDetail($id);
    	if(empty($row)){
    		Application_Form_FrmMessage::Sucessfull('No Record', "/test/index");
    		exit();
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
    	
    	$this->view->testresultkh = $db->getAllTestResult($id,1);
    	$this->view->testresulteng = $db->getAllTestResult($id,2);
    	$this->view->testresultuniver = $db->getAllTestResult($id,3);
    	
    	$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
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
    	$type = $this->getRequest()->getParam("type");
    	$test = $this->getRequest()->getParam("test");
    	if ($type!=1 AND $type!=2 AND $type!=3){ // check it again with branch that has schooloption
    		Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL);
    		exit();
    	}
    	$this->view->type = $type;
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Test_Model_DbTable_DbStudentTest();
    		try {
    			$db->insertTestExam($data,$type,$test);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
    			}
    		}catch (Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$id = $this->getRequest()->getParam("id");//student id
    	$db = new Test_Model_DbTable_DbStudentTest();
    	$schoolOption = $db->getSchoolOptionbyStudentId($id);
    	if($type!=$schoolOption){
    		Application_Form_FrmMessage::message('THIS CAN NOT TEST THIS OPTION');
    		echo "<script>window.close();</script>";exit();
    	}
    	$row  = $db->getStudentTestById($id);
    	$this->view->rs = $row;
    	if(empty($row)){
    		Application_Form_FrmMessage::Sucessfull('No Record', "/test/index");
    	}
    	$result=null;
    	
    	if (!empty($test)){
    		$db = new Test_Model_DbTable_DbStudentTest();
    		$result  = $db->getTestResultById($test,$type,$id);
    		if (empty($result)){
    			Application_Form_FrmMessage::Sucessfull('No Record', "/test/index");
    		}
    		$subject = $db->getSubjectScoreByTest($test);
    		$this->view->subjectScore = $subject;
    	}
    	$this->view->detailscore = $result;
    	$frm = new Test_Form_FrmStudentTest();
    	$frm->FrmEnterResultTest($row,$result,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form = $frm;
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->rs_subjecttestkhmer = $db->getViewById(31);
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
    function webcamAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$_dbgb = new Test_Model_DbTable_DbStudentTest();
    		$serial = $_dbgb->uploadFile($data);
    		print_r($serial);
    		exit();
    	}
    }
    
    function gettermstudyAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$option = empty($data['option'])?null:$data['option'];
    		$rows = $db->getAllTestTerm($data,$option);
    		array_unshift($rows,array ( 'id' =>"",'name' => $this->tr->translate("SELECT_TERM")));
    		print_r(Zend_Json::encode($rows));
    		exit();
    	}
    }
}