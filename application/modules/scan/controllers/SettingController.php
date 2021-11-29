<?php
class Scan_settingController extends Zend_Controller_Action {
public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
	}
	public function addAction(){
		$this->_redirect('/scan/general');
	}
	public function indexAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db_gs = new Scan_Model_DbTable_DbGeneral();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/scan/setting");
					exit();
				}
				$db_gs->updateWebsitesetting($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/scan/setting");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row =array();
		$row['welcomeAudio'] = $db_gs->geLabelByKeyName('welcomeAudio');
		
		$row['confirmGetInAudio'] 	= $db_gs->geLabelByKeyName('confirmGetInAudio');
		$row['denyGetInAudio'] 		= $db_gs->geLabelByKeyName('denyGetInAudio');
		$row['confirmGetOutAudio'] 	= $db_gs->geLabelByKeyName('confirmGetOutAudio');
		$row['denyGetOutAudio'] 	= $db_gs->geLabelByKeyName('denyGetOutAudio');
		
		$this->view->allSchoolOption = $db_gs->getAllSchoolOption();
		$this->view->allAudioGrade = $db_gs->getAllGradeAudio();
		$this->view->allPlaylistvideo = $db_gs->getAllPlaylistvideo();
		$fm = new Scan_Form_FrmGeneral();
		$frm = $fm->FrmGeneral($row);
		
		$this->view->row = $row;
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_general = $frm;
	}
	
}

