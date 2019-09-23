<?php
class Foundation_ChangebranchController extends Zend_Controller_Action {
	
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search=array(
				'branch_id'	=>'',
				'title'	=>'',
				'study_year'=> '',
				'grade'=> '',
				'session'=> '',
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
			);
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_student= new Foundation_Model_DbTable_DbChangeBranch();
		$rs_rows = $db_student->selectAllStudentChangeGroup($search);
		
		$list = new Application_Form_Frmtable();
		if(!empty($rs_rows)){
		}else{
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$collumns = array("BRANCH","STUDENT_ID","STUDENT_NAMEKHMER","NAME_EN","SEX","FROM_GROUP","ACADEMIC_YEAR","GRADE","SESSION","TO_BRANCH","TO_GROUP","ACADEMIC_YEAR","GRADE","SESSION","MOVING_DATE","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'changebranch','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		$this->view->adv_search=$search;	
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbChangeBranch();
 				$_add->addStudentChangeBranch($_data);
 				if(!empty($_data['save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/changebranch");
 				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/changebranch/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Foundation_Form_FrmChangeBranch();
		$frm->FrmAddChangeBranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		
	}
	function revertAction(){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$checkses = $dbgb->checkSessionExpire();
		if (empty($checkses)){
			$dbgb->reloadPageExpireSession();
			exit();
		}
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$db = new Foundation_Model_DbTable_DbChangeBranch();
		$row = $db->getStudentChangeBranchById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/changebranch");
			exit();
		}
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$delete_sms=$tr->translate('CONFIRM_REVERT');
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/foundation/changebranch/revertrecord/id/".$id."'";
		echo"}";
		echo"else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/foundation/changebranch'";
		echo"}
		</script>";
	
	}
	function revertrecordAction(){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$checkses = $dbgb->checkSessionExpire();
		if (empty($checkses)){
			$dbgb->reloadPageExpireSession();
			exit();
		}
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
		
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db = new Foundation_Model_DbTable_DbChangeBranch();
		try {
			$dbacc = new Application_Model_DbTable_DbUsers();
			$rs = $dbacc->getAccessUrl($module,$controller,'revert');
			if(!empty($rs)){
				$db->revertChangeBranch($id);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/changebranch");
				exit();
			}
			Application_Form_FrmMessage::Sucessfull("You no permission","/foundation/changebranch");
			exit();
		}catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			exit();
		}
	}
	function getToGroupAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$grade = $db->getStudentGroupInfoById($data['to_group']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	function getStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbChangeBranch();
			$grade = $db->getStudentInfoById($data['studentid']);
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	function getalltobranchAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$branch_id = empty($data['branch_id'])?0:$data['branch_id'];
			$db = new Foundation_Model_DbTable_DbChangeBranch();
			$arr = $db->getAllBranch($branch_id);
			print_r(Zend_Json::encode($arr));
			exit();
		}
	}
	
}
