<?php
class Foundation_StudenttrandropController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search= array(
						'title' 		=> '',
						'academic_year'	=> '',
						'degree'		=> '',
						'status_search'	=> -1,
				);
			}
			$this->view->search = $search;
			$db_student= new Foundation_Model_DbTable_DbStudenttranDrop();
			$rs_rows = $db_student->getAllStudranDrop($search);
			$list = new Application_Form_Frmtable();
				$collumns = array("STUDENT_ID","STUDENT_NAME","SEX","DEGREE","YEARS","TYPE","SESSION","ROOM","STOP_DATE","REASON","USER","STATUS");
				$link=array(
						'module'=>'accounting','controller'=>'studenttrandrop','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name_kh'=>$link,'name_en'=>$link,));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
			//print_r($form); exit();
	}
	public function stutrandropAction(){
		$id=$this->getRequest()->getParam("id");
		$_db = new Foundation_Model_DbTable_DbStudenttranDrop();
		if($this->getRequest()->isPost())
		{
			try{
				$sms="INSERT_SUCCESS";
				$data = $this->getRequest()->getPost();
				$data["id"]=$id;
				$row=$_db->addStudentDrop($data);
				if($row==-1){
					$sms = "RECORD_EXIST";
				}
				Application_Form_FrmMessage::Sucessfull($sms,"/foundation/studenttrandrop/index");
				Application_Form_FrmMessage::message($sms);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("TRAN_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db= new Foundation_Model_DbTable_DbStudent();
		$row = $db->getStudentById($id);
		$group = $db->getAllgroup();
		//array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
		$this->view->group = $group;
	
		$_db = new Application_Model_DbTable_DbGlobal();
		$row =$_db->getOccupation();
		$this->view->occupation = $row;
	
		$row = $_db->getAllLangLevel(); // degree language
		$this->view->lang_level = $row;
	
		$row = $_db->getAllKnoyBy(); // degree language
		$this->view->know_by = $row;
	
		$row = $_db->getAllDocumentType(); // degree language
		$this->view->doc_type = $row;
	
		$this->view->degree = $db->getAllFecultyName();
	
		$test =  $db->getStudentById($id);
		$this->view->rs = $test;
		$this->view->row = $db->getStudentDocumentById($id);
		//echo $test['group_id'];exit();
	
		$this->view->year = $db->getAllYear();
		$this->view->room = $row =$db->getAllRoom();
	
		$tsub= new Foundation_Form_FrmStudentRegister();
		$frm_register=$tsub->FrmStudropRegister($test);
		Application_Model_Decorator::removeAllDecorator($frm_register);
		$this->view->frm = $frm_register;
	}	
}
