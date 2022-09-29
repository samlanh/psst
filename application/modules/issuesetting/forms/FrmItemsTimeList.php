<?php 
Class Issuesetting_Form_FrmItemsTimeList extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	//protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		//$this->check='dijit.form.CheckBox';
	}
	public function FrmAddTimeList($data=null){
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);
		
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
				'class'=>'fullside',
				'placeholder'=>'07:30 AM',
				));
		
		$_title_en = new Zend_Dojo_Form_Element_TextBox('title_en');
		$_title_en->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		
		$_value = new Zend_Dojo_Form_Element_TextBox('value');
		$_value->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
				'class'=>'fullside',
				'placeholder'=>'7.30',
				));
				
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$id = new Zend_Form_Element_Hidden('id');
		
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		if(!empty($data)){
			$_title->setValue($data['title']);
			$_title_en->setValue($data['title_en']);
			$_value->setValue($data['value']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			
			$id->setValue($data["id"]);
		}
		$this->addElements(array($_title,$_title_en,$_value,$note,$_status,$id));
		
		return $this;
		
	}
	
}