<?php
class Api_AbabillingController extends Zend_Controller_Action
{
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
    	header('Content-type:application/json;charset=utf-8');
    	exit();
    }

   function paymentinfoAction(){
   	
	   	$GetData = $this->getRequest()->getParams();
	   	$Stuid=empty($GetData["id"])?'':$GetData["id"];
	   	$tokend=empty($GetData['token'])?'':$GetData['token'];
	   	$_dbAction = new Api_Model_DbTable_DbabaApi();
	   	
	   	if($_SERVER['REQUEST_METHOD'] == "GET" AND !empty($Stuid) AND !empty($tokend)){
	   		if(!empty($Stuid)){
	   			$studentInfo = $_dbAction->getStudentbyStuID($Stuid,$tokend);
	   			print_r(json_encode($studentInfo,JSON_UNESCAPED_UNICODE ));
	   		}
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   }
   
   
   function confirmpaymentAction(){
	   
	   	$GetData = $this->getRequest()->getParams();
// 	   	$Stuid=$GetData["id"];
	   	  $_dbAction = new Api_Model_DbTable_DbabaApi();
	   
		  if($this->getRequest()->isPost()){
		  	$postData = $this->getRequest()->getPost();
		  	$paymentInfo = $_dbAction->confirmPayment($postData);
		  	print_r(json_encode($paymentInfo));
		  	
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   }
   
   function confirmpaymentstatusAction(){
   		$getData = $this->getRequest()->getParams();
   		$bankTranid = empty($getData['id'])?'':$getData['id'];
   		$token = empty($getData['token'])?'':$getData['token'];
   		$_dbAction = new Api_Model_DbTable_DbabaApi();

   		if($_SERVER['REQUEST_METHOD'] == "GET" AND !empty($bankTranid) AND !empty($token)){
   			
	   		if(!empty($bankTranid)){
	   			$confirm = $_dbAction->confirmPaymentStatus($bankTranid,$token);
	   			print_r(json_encode($confirm));
	   		}
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   	
   }
}