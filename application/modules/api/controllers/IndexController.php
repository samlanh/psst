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
    		if($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="payment"){
    			$_dbAction->paymentAction($GetData);
    		}else if ($GetData['url']=="paymentDetail"){
    			$_dbAction->paymentDetailAction($GetData);
    		}else if ($GetData['url']=="schedule"){
    			$_dbAction->scheduleAction($GetData);
    		}else if ($GetData['url']=="attendance"){
    			$_dbAction->attendanceAction($GetData);
    		}else if ($GetData['url']=="attendanceDetail"){
    			$_dbAction->attendanceDetailAction($GetData);
    		}else if ($GetData['url']=="discipline"){
    			$_dbAction->disciplineAction($GetData);
    		}else if ($GetData['url']=="attendanceDetail"){
    			$_dbAction->attendanceDetailAction($GetData);
    		}
    		else if ($GetData['url']=="score"){
    			$_dbAction->scoreAction($GetData);
    		}else if ($GetData['url']=="evaluation"){
    			$_dbAction->envaluationAction($GetData);
    		}else{
    			echo Zend_Http_Response::responseCodeAsText(401,true);
    		}
    	}else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    		if($this->getRequest()->isPost()){
    			$postData = $this->getRequest()->getPost();
    			if ($GetData['url']=="auth"){// login
    				$_dbAction->loginAction($postData);
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







