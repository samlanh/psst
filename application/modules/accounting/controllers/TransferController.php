<?php
class Accounting_TransferController extends Zend_Controller_Action {
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	$db = new Accounting_Model_DbTable_DbTransferstock();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$rs_rows= $db->getAllTransfer($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TRANSFER_NUMBER","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","NOTE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'transfer','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('transfer_no'=>$link,'transfer_date'=>$link,'fromlocation'=>$link,'tolocation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    }
	public function addAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addTransferStock($data);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->tran_no = $db->getTransferNo();
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsproduct = $db->getallProductName();
		
		$branch = $db->getAllBranchName();
    	array_unshift($branch, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
    	$this->view->branchopt = $branch;
		
		$fm = new Global_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
		
	}
	public function editAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->updateTransferStock($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/accounting/transfer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$this->view->rs = $db->getTransferById($id);
		$this->view->rsdetail = $db->getTransferByIdDetail($id);
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $db->getAllBranchName();
		$this->view->rsproduct = $db->getallProductName();
	}
	function getcurrentproductAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbTransferstock();
			$rs = $db->getProductLocation($data['pro_id'],$data['location_id']);
			if(empty($rs)){$rs = array('pro_qty'=>0);}
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	public function showBarcodesAction(){
		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$ids=explode(',', $id);
			$this->view->pro_id = $ids;
		}
		else{
			//$this->_redirect("/product/index/index");
		}
	
	}
	public function generatebarcodeAction(){
		$this->_helper->layout()->disableLayout();
		$loan_code = $this->getRequest()->getParam('loan_code');		
		header('Content-type: image/png');
		
		//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
		$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
		//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
		$rendererOptions = array();
		$renderer = Zend_Barcode::factory(
				'code128', 'image', $barcodeOptions, $rendererOptions
		)->render();
	
	}
}