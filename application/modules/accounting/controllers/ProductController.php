<?php
class Accounting_ProductController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    							'title' => '',
    							'location' => '',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status_search'=>1,
    					);
    		}
			
			$db =  new Accounting_Model_DbTable_DbProduct();
			$rows = $db->getAllProduct($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rs_rows=$rs_rows->getImgActive($rows, BASE_URL);
			$list = new Application_Form_Frmtable();
			$collumns = array("PRODUCT_NO","BRANCH_NAME","PRODUCT_NAME","PRODUCT_CATEGORY","PRICE",
					"QTY","TOTAL","DATE","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'product','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('pro_code'=>$link,'pro_name'=>$link,'branch_name'=>$link,));
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
				
				$row = $db->addProduct($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/product");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/product/add");
				}
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$frm = new Accounting_Form_FrmServicesuspend();
		$frm_servicesuspend=$frm->FrmServiceSuspend();
		Application_Model_Decorator::removeAllDecorator($frm_servicesuspend);
		$this->view->frm_servicesuspend = $frm_servicesuspend;
		 
		$_pro = new Accounting_Model_DbTable_DbProduct();
		$this->view->branch_name = $_pro->getBrandLocation();
		$this->view->pro_code=$_pro->getProCode();
		$this->view->cat_rows=$_pro->getCatAndMeasure(1);
		$this->view->mea_rows=$_pro->getCatAndMeasure(2);
	
}
public function editAction(){
	$id=$this->getRequest()->getParam('id');
	if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
		$_data['id']=$id;
		try{
				$db = new Accounting_Model_DbTable_DbProduct();
				$row = $db->updateProduct($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/product");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/product/add");
				}
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$frm = new Accounting_Form_FrmServicesuspend();
		$frm_servicesuspend=$frm->FrmServiceSuspend();
		Application_Model_Decorator::removeAllDecorator($frm_servicesuspend);
		$this->view->frm_servicesuspend = $frm_servicesuspend;
		 
		$_pro = new Accounting_Model_DbTable_DbProduct();
		$this->view->branch_name = $_pro->getBrandLocation();
		$this->view->pro_code=$_pro->getProCode();
		$this->view->pro_row=$_pro->getProductById($id);
	    $this->view->pro_locat=$_pro->getProLocationById($id);
	    
	    $this->view->cat_rows=$_pro->getCatAndMeasure(1);
	    $this->view->mea_rows=$_pro->getCatAndMeasure(2);
}

function getStudentAction(){
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		$db = new Accounting_Model_DbTable_DbSuspendservice();
		$studentinfo = $db->getAllStudentInfo($data['studentid']);
		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
		print_r(Zend_Json::encode($studentinfo));
		exit();
	}
}

function getStudentIdAction(){
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		$db = new Accounting_Model_DbTable_DbSuspendservice();
		$year = $db->getStudentID($data['study_year']);
		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
		print_r(Zend_Json::encode($year));
		exit();
	}
}























}