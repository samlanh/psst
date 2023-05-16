<?php

class Application_Model_CustomAuth extends Zend_Controller_Plugin_Abstract
{
	/**
	 * @var Zend_Auth
	 */
	protected $_auth;	
	
	protected $_exception_url = array(
					"default/index/index",
					"default/error/error",
					"default/index/changepassword",
					"default/index/logout",
			);
 	
	public function __construct(Zend_Auth $auth)
	{		
		$this->_auth = $auth;
	}
 
 	
 	protected function rewriteUrl($url){
 		if(!empty($this->_rewrite_url[$url])){
 			return $this->_rewrite_url[$url];
 		}
 		return "";
 	}
	
 	/**
 	 * redirect url to any where not base url (currencysmart.localserver/)
 	 * @param unknown_type $request
 	 * @param unknown_type $controller
 	 * @param unknown_type $action
 	 * @param unknown_type $module
 	 */
	protected function _redirect_homepage($request, $controller, $action, $module)
	{
		if ($request->getControllerName() == $controller
			&& $request->getActionName()  == $action
			&& $request->getModuleName()  == $module)
		{
			return TRUE;
		}
 
		$url = Zend_Controller_Front::getInstance()->getBaseUrl(); 
		$url .= '/'   . $action; 
 
	   return $this->_response->setRedirect($url);
	}
	
	/**
 	 * redirect url to any in base url (currencysmart.localserver)
 	 * @param unknown_type $request
 	 * @param unknown_type $controller
 	 * @param unknown_type $action
 	 * @param unknown_type $module
 	 */
	protected function _redirect($request, $controller, $action, $module)
	{
		if ($request->getControllerName() == $controller
			&& $request->getActionName()  == $action
			&& $request->getModuleName()  == $module)
		{
			return TRUE;
		}
 
		$url = Zend_Controller_Front::getInstance()->getBaseUrl(); 
		$url .= '/'   . $module
			 .=  ($controller === 'index')? '':'/'.$controller
			 .=  ($action === 'index')? '':'/'.$action;
			 
	   return $this->_response->setRedirect($url);
	}
}