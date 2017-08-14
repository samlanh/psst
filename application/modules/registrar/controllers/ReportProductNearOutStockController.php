<?php
class Registrar_ReportProductNearOutStockController extends Zend_Controller_Action {
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
}
