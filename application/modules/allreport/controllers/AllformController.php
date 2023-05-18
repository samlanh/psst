<?php
class Allreport_AllformController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){	
		
	}
	
	
    function monthlyOutstandingStudentAction(){
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,2);
    	$this->view->all_student = $db->getStundetScoreDetailGroup($search,$id,1);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
}





