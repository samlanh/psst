<?php
class Registrar_ChangeproductController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbChangeProduct();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
    		
			$rs_rows= $db->getAllChangeProduct($formdata);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("RECEIPT_NO","NAME","TOTAL_PAYMENT","CREDIT_MEMO","CREATE_DATE","USER","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'changeproduct','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('name'=>$link,'receipt_no'=>$link,'invoice'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbChangeProduct();	
			//print_r($data);exit();			
			try {
				$db->addChangeProduct($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/changeproduct");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();
			}
		}

		$db = new Registrar_Model_DbTable_DbChangeProduct();
    	$test = $this->view->all_product = $db->getAllProduct();
    	
    	$this->view->stu_code = $db->getAllStuCode();
    	$this->view->stu_name = $db->getAllStuName();
    	
    	//print_r($test);exit();
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbChangeProduct();				
			try {
				$db->editChangeProduct($data,$id);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/registrar/changeproduct");
				}else{
					Application_Form_FrmMessage::message("EDIT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				echo $e->getMessage();
			}
		}

		$db = new Registrar_Model_DbTable_DbChangeProduct();
    	$test = $this->view->all_product = $db->getAllProduct();
    	
    	$this->view->stu_code = $db->getAllStuCode();
    	$this->view->stu_name = $db->getAllStuName();
    	
    	
    	$data = $this->view->row = $db->getAllChangeProductById($id);
    	if($data['is_void']==1){
    		Application_Form_FrmMessage::Sucessfull("You can not edit !!! ","/registrar/changeproduct");
    	}
    	
    	$this->view->row_detail = $db->getAllChangeProductDetailById($id);
    }
    
    function getProductPriceAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbChangeProduct();
    		$pro_price = $db->getProductPrice($data['pro_id']);
    		print_r(Zend_Json::encode($pro_price));
    		exit();
    	}
    }
}







