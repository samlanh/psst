<?php
class Global_AcademicyearController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Global_Model_DbTable_DbAcademicyear();
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' => $_data['adv_search']);
			}
			else{
				$search = array(
						'txtsearch' => '');
			}
			$rs_rows= $db->getAllacademicyear($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("FROM_YEAR","TO_YEAR","GENERATION","DATE_START","END_DATE","AMOUNT_MONTH","NOTE");
			 
			$link=array(
					'module'=>'global','controller'=>'academicyear','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('fromyear'=>$link,'toyear'=>$link,'batch'=>$link));
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
		$this->view->adv_search = $search;
	}
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$_dbmodel = new Global_Model_DbTable_DbAcademicyear();
				$_major_id = $_dbmodel->AddNewAcademicyear($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", '/global/Academicyear');
				}
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
				
				 
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$tsub=new Global_Form_FrmTeacher();
		$frm_techer=$tsub->FrmTecher();
		Application_Model_Decorator::removeAllDecorator($frm_techer);
		$this->view->frm_techer = $frm_techer;
		$db = new Application_Model_GlobalClass();
		$this->view->subject_opt = $db->getsunjectOption();
		
	}
	public function editAction()
	{
		
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
			
				$db = new Global_Model_DbTable_DbAcademicyear();
				$db->updateacademicyear($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/academicyear");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id=$this->getRequest()->getParam("id");
		$db=new Global_Model_DbTable_DbAcademicyear();
		$row=$db->getacademicyearById($id);
		
		//$tsub=new Global_Form_FrmTeacher();
		//$frm_techer=$tsub->FrmTecher($row);
		//Application_Model_Decorator::removeAllDecorator($frm_techer);
		//$this->view->frm_techer = $frm_techer;
		$dbs = new Application_Model_GlobalClass();
		$this->view->subject_opt = $dbs->getsunjectOption();
		
		$this->view->teacher_subject = $db->getacademicyearById($id);
		$this->view->rs = $row;
	}
	function addacademicyearAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbAcademicyear();
			$id = $db->addAcademicyear($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

