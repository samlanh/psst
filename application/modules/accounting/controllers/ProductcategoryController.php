<?php
class Accounting_ProductcategoryController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search= array(
						'title' =>'',
						'status_search' =>-1,
				);
			}
			
			$db =  new Accounting_Model_DbTable_DbProduct();
			$rows = $db->getAllCategory($search);
				
			$rs_rows=new Application_Model_GlobalClass();
			$list = new Application_Form_Frmtable();
			$collumns = array("NAME","DATE","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'productcategory','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rows,array('name_kh'=>$link,'name_en'=>$link,));
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$forms=new Accounting_Form_FrmSearchProduct();
			$form=$forms->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db = new Accounting_Model_DbTable_DbProduct();
				$row = $db->addCategory($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/productcategory");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/productcategory/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
					$db = new Accounting_Model_DbTable_DbProduct();
					$row = $db->addCategory($_data);
					
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/productcategory");
					}else{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/productcategory");
					}
					
					Application_Form_FrmMessage::message("EDIT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			$db = new Accounting_Model_DbTable_DbProduct();
			$this->view->row=$db->getGategoryById($id);
	}

  
}