<?php

class Issue_Form_FrmAchievement extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmStudentAchievement($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
		
		$dbAchievement = new Issue_Model_DbTable_DbAchievement();
			
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGroupByBranch(); getAllStudentName();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_branch_id->setValue($request->getParam("branch_id"));
    	if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
		
		$achievementType=  new Zend_Dojo_Form_Element_FilteringSelect('achievementType');
    	$achievementType->setAttribs(
			array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'checkAchievementType();',
				)
		);
    	$achievementTypeOpt = array(
			""=>"",
			-1=>$this->tr->translate("ADD_NEW"),
		);
		$rsAch= $dbAchievement->getAllAchievementType();
		if(!empty($rsAch)) {
			foreach($rsAch As $rr){
				$achievementTypeOpt[$rr['id']]=$rr['name'];
			}
		}
    	$achievementType->setMultiOptions($achievementTypeOpt);
    	$achievementType->setValue($request->getParam("achievementType"));
		
		$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>'fullside height-text',
    			'required'=>true,
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$description=  new Zend_Form_Element_Textarea('description');
    	$description->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'width:99%; font-family: inherit;  min-height:180px !important;'));
    	
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

    	
    	
    	
    	if(!empty($data)){

    		$_branch_id->setValue($data["branchId"]);
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		$achievementType->setValue($data["achievementType"]);
    		$title->setValue($data["title"]);
    		$description->setValue($data["description"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
			
    		
    	}
    	
    	$this->addElements(
			array(
    			$_branch_id,
    			$achievementType,
    			$title,
    			$description,
    			$note,
    			$id,
    			$_status,
    			
    			)
			);
    	return $this;
    }
}

