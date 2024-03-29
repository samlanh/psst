<?php
class Accounting_SpecaildiscountController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/global/degree';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
    public function indexAction()
    {
    	$db_dept=new Accounting_Model_DbTable_DbSpecailDis();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'advance_search' => "",
    				'dis_type'=>"",
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'status_type' => "",
    		);
    	}
        $rs_rows = $db_dept->getAllSpecailDis($search);
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("REQUEST_NAME","PHONE","STUDENT_NAME","DISCOUNT_TYPE","PERIOD","EXPIRE_DATE","STATUS","NOTE","USER");
    	$link=array(
    			'module'=>'accounting','controller'=>'specaildiscount','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('request_name'=>$link,'phone'=>$link,'stu_name'=>$link));
    	
    	$frm = new Global_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    	
    }
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$sms="INSERT_SUCCESS";
    			$_data = $this->getRequest()->getPost();
    			$db = new Accounting_Model_DbTable_DbSpecailDis();
    			$specail_id= $db->AddSpecailDis($_data);
    			if($specail_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms,"/accounting/specaildiscount");
    			}
    			Application_Form_FrmMessage::Sucessfull($sms,"/accounting/specaildiscount/add");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	
    	$frm = new Accounting_Form_FrmSpecail();
    	$frm->FrmAddSpecailDocument(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_specail = $frm;
    }
    
    public function editAction(){
    	$db = new Accounting_Model_DbTable_DbSpecailDis();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try {
    			$sms="INSERT_SUCCESS";
    			$_data = $this->getRequest()->getPost();
    			$specail_id= $db->UpdateSpecailDis($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms,"/accounting/specaildiscount");
    			}
    			Application_Form_FrmMessage::Sucessfull($sms,"/accounting/specaildiscount/add");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	$row = $db->getSpecailDis($id);
    	$document = $db->getSpecailDisDetail($id);
    	$this->view->document = $document;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record","/accounting/specaildiscount");
    		exit();
    	}
    	$frm = new Accounting_Form_FrmSpecail();
    	$frm->FrmAddSpecailDocument($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_specail = $frm;
    }

}

