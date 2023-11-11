<?php

class Mobileapp_ContactController extends Zend_Controller_Action
{
	const REDIRECT_URL='/mobileapp/contact';
	protected $tr;
    public function init()
    {       
        /* Initialize action controller here */
        header('content-type: text/html; charset=utf8');
        defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
        $this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }

    function indexAction(){
    	try{
	    	$db = new Mobileapp_Model_DbTable_DbContact();
	    	$id = $this->getRequest()->getParam("id");
	    	if($this->getRequest()->isPost()){
	    		$_data = $this->getRequest()->getPost();
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("Image File is to large can't upload and Save data !",self::REDIRECT_URL);
					exit();
				}
	    		$db->updateContactLocation($_data);
	    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	    	}
	    	$row = $db->getContact();
	    	$this->view->contact = $row;
	    	
	    	$dbglobal = new Application_Model_DbTable_DbGlobal();
	    	$this->view->lang = $dbglobal->getLaguage();
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }



    public function addAction()
    {
       try{
        $db = new Mobileapp_Model_DbTable_DbContact();
        if($this->getRequest()->isPost()){
            $_data = $this->getRequest()->getPost();
            $db->add($_data);
            if(!empty($_data['save_close'])){
                Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
            }else{
                Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL."/add");
            }
        }
    }catch (Exception $e){
        Application_Form_FrmMessage::message("Application Error");
        Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    }
    }

    public function editAction()
    {
	    $db = new Mobileapp_Model_DbTable_DbContact();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $db->add($_data);
	        Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_FAIL'), self::REDIRECT_URL);
	      }
	    }
	    $id = $this->getRequest()->getParam("id");
	    $id = empty($id)?0:$id;
	    $row = $db->getById($id);
	    $this->view->row = $row;
   		if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   		exit();
	   	}  
    }

}







