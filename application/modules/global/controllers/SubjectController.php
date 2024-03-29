<?php
class Global_SubjectController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Global_Model_DbTable_DbSubjectExam();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' => '',
						'status_search' => -1,
						'schoolOption_search' => ''
					);
			}
			$rs_rows = $db->getAllSujectName($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("SUBJECT_IN_KH","SUBJECT_IN_EN","SHORTCUT","PARENT","STUDY_IN_LANG","SCHOOL_OPTION","MODIFY_DATE","USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'subject','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('shortcut'=>$link,'subject_titlekh'=>$link,'subject_titleen'=>$link));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm1 = new Global_Form_FrmSearchMajor();
		$frm =$frm1->SubjectExam();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
					$sms="INSERT_SUCCESS";
					$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
				 	$_dbmodel->addNewSubjectExam($_data);
			
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/subject");
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/global/subject/add");
				}
				Application_Form_FrmMessage::message($sms);
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}		
		$subject_exam=new Global_Form_FrmAddSubjectExam();
		$frm_subject_exam=$subject_exam->FrmAddSubjectExam();
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
		
		$parent = new Global_Model_DbTable_DbSubjectExam();
		$is_parent = $parent->getAllSubjectParent();
		$this->view->rs = $is_parent;
	}
	function addsubjectAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
			$data['status']=1;	
			$id=$_dbmodel->addNewSubjectExam($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
				$_dbmodel->updateSubjectExam($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/subject/index");
			}catch(Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();
		$row = $_dbmoddel->getSubexamById($id);
		$this->view->rsRow = $row;
		
		$subject_exam = new Global_Form_FrmAddSubjectExam();
		$frm_subject_exam = $subject_exam->FrmAddSubjectExam($row);
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
		
		$parent = new Global_Model_DbTable_DbSubjectExam();
		$is_parent = $parent->getAllSubjectParent();
		$this->view->rs = $is_parent;
		
		$getRow = $parent->getAllSubjectParentByID($id);
		$this->view->row = $getRow;		
	}

	function copyAction()
	{
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
				$_dbmodel->addNewSubjectExam($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/subject/index");
			}catch(Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();
		$row = $_dbmoddel->getSubexamById($id);
		$this->view->rsRow = $row;
		
		$subject_exam = new Global_Form_FrmAddSubjectExam();
		$frm_subject_exam = $subject_exam->FrmAddSubjectExam($row);
		Application_Model_Decorator::removeAllDecorator($frm_subject_exam);
		$this->view->frm_subject_exam = $frm_subject_exam;
		
		$parent = new Global_Model_DbTable_DbSubjectExam();
		$is_parent = $parent->getAllSubjectParent();
		$this->view->rs = $is_parent;
		
		$getRow = $parent->getAllSubjectParentByID($id);
		$this->view->row = $getRow;		
	}

	function addsubjectajaxAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
			$data['status']=1;
			$option=$_dbmodel->addSubjectajax($data);
			print_r(Zend_Json::encode($option));
			exit();
		}
	}	
	function getsubjectAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$data['schoolOption'] = empty($data['schoolOption'])?null:$data['schoolOption'];
			if (!empty($data['academic_year'])){
				$_dbfee = new Accounting_Model_DbTable_DbFee();
				$row = $_dbfee->getFeeById($data['academic_year']);
				$data['schoolOption'] = $row['school_option'];
			}else if (!empty($data['branch_id'])){
				$data['schoolOption'] = $db->getSchoolOptionListByBranch($data['branch_id']);
			}
			$arrayFilter = array(
					"schoolOption"=>$data['schoolOption'],
					"typesubject"=>1,
				);
			$subject = $db->getAllSubjectName($arrayFilter); // 1=select only study subject
			
			$blankTitle=$this->tr->translate("PLEASE_SELECT");
			if(!empty($data['blankTitle'])){
				$blankTitle=$data['blankTitle'];
			}
			array_unshift($subject, array ('id' => 0, 'name' => $blankTitle));
			print_r(Zend_Json::encode($subject));
			exit();
		}
	}
	
	//new create on 24-3-2020
	function getsubjectbybranchAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
	
			$_db = new RsvAcl_Model_DbTable_DbBranch();
    		$row = $_db->getBranchById($data['branch_id']);//get branch info
    		$schoolOption = $row['schooloptionlist'];
	
			if (!empty($schoolOption)){
				$db = new Application_Model_DbTable_DbGlobal();
				$arrayFilter = array(
					"schoolOption"=>$schoolOption,
					"typesubject"=>1,
					'isOption'=>empty($data['isOption'])?'':$data['isOption']
				);
				$subject = $db->getAllSubjectName($arrayFilter);
				if(empty($data['isOption'])){
					array_unshift($subject, array ('id' => '', 'name' => $this->tr->translate("ADD_NEW")));
					array_unshift($subject, array ('id' => 0, 'name' => ''));
				}
				print_r(Zend_Json::encode($subject));
				exit();
			}
		}
	}
	function getsubjectbygradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbItems();
			$group = $db->getGradeSubjectById($data['grade_id']);
			print_r(Zend_Json::encode($group));
			exit();
		}
	}
	
	function checkduplicateAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$title = empty($data['subjectKhTitle'])?"":$data['subjectKhTitle'];
			$subLang = empty($data['subjectLang'])?"":$data['subjectLang'];
    		$id = empty($data['id'])?"":$data['id'];
    		$arr  = array(
    				'title'=>$title,
					'subLang'=>$subLang,
    				'id'=>$id,
    				);
    		
    		$_dbmodel = new Global_Model_DbTable_DbSubjectExam();
    		$result=$_dbmodel->checkuDuplicate($arr);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
}