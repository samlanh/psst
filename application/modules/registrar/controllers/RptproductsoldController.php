<?php
class Registrar_RptproductsoldController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search' 	=>'',
						'start_date'	=>date("Y-m-d"),
						'end_date'		=>date("Y-m-d"),
						'user'			=>'',
						'stu_code'		=>'',
						'stu_name'		=>'',
						'pro_name'		=>'',
				);
			}
			$db = new Registrar_Model_DbTable_DbProductsold();
			$this->view->rspro = $db->getProductSold($search);
			
			$this->view->all_pro = $db->getAllProductInProgramName($search);
			
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
}
