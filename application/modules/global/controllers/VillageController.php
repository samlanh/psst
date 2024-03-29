<?php
class Global_VillageController extends Zend_Controller_Action {
	const REDIRECT_URL='/global';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Global_Model_DbTable_DbVillage();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'province_name'=>12,
						'district_name'=>'',
						'commune_name'=>'');
			}
			$rs_rows= $db->getAllVillage($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("VILLAGENAME_KH","VILLAGE_NAME","COMMNUE_NAME","DISTRICT_NAME","PROVINCE_NAME","DATE","STATUS","BY_USER");
			$link=array(
					'module'=>'global','controller'=>'village','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('village_name'=>$link,'village_namekh'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmVillage();
		$frm = $frm->FrmAddVillage();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_village= $frm;
		$this->view->result = $search;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$db = new Global_Model_DbTable_DbVillage();
			$_data = $this->getRequest()->getPost();
			try{
				$db->addVillage($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
				}else{
					Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'),self::REDIRECT_URL . '/village/index');
				}
			}catch(Exception $e){
				$err = $e->getMessage();
				Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Global_Form_FrmVillage();
		$frm = $fm->FrmAddVillage();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_village = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frm_popup_comm = $dbpop->frmPopupCommune();
		$this->view->frm_popup_district = $dbpop->frmPopupDistrict();
	}
	public function editAction(){
		$db = new Global_Model_DbTable_DbVillage();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db->addVillage($_data);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'),self::REDIRECT_URL . '/village/index');
			}catch(Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db->getVillageById($id);
		$this->view->row=$row;
		if(empty($row)){
			$this->_redirect('global/village');
		}		
		$fm = new Global_Form_FrmVillage();
		$frm = $fm->FrmAddVillage($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_village = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frm_popup_comm = $dbpop->frmPopupCommune();
		$this->view->frm_popup_district = $dbpop->frmPopupDistrict();
		
	}
	public function addNewvillageAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$data['commune_name']=$data['popup_commune_name'];
			$db_vill = new Global_Model_DbTable_DbVillage();
			$id = $db_vill->addVillage($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	public function addVillageAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$db_vill = new Global_Model_DbTable_DbVillage();
			$id = $db_vill->addVillageByAjax($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
      function getAllvillageAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbVillage();
			$rows = $db->getAllvillagebyCommune($data['commune_id']);
 			array_unshift($rows, array ( 'id' => -1, 'name' => $this->tr->translate("ADD_NEW")) );
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
}
