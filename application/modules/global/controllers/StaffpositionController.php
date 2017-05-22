<?php
class Global_StaffpositionController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db_dept=new Global_Model_DbTable_DbDept();
			$db = new Global_Model_DbTable_DbStaffPosition();
			$rs_rows= $db->getAllStaffPosition();
			 
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","STATUS","CREATE_DATE");
			$link=array(
					'module'=>'global','controller'=>'staffposition','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
   function addAction()
   {
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try {
	   			$db = new Global_Model_DbTable_DbStaffPosition();
	   			$id = $db->addNewStaffPosition($_data);
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
	   
	   		} catch (Exception $e) {
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   
	   	}
   }
   public function editAction()
   {
	   	$id=$this->getRequest()->getParam("id");
	   
	   	if($this->getRequest()->isPost())
	   	{
	   		try{
		   		$data = $this->getRequest()->getPost();
		   		$data["id"]=$id;
		   		$db = new Global_Model_DbTable_DbStaffPosition();
		   		$db->updateStaffPosition($data);
		   		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/staffposition/index");
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	
	   	$db = new Global_Model_DbTable_DbStaffPosition();
	   	$row = $db->getStaffPositionById($id);
	   	$this->view->rs = $row;
   }
   function addroomAction()//ajax
   {
   	if($this->getRequest()->isPost()){
   			$_data = $this->getRequest()->getPost();
   			$_dbmodel = new Global_Model_DbTable_DbStaffPosition();
   			$roomid = $_dbmodel->addAjaxRoom($_data);
   			print_r(Zend_Json::encode($roomid));
   			exit();
   	}
   }
}

