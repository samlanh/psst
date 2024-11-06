<?php
class Global_GradeController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/grade';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
    public function indexAction()
    {
    	try{
	    	$db = new Global_Model_DbTable_DbItemsDetail();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'items_search'=>"",
	    				'status_search' => -1,
	    				'is_onepayment'=>-1,
	    				'auto_payment'=>-1
	    		);
	    	}
	    	$type=1; //Degree
	    	$rs_rows= $db->getAllItemsDetail($search,$type);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("MAJOR_KHNAME","MAJOR_ENNAME","SHORTCUT","DEGREE","ONE_PAYMENT","ORDERING","NOTE","CREATE_DATE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'global','controller'=>'grade','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('shortcut'=>$link ,'title'=>$link ,'title_en'=>$link,'ordering'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_data['auto_payment']=0;
    			$_major_id = $db->AddItemsDetail($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			Application_Form_FrmMessage::message($sms);
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=1; //Degree
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$d_row = $_dbgb->getAllItems(1);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    	
    	$_db = new Foundation_Model_DbTable_DbGroup();
    	$this->view->row_year=$_db->getAllYears();
    	$this->view->subject_opt = $_db->getAllSubjectStudy();
    }
    
	public function editAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$_data['auto_payment']=0;
	    		$db->updateItemsDetail($_data,$id);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=1; //Degree
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->rowData = $row; 
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$d_row = $_dbgb->getAllItems(1);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;    

    	$_db = new Foundation_Model_DbTable_DbGroup();
    	$this->view->row_year=$_db->getAllYears();
    	$this->view->subject_opt = $_db->getAllSubjectStudy();
    	
    	$db = new Global_Model_DbTable_DbItems();
    	$rs =  $db->getGradeSubjectById($id);
    	$this->view->row=$rs;
   }    
   public function copyAction(){
    	$db = new Global_Model_DbTable_DbItemsDetail();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$_data['auto_payment']=0;
	    		$db->AddItemsDetail($_data,$id);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=1; //Degree
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$frm = new Global_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$d_row = $_dbgb->getAllItems(1);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    	
    	$_db = new Foundation_Model_DbTable_DbGroup();
    	$this->view->row_year=$_db->getAllYears();
    	$this->view->subject_opt = $_db->getAllSubjectStudy();
    	 
    	$db = new Global_Model_DbTable_DbItems();
    	$rs =  $db->getGradeSubjectById($id);
    	$this->view->row=$rs;
    }
    function adddegreeAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItemsDetail();
    			$degree = $db->addDegreeByAjax($data);
    			print_r(Zend_Json::encode($degree));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    function addDeptandsubjectAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Global_Model_DbTable_DbItemsDetail();
    			$degree = $db->addDegreeByAjax($data);
    			 
    			$_db = new Global_Model_DbTable_DbGroup();
    			$sub_option = $_db->getAllSubjectStudy();
    			 
    			$result = array(
    					"degree"=>$degree,
    					"sub_option"=>$sub_option,
    			);
    			 
    			print_r(Zend_Json::encode($result));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    
    function getAllitemidAction(){
   	 	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$rs = $db->getAllItemDetail($data);
			if(!empty($data['addNew'])){
				array_unshift($rs, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
			//	array_unshift($rs, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
			}
    		
			
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
	function productinfoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Global_Model_DbTable_DbItemsDetail();
			$_row = $db->getProductInfoByLocationItem($data);
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}
	function getGradeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$param = array(
				'categoryId'=>$data['dept_id']
			);
			$grade = $_dbgb->getAllGradeStudyByDegree($param);
			if(empty($data['noaddnew'])){
				array_unshift($grade, array ( 'id' => -1, 'name' =>$this->tr->translate("ADD_NEW")));
			}
			array_unshift($grade, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GRADE")));
			print_r(Zend_Json::encode($grade));
			exit();
		}
	}
}