<?php
class Accounting_ProductsetController extends Zend_Controller_Action {
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
    			$search['sale_set']=1;
    		}
    		else{
    			$search=array(
    							'title' => '',
    							//'location' => '',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status_search'=>1,
    							'sale_set'=>1,
    					);
    		}
			
			$db =  new Accounting_Model_DbTable_DbProductset();
			$rows = $db->getAllProductSetGroup($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rs_rows=$rs_rows->getImgActive($rows, BASE_URL);
			$list = new Application_Form_Frmtable();
			$collumns = array("PRODUCT_CODE","PRODUCT_NAME","PRODUCT_CATEGORY","PRICE",
					"DATE","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'productset','action'=>'edit',
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
				$db = new Accounting_Model_DbTable_DbProductset();
				
				$row = $db->addProductSetGroup($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/productset");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/productset/add");
				}
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
			 
			$_pro = new Accounting_Model_DbTable_DbProductset();
			$this->view->productopt = $_pro->getAllProductOption();
			$this->view->pro_code=$_pro->getProCode();
			
			$pro_cate = $_pro->getProductCategory();
			
			array_unshift($pro_cate, array('id'=>'-1' , 'name'=>$this->tr->translate("ADD_NEW")));
			
			$this->view->cat_rows = $pro_cate;
			
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
					$db = new Accounting_Model_DbTable_DbProductset();
					$row = $db->updateProductSetDetail($_data);
					
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/productset");
					}else{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/accounting/productset/add");
					}
					
					Application_Form_FrmMessage::message("EDIT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
					echo $e->getMessage();
				}
			}
			 
			$_pro = new Accounting_Model_DbTable_DbProductset();
			//$this->view->branch_name = $_pro->getBrandLocation();
			
			$this->view->productopt = $_pro->getAllProductOption();
			
			$this->view->pro_detail=$_pro->getProDetailById($id);
			
			$this->view->pro_code=$_pro->getProCode();
			$this->view->pro_row=$_pro->getProductById($id);
		    $this->view->pro_locat=$_pro->getProLocationById($id);
		    
		    $this->view->cat_rows=$_pro->getProductCategory();
	}



	function addNewProCateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbProductset();
			$pro_cate = $db->AddProCate($data);
			print_r(Zend_Json::encode($pro_cate));
			exit();
		}
	}





}





