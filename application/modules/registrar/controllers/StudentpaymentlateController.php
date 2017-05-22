<?php
class Registrar_StudentpaymentlateController extends Zend_Controller_Action {	
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
						'adv_search' =>'',
						'study_year' =>-1,
						'branch_id'=>0,
						'service' =>-1,
						'end_date'=> date('Y-m-d'),
				);
			}
			$db = new Registrar_Model_DbTable_DbRptStudentPaymentLate();
			$abc = $this->view->row = $db->getAllStudentPaymentLate($search);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	public function addAction(){
		$this->_redirect('registrar/studentpaymentlate/index');
	}
}