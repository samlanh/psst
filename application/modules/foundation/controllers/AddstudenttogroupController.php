
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
					'study_year' => '',
					'group' => '',
					'degree' => '',
					'grade' => '',
					'session' => '',
					'room' => '',
					);
		}
		
		$rs= $db->getGroupDetail($search);
		$list = new Application_Form_Frmtable();
		$this->view->search = $search;
		if(!empty($rs)){
		}
		else{
			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
		}
		$collumns = array("BRANCH","GROUP_ID","ACADEMIC_YEAR","DEGREE","GRADE","SESSION","ROOM_NAME","SEMESTER","NOTE","STATUS","AMOUNT_STUDENT","REMAIN_STUDENT");
		$link=array(
				'module'=>'foundation','controller'=>'addstudenttogroup','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, $rs,array());
		
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
						'academic_year'=> ''
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
				if(empty($_data['public-methods'])){
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
	
	public function submit1Action(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbAddStudentToGroup();
				$row = $db->editStudentGroup($_data, $id);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->_redirect('/foundation/addstudenttogroup/index');
	}
	function editAction(){
		$this->_redirect('/foundation/addstudenttogroup');
		exit();
		$id=$this->getRequest()->getParam("id");
		$_db = new Foundation_Model_DbTable_DbAddStudentToGroup();
		$g_id = $_db->getGroupById($id);
		
		$this->view->id = $g_id;
		$row = $_db->getStudentGroup($id);
		$this->view->rr = $row;
			try{
				if($this->getRequest()->isPost()){
					$_data=$this->getRequest()->getPost();
					$search = array(
							'degree' => $_data['degree'],
							'grade' => $_data['grade'],
							'session' => $_data['session'],
							'academy'=> $_data['academy']);
					$rs = $_db->getSearchStudent($search);
					$this->view->rs = $rs;
				}else{
					$search = array(
							'degree' => 0,
							'grade' => 0,
							'session' => 0,
							'academy'=> 0
							);
				}
			
				$this->view->value=$search;
		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $_db->getAllDegreeName();
		
		$group = new Foundation_Model_DbTable_DbAddStudentToGroup();
		$group_option = $group->getGroupToEdit();
		array_unshift($group_option, array ( 'id' => -1, 'name' =>$this->tr->translate("ADD_NEW")) );
		$this->view->group = $group_option;
		$this->view->room = $group->getRoom();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$this->view->rs_session=$db->getSession();
	}
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
// 			$db = new Foundation_Model_DbTable_DbStudent();
// 			$grade = $db->getAllGrade($data['dept_id']);
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$grade = $_dbgb->getAllGradeStudyByDegree($data['dept_id']);
			array_unshift($grade, array ( 'id' => '', 'name' => $this->tr->translate("SELECT_GRADE")) );
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
	
	
}
