<?php

class Mobileapp_IndexController extends Zend_Controller_Action
{
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
    	$this->_redirect("mobileapp/label");
    }

    public function addAction()
    {
       try{
        $db = new Mobileapp_Model_DbTable_DbNotice();
        if($this->getRequest()->isPost()){
            $_data = $this->getRequest()->getPost();
            $db->add($_data);
            if(!empty($_data['save_close'])){
                $this->_redirect("mobileapp/index");
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
    }

    public function editAction()
    {
       
    $db = new Mobileapp_Model_DbTable_DbNotice();
    if($this->getRequest()->isPost()){
      $_data = $this->getRequest()->getPost();
      try{
        $db->add($_data);
        //Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'),self::REDIRECT_URL . '/Banner');
        $this->_redirect("mobileapp/index");
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
     $this->_redirect('mobileapp/index');
    }   
    //$fm = new Other_Form_FrmBanner();
    //$frm = $fm->FrmAddBanner($row);
    //Application_Model_Decorator::removeAllDecorator($frm);
    //$this->view->frm = $frm;  


    }


}







