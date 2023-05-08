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
		$this->view->rs = $db->getAllStaffSelected($condition);
		$this->view->groupByType = $db->getAllStaffSelectedGroupBy($condition);
// 		print_r($db->getAllStaffSelectedGroupBy($condition));exit();
		
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
						'teacher_type' => -1,
						'nationality' => '',
						'branch_id' => '',
						'status' => -1,
						'active_type' => -1,
					);
			}
		
		$this->view->rs= $db->getAllTeacher($search);
		$this->view->groupByType = $db->getAllTeacherCard($search);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
		
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
					'teacher_type' => -1,
					'nationality' => '',
					'branch_id' => '',
					'end_date'=>date('Y-m-d',strtotime("+5 day")),
			);
		}
		$this->view->rs= $db->getTeachDocumentAlert($search);
		
		$frm = new Global_Form_FrmSearchMajor();
		$this->view->frm_search = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		
		$this->view->search =$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
	}
	
}

