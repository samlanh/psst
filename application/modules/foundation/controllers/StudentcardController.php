<?php
class Foundation_StudentcardController extends Zend_Controller_Action {
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
					
					if(isset($_data['print_card'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentcard/index");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/foundation/studentcard/index");
					}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}
			$_pur = new Stock_Model_DbTable_DbAdjustStock();
			
			$this->view->rq_code=$_pur->getAjustCode();
			$this->view->bran_name=$_pur->getAllBranch();
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
			
			
	}
	
    
    
	
    function getStudentBylocationAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
			$branch_id = $data['branch_id'];
		
		
    		$db = new Foundation_Model_DbTable_DbStudent();
    		$rows= $db->getAllStudentBybranch($branch_id);
			
    		array_unshift($rows, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_STUDENT_NAME")));
    		print_r(Zend_Json::encode($rows));
    		exit();
    	}
    }
	function getStudentinfoByidAction(){
		if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
			$stu_id=$data['stu_id'];
    		$db = new Foundation_Model_DbTable_DbStudent();
    		$row= $db->getStdyInfoById($stu_id);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}


	}
	
}