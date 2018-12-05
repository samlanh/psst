<?php 
Class Global_Form_FrmTeacher extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $textarea=null;
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
		$this->textarea = 'dijit.form.Textarea';
		//$this->check='dijit.form.CheckBox';
	}
	public function FrmTecher($_data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_db->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);
		
		$_enname = new Zend_Dojo_Form_Element_TextBox('en_name');
		$_enname->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
		
		$_khname = new Zend_Dojo_Form_Element_TextBox('kh_name');
		$_khname->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
		
		$code = new Zend_Dojo_Form_Element_TextBox('code');
		$code->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside','style'=>'color:red;'));
		$db = new Application_Model_DbTable_DbGlobal();
		$code_num = $db->getTeacherCode();
		$code->setValue($code_num);
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$phone = new Zend_Dojo_Form_Element_NumberTextBox('phone');
		$phone->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true','placeholder'=>$this->tr->translate("PLEASE_SELECT_PHONE")));
		
		$sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$options=array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
		$sex->setMultiOptions($options);
		
		$dob = new Zend_Dojo_Form_Element_DateTextBox('dob');
		$dob->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$dob->setValue(date('Y-m-d'));
		
		$rs_province = $_dbgb->getAllProvince();
		$opt = array();
		if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['id']]=$row['name'];
		$_province_id = new Zend_Dojo_Form_Element_FilteringSelect("province_id");
		$_province_id->setMultiOptions($opt);
		$_province_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$start_date->setValue(date('Y-m-d'));
		
		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$end_date->setValue(date('Y-m-d'));
		
		$_adress = new Zend_Dojo_Form_Element_TextBox('address');
		$_adress->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_user = new Zend_Dojo_Form_Element_TextBox('user_name');
		$_user->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$optionNation = $_db->getViewByType(21);//Nation
    	if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
    	$_nationality = new Zend_Dojo_Form_Element_FilteringSelect("nationality");
    	$_nationality->setMultiOptions($_arr_opt_nation);
    	$_nationality->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'popupNation(1);',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	$_nation = new Zend_Dojo_Form_Element_FilteringSelect("nation");
    	$_nation->setMultiOptions($_arr_opt_nation);
    	$_nation->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'onChange'=>'popupNation(2);',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
		
		$_position = new Zend_Dojo_Form_Element_TextBox('position_add');
		$_position->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_email = new Zend_Dojo_Form_Element_TextBox('email');
		$_email->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_passport = new Zend_Dojo_Form_Element_TextBox('passport_no');
		$_passport->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		$_card = new Zend_Dojo_Form_Element_NumberTextBox('card_no');
		$_card->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside','placeholder'=>$this->tr->translate("PLEASE_SELECT_CARD_NO")));
		
		$_experiences = new Zend_Dojo_Form_Element_Textarea('experiences');
		$_experiences->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:40px;',));
		
		$_agreement = new Zend_Dojo_Form_Element_Textarea('agreement');
		$_agreement->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:40px;',));
		
		$home_num = new Zend_Dojo_Form_Element_TextBox('home_num');
		$home_num->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$_note = new Zend_Dojo_Form_Element_NumberTextBox('note');
		$_note->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
		 
		$street_num = new Zend_Dojo_Form_Element_TextBox('street_num');
		$street_num->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>true
		));
		$degree_opt = $db->getAllDegree();
		$_degree->setMultiOptions($degree_opt);
		
		$_dbd = new Global_Model_DbTable_DbTeacher();
		$_arr_opt_degree = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionDegree = $_dbd->getAllDegree();
		if(!empty($optionDegree))foreach($optionDegree AS $row) $_arr_opt_degree[$row['id']]=$row['name'];
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
		$_degree->setMultiOptions($_arr_opt_degree);
		$_degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside height-text',));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_teacher=  new Zend_Dojo_Form_Element_FilteringSelect('teacher_type');
		$_teacher->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_teacher_opt = array(
				1=>$this->tr->translate("TEACHER_KHMER"),
				0=>$this->tr->translate("TEACHER_FOREIGNER"));
		$_teacher->setMultiOptions($_teacher_opt);
		
		$_staff=  new Zend_Dojo_Form_Element_FilteringSelect('staff_type');
		$_staff->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_staff_opt = array(
				1=>$this->tr->translate("TEACHER"),
				2=>$this->tr->translate("STAFF"));
		$_staff->setMultiOptions($_staff_opt);
		
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
		
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		
		$id=  new Zend_Form_Element_Hidden('id');
		if(!empty($_data)){
 			$id->setValue($_data['id']);
 			$_nation->setValue($_data['nation']);
			$code->setValue($_data['teacher_code']);
			$_enname->setValue($_data['teacher_name_en']);
			$_khname->setValue($_data['teacher_name_kh']);
			$_branch_id->setValue($_data['branch_id']);
			$_staff->setValue($_data['staff_type']);
			$sex->setValue($_data['sex']);
			$phone->setValue($_data['tel']);
			$_nationality->setValue($_data['nationality']);
			$dob->setValue($_data['dob']);
			$_email->setValue($_data['email']);
			$_degree->setValue($_data['degree']);
 			$_note->setValue($_data['note']);
			$_status->setValue($_data['status']);
			$street_num->setValue($_data['street_num']);
			$home_num->setValue($_data['home_num']);
			$_user->setValue($_data['user_name']);
			
			$_province_id->setValue($_data['province_id']);
			$_position->setValue($_data['position_add']);
			$_passport->setValue($_data['passport_no']);
			$_experiences->setValue($_data['experiences']);
			$_card->setValue($_data['card_no']);
			$start_date->setValue($_data['start_date']);
			$end_date->setValue($_data['end_date']);
			$_agreement->setValue($_data['agreement']);
			$_schoolOption->setValue($_data['schoolOption']);
			$_user->setValue($_data['user_name']);
		}
		$this->addElements(array($id,$_enname,$home_num,$_staff,$_note,$street_num,$_province_id,$_branch_id,$_nation,$end_date,$_teacher,$_khname,$code,$phone,$_user,$_card,$_passport,$_nationality,$_experiences,$_agreement,$_position,$sex,$dob,
				$_email,$start_date,$_degree,$_status,$_submit,$_schoolOption));
		
		return $this;
	}
	
}