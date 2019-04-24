<?php

class Issue_Form_FrmIssueCertificate extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmIssueCertificate($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getCompleteGroup();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_branch_id->setValue($request->getParam("branch_id"));
    	if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	
    	$dept = new Zend_Dojo_Form_Element_TextBox('dept_eng');
    	$dept->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>'fullside height-text',
    			'required'=>true,
    			'placeholder'=>$this->tr->translate("FACULTY_ENNAME"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Department")
    	));
    	
    	$dept_kh = new Zend_Dojo_Form_Element_TextBox('dept_kh');
    	$dept_kh->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("FACULTY_KHNAME"),
    			'required'=>true,
    			'missingMessage'=>$this->tr->translate("Forget Enter Department")
    	));
    	
    	$program_kh = new Zend_Dojo_Form_Element_TextBox('program_kh');
    	$program_kh->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("MAJOR_KHNAME"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Program")
    	));
    	
    	$program_en = new Zend_Dojo_Form_Element_TextBox('program_en');
    	$program_en->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("MAJOR_ENNAME"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Program")
    	));
    	
    	$from_date= new Zend_Dojo_Form_Element_DateTextBox('from_date');
    	$from_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$from_date->setValue(date("Y-m-d"));
    	
    	$to_date= new Zend_Dojo_Form_Element_DateTextBox('to_date');
    	$date = date("Y-m-d",strtotime("+1 month"));
    	$to_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$to_date->setValue($date);
    	
    	
    	$issue_date= new Zend_Dojo_Form_Element_DateTextBox('issue_date');
    	$issue_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$issue_date->setValue(date("Y-m-d"));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
    	
    	$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
    	$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
    	$_status_opt = array(
    			1=>$this->tr->translate("ACTIVE"),
    			0=>$this->tr->translate("DACTIVE"));
    	$_status->setMultiOptions($_status_opt);
    	$_status->setValue($request->getParam("status"));
    	
    	$id = new Zend_Form_Element_Hidden('id'); 

    	$adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
    	$adv_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$this->tr->translate("SEARCH")));
    	$adv_search->setValue($request->getParam("adv_search"));
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$_date = $request->getParam("start_date");
    	 
    	if(!empty($_date)){
    		$start_date->setValue($_date);
    	}
    	 
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
    	$_status_search->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
    	$_status_opt = array(
    			-1=>$this->tr->translate("ALL_STATUS"),
    			1=>$this->tr->translate("ACTIVE"),
    			0=>$this->tr->translate("DACTIVE"));
    	$_status_search->setMultiOptions($_status_opt);
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	
    	if(!empty($data)){

    		$_branch_id->setValue($data["branch_id"]);
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		$dept->setValue($data["dept_eng"]);
    		$dept_kh->setValue($data["dept_kh"]);
    		$program_kh->setValue($data["program_kh"]);
    		$program_en->setValue($data["program_en"]);
    		
    		$from_date->setValue("");
    		$to_date->setValue("");
    		if (!empty($data["from_date"])){
    			$from_date->setValue($data["from_date"]);
    		}
    		if (!empty($data["to_date"])){
    			$to_date->setValue($data["to_date"]);
    		}
    		if (!empty($data["issue_date"])){
    			$issue_date->setValue($data["issue_date"]);
    		}
    		
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    	}
    	
    	$this->addElements(array(
    			$_branch_id,
    			$dept,
    			$dept_kh,
    			$program_kh,
    			$program_en,
    			$from_date,
    			$to_date,
    			$issue_date,
    			$note,
    			$id,
    			$_status,
    			$adv_search,
    			$start_date,
    			$end_date,
    			$_status_search,
    			
    			));
    	return $this;
    }
}

