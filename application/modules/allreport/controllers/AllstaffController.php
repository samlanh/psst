<?php
class Allreport_AllstaffController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{	
	}
	
	public function staffIdselectedAction(){
		$id=$this->getRequest()->getParam('id');
		//print_r($id);
		$k = 0;
		$condition = '';
		$ids = explode(',', $id);
		foreach ($ids as $id_staff){
			if($k==0){
				$condition .= $id_staff;
				$k=1;
			}else{
				$condition .= ' or id = '.$id_staff;
			}
		}
		//echo $condition;
		$db = new Allreport_Model_DbTable_DbRptAllStaff();
		$this->view->rs = $rs_rows = $db->getAllStaffSelected($condition);
		
		
	}
	
	public function rptAllStaffAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'branch_id'		=>0,
					'degree'		=>0,
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'session' 		=>'',
					'stu_type' 		=>-1,
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$group= new Allreport_Model_DbTable_DbRptAllStaff();
		$this->view->rs = $rs_rows = $group->getAllStaff($search);
		$this->view->search=$search;
	}
	
	
}

