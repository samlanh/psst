<?php
class Stock_AdjuststockController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
			$db =  new Accounting_Model_DbTable_DbAdjustStock();
			$rows = $db->getAllAdjustStock($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rows=$rs_rows->getImgActive($rows, BASE_URL);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("ADJUST_NO","TITLE","NOTE","DATE","TOTAL","STATUS","USER");
			$link=array(
					'module'=>'stock','controller'=>'adjuststock','action'=>'edit',
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
					$db = new Accounting_Model_DbTable_DbAdjustStock();
					$row = $db->addAdjustStock($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/adjuststock");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/adjuststock/add");
					}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			$_pur = new Accounting_Model_DbTable_DbAdjustStock();
// 			$pro=$_pur->getProducCutStockLater();
// 			array_unshift($pro, array ( 'id' => -1,'name' => 'Add New'));
// 			$this->view->product= $pro;
// 			print_r($pro);exit();
			
			$this->view->rq_code=$_pur->getAjustCode();
			$this->view->bran_name=$_pur->getAllBranch();
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
			
			$db = new Global_Model_DbTable_DbItemsDetail();
			$d_row= $db->getAllProductsNormal(2);//
			array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
			array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
			$this->view->product= $d_row;
	}
	
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
					$db = new Accounting_Model_DbTable_DbAdjustStock();
					$row = $db->updateAdjustStock($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/adjuststock");
					}else{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/adjuststock");
					}
					Application_Form_FrmMessage::message("EDIT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			$_pur = new Accounting_Model_DbTable_DbAdjustStock();

			$this->view->row=$_pur->getAdjustStockById($id);
			$this->view->row_detail=$_pur->getAdjustStockDetail($id);
			
			$this->view->rq_code=$_pur->getAjustCode();
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
			
			$db = new Global_Model_DbTable_DbItemsDetail();
			$d_row= $db->getAllProductsNormal(2);//
			array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
			array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
			$this->view->product= $d_row;
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
    		array_unshift($gty, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		array_unshift($gty, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }

}