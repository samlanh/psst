<?php
class RsvAcl_LogactivityController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/rsvacl/acl';
	
	public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
        try{
			$db = new RsvAcl_Model_DbTable_Dblogactivity();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
					'user'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d')
				);
			}
			$rs_rows= $db->getAllUserLogActivity($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BY_USER","IP_ADDRESS","LOG_DATE","LOG_TYPE");
			$link=array('module'=>'rsvacl','controller'=>'lecturer','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
    }
    
    

}

