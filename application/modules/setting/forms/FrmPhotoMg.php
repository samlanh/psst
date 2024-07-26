<?php 
Class Setting_Form_FrmPhotoMg extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmPhotoMg($data=null){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$branchId = new Zend_Dojo_Form_Element_FilteringSelect("branchId");
    	$branchId->setMultiOptions($_arr_opt_branch);
    	$branchId->setAttribs(
				array(
					'dojoType'=>'dijit.form.FilteringSelect',
					'autoComplete'=>'false',
					'required'=>'false',
					'queryExpr'=>'*${0}*',
					'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
					'class'=>'fullside height-text',
				)
			);
    	if (count($optionBranch)==1){
    		$branchId->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$branchId->setValue($row['id']);
    		}
    	}
		
		$recordType = new Zend_Dojo_Form_Element_FilteringSelect("recordType");
    	$recordType->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("RECORD_TYPE"),
    			'class'=>'fullside height-text',));
    	$_arr =array(
    			1=>$this->tr->translate("STUDENT")
				,2=>$this->tr->translate("FATHER")
				,3=>$this->tr->translate("MOTHER")
				,4=>$this->tr->translate("GUARDIAN")
				,5=>$this->tr->translate("TEACHER")
				,6=>$this->tr->translate("STAFF")
    	);
    	$recordType->setMultiOptions($_arr);
		
		$photoStatus = new Zend_Dojo_Form_Element_FilteringSelect("photoStatus");
    	$photoStatus->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("RECORD_TYPE"),
    			'class'=>'fullside height-text',));
    	$_arr =array(
    			1=>$this->tr->translate("NOT_PHOTO")
				,2=>$this->tr->translate("HAS_PHOTO")
    	);
    	$photoStatus->setMultiOptions($_arr);


		$id = new Zend_Form_Element_Hidden('id');
		$id->setAttribs(
				array('dojoType'=>'dijit.form.TextBox',)
		);
		
		if(!empty($data)){
			
			
		}
		
		$this->addElements(array(
			$id,
			$branchId,
			$recordType,
			$photoStatus,
			
		));
		
		return $this;
	}
}