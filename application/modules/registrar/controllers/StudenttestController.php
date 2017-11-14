<?php

class Registrar_StudenttestController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/studenttest';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbStudentTest();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'txtsearch'=>'',
    					'degree' => '',
    					'result_status' => '',
    					'start_date'=> null,
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllStudentTest($search);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("SERIAL","STUDENT_ID","NAME_KH","NAME_EN","SEX","PHONE","TEST_DATE","DEGREE","GRADE","SESSION","TIME","BY_USER","STATUS","PROFILE");
    		$link=array(
    				'module'=>'registrar','controller'=>'studenttest','action'=>'edit',
    		);
    		$link1=array(
						'module'=>'registrar','controller'=>'studenttest','action'=>'profile'
			);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('receipt'=>$link,'kh_name'=>$link,'en_name'=>$link,'Profile'=>$link1));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Registrar_Model_DbTable_DbStudentTest();	
// 			print_r($data);exit();			
			try {
				$db->addStudentTest($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/registrar/studenttest");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();exit();
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $db->getAllDegreeName();
		$this->view->session = $db->getAllSession();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbStudentTest();				
			try {
				$db->updateStudentTest($data,$id);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', "/registrar/studenttest");		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbStudentTest();
		$this->view->rs = $row  = $db->getStudentTestById($id);
		if($row['register']==1){
			Application_Form_FrmMessage::Sucessfull('You can not edit because student already registered !!! ', "/registrar/studenttest");
		}
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->degree = $db->getAllDegreeName();
		$this->view->session = $db->getAllSession();
    }
    
    function profileAction(){
    	$id = $this->getRequest()->getParam('id');
    	$db = new Registrar_Model_DbTable_DbStudentTest();
    	$this->view->row = $row = $db->getStudentTestProfileById($id);
    	//print_r($row);exit();
    }
    

}







