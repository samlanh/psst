<?php

class ExternalController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }

    public function indexAction()
    {
    	
    	$session_user=new Zend_Session_Namespace("AAAAAA");
    	$username = $session_user->first_name;
    	$user_id = $session_user->user_id;
    	if (!empty($user_id)){
    		$this->_redirect("/home");
    	}
    	$this->_helper->layout()->disableLayout();
		$form=new Application_Form_FrmLogin();				
		$form->setAction('index');		
		$form->setMethod('post');
		$form->setAttrib('accept-charset', 'utf-8');
		$this->view->form=$form;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);		
        
		if($this->getRequest()->isPost())		
		{
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$sys = $dbgb->getPh();
			if (!$sys){
 				//$session_user=new Zend_Session_Namespace(SYSTEM_SES);
 				//$session_user->unsetAll();
				//Application_Form_FrmMessage::redirectUrl("/");
				//exit();
			}
			
			$formdata=$this->getRequest()->getPost();
			if($form->isValid($formdata))
			{
				$session_lang=new Zend_Session_Namespace('lang');
				$session_lang->lang_id=$formdata["lang"];//for creat session
				Application_Form_FrmLanguages::getCurrentlanguage($formdata["lang"]);//for choose lang for when login
				$user_name=$form->getValue('txt_user_name');
				$password=$form->getValue('txt_password');
				$db_user=new Application_Model_DbTable_DbUsers();
				if($db_user->userAuthenticate($user_name,$password)){					
					
					$session_user=new Zend_Session_Namespace(SYSTEM_SES);
					$user_id=$db_user->getUserID($user_name);
					$user_info = $db_user->getUserInfo($user_id);
					
					$arr_acl=$db_user->getArrAcl($user_info['user_type']);
					$a_i = 0;
					$arr_actin = array();
					$arr_module=array();
					for($i=0; $i<count($arr_acl);$i++){
						$arr_module[$i]=$arr_acl[$i]['module'];
					}
					$arr_module=(array_unique($arr_module));
					$arr_actin=(array_unique($arr_actin));
					$arr_module=$this->sortMenu($arr_module);
						
					$session_user->arr_acl = $arr_acl;
					$session_user->arr_module = $arr_module;
					$session_user->arr_actin = $arr_actin;
					
					$session_user->user_id=$user_id;
					$session_user->user_name=$user_name;
					$session_user->pwd=$password;
					$session_user->level= $user_info['user_type'];
					$session_user->last_name= $user_info['last_name'];
					$session_user->first_name= $user_info['first_name'];
					$session_user->branch_id= $user_info['branch_id'];
					$session_user->branch_list= $user_info['branch_list'];
					$session_user->schoolOption= $user_info['schoolOption'];

					$db = new Application_Model_DbTable_DbUsers();
					$creditmemo = $db->getAccessUrl("accounting","creditmemo","index");
					if (empty($creditmemo)){
						$session_user->isnot_creditmemo_acl= 1;
					}
					$lecturer_acl = $db->getAccessUrl("foundation","lecturer","index");
					if (empty($lecturer_acl)){
						$session_user->isnot_lecturer_acl= 1;
					}
					$student_acl = $db->getAccessUrl("foundation","register","index");
					if (empty($student_acl)){
						$session_user->isnot_student_acl= 1;
					}
					$cutstock_acl = $db->getAccessUrl("stock","cutstock","index");
					if (empty($cutstock_acl)){
						$session_user->isnot_cutstock_acl= 1;
					}
					
					$crm_acl = $db->getAccessUrl("home","crm","index");
					if (empty($crm_acl)){
						$session_user->isnot_crm_acl= 1;
					}
					$session_user->timeout= time();
					
					$session_user->lock();
					$log=new Application_Model_DbTable_DbUserLog();
					$log->insertLogin($user_id);
					foreach ($arr_module AS $i => $d){
						$url = self::REDIRECT_URL;
						break;
					}	
					Application_Form_FrmMessage::redirectUrl("/home");	
					exit();										
				}
				else{					
					$this->view->msg = 'ឈ្មោះ​អ្នក​ប្រើ​ប្រាស់ និង ពាក្យ​​សំងាត់ មិន​ត្រឺម​ត្រូវ​ទេ ';
				}	
			}
			else{				
				$this->view->msg = 'លោកអ្នកមិនមានសិទ្ធិប្រើប្រាស់ទេ!';
			}			
		}
		$session_lang=new Zend_Session_Namespace('lang');
		if (empty($session_lang->lang_id)){
			$session_lang->lang_id =1;
		}
		$this->view->rslang = $session_lang->lang_id;
    }
}





