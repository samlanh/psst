<?php 
Class Global_Form_FrmProgramType extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	protected $tarea;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmProgramType($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		
		$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$title_en = new Zend_Dojo_Form_Element_TextBox('title_en');
    	$title_en->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			//'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Shortcut")
    			
    	));

		$isSingleProgram=  new Zend_Dojo_Form_Element_FilteringSelect('isSingleProgram');
		$isSingleProgram->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside','required'=>'true'));
		$_program_opt = array(
				1=>$this->tr->translate("SINGLE_PROGRAM"),
				2=>$this->tr->translate("MULTIPLE_PROGRAM"));
		$isSingleProgram->setMultiOptions($_program_opt);

		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if (!empty($data)){
			$title->setValue($data['title']);
			$title_en->setValue($data['titleEn']);
			$_shortcut->setValue($data['shortcut']);
			$isSingleProgram->setValue($data['isSingleProgram']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
		}
		$this->addElements(array(
			$title,
			$title_en,
			$_shortcut,
			$isSingleProgram,
			$note,
			$id,
			$_status,
		));
		
		return $this;
		
	}
}