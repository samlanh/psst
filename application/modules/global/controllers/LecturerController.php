<?php
class Global_LecturerController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Global_Model_DbTable_DbTeacher();
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title']);
			}
			else{
				$search = array(
						'title' => '');
			}
			$rs_rows= $db->getAllTeacher($search);
			//print_r($rs_rows);exit();
			$list = new Application_Form_Frmtable();
			$collumns = array("ID_NUMBER","TEACHER_NAME","SEX","NATIONALITY","PHONE","NOTE","STATUS");
			 
			$link=array(
					'module'=>'global','controller'=>'lecturer','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('teacher_code'=>$link,'teacher_name_kh'=>$link,'teacher_name_en'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			
			
		}
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$frm = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db = new Global_Model_DbTable_DbTeacher();
				$id = $db->AddNewStaff($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", '/global/lecturer');
				}
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
				 
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				echo $e->getMessage();
			}
		}
		
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$this->view->branch_id = $_db->getAllBranch();
		
		$db = new Global_Model_DbTable_DbTeacher();
		$position = $db->getAllPosition();
		array_unshift($position, array('id'=>-1,'name'=>'បន្ថែមថ្មី'));
		$this->view->position = $position;
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$data['id'] = $id;
				$db = new Global_Model_DbTable_DbTeacher();
				$db->updateStaff($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/lecturer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		
		$db=new Global_Model_DbTable_DbTeacher();
		$row=$db->getTeacherById($id);
		$this->view->rs = $row;
		
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher($row);
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
		
		$this->view->branch_id = $db->getAllBranch();
		
		$db = new Global_Model_DbTable_DbTeacher();
		$position = $db->getAllPosition();
		array_unshift($position, array('id'=>-1,'name'=>'បន្ថែមថ្មី'));
		$this->view->position = $position;
		
		$db = new Global_Model_DbTable_DbTeacher();
		$position = $db->getAllPosition();
		array_unshift($position, array('id'=>-1,'name'=>'បន្ថែមថ្មី'));
		$this->view->position = $position;
	}
	function addPositionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbTeacher();
			$id = $db->addNewPosition($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
	function getTeacherIdAction(){
		$db = new Global_Model_DbTable_DbTeacher();
		$code = $db->getTeacherCode();
		print_r(Zend_Json::encode($code));
		exit();
	}
	
}

