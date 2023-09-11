<?php
class Issue_AchievementController extends Zend_Controller_Action {
	
	const REDIRECT_URL='/issue/achievement';
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
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'branch_id' => '',
					'group' => '',
					'status' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$db = new Issue_Model_DbTable_DbAchievement();
			$rs_rows= $db->getAllAchievement($search);
			$list = new Application_Form_Frmtable();
				
			$collumns = array("BRANCH","GROUP_CODE","STUDENT","USER","DATE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'achievement','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'group_code'=>$link));
			
			$this->view->search = $search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->frm=$form;
	}
	public function addAction(){
		$_db = new Issue_Model_DbTable_DbAchievement();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="INSERT_SUCCESS";
				$_db->addStudentAchievement($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL);
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL."/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$rsAch= $_db->getAllAchievementType();
		array_unshift($rsAch, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->achievementType = $rsAch;
		
		$frm = new Issue_Form_FrmAchievement();
		$frm->FrmStudentAchievement(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	public function editAction(){
		$_db = new Issue_Model_DbTable_DbAchievement();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$sms="EDIT_SUCCESS";
				$_db->updateIssueLetterpraise($_data);
				Application_Form_FrmMessage::Sucessfull($sms,self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row =$_db->getStudentAchievementById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL);
			exit();
		}
		$this->view->row = $row;
		$rsAch= $_db->getAllAchievementType();
		array_unshift($rsAch, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->achievementType = $rsAch;
		
		$frm = new Issue_Form_FrmAchievement();
		$frm->FrmStudentAchievement($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
}