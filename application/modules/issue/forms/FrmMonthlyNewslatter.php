<?php

class Issue_Form_FrmMonthlyNewslatter extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmAddMonthlyNewslatter($data){
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$typeItems = empty($typeItems)?1:$typeItems;
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
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',  			 
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MONTHLY"),2=>$this->tr->translate("SEMESTER"));
    	$_exam_type = new Zend_Dojo_Form_Element_FilteringSelect("exam_type");
    	$_exam_type->setMultiOptions($_arr);
    	$_exam_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'checkExamType(); getSubjectByGroup();checkDuplicateRecord();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	$_arr = array(1=>$this->tr->translate("SEMESTER1"),2=>$this->tr->translate("SEMESTER2"));
    	$_for_semester = new Zend_Dojo_Form_Element_FilteringSelect("for_semester");
    	$_for_semester->setMultiOptions($_arr);
    	$_for_semester->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'checkDuplicateRecord()',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	$_opt_month = array();
    	$_allMonth = $_dbgb->getAllMonth();
    	if(!empty($_allMonth))foreach($_allMonth AS $row) $_opt_month[$row['id']]=$row['name'];
    	$_for_month = new Zend_Dojo_Form_Element_FilteringSelect("for_month");
    	$_for_month->setMultiOptions($_opt_month);
    	$_for_month->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'onChange'=>'checkDuplicateRecord()',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$for_date= new Zend_Dojo_Form_Element_DateTextBox('for_date');
    	$for_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d");
    	$for_date->setValue($_date);
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")));
    			
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
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
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
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	if (count($optionBranch)==1){
    		$_branch_search->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_search->setValue($row['id']);
    		}
    	}
    	$_branch_search->setValue($request->getParam("branch_search"));
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$title->setValue($data["title"]);
    		$for_date->setValue($data["for_date"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    		$_exam_type->setValue($data["exam_type"]);
    		$_for_semester->setValue($data["for_semester"]);
    		$_for_month->setValue($data["for_month"]);
    	}
    	$this->addElements(array(
    			$_branch_id,
    			$title,
    			$_exam_type,
    			$_for_semester,
    			$_for_month,
    			$for_date,
				$note,
				$_status,
				$id,
				$advance_search,
				$_status_search,
    			$start_date,
    			$end_date,
    			$_branch_search
    			));
    	return $this;
    }
}