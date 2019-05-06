<?php

class Registrar_CateincomeController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/cateincome';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbCateIncome();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"status"=>-1,
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllCateIncome($search);//call frome model
			$this->view->row = $rs_rows;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbCateIncome();				
			try{
				$sms="INSERT_SUCCESS";
				$cate = $db->addCateIncome($data);
				if($cate==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/registrar/cateincome");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,'/registrar/cateincome/add');
				}				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$db = new Registrar_Model_DbTable_DbCateIncome();
		$this->view->parent = $db->getParentCateIncome();
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Registrar_Model_DbTable_DbCateIncome();				
			try {
				$db->updateCateIncome($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/cateincome");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbCateIncome();
		$row  = $db->getCateIncomeById($id);
		$this->view->rs = $row;
		
		$this->view->parent = $db->getParentCateIncome($id);
    }

}







