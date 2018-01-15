<?php
class Accounting_RequestProductController extends Zend_Controller_Action {
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
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status_search'=>1,
    					);
    		}
			$db =  new Accounting_Model_DbTable_DbRequestProduct();
			$rows = $db->getAllRequest($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("REQUEST_NO","REQUEST_NAME","PURPOSE","REQUEST_DATE","TOTAL","STATUS","DATE");
			$link=array(
					'module'=>'accounting','controller'=>'requestproduct','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rows,array('request_no'=>$link,'request_name'=>$link,'purpose'=>$link,));
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$form=new Accounting_Form_FrmSearchProduct();
			$form=$form->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
					$db = new Accounting_Model_DbTable_DbRequestProduct();
					$row = $db->addRequest($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/requestproduct");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/requestproduct/add");
					}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			$_pur = new Accounting_Model_DbTable_DbRequestProduct();
			$pro=$_pur->getProducCutStockLater();
			array_unshift($pro, array ( 'id' => -1,'name' => 'Add New'));
			$this->view->product= $pro;
			
			$this->view->rq_code=$_pur->getRequestCode();
			$this->view->bran_name=$_pur->getAllBranch();
			
			$db_gr=new Global_Model_DbTable_DbGrade();
			$d_row=$db_gr->getNameGradeAll();
			array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
			$this->view->grade_name=$d_row;
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
	}
	
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
					$db = new Accounting_Model_DbTable_DbRequestProduct();
					$row = $db->updateRequest($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/requestproduct");
					}else{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/requestproduct");
					}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			$_pur = new Accounting_Model_DbTable_DbRequestProduct();
			$pro=$_pur->getProducCutStockLater();
			$this->view->row=$_pur->getRequestById($id);
			$this->view->row_detail=$_pur->getRequestDetail($id);
			array_unshift($pro, array ( 'id' => -1,'name' => 'Add New'));
			$this->view->product= $pro;
			
			$this->view->rq_code=$_pur->getRequestCode();
			$this->view->bran_name=$_pur->getAllBranch();
			
			$db_gr=new Global_Model_DbTable_DbGrade();
			$d_row=$db_gr->getNameGradeAll();
			array_unshift($d_row, array ( 'id' => -1,'name' => 'បន្ថែមថ្មី'));
			$this->view->grade_name=$d_row;
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
	}

    function getSupplierInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbPurchase();
    		$row = $db->getSuplierInfo($data['sup_id']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
    
    function addProductAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_dbmodel = new Accounting_Model_DbTable_DbPurchase();
    		$id = $_dbmodel->ajaxAddProduct($_data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
    
    function getProductqtyAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRequestProduct();
    		$gty= $db->getProductQty($data['branch_id'],$data['pro_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    
    function getProBylocationAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRequestProduct();
    		$gty= $db->getAllProductBybranch($data['branch_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }

}