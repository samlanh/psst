<?php
class Allreport_ScanrpController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
	}
	public function rptStudentvaccineAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 	=>'',
				'branch_id' 	=>'',
				'start_date'	=>date("Y-m-d"),
				'end_date'		=>date("Y-m-d"),
				'is_vaccined' => -1,
				'is_covidTested' => -1,
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptScanning();
		$this->view->rs = $group->getAllStudentSetCovidFeature($search);
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	public function rptScantransactionAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
				'adv_search' 	=>'',
				'branch_id' 	=>'',
				'start_date'	=>date("Y-m-d"),
				'end_date'		=>date("Y-m-d")
			);
		}
		
		$group= new Allreport_Model_DbTable_DbRptScanning();
		$this->view->rs = $group->getAllScantransaction($search);
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	
	
}