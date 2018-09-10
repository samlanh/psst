<?php

class Home_Form_FrmCrm extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddCRM($data){
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
    	$kh_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Name In Khmer")
    	));
    	
    	$_first_name = new Zend_Dojo_Form_Element_TextBox('first_name');
    	$_first_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    			
    	));
    	
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	$_from_school = new Zend_Dojo_Form_Element_TextBox('old_school');
    	$_from_school->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("FROM_SCHOOL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	
    	));
    	
    	$reason=  new Zend_Form_Element_Textarea('reason');
    	$reason->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_reference_name = new Zend_Dojo_Form_Element_TextBox('reference_name');
    	$_reference_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	
    	));
    	
    	$_parent_tel = new Zend_Dojo_Form_Element_TextBox('parent_tel');
    	$_parent_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Tel")
    			 
    	));
    	
    	$_arr = array(0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("WAITING_TEST"),3=>$this->tr->translate("COMPLETED"));
    	$_crm_status = new Zend_Dojo_Form_Element_FilteringSelect("crm_status");
    	$_crm_status->setMultiOptions($_arr);
    	$_crm_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("WAITING_TEST"),3=>$this->tr->translate("COMPLETED"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    		//$_date = date("Y-m-d");
    	}
    	$start_date->setValue($_date);
    		
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$kh_name->setValue($data["kh_name"]);
    		$_first_name->setValue($data["first_name"]);
    		$_last_name->setValue($data["en_name"]);
    		$_sex->setValue($data["sex"]);
    		$_from_school->setValue($data["old_school"]);
    		$reason->setValue($data["reason"]);
    		$_reference_name->setValue($data["parent_name"]);
    		$_parent_tel->setValue($data["parent_tel"]);
    		$_crm_status->setValue($data["crm_status"]);
    		$id->setValue($data["id"]);
    		
    	}
    	
    	$this->addElements(array(
    			$_branch_id,
    			$kh_name,
				$_first_name,
				$_last_name,
				$_sex,
    			$_from_school,
				$reason,
				$_reference_name,
				$_crm_status,
    			$id,
    			$_parent_tel,
    			
    			$advance_search,
    			$_status_search,
    			$start_date,
    			$end_date
    			));
    	return $this;
    }
    
    function FrmAddCRMContactHistory($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$userinfo = $_dbgb->getUserInfo();
    	
    	$contact_date= new Zend_Dojo_Form_Element_DateTextBox('contact_date');
    	$contact_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d");
    	$contact_date->setValue($_date);
    	
    	$feedback=  new Zend_Form_Element_Textarea('feedback');
    	$feedback->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'required'=>'true',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_arr = array(0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("WAITING_TEST"),3=>$this->tr->translate("COMPLETED"));
    	$_proccess = new Zend_Dojo_Form_Element_FilteringSelect("proccess");
    	$_proccess->setMultiOptions($_arr);
    	$_proccess->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$next_contact= new Zend_Dojo_Form_Element_DateTextBox('next_contact');
    	$next_contact->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d",strtotime("+15 day"));
    	$next_contact->setValue($_date);
    	
    	
    	$crm_id = new Zend_Form_Element_Hidden('id');
    	$recordbranhc="";
    	if (!empty($data['branch_id'])){
    		$recordbranhc=$data['branch_id'];
    	}
    	$_arr_opt_user = array();
    	$optionUser = $_dbgb->getAllUser($recordbranhc);
    	if(!empty($optionUser))foreach($optionUser AS $row) $_arr_opt_user[$row['id']]=$row['name'];
    	$_user_contact = new Zend_Dojo_Form_Element_FilteringSelect("user_contact");
    	$_user_contact->setMultiOptions($_arr_opt_user);
    	$_user_contact->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_user_contact->setValue($userinfo['user_id']);
    	
    	
    	if ($userinfo['level']!=1){
    		$contact_date->setAttribs(array(
    				'readonly'=>"readonly",
    		));
    		
    		$_user_contact->setAttribs(array(
    				'readonly'=>"readonly",
    		));
    	}
    	if(!empty($data)){
    		$crm_id->setValue($data["id"]);
    		$_proccess->setValue($data["crm_status"]);
    	}
    	
    	
    	$this->addElements(array(
    			$contact_date,
    			$feedback,
    			$_proccess,
    			$next_contact,
    			$_user_contact,
    			$crm_id
    	));
    	return $this;
    }
}

