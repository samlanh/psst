<?php
class Registrar_CalculatorController extends Zend_Controller_Action {	
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');

	}

    public function indexAction()
    {
		$frm_cal = new Global_Form_FrmCal();
		$myform = $frm_cal -> FrmCalculator();
		Application_Model_Decorator::removeAllDecorator($myform);
		$this->view->frm_cal = $myform;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode=$key->getKeyCodeMiniInv(TRUE);
        
    }
    public function addAction()
    {
    	$this->_redirect("/registrar/calculator");
    	
    }
}
