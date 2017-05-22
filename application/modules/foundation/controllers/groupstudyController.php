<?php
class Foundation_GroupstudyController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' => '',
						'study_year' => '',
						'grade_bac' => '',
						'time' => '',
						'session' =>'',
						'start_date'=>date("Y-m-d"),
						'end_date' => date("Y-m-d")
						);
			}
			$db = new Foundation_Model_DbTable_DbGroupStudy();
			$rs_rows= $db->getAllGroups($search);
			$glClass = new Application_Model_GlobalClass();
			//$rs_rows = $glClass->getGetPayTerm($rs_rows, BASE_URL );
			$list = new Application_Form_Frmtable();
			
			$collumns = array("GROUP_CODE","YEARS","SEMESTER","DEGREE","GRADE","SESSION","ROOM_NAME","START_DATE","END_DATE","NOTE");
			
			$link=array(
					'module'=>'foundation','controller'=>'groupstudy','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('group_code'=>$link,'tuitionfee_id'=>$link,'degree'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
		$frm = new Application_Form_FrmOther();
		$this->view->add_major = $frm->FrmAddMajor(null);
		$frm = new Global_Form_FrmSearchMajor();
		$frm = $frm->frmSearchTeacher();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$db= new Foundation_Model_DbTable_DbGroupStudy();
				$db->AddNewGroup($data);
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/foundation/groupstudy");
				}
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$_db = new Foundation_Model_DbTable_DbGroupStudy();
		$this->view->degree = $rows = $_db->getAllFecultyName();
		
		
		$years=new Foundation_Model_DbTable_DbGroupStudy();
		$this->view->row_year=$years->getAllYears();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->payment_term = $model->getAllPaymentTerm(null,1);
		$room = $model->getAllRoom();
		array_unshift($room, array ('id' => -1,'name' => 'Add New'));
		array_unshift($room, array ( 'id' => 0,'name' => 'Select Room'));
		$this->view->room = $room;
		
		//print_r($room);exit();
		$_model = new Foundation_Model_DbTable_DbGroupStudy();
    	$this->view->subject = $_model->getAllSubjectStudy();
		$this->view->year = $_model->getAllYear();
		//print_r($this->view->year);exit();
	}
		
		
	function editAction(){
		$db= new Foundation_Model_DbTable_DbGroupStudy();
		
		if($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				
				$db->updateGroup($data);
				if(!empty($data['save'])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !", "/foundation/groupstudy/index");
				}
				//Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$id=$this->getRequest()->getParam("id");
		
		$this->view->rs =$test =  $db->getGroupById($id);
		//print_r($test);exit();
		
		$this->view->row = $db->getGroupSubjectById($id);
		
// 		print_r($this->view->row);exit();
		
		
		$db = new Foundation_Model_DbTable_DbGroupStudy();
		$this->view->degree = $rows = $db->getAllFecultyName();
		
		$model = new Application_Model_DbTable_DbGlobal();
		
		$faculty =  $model->getAllMajor();
		array_unshift($faculty, Array('id'=> -1 ,'name' =>'Add New'));
		$this->view->faculty =$faculty;
	
		$room = $model->getAllRoom();
		array_unshift($room, Array('id'=> 0 ,'name' =>'Select Room'));
		array_unshift($room, Array('id'=> -1 ,'name' =>'Add New'));
		$this->view->room =$room;
	
		
		$_model = new Foundation_Model_DbTable_DbGroupStudy();
		$this->view->subject = $_model->getAllSubjectStudy();
		$years=new Foundation_Model_DbTable_DbGroupStudy();
		$this->view->row_year=$years->getAllYears();
		$this->view->year = $_model->getAllYear();
	}
	
	
	function addRoomAction(){
		if($this->getRequest()->isPost()){
			try{
				$data=$this->getRequest()->getPost();
				$db = new Foundation_Model_DbTable_DbGroupStudy();
				$room = $db->addNewRoom($data);
				print_r(Zend_Json::encode($room));
				exit();
			}catch (Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
		}
	}
	
	
	
	
	
	
}

