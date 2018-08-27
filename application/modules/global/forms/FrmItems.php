<?php

class Global_Form_FrmItems extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddDegree($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	
    	$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			//'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Shortcut")
    			
    	));
    	
    	$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllSchoolOption($userinfo['branch_list']);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_schoolOption = new Zend_Dojo_Form_Element_FilteringSelect("schoolOption");
    	$_schoolOption->setMultiOptions($_arr_opt);
    	$_schoolOption->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	
    	$_typopt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$OptionType = $_dbgb->getAllDegreetype();
    	if(!empty($OptionType))foreach($OptionType AS $row) $_typopt[$row['id']]=$this->tr->translate(strtoupper($row['name']));
    	$_type = new Zend_Dojo_Form_Element_FilteringSelect("type");
    	$_type->setMultiOptions($_typopt);
    	$_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important;'));
    	
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_save = new Zend_Dojo_Form_Element_Button('save');
    	$_save->setAttribs(array(
    			'dojoType'=>'dijit.form.Button',
    			'onclick'=>"dijit.byId('frm_add_tran').submit();",
    			'class'=>'dijitEditorIcon',
    	));
    	$_save->setLabel($this->tr->translate("ADD_NEW"));
    	
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
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllSchoolOption($userinfo['branch_list']);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_schoolOption_search = new Zend_Dojo_Form_Element_FilteringSelect("schoolOption_search");
    	$_schoolOption_search->setMultiOptions($_arr_opt);
    	$_schoolOption_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_schoolOption_search->setValue($request->getParam("schoolOption_search"));
    	 
    	 
    	$_typopt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$OptionType = $_dbgb->getAllDegreetype();
    	if(!empty($OptionType))foreach($OptionType AS $row) $_typopt[$row['id']]=$this->tr->translate(strtoupper($row['name']));
    	$_type_search = new Zend_Dojo_Form_Element_FilteringSelect("type_search");
    	$_type_search->setMultiOptions($_typopt);
    	$_type_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_type_search->setValue($request->getParam("type_search"));
    			
    	if(!empty($data)){
    		$title->setValue($data["title"]);
    		$_shortcut->setValue($data["shortcut"]);
    		if ($data["type"]==1){
    			$_schoolOption->setValue($data["schoolOption"]);
    		}
    		$_type->setValue($data["type"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    	}
    	
    	$this->addElements(array(
    			$title,
    			$_shortcut,
    			$_schoolOption,
    			$_type,
    			$note,
    			$_status,
    			$_save,
    			$id,
    			$advance_search,
    			$_status_search,
    			$_schoolOption_search,
    			$_type_search
    			));
    	return $this;
    }
}

