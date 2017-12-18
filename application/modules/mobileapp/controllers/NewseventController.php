<?php

class Mobileapp_NewseventController extends Zend_Controller_Action
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
			$db = new Mobileapp_Model_DbTable_DbNewsEvent();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllNewEvent($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","DATE","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'about','action'=>'edit',
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
        $db = new Mobileapp_Model_DbTable_DbNewsEvent();
        if($this->getRequest()->isPost()){
            $_data = $this->getRequest()->getPost();
            $db->add($_data);
            if(!empty($_data['save_close'])){
                $this->_redirect("mobileapp/newsevent");
            }else{
                Application_Form_FrmMessage::message("INSERT_SUCCESS");
            }
        }
       // $frm = new Other_Form_FrmBanner();
       // $frm_manager=$frm->FrmAddBanner();
     //   Application_Model_Decorator::removeAllDecorator($frm_manager);
       // $this->view->frm = $frm_manager;
    }catch (Exception $e){
        Application_Form_FrmMessage::message("Application Error");
        Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    }
  //  $dbglobal = new Application_Model_DbTable_DbVdGlobal();
    //    $this->view->lang = $dbglobal->getLaguage();
    }

    public function editAction()
    {
       
    $db = new Mobileapp_Model_DbTable_DbNewsEvent();
    if($this->getRequest()->isPost()){
      $_data = $this->getRequest()->getPost();
      try{
        $db->add($_data);
        //Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'),self::REDIRECT_URL . '/Banner');
        $this->_redirect("mobileapp/newsevent");
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
     $this->_redirect('mobileapp/newsevent');
    }   
    //$fm = new Other_Form_FrmBanner();
    //$frm = $fm->FrmAddBanner($row);
    //Application_Model_Decorator::removeAllDecorator($frm);
    //$this->view->frm = $frm;  


    }


}







