<?php
class Placement_Form_FrmQuestion extends Zend_Dojo_Form
{
	protected  $tr;
    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddQuestion($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userinfo = $_dbgb->getUserInfo();
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]= preg_replace( "/\r|\n/", "", strip_tags(htmlspecialchars($row['name'])));
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
				
		if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_QUESTION_TYPE"));
    	$rows = $_dbgb->getAllQuestionType();
    	if(!empty($rows))foreach($rows AS $row) $_arr_opt[$row['id']]= preg_replace( "/\r|\n/", "", strip_tags(htmlspecialchars($row['name'])));
    	$_question_type = new Zend_Dojo_Form_Element_FilteringSelect("question_type");
    	$_question_type->setMultiOptions($_arr_opt);
    	$_question_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'onChange'=>'checkTypeQuestion();',
    			'queryExpr'=>'*${0}*',));
    	
    	$_question_title=  new Zend_Form_Element_Textarea('question_title');
    	$_question_title->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_ordering = new Zend_Dojo_Form_Element_NumberTextBox('ordering');
    	$_ordering->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Ordering")
    	));
    	
    	
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	
    	$_arr_opt = array();
    	$rows = $_dbgb->getPlacementTestType();
    	if(!empty($rows))foreach($rows AS $row) $_arr_opt[$row['id']]= preg_replace( "/\r|\n/", "", strip_tags(htmlspecialchars($row['name'])));
    	$_test_type = new Zend_Dojo_Form_Element_FilteringSelect("test_type");
    	$_test_type->setMultiOptions($_arr_opt);
    	$_test_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    	));
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("adv_search"));
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("START_DATE"),
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    	}
    	$start_date->setValue($_date);
    		
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("END_DATE"),
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
		
    	if(!empty($data)){
    		
//     		$_branch_id->setValue($data["branch_id"]);
    		$_question_type->setValue($data["question_type"]);
    		$_question_title->setValue($data["question_title"]);
    		$_test_type->setValue($data["test_type"]);
    		$_ordering->setValue($data["ordering"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		
    		$id->setValue($data["id"]);
    	}
    	
    	$this->addElements(array(
    			$_branch_id,
    			$_question_type,
    			$_question_title,
				$_ordering,
				$note,
				$_test_type,
				$_status,
				$start_date,
				$end_date,
    			$id
    			));
    	return $this;
    }
    
}