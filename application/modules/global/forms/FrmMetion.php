<?php

class Global_Form_FrmMetion extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddMetionSetting($data){
    	
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
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
    	$_degree->setMultiOptions($_arr_opt);
    	$_degree->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			// 				'required'=>false,
    			'onChange'=>'checkaddItems();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
    	
    	$_typopt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$academic = $_dbgb->getAllAcademicYear();
    	if(!empty($academic))foreach($academic AS $row) $_typopt[$row['id']]=$this->tr->translate(strtoupper($row['name']));
    	$_academic = new Zend_Dojo_Form_Element_FilteringSelect("academic_year");
    	$_academic->setMultiOptions($_typopt);
    	$_academic->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	$_arr = array(0=>$this->tr->translate("BY_DEGREE"),1=>$this->tr->translate("BY_GRADE"));
    	$setting_type = new Zend_Dojo_Form_Element_FilteringSelect("setting_type");
    	$setting_type->setMultiOptions($_arr);
    	$setting_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$setting_type->setMultiOptions($_arr);
    	 
    	
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
    			
    	if(!empty($data)){
    		$_academic->setValue($data["academic_year"]);
    		$_degree->setValue($data["degree"]);
    		$setting_type->setValue($data["setting_type"]);
    		$title->setValue($data["title"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    	}
    	
    	$this->addElements(array(
    			$title,
    			$_degree,
				$note,
    			$setting_type,
				$_academic,
    			$_status,
    			$id,
    			$advance_search,
    			$_status_search,
    			));
    	return $this;
    }
}

