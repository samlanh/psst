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
    	$_dbAction = new Api_Model_DbTable_DbActions();
    	$GetData = $this->getRequest()->getParams();
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if ($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="payment"){
    			$_dbAction->paymentAction($GetData);
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
   
}







