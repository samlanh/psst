<?php
class Foundation_GroupstudentchangegroupController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search'	=>'',
						'branch_id' => '',
						'academic_year' => '',
						'grade'	=>'',
						'session' => '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$db_student= new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$rs_rows = $db_student->selectAllStudentChangeGroup($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","FROM_GROUP","ACADEMIC_YEAR","GRADE","TYPE","TO_GROUP","ACADEMIC_YEAR","GRADE","SESSION","MOVING_DATE","NOTE","STATUS");
			$link=array(
					'module'=>'foundation','controller'=>'groupstudentchangegroup','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows);
			$this->view->adv_search = $search;
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$_add = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
 				$_add->addGroupStudentChangeGroup($data);
  				if(!empty($data['save_close'])){
  					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/groupstudentchangegroup");
  				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/groupstudentchangegroup/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$tsub= new Foundation_Form_FrmGroupStuChangeGroup();
		$frm=$tsub->FrmAddGroupChangeGroup();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
				$row=$db->updateStudentChangeGroup($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/foundation/groupstudentchangegroup/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}	
		$db= new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		$result = $db->getAllGroupStudentChangeGroupById($id);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/foundation/groupstudentchangegroup/index");
			exit();
		}
		$this->view->rs = $result;
		$this->view->studentpass = $db->selectStudentPass($result['from_group']);
		$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
		$this->view->row = $db->getfromGroup();
		$g_new=$db->getGroupNewAll();
		array_unshift($g_new,array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->g_new=$g_new;
		$this->view->rows = $db->gettoGroup();
		$this->view->change_type = $db->getChangeType();

		$tsub= new Foundation_Form_FrmGroupStuChangeGroup();
		$frm=$tsub->FrmAddGroupChangeGroup($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch = $db->getAllBranch();
		$this->view->branch = $branch;
	}
	
	
	function getToGroupAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$grade = $db->getStudentGroupInfoById($data['to_group']);
			print_r(Zend_Json::encode($grade));
		}
		exit();
	}
	
	function getAllStudentAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$student = $db->getAllStudentFromGroupChangeGroup($data);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}
	
	function getAllStudentUpdateAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Foundation_Model_DbTable_DbGroupStudentChangeGroup();
			$student = $db->getAllStudentFromGroupUpdate($data['from_group']);
			print_r(Zend_Json::encode($student));
			exit();
		}
	}	
	
	
    
	
}











