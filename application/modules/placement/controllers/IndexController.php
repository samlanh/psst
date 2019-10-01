<?php
class Placement_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/placement/index';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    }
    public function indexAction()
    {
    	try{
    		$db = new Placement_Model_DbTable_DbSection();
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
			$rs_rows= $db->getAllSection($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TITLE","TEST_TYPE","DATE","STATUS");
    		$link=array(
    				'module'=>'placement','controller'=>'index','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('title'=>$link,'serial'=>$link,'stu_khname'=>$link,));
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
			$db = new Placement_Model_DbTable_DbSection();	
			try {
				$db->addSection($data);
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
		
		$frm = new Placement_Form_FrmSection();
    	$frm->FrmAddSection(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    	
    }
    
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	$db = new Placement_Model_DbTable_DbSection();
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
    			$db->addSection($data);
    			$this->_redirect(self::REDIRECT_URL);
				exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$row = $db->getSectionById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::MessageBacktoOldHistory("NO_RECORD");
    		exit();
    	}
    	$frm = new Placement_Form_FrmSection();
    	$frm->FrmAddSection($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
    
    function allsectionAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	
    		$test_type = empty($data['test_type'])?"":$data['test_type'];
    		$parent = empty($data['parent'])?null:$data['parent'];
    		$arr  = array(
    				'test_type'=>$test_type,
    		);
    		$_dbmodel = new Application_Model_DbTable_DbGlobal();
    		$result=$_dbmodel->getAllSections($arr,$parent);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
}