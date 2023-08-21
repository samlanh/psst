<?php

class Mobileapp_StudentbusscheduleController extends Zend_Controller_Action
{
	const REDIRECT_URL='/mobileapp/studentbusschedule';
	protected $tr;
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	 defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	 $this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }

    public function indexAction()
    {
        try{
			$db = new Mobileapp_Model_DbTable_DbBusSchedule();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d')
				);
			}
			$rs_rows= $db->getAllStudentBus($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","BUS_CODE","DRIVER","BUS_PLATE_NO","DATE","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'studentbusschedule','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('BranchName'=>$link,'SchoolBus'=>$link,'Driver'=>$link ));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmSearch();
		$frm = $frm->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
    }
  
    public function addAction()
    {
		$db = new Mobileapp_Model_DbTable_DbBusSchedule();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $db->addBusSchedule($_data);
	       	Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	      }
	    }
		$frm = new Mobileapp_Form_FrmSchoolBus();
		$frm = $frm->FrmSchoolBusSchedule();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
    }

    public function editAction()
    {
       
	    $db = new Mobileapp_Model_DbTable_DbBusSchedule();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $db->editStudentBus($_data);
	       	Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
	      }
	    }
	    $id = $this->getRequest()->getParam("id");
	    $row = $db->getById($id);
	    $this->view->row = $row;
	  
    	// if (empty($row)){
	   	// 	Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   	// 	exit();
	   	// }

		$rowDetail = $db->getBusScheduleDetail($row);
		$this->view->rowDetail = $rowDetail;

		$frm = new Mobileapp_Form_FrmSchoolBus();
		$frm = $frm->FrmSchoolBusSchedule();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
   }


}







