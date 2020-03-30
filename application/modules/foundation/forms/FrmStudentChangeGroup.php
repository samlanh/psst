<?php

class Foundation_Form_FrmStudentChangeGroup extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmAddStudentChangeGroup($data=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	
    	$_arr_opt_branch = array(""=>$tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',
    			'queryExpr'=>'*${0}*',
    			'autoComplete'=>'false'));
    	if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('gender');
    	$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside','readonly'=>'readonly'));
    	$sex_opt = array(
    			1=>$tr->translate("MALE"),
    			2=>$tr->translate("FEMALE"));
    	$_sex->setMultiOptions($sex_opt);
    	
    	$moving_date = new Zend_Dojo_Form_Element_DateTextBox('moving_date');
    	$date = date("Y-m-d");
    	$moving_date->setAttribs(array(
    			'data-dojo-Type'=>"dijit.form.DateTextBox",
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',
    			'required'=>true));
    	$moving_date->setValue($date);
    	
    	$note = new Zend_Dojo_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>$this->textarea,'class'=>'fullside',
    			'style'=>'min-height: 65px !important;',
    	));
    	
    	$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$opt_status = array(
    			1=>$tr->translate('ACTIVE'),
    			0=>$tr->translate('DEACTIVE'),
    	);
    	$status->setMultiOptions($opt_status);
    	$status->setAttribs(array(
    			'dojoType'=>$this->filter,
    			'class'=>'fullside',));
    	
    	$id = new Zend_Form_Element_hidden('id');
    	$id->setAttribs(array('dojoType'=>"dijit.form.TextBox",
    			'class'=>'fullside'));
    	
    	$from_grade = new Zend_Form_Element_hidden('from_grade');
    	$from_grade->setAttribs(array('dojoType'=>"dijit.form.TextBox",
    			'class'=>'fullside'));
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data['branch_id']);
//     		$_sex->setValue($data['is_stu_new']);
    		$moving_date->setValue($data['moving_date']);
    		$note->setValue($data['note']);
    		$id->setValue($data['id']);
//     		$from_grade->setValue($data['is_stu_new']);
    		$status->setValue($data['status']);
    	}
    	$this->addElements(array(
    			$_branch_id,
    			$_sex,
				$moving_date,
				$note,
				$id,
    			$from_grade,
				$status,
    			));
    	return $this;
    }
}