<?php
class Issue_CommentController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'advance_search' 	=> '',
						'status_search' 	=> -1
					);
			}
			$db = new Issue_Model_DbTable_DbComment();
			$rs_rows= $db->getAllComment($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("COMMENT","CREATED_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'comment','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('comment'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmItems();
		$frm->FrmAddDegree(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Issue_Model_DbTable_DbComment();
				$_occupa = $_dbmodel->addComment($_data);
				if($_occupa==-1){
					$sms = "RECORD_EXIST";
				}
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/comment");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/issue/comment/add");
				}
				Application_Form_FrmMessage::message($sms);				
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Issue_Model_DbTable_DbComment();
				$db->updateComment($data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/comment/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Issue_Model_DbTable_DbComment();
		$this->view->rs=$db->getCommentById($id);
	}
}