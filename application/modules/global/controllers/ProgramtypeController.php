<?php
class Global_ProgramtypeController extends Zend_Controller_Action {
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
					'status' => $_data['status_search']
				);
			}else{
				$search = array(
					'title' => '',
					'status' => -1
				);
			}
 			$db = new Global_Model_DbTable_DbProgramType();
 			$rs_rows= $db->getAllProgramType($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","TITLE_EN","SHORTCUT","TYPE","DEGREE","CREATED_DATE","BY_USER","STATUS");
			$link=array(
				'module'=>'global','controller'=>'programtype','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'titleEn'=>$link));
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
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Global_Model_DbTable_DbProgramType();
				$_dbmodel->addProgramType($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/programtype");
				}
				Application_Form_FrmMessage::Sucessfull($sms,"/global/programtype/add");			
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		
		$_dbg = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_dbg->getAllDegreeName();
		
		
		$tsub=new Global_Form_FrmProgramType();
		$frm=$tsub->FrmProgramType();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$db = new Global_Model_DbTable_DbProgramType();

		if($this->getRequest()->isPost()){
			$_dbmodel = new Global_Model_DbTable_DbProgramType();
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel->updateProgramType($_data);
				Application_Form_FrmMessage::Sucessfull($sms,"/global/programtype");	
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$row=$db->getProgramTypeId($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/global/programtype");
		}else{
			$this->view->row = $row;
			
			$_db = new Application_Model_DbTable_DbGlobal();
			$this->view->faculty = $_db->getAllDegreeName();
		
			$tsub=new Global_Form_FrmProgramType();
			$frm=$tsub->FrmProgramType($row);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm = $frm;
		}
		
		
	}
	
	function getProgramListAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobalUp();
			$result = $db->getAllProgramTypeList($data);
			if(!empty($data['selectOption'])){
				array_unshift($result, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_FAMILY")));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}