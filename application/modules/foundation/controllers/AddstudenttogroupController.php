<?php
class Foundation_AddstudenttogroupController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Foundation_Model_DbTable_DbAddStudentToGroup();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
					'adv_search' => '',
					'branch_id' => '',
					'academic_year' => '',
					'group' => '',
					'degree' => '',
					'grade' => '',
					'session' => '',
					'room' => '',
					);
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$last = $dbgb->getLatestAcadmicYear();
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
		}
		$rs= $db->getGroupDetail($search);
		$list = new Application_Form_Frmtable();
		$this->view->search = $search;
		$collumns = array("BRANCH","GROUP_ID","ACADEMIC_YEAR","SESSION","ROOM_NAME","SEMESTER","NOTE","STATUS");
		$link=array(
				'module'=>'foundation','controller'=>'addstudenttogroup','action'=>'edit',
		);
		
		$actionLink = array(
						array("title" =>"STUDENT_LIST_REPORT","recordConnect" =>"id" ,"link" => "/allreport/allstudent/rpt-student-group","linkType"=>"inframe" )
						,array("title" =>"STUDENT_LIST","recordConnect" =>"id" ,"link" => "/allreport/allstudent/rpt-student-list","linkType"=>"inframe" )
						);
				
			$additionalOption = array(
				"actionLink" => $actionLink,
			);
			
		$this->view->list=$list->getCheckList(10, $collumns, $rs,array(),$additionalOption);
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		$db = new Foundation_Model_DbTable_DbAddStudentToGroup();
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = $_data;
				$rs =$db->getSearchStudent($search);
				$this->view->rs = $rs;
			}else{
				$search = array(
						'branch_id' => '',
						'degree' => '',
						'grade' => '',
						'session' => '',
						'academic_year'=> '',
						'sortStundent'=>0
					);
			}
			$this->view->search=$search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function submitAction(){
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				if(empty($_data['identity'])){
					Application_Form_FrmMessage::Sucessfull("PLEASE SELECT STUDENTS FIRST",'/foundation/addstudenttogroup/add');
					exit();
				}
				$db = new Foundation_Model_DbTable_DbAddStudentToGroup();
				$db->addStudentGroup($_data);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				if(isset($_data['save_close'])){
					$this->_redirect('/foundation/addstudenttogroup');
				}else{
					$this->_redirect('/foundation/addstudenttogroup/add');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
}