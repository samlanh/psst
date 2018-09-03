<?php

class Mobileapp_FeedbackController extends Zend_Controller_Action
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
			$db = new Mobileapp_Model_DbTable_DbFeedBack();
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
			$rs_rows= $db->getAllFeedback($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("FEEDBACK","STUDENT","DATE","VIEW","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'contact','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$frm = new Application_Form_FrmSearch();
		$frm = $frm->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
    }
    public function replyAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	if (empty($id)){
    		$this->_redirect("mobileapp/feedback");
    	}
    	$search = null;
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	$this->view->stu_id =$id;
    	$db = new Mobileapp_Model_DbTable_DbFeedBack();
    	$rs_rows= $db->getStuIdById($search,$id);
    	$this->view->studentChat = $rs_rows;
    	if (empty($rs_rows)){
    		$this->_redirect("mobileapp/feedback");
    	}
    	$db->updateMessage($id);
    }
	function sentmessageAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Mobileapp_Model_DbTable_DbFeedBack();
			$data=$db->AddFeedback($data);
			print_r(Zend_Json::encode($data));
			exit();
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
                $this->_redirect("mobileapp/contact");
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
       
    $db = new Mobileapp_Model_DbTable_DbContact();
    if($this->getRequest()->isPost()){
      $_data = $this->getRequest()->getPost();
      try{
        $db->add($_data);
        //Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'),self::REDIRECT_URL . '/Banner');
        $this->_redirect("mobileapp/contact");
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
     $this->_redirect('mobileapp/contact');
    }   
    //$fm = new Other_Form_FrmBanner();
    //$frm = $fm->FrmAddBanner($row);
    //Application_Model_Decorator::removeAllDecorator($frm);
    //$this->view->frm = $frm;  

    }


}







