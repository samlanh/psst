<?php
class Registrar_InitilizeuseController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$dbIni = new Registrar_Model_DbTable_DbInitilizeservice();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    				'adv_search' => '',
    		    	'academic_year' => '',
    		    	'branch_id'=>0,
    		    	'groupId'=>0,
    		    	'degree'=>0,
    		    	'gradeId'=>0,
    			);
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$last = $dbgb->getLatestAcadmicYear();
				if(!empty($last)){
					$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
				}
    		}
			$search["distinctStudent"] = 1;
			$this->view->search = $search;
			
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
		
		}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	public function addAction(){
		
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Registrar_Model_DbTable_DbInitilizeservice();
				$_discount = $_dbmodel->addInitilizeService($_data);
				$inFrame = empty($_data['inFrame']) ? "" : "true";
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/initilizeuse/index?inFrame=");
				}elseif(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/initilizeuse/add?inFrame=");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$model = new Application_Model_DbTable_DbGlobal();
		
		$itemType = $model->getAllItems();
		array_unshift($itemType, array ('id' => "", 'name' => $this->tr->translate("PLEASE_SELECT")));
		$this->view->itemType = $itemType;
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id) ? 0 : $id;
		$dbSt = new Foundation_Model_DbTable_DbStudent();
		$row = $dbSt->getStudentById($id);
		$this->view->studentRow = $row;
		
		$tsub=new Registrar_Form_FrmInitial();
		$frm_discount=$tsub->FrmInitialItem();
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;
	}
	function getstudentinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbInitilizeservice();
    		$rs = $db->getStudentInfo($data);
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
	
	function searchingAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
			$data["distinctStudent"] = 1;
    		$db = new Registrar_Model_DbTable_DbInitilizeservice();
    		$rs = $db->getSearchingResult($data);
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
	function getStuserviceAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbInitilizeservice();
    		$rs = $db->getStuserviceContent($data);
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
	
}

