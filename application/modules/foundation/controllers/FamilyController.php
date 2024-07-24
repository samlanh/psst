<?php 
class Foundation_FamilyController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		defined('SHOW_TEACH_DOCUMENT') || define('SHOW_TEACH_DOCUMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('teacher_doc'));
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbFamily();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'adv_search' => '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> -1,
						);
			}
			$db->updateFamilyIdInStudentOldData();
			$rs_rows= $db->getAllFamily($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("familyCode","FATHER_NAME","PHONE","MOTHER_NAME","FAMILY_TYPE","laonNumber","NUMBER_HOME","CREATE_DATE","USER","STATUS");
			$link=array('module'=>'foundation','controller'=>'family','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('familyCode'=>$link,'fatherNameKh'=>$link,'teacher_name_en'=>$link,'branch_name'=>$link));
		
			$this->view->search = $search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Foundation_Form_FrmFamily();
		$this->view->frm_search = $frm->FrmSearchFamily($search);
		Application_Model_Decorator::removeAllDecorator($frm);
	}
	
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			if (empty($_data)){
				Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/family");
				exit();
			}
			try {
				$sms="INSERT_SUCCESS";
				$dbmodel = new Foundation_Model_DbTable_DbFamily();
				$id = $dbmodel->addNewFamily($_data);
				if($id==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/foundation/family');
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/foundation/family/add');
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$dbmodel = new Foundation_Model_DbTable_DbFamily();
		$this->view->familyCode = $dbmodel->getFamilyCode();
		$tsub=new Foundation_Form_FrmFamily();
		$frm=$tsub->FrmFrmFamily();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	public function editAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");	   
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/family");
					exit();
				}
				$data['id'] = $id;
				$db = new Foundation_Model_DbTable_DbFamily();
				$db->updateFamily($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/family");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_db = new Foundation_Model_DbTable_DbFamily();
		$row = $_db->getFamilyById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/foundation/family");
			exit();
		}
		$this->view->rs = $row;
		
		$tsub = new Foundation_Form_FrmFamily();
		$frm = $tsub->FrmFrmFamily($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	function getFamilyListAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbFamily();
			$result = $db->getAllFamilyList($data);
			if(!empty($data['selectOption'])){
				array_unshift($result, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_FAMILY")));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	
	
}