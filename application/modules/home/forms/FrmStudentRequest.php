<?php

class Home_Form_FrmStudentRequest extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmStudentRequest($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	
    	$dbCRM = new Home_Model_DbTable_DbCRM();
    		
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'autoComplete'=>'false',
				'required'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
    			'class'=>'fullside height-text',));
    	$_branch_search->setValue($request->getParam("branch_search"));
    	if (count($optionBranch)==1){
    		$_branch_search->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_search->setValue($row['id']);
    		}
    	}

		$session_type = new Zend_Dojo_Form_Element_FilteringSelect("session_type");
    	$session_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("SESSION_TYPE"),
    			'class'=>'fullside height-text',));
    	 
    	$_arr =array(
    			''=>$this->tr->translate("SELECT_TYPE"),
    			1=>$this->tr->translate("FULL_DAY"),
    			2=>$this->tr->translate("MORNING"),
				3=>$this->tr->translate("AFTERNOON"),
    	);
    	$session_type->setMultiOptions($_arr);
 	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("START_DATE"),
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	$start_date->setValue($_date);
    		
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("END_DATE"),
    			'required'=>'false'));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	$request_status = new Zend_Dojo_Form_Element_FilteringSelect("request_status");
    	$request_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("FOLLOWU_STATUS"),
    			'class'=>'fullside height-text',));
    	 
    	$_arr =array(
    			''=>$this->tr->translate("REQUEST_STATUS"),
    			0=>$this->tr->translate("Pending"),
    			1=>$this->tr->translate("Appoved"),
				2=>$this->tr->translate("Rejected"),
    	);
    	$request_status->setMultiOptions($_arr);

    	$this->addElements(array(

    			$advance_search,
    			$_branch_search,
				$session_type,
    			$request_status,
    			$start_date,
    			$end_date,
    			
    			));
    	return $this;
    }
 
}