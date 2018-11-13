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
		$this->view->groupByType = $rs_rows = $db->getAllStaffSelectedGroupBy($condition);
		
	}
	
	public function rptAllStaffAction(){
		$db = new Allreport_Model_DbTable_DbRptAllStaff();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' => '',
						'degree' => '',
						'staff_type' => '',
						'nationality' => '',
						'branch_id' => '',
						'status' => -1);
			}
		
		$this->view->rs= $db->getAllTeacher($search);
		$this->view->groupByType = $db->getAllTeacherCard($search);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
	}
	public function rptTeacheralertAction(){
		$db = new Allreport_Model_DbTable_DbRptAllStaff();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
					'title' => '',
					'degree' => '',
					'nationality' => '',
					'branch_id' => '',
					'end_date'=>date('Y-m-d',strtotime("+5 day")),
			);
		}
		$this->view->rs= $db->getTeachDocumentAlert($search);
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		
		$this->view->search =$search;
	}
	
}

