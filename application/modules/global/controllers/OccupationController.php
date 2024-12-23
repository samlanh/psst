<?php
class Global_OccupationController extends Zend_Controller_Action {
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
			$db = new Global_Model_DbTable_DbOccupation();
			$rs_rows= $db->getAllOccupation($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("OCCUPATION_KHNAME","OCCUPATION_ENNAME","CREATE_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'occupation','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('occu_name'=>$link,'occu_enname'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchOccupation();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->frm_search = $frms;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Global_Model_DbTable_DbOccupation();
				$_occupa = $_dbmodel->addNewOccupation($_data);
				if($_occupa==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/occupation");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/global/occupation/add");
				}
				Application_Form_FrmMessage::message($sms);				
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbOccupation();
				$db->updateOccupation($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/occupation/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$db = new Global_Model_DbTable_DbOccupation();
		$this->view->rs=$db->getOccupationById($id);
	}
	function addcompositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbOccupation();
			$id = $db->addOccupation($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}