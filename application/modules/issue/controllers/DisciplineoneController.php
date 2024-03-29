<?php
class Issue_DisciplineoneController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStudentdisciplineOne();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group_name' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			
			$this->view->search=$search;
			$rs_rows = $db->getAllDiscipline($search);
			$list = new Application_Form_Frmtable();
			$collumns = array( "BRANCH","STUDENT_ID","NAME","GROUP","ACADEMIC_YEAR","DEGREE","GRADE","ROOM","SESSION","MISTAKE_DATE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'disciplineone','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'academic_year'=>$link,'stu_code'=>$link,'stu_name'=>$link,'group_name'=>$link,'academy'=>$link,'degree'=>$link,'grade'=>$link,'semester'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Issue_Model_DbTable_DbStudentdisciplineOne();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(isset($_data['save_new'])){
					 $rs = $db->addDisciplineOne($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/disciplineone/add");
				}else {
					 $rs =  $db->addDisciplineOne($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/disciplineone");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$branch = $db_global->getAllBranch();
		$this->view->branch = $branch;
		
		$this->view->row_year = $db_global->getAllYear();
		$this->view->session = $db_global->getSession();
		$this->view->degree = $db_global->getDegree();
		$this->view->grade = $db_global->getAllGrade();
		$this->view->room = $db_global->getAllRoom();
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_model = new Issue_Model_DbTable_DbStudentdisciplineOne();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				$rs = $_model->updateStudentAttendenceOne($_data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/issue/disciplineone");		
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$result = $_model->getAttendencetByIDDiscipline($id);
		if (empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/issue/disciplineone");
			exit();
		}
		$this->view->row=$result;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db_global->getAllBranch();;
		$this->view->row_year=$db_global->getAllYear();
		$this->view->session=$db_global->getSession();
		$this->view->degree=$db_global->getDegree();
		$this->view->group = $db_global->getAllgroupStudyNotPass($result['group_id']);
		$this->view->room = $row =$db_global->getAllRoom();
		$this->view->grade = $db_global->getAllGrade();
		
	}
}