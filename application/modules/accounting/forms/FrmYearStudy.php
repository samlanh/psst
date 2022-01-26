<?php 
Class Accounting_Form_FrmYearStudy extends Zend_Dojo_Form {
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
	public function FrmYearStudy($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		
		$fromYear = new Zend_Dojo_Form_Element_NumberTextBox('fromYear');
		$fromYear->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside','required'=>'true',));
		
		$toYear = new Zend_Dojo_Form_Element_NumberTextBox('toYear');
		$toYear->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside','required'=>'true',));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if (!empty($data)){
			$fromYear->setValue($data['fromYear']);
			$toYear->setValue($data['toYear']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
		}
		$this->addElements(array($fromYear,$toYear,$id,$_status));
		
		return $this;
		
	}
}