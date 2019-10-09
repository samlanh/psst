<?php
class Placement_QuestionController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/placement/question';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    }
    public function indexAction()
    {
    	try{
    		$db = new Placement_Model_DbTable_DbQuestion();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search'=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					'status'=>'',
    					'test_type'=>''
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllQuestion($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TEST_TYPE","QUESTION","SECTION","QUESTION_TYPE","DATE","USER","STATUS");
    		$link=array(
    				'module'=>'placement','controller'=>'question','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('test_type'=>$link,'serial'=>$link,'stu_khname'=>$link,));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$dbcrm = new Home_Model_DbTable_DbCRM();
    	$crm = $dbcrm->getAllCompleteCRM();
    	$this->view->crm = $crm;
    	
    	$frm = new Placement_Form_FrmSection();
    	$frm->FrmSearch(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		// Check Session Expire
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$checkses = $dbgb->checkSessionExpire();
    		if (empty($checkses)){
    			$dbgb->reloadPageExpireSession();
    			exit();
    		}
    		
			$data=$this->getRequest()->getPost();	
			$db = new Placement_Model_DbTable_DbQuestion();	
			try {
				$db->addQuestion($data);
				if(!empty($data['saveclose'])){
					$this->_redirect(self::REDIRECT_URL);
					exit();
				}	
				$this->_redirect(self::REDIRECT_URL."/add");
				exit();
// 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
			} catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("INSERT_FAIL");
			}
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$this->view->optionTrueFalse = $_dbgb->getOptionTrueFalse();
		$frm = new Placement_Form_FrmQuestion();
    	$frm->FrmAddQuestion(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    	
    }
    
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	$db = new Placement_Model_DbTable_DbQuestion();
    	if($this->getRequest()->isPost()){
    		// Check Session Expire
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$checkses = $dbgb->checkSessionExpire();
    		if (empty($checkses)){
    			$dbgb->reloadPageExpireSession();
    			exit();
    		}
    		
    		$data=$this->getRequest()->getPost();
    		try {
    			$db->editQuestion($data);
    			$this->_redirect(self::REDIRECT_URL);
				exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$row = $db->getQuestionById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::MessageBacktoOldHistory("NO_RECORD");
    		exit();
    	}
    	$this->view->row = $row;
    	$this->view->rowDetail = $db->getQuestionDetailById($id);
    	
    	$section_id = empty($row['section_id'])?0:$row['section_id'];
    	$check = $db->chcekSectionInUse($section_id);
    	if (!empty($check)){
    		Application_Form_FrmMessage::MessageBacktoOldHistory("UNAVAILABLE_TO_EDIT");
    		exit();
    	}
    	
    	$frm = new Placement_Form_FrmQuestion();
    	$frm->FrmAddQuestion($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$this->view->optionTrueFalse = $_dbgb->getOptionTrueFalse();
    }
    
    
}