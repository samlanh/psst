<?php

class Mobileapp_SchoolbusController extends Zend_Controller_Action
{
	const REDIRECT_URL='/mobileapp/schoolbus';
	protected $tr;
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
			$db = new Mobileapp_Model_DbTable_DbStudentBus();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d')
				);
			}
		
			$rs_rows= $db->getAllStudentBus($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","BUS_CODE","BUS_PLATE_NO","BUS_TYPE","DATE","STATUS");
			$link=array(
					'module'=>'mobileapp','controller'=>'schoolbus','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'busCode'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$frm = new Application_Form_FrmSearch();
		$frm = $frm->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
    }
  
    public function addAction()
    {
		$db = new Mobileapp_Model_DbTable_DbStudentBus();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $db->addStudentBus($_data);
	       	Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	      }
	    }
		$frm = new Mobileapp_Form_FrmSchoolBus();
		$frm = $frm->FrmAddSchoolBus();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
    }

    public function editAction()
    {
       
	    $db = new Mobileapp_Model_DbTable_DbStudentBus();
	    if($this->getRequest()->isPost()){
	      $_data = $this->getRequest()->getPost();
	      try{
	        $db->editStudentBus($_data);
	       	Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	      }catch(Exception $e){
	        $err =$e->getMessage();
	        Application_Model_DbTable_DbUserLog::writeMessageError($err);
	        Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
	      }
	    }
	    $id = $this->getRequest()->getParam("id");
	    $row = $db->getById($id);
	    $this->view->row = $row;
	  
    	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull($this->tr->translate('NO_RECORD'), self::REDIRECT_URL);
	   		exit();
	   	}
	    // $dbglobal = new Application_Model_DbTable_DbGlobal();
	    // $this->view->lang = $dbglobal->getLaguage();

		$frm = new Mobileapp_Form_FrmSchoolBus();
		$frm = $frm->FrmAddSchoolBus($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
   }
   function getbusbybranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Mobileapp_Model_DbTable_DbStudentBus();
			$rows = $db->getStudentBus($data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function getdriverAction(){ 
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
	
			$_db = new RsvAcl_Model_DbTable_DbBranch();
    		$row = $_db->getBranchById($data['branch_id']);//get branch info
    		$schoolOption = $row['schooloptionlist'];
	
			$db = new Application_Model_DbTable_DbGlobal();
			$teacher = $db->getAllTeahcerName($data['branch_id'],$schoolOption,$data['staff_type']);
			array_unshift($teacher, array ('id' => '', 'name' => $this->tr->translate("SELECT_DRIVER")));
			print_r(Zend_Json::encode($teacher));
			exit();
		}
	}


}







