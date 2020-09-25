<?php

class Foundation_Form_FrmGraduate extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmAddGraduate($data=null){
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
    	
    	$type= new Zend_Dojo_Form_Element_FilteringSelect('type');
    	$type->setAttribs(array('dojoType'=>$this->filter,
    			'class'=>'fullside',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    			'required'=>false
    	));
    	$optRs=$_dbgb->getViewById(9);
    	unset($optRs[0]);
    	$opt_type = array();
    	if(!empty($optRs))foreach ($optRs As $rs)$opt_type[$rs['key_code']]=$rs['view_name'];
    	$type->setMultiOptions($opt_type);
    	
    	
    	$graduate_date = new Zend_Dojo_Form_Element_DateTextBox('graduate_date');
    	$date = date("Y-m-d");
    	$graduate_date->setAttribs(array(
    			'data-dojo-Type'=>"dijit.form.DateTextBox",
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',
    			'required'=>true));
    	$graduate_date->setValue($date);
    	
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
    	

    	
    	if(!empty($data)){
    		$_branch_id->setValue($data['branch_id']);
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		$type->setValue($data['type']);
    		$graduate_date->setValue($data['graduate_date']);
    		$note->setValue($data['note']);
    		$id->setValue($data['id']);
    		$status->setValue($data['status']);
    	}
    	$this->addElements(array(
    			$_branch_id,
    			$graduate_date,
    			$type,
				$note,
				$id,
				$status,
    			));
    	return $this;
    }
}