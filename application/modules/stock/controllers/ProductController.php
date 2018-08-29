<?php
class Stock_ProductController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	const REDIRECT_URL = '/stock/product';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction()
    {
    	try{
	    	$db = new Global_Model_DbTable_DbItemsDetail();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'items_search'=>"",
	    				'product_type_search'=>-1,
	    				'status_search' => -1
	    		);
	    	}
	    	$type=3; //Product
	    	$rs_rows= $db->getAllProduct($search,$type);
	    	$glClass = new Application_Model_GlobalClass();
	    	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("CODE","PRODUCT_NAME","PRODUCT_CATEGORY","UNIT_COST","QTY","TYPE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'stock','controller'=>'product','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('code'=>$link ,'title'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->AddProduct($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    			
    	}
    	$type=3; //Product
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$model = new Application_Model_DbTable_DbGlobal();
    	$branch = $model->getAllBranchName();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
    	
    }
    
    public function editAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$db->updateProduct($_data);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
	    		exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=3; //Product
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	$this->view->productBranch = $db->getProductLocation($id);
    	
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	 
    	$model = new Application_Model_DbTable_DbGlobal();
    	$branch = $model->getAllBranchName();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
		
    }
// 	function addNewProCateAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Accounting_Model_DbTable_DbProduct();
// 			$pro_cate = $db->AddProCate($data);
// 			print_r(Zend_Json::encode($pro_cate));
// 			exit();
// 		}
// 	}
    public function copyAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
    			$_data = $this->getRequest()->getPost();
    			$db->AddProduct($_data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=3; //Product
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	$this->view->productBranch = $db->getProductLocation($id);
    	 
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	 
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$model = new Application_Model_DbTable_DbGlobal();
    	$branch = $model->getAllBranchName();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
    
    }
}