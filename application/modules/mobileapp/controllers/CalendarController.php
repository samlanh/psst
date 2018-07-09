<?php

class Mobileapp_CalendarController extends Zend_Controller_Action
{
    public function init()
    {       
        /* Initialize action controller here */
        header('content-type: text/html; charset=utf8');
        defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
		try{
			$db = new Mobileapp_Model_DbTable_DbCalendar();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllCalendar($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","AMOUNT_DAY","START_DATE","END_DATE","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'calendar','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link));
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
       try{
			$db = new Mobileapp_Model_DbTable_DbCalendar();
			if($this->getRequest()->isPost()){
				$_data = $this->getRequest()->getPost();
				$db->add($_data);
				if(!empty($_data['save_close'])){
					$this->_redirect("mobileapp/calendar");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllFecultyNamess(1);
		
		$frm = new Mobileapp_Form_Frmcalendar();
		$frm_holiday=$frm->FrmAddHoliday(null);
		Application_Model_Decorator::removeAllDecorator($frm_holiday);
		$this->view->frm_holiday = $frm_holiday;

    }

    public function editAction()
    {
       
		$db = new Mobileapp_Model_DbTable_DbCalendar();
		if($this->getRequest()->isPost()){
		  $_data = $this->getRequest()->getPost();
		  try{
			$db->add($_data);
			$this->_redirect("mobileapp/calendar");
		  }catch(Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		  }
		}

		$id = $this->getRequest()->getParam("id");
		$row = $db->getById($id);
		$this->view->row = $row;
	  
		if(empty($row)){
		 $this->_redirect('mobileapp/calendar');
		}   
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllFecultyNamess(1);
		
		$frm = new Mobileapp_Form_Frmcalendar();
		$frm_holiday=$frm->FrmAddHoliday($row);
		Application_Model_Decorator::removeAllDecorator($frm_holiday);
		$this->view->frm_holiday = $frm_holiday; 


    }


}







