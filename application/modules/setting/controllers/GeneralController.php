<?php
class Setting_generalController extends Zend_Controller_Action {
public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function addAction(){
		$this->_redirect('/setting/general');
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
		$row['discount_percent'] = $db_gs->geLabelByKeyName('discount_percent');
		$row['discount_amount'] = $db_gs->geLabelByKeyName('discount_amount');
		$row['other_fee'] = $db_gs->geLabelByKeyName('other_fee');
		$row['test_period'] = $db_gs->geLabelByKeyName('test_period');
		$row['trasfer_st_cut'] = $db_gs->geLabelByKeyName('trasfer_st_cut');
		$row['sale_cut_stock'] = $db_gs->geLabelByKeyName('sale_cut_stock');
		
		$row['welcomeAudio'] = $db_gs->geLabelByKeyName('welcomeAudio');
		$row['logo'] = $db_gs->geLabelByKeyName('logo');
		$row['settingStuID'] = $db_gs->geLabelByKeyName('settingStuID');
		
		$row['schooolNameKh'] = $db_gs->geLabelByKeyName('schooolNameKh');
		$row['schooolNameEng'] = $db_gs->geLabelByKeyName('schooolNameEng');
		
		$row['hornorTableSetting'] = $db_gs->geLabelByKeyName('hornorTableSetting');

		$row['new_stuid_test'] = $db_gs->geLabelByKeyName('new_stuid_test');
		$row['doc_display'] = $db_gs->geLabelByKeyName('doc_display');
		$row['name_required'] = $db_gs->geLabelByKeyName('name_required');
		$row['entry_stuid'] = $db_gs->geLabelByKeyName('entry_stuid');
		$row['pay_as_group'] = $db_gs->geLabelByKeyName('pay_as_group');
		$row['show_pic_receipt'] = $db_gs->geLabelByKeyName('show_pic_receipt');
		$row['test_online'] = $db_gs->geLabelByKeyName('test_online');
		$row['show_groupin_payment'] = $db_gs->geLabelByKeyName('show_groupin_payment');
		$row['receipt_paddingtop'] = $db_gs->geLabelByKeyName('receipt_paddingtop');
		$row['receipt_start_setting'] = $db_gs->geLabelByKeyName('receipt_start_setting');
		
		$row['studentPrefixOpt'] = $db_gs->geLabelByKeyName('studentPrefixOpt');
		$row['studentIPrefix'] = $db_gs->geLabelByKeyName('studentIPrefix');
		$row['studentIdLength'] = $db_gs->geLabelByKeyName('studentIdLength');
		$row['teacher_doc'] = $db_gs->geLabelByKeyName('teacher_doc');
		$row['payment_date'] = $db_gs->geLabelByKeyName('payment_date');
		$row['studyday_schedule'] = $db_gs->geLabelByKeyName('studyday_schedule');
		
		$row['criteriaLimitation'] = $db_gs->geLabelByKeyName('criteriaLimitation');
		$row['footerScoreDocument'] = $db_gs->geLabelByKeyName('footerScoreDocument');

		$row['branch_display_setting'] = $db_gs->geLabelByKeyName('branch_display_setting');

		$row['receive_note_print'] = $db_gs->geLabelByKeyName('receive_note_print');

		$row['study_info_setting'] = $db_gs->geLabelByKeyName('study_info_setting');
		
		$this->view->allSchoolOption = $db_gs->getAllSchoolOption();
		$this->view->allAudioGrade = $db_gs->getAllGradeAudio();
		$this->view->allPlaylistvideo = $db_gs->getAllPlaylistvideo();
		$fm = new Setting_Form_FrmGeneral();
		$frm = $fm->FrmGeneral($row);
		
		$this->view->row = $row;
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	public function editAction()
	{
	}
	
}

