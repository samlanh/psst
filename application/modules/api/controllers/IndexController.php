<?php
class Api_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/api/index';
	
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	
    }
    public function paymentAction(){
    	
    	
    	$this->_helper->layout()->disableLayout();
    	$search = $this->getRequest()->getParam('issearch');
    	if(!empty($search)){
    		$search = $this->getRequest()->getParams();
    	}
    	else{
    		$search = array(
						'adv_search' =>'',
						'branch_id'     =>0,
						'degree'     =>'',
						'grade_all'  =>'',
						'session'    =>'',
						'all_payment'=>'all',
						'student_payment'=>'',
						'student_test'=>'',
						'income'    =>'',
						'stu_code'  =>'',
						'stu_name'  =>'',
						'expense'   =>'',
						'change_product'=>'',
						'customer_payment'=>'',
						'clear_balance'=>'',
						'start_date'=> '',
						'end_date'  => date('Y-m-d'),
				);
    	}
    	$db = new Registrar_Model_DbTable_DbReportStudentByuser();
    	$studentPayment = $db->getDailyReport($search);
    	$array_data = array(
    			'studentPayment' => $studentPayment,
    			);
    	print_r(Zend_Json::encode($array_data));
    	exit();
    }
}







