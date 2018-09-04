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
		
		$_enname = new Zend_Dojo_Form_Element_TextBox('en_name');
		$_enname->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
		
		$_khname = new Zend_Dojo_Form_Element_TextBox('kh_name');
		$_khname->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
		
		$code = new Zend_Dojo_Form_Element_TextBox('code');
		$code->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside','style'=>'color:red;'));
		$db = new Application_Model_DbTable_DbGlobal();
		$code_num = $db->getTeacherCode();
		$code->setValue($code_num);
		
		$phone = new Zend_Dojo_Form_Element_NumberTextBox('phone');
		$phone->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'));
		
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
		
		$_nationality = new Zend_Dojo_Form_Element_TextBox('nationality');
		$_nationality->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_position = new Zend_Dojo_Form_Element_TextBox('position_add');
		$_position->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_email = new Zend_Dojo_Form_Element_TextBox('email');
		$_email->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_passport = new Zend_Dojo_Form_Element_TextBox('passport_no');
		$_passport->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		$_card = new Zend_Dojo_Form_Element_NumberTextBox('card_no');
		$_card->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'));
		
		$_experiences = new Zend_Dojo_Form_Element_Textarea('experiences');
		$_experiences->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:40px;',));
		
		$_agreement = new Zend_Dojo_Form_Element_Textarea('agreement');
		$_agreement->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:40px;',));
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));	
		$degree_opt = $db->getAllDegree();
		$_degree->setMultiOptions($degree_opt);
		
// 		$rs_roow = $_db->getAllRoom();
// 		$arr_room = array(-1=>$tr->translate("SELECT_ROOM"));
// 		if(!empty($rs_roow))foreach($rs_roow AS $row) $arr_room[$row['id']]=$row['name'];
// 		$room->setMultiOptions($arr_room);
		
		$_note =  new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));

		$_photo = new Zend_Form_Element_File('photo');
		
		
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
				2=>$this->tr->translate("TEACHER_FOREIGNER"));
		$_teacher->setMultiOptions($_teacher_opt);
		
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		
		$id=  new Zend_Form_Element_Hidden('id');
		if(!empty($_data)){
// 			$id->setValue($_data['id']);
			$code->setValue($_data['teacher_code']);
// 			$_enname->setValue($_data['teacher_name_en']);
// 			$_khname->setValue($_data['teacher_name_kh']);
// 			$sex->setValue($_data['sex']);
// 			$phone->setValue($_data['tel']);
// 			$pob->setValue($_data['pob']);
// 			$dob->setValue($_data['dob']);
// 			$_adress->setValue($_data['address']);
//			$end_date->setValue($_data['end_date']);
// 			$_degree->setValue($_data['degree']);
// 			$_note->setValue($_data['note']);
// 			$_status->setValue($_data['status']);
		}
		$this->addElements(array($id,$_enname,$_note,$end_date,$_teacher,$_khname,$code,$phone,$_user,$_card,$_photo,$_passport,$_nationality,$_experiences,$_agreement,$_position,$sex,$dob,$_adress,$_email,$start_date,$_degree,$_status,$_submit));
		
		return $this;
		
	}
	
}