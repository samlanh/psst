<?php
class Allreport_PlacementtestController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){	
	}
    function rptPlacementtestAction(){
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
    	
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbPlacementest();
    	$this->view->row = $db->getAllPlacementTest($search);
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id= empty($search['branch_id'])?1:$search['branch_id'];
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    	$this->view->rsfooteracc = $frm->getFooterAccount();
    }
    function placementProfileAction(){
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	$db = new Allreport_Model_DbTable_DbPlacementest();
    	$row = $db->getPlacementById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::messageAlert("NO_RECORD","/allreport/placementtest/rpt-placementtest");
    		exit();
    	}
    	$this->view->row = $row;
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id= empty($row['branch_id'])?1:$row['branch_id'];
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    	$this->view->rsfooteracc = $frm->getFooterAccount();
    }
    function answerSheetAction(){
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	$db = new Allreport_Model_DbTable_DbPlacementest();
    	$question = $db->getAllQuestionBySettingExam($id);
    	$this->view->question = $question;
    	
    	$_dbpl= new Application_Model_DbTable_DbPlacementTest();
    	$this->view->setting = $_dbpl->getPlacementSetting($id);
    }
}