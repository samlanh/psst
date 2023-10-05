<?php 
Class Global_Form_FrmAddSubjectExam extends Zend_Dojo_Form {
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
	public function FrmAddSubjectExam($data=null){
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$Option = $_dbgb->getAllSchoolOption($userinfo['branch_list']);
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$_schoolOption = new Zend_Dojo_Form_Element_FilteringSelect("schoolOption");
		$_schoolOption->setMultiOptions($_arr_opt);
		$_schoolOption->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$_subject_exam = new Zend_Dojo_Form_Element_TextBox('subject_kh');
		$_subject_exam->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
				'class'=>'fullside',
				));
		
		$_subject_kh = new Zend_Dojo_Form_Element_TextBox('subject_en');
		$_subject_kh->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_score_percent = new Zend_Dojo_Form_Element_TextBox('score_percent');
		$_score_percent->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_type_subject=  new Zend_Dojo_Form_Element_FilteringSelect('type_subject');
		$_type_subject->setAttribs(
			array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'onChange'=>'langOption()',
			));
		$_type_subject_opt = array(
				1=>$this->tr->translate("STUDY_SUBJECT"),
				2=>$this->tr->translate("NOT_STUDY_SUBJECT"));
		$_type_subject->setMultiOptions($_type_subject_opt);

		$_subject_lang=  new Zend_Dojo_Form_Element_FilteringSelect('subject_lang');
		$_subject_lang->setAttribs(
			array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				
			));
		$_subject_lang_opt = array(
				''=>$this->tr->translate("PLEASE_SELECT"),
				1=>$this->tr->translate("STUDY_IN_KHMER"),
				2=>$this->tr->translate("STUDY_IN_ENGLISH"),
				3=>$this->tr->translate("STUDY_IN_CHINESE")
			);
		$_subject_lang->setMultiOptions($_subject_lang_opt);

		
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		
		$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(
    			array('dojoType'=>'dijit.form.TextBox',)
    			);
				
		if(!empty($data)){
			$_score_percent->setValue($data['shortcut']);
			$_subject_exam->setValue($data['subject_titlekh']);
			$_subject_kh->setValue($data['subject_titleen']);
			$_status->setValue($data['status']);
			$_schoolOption->setValue($data['schoolOption']);
			$_type_subject->setValue($data['type_subject']);
			$_subject_lang->setValue($data['subject_lang']);
			
			$id->setValue($data["id"]);
		}
		$this->addElements(array($id,$_type_subject,$_subject_exam,$_status,$_submit,$_subject_kh,$_score_percent,$_schoolOption,$_subject_lang));
		
		return $this;
		
	}
	
}