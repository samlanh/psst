<?php
class Allreport_StockController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction(){
	}
	public function rptProductLocationAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}
			else{
				$search = array(
						'title' =>'',
						'location' =>'',
						'product'=>'',
						'status_search'=>1,
						'category_id'=>0,
				);
			}
			$db = new Allreport_Model_DbTable_DbProductList();
			$this->view->pro_loc = $db->getProductLocation($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptListProductAction(){
		$id=$this->getRequest()->getParam('id');
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' =>$_data['txtsearch'],
						'start_date'=> $_data['from_date'],
						'end_date'=> $_data['to_date']
				);
			}
			else{
				$search = array(
						'txtsearch' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbProductList();
			$this->view->list_pro = $db->getProductsByLocId($id);
			$this->view->branch_name = $db->getLocationNameById($id);
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function rptPurchaseAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'location' =>'',
						'status_search'=>1,
						'supplier_id'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->pur_code = $db->getPurchaseCodeSuplier($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptPurchaseSupplierAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = $search = array(
						'title' =>'',
						'location' =>'',
						'status_search'=>1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$id=$this->getRequest()->getParam('id');
			$this->view->pur_detail=$db->getPurchaseProductDetail($id,$search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptPurchaseallAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' =>'',
					'location' =>'',
					'supplier_id'=>-1,
					'category_id'=>-1,
					'product' =>'',
					'status_search'=>1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->pur_all = $db->getAllPurchase($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptProductsoldAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'study_year'=>'',
						'user' 		=>'',
						'product'=>'',
						'category_id'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'	=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptProductsold();
			$this->view->product_sold = $db->getAllProductSold($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
    public function rptTransferAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$this->view->search=$search;
    		$this-> view->all_transfer = $db->getAllTransfer($search);	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
	}
    public function rptTransferdetailAction(){
		$db = new Accounting_Model_DbTable_DbTransferstock();
		$id=$this->getRequest()->getParam("id");
		$this->view->rs = $db->getTransferById($id);
		$this->view->rsdetail = $db->getTransferByIdDetail($id);
	}
	function rptRequestProductAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'title'	        =>	'',
						'request_for' 	=> -1,
						'for_section' 	=> -1,
						'branch_id'		=>  '',
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
						'status_search'	=> 1
				);
			}
			$db=new Allreport_Model_DbTable_DbRequestStock();
			$ds=$this->view->rows=$db->getAllRequestProduct($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$_pur =  new Accounting_Model_DbTable_DbRequestProduct();
		$req_for = $_pur->getAllRequestFor();
		$this->view->rq_for = $req_for;
		
		$for_section = $_pur->getAllForSection();
		$this->view->for_section = $for_section;
		
		$this->view->search = $search;
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	
	function rptSummaryRequestProductAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'request_for' 	=> -1,
						'for_section' 	=> -1,
						'branch_id'		=>  '',
						'category_id'	=>  '',
						'product'		=>  '',
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
				);
			}
				
			$db=new Allreport_Model_DbTable_DbRequestStock();
			$ds=$this->view->rows=$db->getAllRequestProductDetail($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			echo $e->getMessage();
		}
	
		$_pur =  new Accounting_Model_DbTable_DbRequestProduct();
		$req_for = $_pur->getAllRequestFor();
		$this->view->rq_for = $req_for;
	
		$for_section = $_pur->getAllForSection();
		$this->view->for_section = $for_section;
	
		$this->view->search = $search;
	
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search_pro=$form;
	}
	
	
	public function reprintRequestProductAction(){
		$db = new Allreport_Model_DbTable_DbRequestStock();
		$id=$this->getRequest()->getParam("id");
		$this->view->req = $db->getRequestProductById($id);
		$this->view->req_detail = $db->getAllRequestProductDetailById($id);
	}
	function rptAdjustStockdetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'title'	        =>	'',
						'branch_id'		=>  '',
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
						'status_search'	=> 1
				);
			}
			$this->view->search = $search;
			$db=new Allreport_Model_DbTable_DbRequestStock();
			$ds=$this->view->rows=$db->getAllAdjustStockDetail($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function alertstockAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'location' =>'',
						'status_search'=>1,
						'category_id'=>0,
				);
			}
			$db = new Registrar_Model_DbTable_DbReportProductNearOutStock();
			$this->view->pro_loc = $db->getProductLocation($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptproductqtysoldAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search' 	=>'',
						'start_date'	=>date("Y-m-d"),
						'end_date'		=>date("Y-m-d"),
						'pro_name'		=>'',
						'pro_cate'		=>'',
						'user'=>''
				);
			}
			$db = new Registrar_Model_DbTable_DbProductsold();
			$this->view->rspro = $db->getProductSold($search);
			
			$this->view->pro_cate = $db->getAllProductCategory();
			$this->view->all_pro = $db->getAllProductInProgramName();
				
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	
	function rptSummaryStockAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'title'	        =>	'',
						'branch_id'		=>  '',
						'pro_name'		=>  '',
						'pro_type'		=>  '',
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
						'status_search'	=> 1
				);
			}
			$this->view->search = $search;
			$db=new Allreport_Model_DbTable_DbRptSummaryStock();
			$ds=$this->view->rows=$db->getAllProduct($search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			echo $e->getMessage();//exit();
		}
		
		$db=new Allreport_Model_DbTable_DbRptSummaryStock();
		$this->view->pro_name=$db->getAllProductName();
		
		$this->view->pro_type=$db->getAllProductType();
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
}