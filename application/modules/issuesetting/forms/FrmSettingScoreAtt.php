<?php

class Issuesetting_Form_FrmSettingScoreAtt extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmAddScoreSetting($data){
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
		
		$score = new Zend_Dojo_Form_Element_NumberTextBox('score');
    	$score->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'placeholder'=>$this->tr->translate("score"),
    	));
    	$score->setValue(100);
		
		$degreeId = new Zend_Dojo_Form_Element_FilteringSelect("degreeId");
		$degreeId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
				
		$degreeResult = $_dbgb->getAllItems(1);
		$degreeOpt =array();
		if(!empty($degreeResult))foreach($degreeResult AS $row){
			 $degreeOpt[$row['id']]=$row['name'];
		}
		$degreeId->setMultiOptions($degreeOpt);
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:80px !important; max-width:100%;'));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_arr = array(
				0=>$this->tr->translate("PLEASE_SELECT"),
				2=>$this->tr->translate("ABSENT"),
				3=>$this->tr->translate("PERMISSION"),
				4=>$this->tr->translate("LATE"),
				5=>$this->tr->translate("EARLY_LEAVE"),
				);
    	$attendanceType = new Zend_Dojo_Form_Element_FilteringSelect("attendanceType");
    	$attendanceType->setMultiOptions($_arr);
    	$attendanceType->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
				'onChange'=>'addRow()',
    			'class'=>'fullside height-text',)
				);
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    
    	
    	
		
    	
    	if(!empty($data)){
			$_branch_id->setValue($data["branchId"]);
			$title->setValue($data["title"]);
			$degreeId->setValue($data["degreeId"]);
			$score->setValue($data["score"]);
			
			$note->setValue($data["note"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
    	}
    	$this->addElements(
				array(
					$_branch_id,
					$title,
					$score,
					$degreeId,
					$note,
					$_status,
					$attendanceType,
					$id
    			)
			);
    	return $this;
    }
}