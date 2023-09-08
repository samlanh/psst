<?php

class Accounting_NotificationController extends Zend_Controller_Action
{
	const REDIRECT_URL='/accounting/notification';
	const FromDepartment='2';
	const OptNotification='3';
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
			$db = new Accounting_Model_DbTable_DbNotification();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'branch_id' => '',
						'group' => '',
						'status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllNotification($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","TITLE","NORTIFICATION_OPT","GROUP_CODE","STUDENT","DATE","STATUS");
			$link=array(
					'module'=>'accounting','controller'=>'notification','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link,'option_type'=>$link));
			$this->view->search=$search;
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
	        $db = new Accounting_Model_DbTable_DbNotification();
			$dbPushNoti = new Api_Model_DbTable_DbPushNotification();
	        if($this->getRequest()->isPost()){
	            $_data = $this->getRequest()->getPost();
	            $_data["fromDepartment"] = self::FromDepartment;
	            $_data["optNotification"] = self::OptNotification;
	            $rs = $db->addNotification($_data);
				$notify = array(
						"notificationId" => $rs,
						"optNotification" => self::OptNotification,
						"degreeId" => 0,
						"groupId" => empty($data['groupId'])?"":$data['groupId'],
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
			$this->view->rsbranch = $dbglobal->getAllBranch();
	        $this->view->lang = $dbglobal->getLaguage();
	       
	        
	    }catch (Exception $e){
	        Application_Form_FrmMessage::message("Application Error");
	        Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    }
    }

    public function editAction()
    {
       
	    $db = new Accounting_Model_DbTable_DbNotification();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
		 
	      try{
			$_data["fromDepartment"] = self::FromDepartment;
			$_data["optNotification"] = self::OptNotification;
	        $db->addNotification($_data);
			Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
	      }
	    }
	
	    $id = $this->getRequest()->getParam("id");
		$id=empty($id) ?0:$id;
	    $row = $db->getById($id);
	    $this->view->row = $row;
    	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   		exit();
	   	}
	    
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $dbglobal->getAllBranch();
		$this->view->lang = $dbglobal->getLaguage();

   }
   function deleteAction(){
	   	try{
	   		$id = $this->getRequest()->getParam("id");
	   		$db = new Accounting_Model_DbTable_DbNotification();
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







