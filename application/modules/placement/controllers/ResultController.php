<?php
class Placement_ResultController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/placement/result';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    }
    public function indexAction()
    {
    	try{
    		$db = new Placement_Model_DbTable_DbPlacementTest();
    		
    		
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
	    		if($this->getRequest()->isPost()){
	    			$search=$this->getRequest()->getPost();
	    		}
	    		else{
	    			$search = array(
	    					'title'=>'',
	    					'branch_id'=> '',
	    					'test_type'=>'',
	    					'start_date'	=> date('Y-m-d'),
	    					'end_date'		=> date('Y-m-d'),
	    			);
	    		}
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllPlacementTest($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_NAMEKHMER","NAME_ENGLISH","SEX","SETTING_EXAME","EXAM","DURATION","EXAM_SCORE","RESULT_SCORE","SPEAKING","LISTENNING");
    		$link=array(
    				'module'=>'placement','controller'=>'result','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('title'=>$link,'serial'=>$link,'stu_khname'=>$link,));
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
    	$this->_redirect(self::REDIRECT_URL);
		exit();
    	
    }
    
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	$db = new Placement_Model_DbTable_DbPlacementTest();
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
    			$db->updatePlacementTest($data);
    			$this->_redirect(self::REDIRECT_URL);
				exit();
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	$row = $db->getPlacementById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::MessageBacktoOldHistory("NO_RECORD");
    		exit();
    	}
    	$this->view->row = $row;
    	$frm = new Placement_Form_FrmSection();
    	$frm->FrmUpdateResult($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_crm = $frm;
    }
}