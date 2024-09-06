<?php

class Mobileapp_NotificationController extends Zend_Controller_Action
{
	const REDIRECT_URL='/mobileapp/notification';
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
			$db = new Mobileapp_Model_DbTable_DbNotification();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'branch_id' => '',
						'degree' => '',
						'group' => '',
						'status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllNotification($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","TITLE","NORTIFICATION_OPT","DEGREE","GROUP_CODE","STUDENT","DATE","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'notification','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link,'option_type'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->frm=$form;
    }
  
    public function addAction()
    {
       try{
	        $db = new Mobileapp_Model_DbTable_DbNotification();
			$dbPushNoti = new Api_Model_DbTable_DbPushNotification();
	        if($this->getRequest()->isPost()){
	            $_data = $this->getRequest()->getPost();
	            $rs = $db->add($_data);
				$notify = array(
						"notificationId" => $rs,
						"optNotification" => $_data["opt_notification"],
						"degreeId" => $_data["degree"],
						"groupId" => $_data["groupId"],
						"studentId" => $_data["studentId"],
						"typeNotify" => "notificationArticle",
					);
				$dbPushNoti->pushNotificationAPI($notify);
	            if(isset($_data['save_close'])){
	              Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
	            }else{
	                Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL."/add");
	            }
	        }
	       
	        $dbglobal = new Application_Model_DbTable_DbGlobal();
			$rsbranch = $dbglobal->getAllBranch();
			array_unshift($rsbranch, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
			$this->view->rsbranch = $rsbranch; 
			
	        $this->view->lang = $dbglobal->getLaguage();
	        
	        $this->view->rsDegree = $dbglobal->getAllItems(1);//degree
	        
	    }catch (Exception $e){
	        Application_Form_FrmMessage::message("Application Error");
	        Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    }
    }

    public function editAction()
    {
       
	    $db = new Mobileapp_Model_DbTable_DbNotification();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $rs = $db->add($_data);
			
			$notify = array(
						"notificationId" => $rs,
						"optNotification" => $_data["opt_notification"],
						"degreeId" => $_data["degree"],
						"groupId" => $_data["groupId"],
						"studentId" => $_data["studentId"],
						"typeNotify" => "notificationArticle",
					);
				$dbPushNoti = new Api_Model_DbTable_DbPushNotification();
				$dbPushNoti->pushNotificationAPI($notify);
				
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
	  
    	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   		exit();
	   	}
	    
	    $dbglobal = new Application_Model_DbTable_DbGlobal();
		$rsbranch = $dbglobal->getAllBranch();
		array_unshift($rsbranch, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
		$this->view->rsbranch = $rsbranch; 
	    
	    $this->view->lang = $dbglobal->getLaguage();
	    $this->view->rsDegree = $dbglobal->getAllItems(1);//degree

   }
   function deleteAction(){
	   	try{
	   		$id = $this->getRequest()->getParam("id");
	   		$db = new Mobileapp_Model_DbTable_DbNotification();
	   		if (!empty($id)) {
				$row = $db->getById($id);
				$this->view->row = $row;
				if (empty($row)){
					Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
					exit();
				}else{
					$db->deleteData($id);
					Application_Form_FrmMessage::message($this->tr->translate('DELETE_SUCCESS'));
					echo "<script>window.close();</script>";
				}
	   			
	   		}
	   	}catch(Exception $e){
	   		Application_Form_FrmMessage::message($this->tr->translate('DELETE_FAIL'));
	   		$err =$e->getMessage();
	   		Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		echo "<script>window.close();</script>";
	   	}
   }

}







