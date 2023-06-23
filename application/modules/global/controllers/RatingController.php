<?php
class Global_RatingController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
 			$db = new Global_Model_DbTable_DbRating();
 			$rs_rows= $db->getAllRating($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("RATING","CREATE_DATE","USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'rating','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('rating'=>$link,));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Global_Model_DbTable_DbRating();
				$_discount = $_dbmodel->addNewRating($_data);
				if($_discount==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/rating");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/global/rating/add");
				}
				Application_Form_FrmMessage::message($sms);				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		try{
			$data = $this->getRequest()->getPost();
			if($this->getRequest()->isPost())
			{
				$db = new Global_Model_DbTable_DbRating();
				$db->updateRating($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/rating/index");
			}
		}catch(Exception $e){
			Application_Form_FrmMessage::message("EDIT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$id=$this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbRating();
		$this->view->rs=$db->getRatingById($id);

	}

}