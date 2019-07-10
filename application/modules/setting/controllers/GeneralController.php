<?php
class Setting_generalController extends Zend_Controller_Action {
public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db_gs = new Setting_Model_DbTable_DbGeneral();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db_gs->updateWebsitesetting($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/setting/general");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row =array();
		$row['branch_add'] = $db_gs->geLabelByKeyName('branch_add');
		$row['branch_tel'] = $db_gs->geLabelByKeyName('branch_tel');
		$row['branch_email'] = $db_gs->geLabelByKeyName('branch_email');
		$row['label_animation'] = $db_gs->geLabelByKeyName('label_animation');
		$row['show_header_receipt'] = $db_gs->geLabelByKeyName('show_header_receipt');
		$row['receipt_print'] = $db_gs->geLabelByKeyName('receipt_print');
		
		$row['payment_day_alert'] = $db_gs->geLabelByKeyName('payment_day_alert');
		$row['trasfer_st_cut'] = $db_gs->geLabelByKeyName('trasfer_st_cut');
		
		$this->view->allSchoolOption = $db_gs->getAllSchoolOption();
		$fm = new Setting_Form_FrmGeneral();
		$frm = $fm->FrmGeneral($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	
}

