<?php 
Class Foundation_Form_FrmStudentRegister extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
	protected $t_date=null;
	protected $t_num=null;
	protected $text=null;
	protected $textarea=null;
	protected $_degree=null;
	protected $_khname=null;
	protected $_enname=null;
	protected $_phone=null;
	protected $_batch=null;
	protected $_year=null;
	protected $_session=null;
	protected $_dob=null;
	protected $_pay_date=null;
	public function init()
	{
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
	}
	public function FrmStudentRegister($data=null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$name_kh = new Zend_Dojo_Form_Element_ValidationTextBox('name_kh');
		$name_kh->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'
		));
	
		$name_en = new Zend_Dojo_Form_Element_ValidationTextBox('name_en');
		$name_en->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside'));
		
		$last_name = new Zend_Dojo_Form_Element_ValidationTextBox('last_name');
		$last_name->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside'));
		
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
				'class'=>'fullside height-text',));
// 		$studen_national =  new Zend_Dojo_Form_Element_FilteringSelect('studen_national');
// 		$studen_national->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onChange'=>'getallGrade();getStudentNo()',
		
// 		));
// 		$rs_nation = $_db->getAllNation();
// 		$arr_opt = array();
// 		if(!empty($rs_nation))foreach($rs_nation AS $row) $arr_opt[$row['id']]=$row['name'];
// 		$studen_national->setMultiOptions($arr_opt);
		
// 		$_arr_opt_nation = array(""=>$tr->translate("PLEASE_SELECT"),"-1"=>$tr->translate("ADD_NEW"));
// 		$optionNation = $_db->getViewByType(21);//Nation
// 		if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
// 		$studen_national = new Zend_Dojo_Form_Element_FilteringSelect("studen_national");
// 		$studen_national->setMultiOptions($_arr_opt_nation);
// 		$studen_national->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'onChange'=>'popupNation(1);',
// 				'missingMessage'=>'Invalid Module!',
// 				'class'=>'fullside height-text',));
		
// 		$_nation = new Zend_Dojo_Form_Element_FilteringSelect("nation");
// 		$_nation->setMultiOptions($_arr_opt_nation);
// 		$_nation->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				'onChange'=>'popupNation(2);',
// 				'missingMessage'=>'Invalid Module!',
// 				'class'=>'fullside height-text',));
	
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
		
		$date_of_birth = new Zend_Dojo_Form_Element_DateTextBox('date_of_birth');
		$date = date("2000-m-d");
		$date_of_birth->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				'required'=>true));
		$date_of_birth->setValue($date);
	
		$pob = new Zend_Dojo_Form_Element_Textarea('pob');
		$pob->setAttribs(array('dojoType'=>$this->textarea,
				'class'=>'fullside',
				//'value'=>'ភ្នំពេញ',
				//'placeholder'=>$tr->translate("SELECT_YEAR"),
				'style'=>'min-height:55px;',
				));
		
		
		$phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$phone->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'012345678',
				'class'=>'fullside'
			));
		
		$home_note = new Zend_Dojo_Form_Element_TextBox('home_note');
		$home_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$way_note = new Zend_Dojo_Form_Element_TextBox('way_note');
		$way_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$village_note = new Zend_Dojo_Form_Element_TextBox('village_note');
		$village_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$commun_note = new Zend_Dojo_Form_Element_TextBox('commun_note');
		$commun_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$distric_note = new Zend_Dojo_Form_Element_TextBox('distric_note');
		$distric_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		
		$rs_province = $_db->getAllProvince();
		$opt = '' ;//array(-1=>$this->tr->translate("SELECT_DEPT"));
		if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['id']]=$row['name'];
			
		$_student_province = new Zend_Dojo_Form_Element_FilteringSelect("student_province");
		$_student_province->setMultiOptions($opt);
		$_student_province->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$student_id = new Zend_Dojo_Form_Element_TextBox('student_id');
		$student_id->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside',
				'style'=>'color: red;',
				));
		
		$db = new Global_Model_DbTable_DbGroup();
		$rs_year = $db->getAllYears();
		$opt_year = array() ;//array(-1=>$this->tr->translate("SELECT_DEPT"));
		if(!empty($rs_year))foreach($rs_year AS $row) $opt_year[$row['id']]=$row['years'];			
		$_academic_year = new Zend_Dojo_Form_Element_FilteringSelect("academic_year");
		$_academic_year->setMultiOptions($opt_year);
		$_academic_year->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'placeholder'=>$tr->translate("SELECT_YEAR"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$session = new Zend_Dojo_Form_Element_FilteringSelect("session");
		$opt_session = array(
				1=>$tr->translate('MORNING'),
				2=>$tr->translate('AFTERNOON'),
				3=>$tr->translate('EVERNING'),
				4=>$tr->translate('WEEKEND'),
		);
		$session->setMultiOptions($opt_session);
		$session->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',));
		
		$degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getallGrade();getStudentNo()',
	
		));
		$rs_degree = $_db->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
		
		$_arr_opt_degree = array(""=>$tr->translate("PLEASE_SELECT_DEGREE"));
		$optionDegree = $_db->getAllDegreeMent(21);//Nation
		if(!empty($optionDegree))foreach($optionDegree AS $row) $_arr_opt_degree[$row['id']]=$row['name'];
		$degree_stu = new Zend_Dojo_Form_Element_FilteringSelect("calture");
		$degree_stu->setMultiOptions($_arr_opt_degree);
		$degree_stu->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'onChange'=>'popupNation(1);',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		
		$room =  new Zend_Dojo_Form_Element_FilteringSelect('room');
		$room->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		
		));
		$rs_roow = $_db->getAllRoom();
		$arr_room = array(-1=>$tr->translate("SELECT_ROOM"));
		if(!empty($rs_roow))foreach($rs_roow AS $row) $arr_room[$row['id']]=$row['name'];
		$room->setMultiOptions($arr_room);
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
				1=>$tr->translate('ACTIVE'),
				0=>$tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		
		$_stutype =  new Zend_Dojo_Form_Element_FilteringSelect('stu_denttype');
		$_stutype->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$type_opt = array(
				1=>$tr->translate("NEW_STUDENT"),
				0=>$tr->translate("OLD_STUDENT"));
		$_stutype->setMultiOptions($type_opt);
		
		$remark = new Zend_Dojo_Form_Element_Textarea('remark');
		$remark->setAttribs(array(
				'dojoType'=>$this->textarea,'class'=>'fullside',
				'style'=>'min-height: 60px !important;',
		));
		
		$fa_name_en = new Zend_Dojo_Form_Element_TextBox('fa_name_en');
		$fa_name_en->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$fa_dob = new Zend_Dojo_Form_Element_DateTextBox('fa_dob');
		$fa_dob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				));
// 		$fa_dob->setValue();
		$fa_phone = new Zend_Dojo_Form_Element_ValidationTextBox('fa_phone');
		$fa_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		
		
		$mom_name_en = new Zend_Dojo_Form_Element_TextBox('mom_name_en');
		$mom_name_en->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$mo_dob = new Zend_Dojo_Form_Element_DateTextBox('mo_dob');
		$mo_dob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		// 		$fa_dob->setValue();
		
		
		$mon_phone = new Zend_Dojo_Form_Element_ValidationTextBox('mon_phone');
		$mon_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		
		$guardian_name_en = new Zend_Dojo_Form_Element_TextBox('guardian_name_en');
		$guardian_name_en->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$guardian_dob = new Zend_Dojo_Form_Element_DateTextBox('guardian_dob');
		$guardian_dob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		// 		$fa_dob->setValue();
		
		$from_school = new Zend_Dojo_Form_Element_TextBox('from_school');
		$from_school->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$sponser = new Zend_Dojo_Form_Element_TextBox('sponser');
		$sponser->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$sponser_phone = new Zend_Dojo_Form_Element_TextBox('sponser_phone');
		$sponser_phone->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$guardian_phone = new Zend_Dojo_Form_Element_ValidationTextBox('guardian_phone');
		$guardian_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		$id = new Zend_Form_Element_hidden('id');
		
		if($data!=null){
			$id->setValue($data['stu_id']);
			$name_kh->setValue($data['stu_khname']);
			$name_en->setValue($data['stu_enname']);
			$last_name->setValue($data['last_name']);
			$_sex->setValue($data['sex']);
			if (!empty($data['dob'])){
			$date_of_birth->setValue(date("Y-m-d",strtotime($data['dob'])));
			}
			$pob->setValue($data['pob']);
			$phone->setValue($data['tel']);
			$home_note->setValue($data['home_num']);
		//	$_stutype->setValue($data['stu_denttype']);
			$way_note->setValue($data['street_num']);
			$village_note->setValue($data['village_name']);
			$_branch_id->setValue($data['branch_id']);
			$commun_note->setValue($data['commune_name']);
			$distric_note->setValue($data['district_name']);
			$_student_province->setValue($data['province_id']);
			$student_id->setValue($data['stu_code']);
			$_academic_year->setValue($data['academic_year']);
			$session->setValue($data['session']);
			$degree->setValue($data['degree']);
			$degree_stu->setValue($data['calture']);
			$room->setValue($data['room']);
			$status->setValue($data['status']);
			$remark->setValue($data['remark']);
			$fa_name_en->setValue($data['father_enname']);
			if (!empty($data['father_dob'])){
			$fa_dob->setValue(date("Y-m-d",strtotime($data['father_dob'])));
			}
			$fa_phone->setValue($data['father_phone']);
			$mom_name_en->setValue($data['mother_enname']);
			if (!empty($data['mother_dob'])){
			$mo_dob->setValue(date("Y-m-d",strtotime($data['mother_dob'])));
			}
			$mon_phone->setValue($data['mother_phone']);
			$guardian_name_en->setValue($data['guardian_enname']);
			if (!empty($data['guardian_dob'])){
			$guardian_dob->setValue(date("Y-m-d",strtotime($data['guardian_dob'])));
			}
			$guardian_phone->setValue($data['guardian_tel']);
			
			$from_school->setValue($data['from_school']);
			$sponser->setValue($data['sponser']);
			$sponser_phone->setValue($data['sponser_phone']);
		}
	
		$this->addElements(
				array(
						$id,
						$name_kh,
						$name_en,
						$last_name,
						$_sex,
						$date_of_birth,
						$pob,
						$_branch_id,
						$_stutype,
						$phone,
						$home_note,
						$way_note,
						$village_note,
						$commun_note,
						$distric_note,
						$_student_province,
						$student_id,
						$_academic_year,
						$session,
						$degree,
						$degree_stu,
						$room,
						$status,
						$remark,
						$fa_name_en,
						$fa_dob,
						$fa_phone,
						$mom_name_en,
						$mo_dob,
						$mon_phone,
						$guardian_name_en,
						$guardian_dob,
						$guardian_phone,
						$from_school,
						$sponser,
						$sponser_phone,
						)
				);
		
		return $this;
	}
	public function FrmStudropRegister($data=null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
	
		$student_id = new Zend_Dojo_Form_Element_TextBox('student_id');
		$student_id->setAttribs(array('dojoType'=>$this->text,'readonly'=>'readonly',
				'class'=>'fullside',
				'style'=>'color: red;',
		));
		
		$name_kh = new Zend_Dojo_Form_Element_ValidationTextBox('name_kh');
		$name_kh->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','readonly'=>'readonly','required'=>'true'
		));
		
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside','readonly'=>'readonly',));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
		
		$db = new Global_Model_DbTable_DbGroup();
		$rs_year = $db->getAllYears();
		$opt_year = array() ;//array(-1=>$this->tr->translate("SELECT_DEPT"));
		if(!empty($rs_year))foreach($rs_year AS $row) $opt_year[$row['id']]=$row['years'];
		$_academic_year = new Zend_Dojo_Form_Element_FilteringSelect("academic_year");
		$_academic_year->setMultiOptions($opt_year);
		$_academic_year->setAttribs(array('readonly'=>'readonly',
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'placeholder'=>$tr->translate("SELECT_YEAR"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
	
		$session = new Zend_Dojo_Form_Element_FilteringSelect("session");
		$opt_session = array(
				1=>$tr->translate('MORNING'),
				2=>$tr->translate('AFTERNOON'),
				3=>$tr->translate('EVERNING'),
				4=>$tr->translate('WEEKEND'),
		);
		$session->setMultiOptions($opt_session);
		$session->setAttribs(array(
				'readonly'=>'readonly',
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',));
		
		$reason = new Zend_Dojo_Form_Element_Textarea('reason');
		$reason->setAttribs(array(
				'dojoType'=>$this->textarea,'class'=>'fullside',
				'style'=>'min-height: 67px !important;',
		));
		
		$date_stop = new Zend_Dojo_Form_Element_DateTextBox('date_stop');
		$date_stop->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'required'=>'true',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		$date_stop->setValue(date('Y-m-d'));
		
	 
	
		$degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$degree->setAttribs(array(
				'readonly'=>'readonly',
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'value'=>'now',
				'onChange'=>'getallGrade();getStudentNo()',
	
		));
		$rs_degree = $_db->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
	
		$degree_stu =  new Zend_Dojo_Form_Element_FilteringSelect('calture');
		$degree_stu->setAttribs(array(
				'readonly'=>'readonly',
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getallGrade();getStudentNo()',
	
		));
		$_db = new Application_Model_DbTable_DbGlobal();
		$arr_degree = $_db->getAllDegreeStu();
		$degree_stu->setMultiOptions($arr_degree);
	
	
		$room =  new Zend_Dojo_Form_Element_FilteringSelect('room');
		$room->setAttribs(array(
				'readonly'=>'readonly',
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
	
		));
		$rs_roow = $_db->getAllRoom();
		$arr_room = array(-1=>$tr->translate("SELECT_ROOM"));
		if(!empty($rs_roow))foreach($rs_roow AS $row) $arr_room[$row['id']]=$row['name'];
		$room->setMultiOptions($arr_room);
		
		$stu_stop =  new Zend_Dojo_Form_Element_FilteringSelect('stu_stop');
		$stu_stop->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		
		));
		$_db = new Foundation_Model_DbTable_DbStudentDrop();
		$rs_stop = $_db->getAllDropType();
		$arr_stop = array(-1=>$tr->translate("SELECT_TYPE"));
		if(!empty($rs_stop))foreach($rs_stop AS $row) $arr_stop[$row['id']]=$row['name'];
		$stu_stop->setMultiOptions($arr_stop);
	
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
				1=>$tr->translate('ACTIVE'),
				0=>$tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		$remark = new Zend_Dojo_Form_Element_Textarea('remark');
		$remark->setAttribs(array(
				'dojoType'=>$this->textarea,'class'=>'fullside',
				'style'=>'min-height: 60px !important;',
		));
	
		$id = new Zend_Form_Element_hidden('id');
	
		if($data!=null){
			$id->setValue($data['stu_id']);
			$name_kh->setValue($data['stu_khname']);
			$_sex->setValue($data['sex']);
			$student_id->setValue($data['stu_code']);
			$_academic_year->setValue($data['academic_year']);
			$session->setValue($data['session']);
			$degree->setValue($data['degree']);
	//		$reason->setValue($data['reason']);
		//	$date_stop->setValue($data['date_stop']);
			$degree_stu->setValue($data['calture']);
			$room->setValue($data['room']);
			$status->setValue($data['status']);
			$remark->setValue($data['remark']);
		}
	
		$this->addElements(
				array(
						$id,
						$student_id,
						$_academic_year,
						$session,
						$degree,
						$_sex,
						$reason,
						$name_kh,
						$stu_stop,
						$degree_stu,
						$date_stop,
						$room,
						$status,
						$remark,
				)
		);
	
		return $this;
	}
	
	
}