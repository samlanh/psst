<?php
class Registrar_RptbytypeController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
    public function indexAction(){
    	try{
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' =>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$db = new Registrar_Model_DbTable_DbRptByType();
    		$data=$this->view->row = $db->getAllStudentPaymentByType($search,1);
    		$data1=$this->view->rs = $db->getAllStudentPaymentByType($search,2);
    		//print_r($data);
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$this->view->search = $search;
    }
    
}
