<?php

class RsvAcl_UserController extends Zend_Controller_Action
{
	
	const REDIRECT_URL = '/rsvacl';
	const MAX_USER = 20;
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $user_typelist = array();
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT u.user_type_id AS id,u.user_type AS name FROM `rms_acl_user_type` u where u.`status`=1";
    	$this->user_typelist = $db->getGlobalDb($sql);
    }
    public function indexAction()
    {
		$db_user=new Application_Model_DbTable_DbUsers();
        $this->view->activelist =$this->activelist;       
        $this->view->user_typelist =$this->user_typelist;   
        $this->view->active =-1;
        $tr = Application_Form_FrmLanguages::getCurrentlanguage();
        if($this->getRequest()->isPost()){     	
        	$_data=$this->getRequest()->getPost();
        }else{
        	$_data = array(
        			'status_search'=>-1,
        			'user_type'=>-1,
        			'txtsearch'=>''
        	);
        }
        $rs_rows = $db_user->getUserList($_data);
        $_rs = array();
        foreach ($rs_rows as $key =>$rs){
        	$branch=$tr->translate("FULLCONTROL");
        	if ($rs['typeid']!=1){
        		$branch= count(explode(",",$rs['branch_list']))." ".$tr->translate("BRANCH");
        	}
        	$_rs[$key] =array(
        	'id'=>$rs['id'],
        	'branch_access'=>$branch,
        	'name'=>$rs['last_name'].' '.$rs['name'],
        	'user_name'=>$rs['user_name'],
        	'user_type'=>$rs['users_type'],
        	'status'=>$rs['status']);
        }
        $list = new Application_Form_Frmtable();
        if(!empty($_rs)){
        	$glClass = new Application_Model_GlobalClass();
        	$rs_rows = $glClass->getImgActive($_rs, BASE_URL, true);
        }
        else{
        	$result = Application_Model_DbTable_DbGlobal::getResultWarning();
        }
        $collumns = array("BRANCH_ACCESS","LASTNAME_FIRSTNAME","USER_NAME","USER_TYPE","STATUS");
        $link=array(
        		'module'=>'rsvacl','controller'=>'user','action'=>'edit',
        );
        $this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_access'=>$link,'user_type'=>$link,'user_name'=>$link,'name'=>$link));
    	$this->view->user_type = $_data['user_type'];
    	$this->view->txtsearch = $_data['txtsearch'];
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_user = $frm;
    }
	public function addAction()
	{
			$db_user=new Application_Model_DbTable_DbUsers();
			if($this->getRequest()->isPost()){
				$userdata=$this->getRequest()->getPost();
				try {
					$sms="INSERT_SUCCESS";
					$_user = $db_user->insertUser($userdata);
					if($_user==-1){
						$sms = "RECORD_EXIST";
					}
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL);
				} catch (Exception $e) {
				}
			}
			$db  = new Application_Model_DbTable_DbGlobal();
			$this->view->rs_branch = $db->getAllBranch();
			
			$user_type = $this->user_typelist;
			$this->view->user_typelist =$user_type;
			array_unshift($user_type, array('id'=>-1,'name'=>$this->tr->translate("ADD_NEW")));
			$this->view->user_type = $user_type;
			$this->view->schoolOption = $db->getAllSchoolOption();
	}
	public function editAction()
	    {
	    	$db_user=new Application_Model_DbTable_DbUsers();
	    	if($this->getRequest()->isPost()){
				$userdata=$this->getRequest()->getPost();	
				try {
					$sms="INSERT_SUCCESS";
					$_user = $db = $db_user->updateUser($userdata);	
					if($_user==-1){
						$sms = "RECORD_EXIST";
					}			
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL);		
				} catch (Exception $e) {
					
				}
			}
			$us_id = $this->getRequest()->getParam('id');
			$us_id = (empty($us_id))? 0 : $us_id;
			
			$this->view->user_edit = $db_user->getUserEdit($us_id);

			$user_type = $this->user_typelist;
			$this->view->user_typelist =$user_type;
				
			array_unshift($user_type, array('id'=>-1,'name'=>$this->tr->translate("ADD_NEW")));
			$this->view->user_type = $user_type;
			
			$db  = new Application_Model_DbTable_DbGlobal();
			$this->view->rs_branch = $db->getAllBranch();
			$this->view->schoolOption = $db->getAllSchoolOption();
    }
//     public function changePasswordAction()
// 	{
// 		$session_user=new Zend_Session_Namespace('authstu');
		
// 		if($session_user->user_id==$this->getRequest()->getParam('id') OR $session_user->level == 1){
// 			$form = new RsvAcl_Form_FrmChgpwd();	
// 			//echo $form->getElement('current_password'); exit;	
// 			$this->view->form=$form;
// 			//echo "Work"; exit; 
			
// 			if($this->getRequest()->isPost())
// 			{
// 				$db=new RsvAcl_Model_DbTable_DbUser();
// 				$user_id=$this->getRequest()->getParam('id');		
// 				if(!$user_id) $user_id=0;			
// 				$current_password=$this->getRequest()->getParam('current_password');
// 				$password=$this->getRequest()->getParam('password');
// 				if($db->isValidCurrentPassword($user_id,$current_password)){ 
// 					$db->changePassword($user_id, md5($password));	
// 					      //write log file 
// 					             $userLog= new Application_Model_Log();
// 					    		 $userLog->writeUserLog($user_id);
// 					     	  //End write log file		
// 					Application_Form_FrmMessage::message('Password has been changed');
// 					Application_Form_FrmMessage::redirector('/rsvacl/user/view-user/id/'.$user_id);
// 				}else{
// 					Application_Form_FrmMessage::message('Invalid current password');
// 				}
// 			}		
// 		}else{ 
// 			   Application_Form_FrmMessage::message('Access Denied!');
// 		       Application_Form_FrmMessage::redirector('/rsvacl');	
// 		}
// 	}
	function getschooloptionAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$gty= $db->getAllSchoolOption($data['branch_id']);
			print_r(Zend_Json::encode($gty));
			exit();
		}
	}
	function getschooloptionbybranchlistAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$branchList="";
			if (!empty($data['selector'])){
				foreach ($data['selector'] as $rs){
					if (empty($branchList)){
						$branchList = $rs;
					}else { $branchList = $branchList.",".$rs;
					}
				}
			}
			$db = new Application_Model_DbTable_DbGlobal();
			$gty= $db->getAllSchoolOption($branchList);
			print_r(Zend_Json::encode($gty));
			exit();
		}
	}
}
