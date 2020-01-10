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
    	$this->_helper->layout()->disableLayout();
    	$GetData = $this->getRequest()->getParams();
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if ($GetData['url']=="profile"){
    		}else if ($GetData['url']=="payment"){
    			$this->payment($GetData);
    		}else if ($GetData['url']=="schedule"){
    		}else if ($GetData['url']=="score"){
    		}else if ($GetData['url']=="attendance"){
    		}else if ($GetData['url']=="evaluation"){
//     				$this->payment($GetData);
    		}else{
    			echo Zend_Http_Response::responseCodeAsText(401,true);
    		}
    	}else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    		if($this->getRequest()->isPost()){
    			$PostData = $this->getRequest()->getPost();
    			if ($GetData['url']=="auth"){ // login
    				echo $_SERVER['REQUEST_METHOD'];exit();
    			}else{
    				echo Zend_Http_Response::responseCodeAsText(401,true);
    			}
				
			}else{
				echo Zend_Http_Response::responseCodeAsText(405,true);
			}
    	}else{
    		echo Zend_Http_Response::responseCodeAsText(405,true);
    	}
    }
    public function payment($search){
    	try{
	    	$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
	    	$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
	    	$db = new Api_Model_DbTable_DbApi();
	    	$row = $db->getDailyReport($search);
	    	if ($row['status']){
	    		$arrResult = array(
	    				"result" => $row['value'],
	    				"code" => "SUCCESS",
	    		);
	    	}else{
	    		$arrResult = array(
	    				"code" => "ERR_",
	    				"message" => $row['value'],
	    		);
	    	}
	    	print_r(Zend_Json::encode($arrResult));
	    	exit();
    	}catch(Exception $e){
    		$arrResult = array(
	    			"code" => "ERR_",
    				"message" => $e->getMessage(),
	    	);
	    	print_r(Zend_Json::encode($arrResult));
	    	exit();
    	}
    }
}







