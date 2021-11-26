<?php
class Scan_IndexController extends Zend_Controller_Action
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
   
}