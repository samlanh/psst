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
		
		$studen_national = new Zend_Dojo_Form_Element_TextBox('studen_national');
		$studen_national->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
	
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
				'style'=>'min-height:40px;',
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
		
		
		$rs_province = $_db->getProvince();
		$opt = '' ;//array(-1=>$this->tr->translate("SELECT_DEPT"));
		if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['province_id']]=$row['province_en_name'];
			
		$_student_province = new Zend_Dojo_Form_Element_FilteringSelect("student_province");
		$_student_province->setMultiOptions($opt);
		$_student_province->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$student_id = new Zend_Dojo_Form_Element_TextBox('student_id');
		$student_id->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside',
				'style'=>'color: red;',
				));
		
		$rs_year = $_db->getAllYear();
		$opt_year = array() ;//array(-1=>$this->tr->translate("SELECT_DEPT"));
		if(!empty($rs_year))foreach($rs_year AS $row) $opt_year[$row['id']]=$row['name'];			
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
	
		));
		$rs_degree = $_db->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
		
		
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
		$fa_national = new Zend_Dojo_Form_Element_TextBox('fa_national');
		$fa_national->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
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
		$mom_nation = new Zend_Dojo_Form_Element_TextBox('mom_nation');
		$mom_nation->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
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
		$guardian_national = new Zend_Dojo_Form_Element_TextBox('guardian_national');
		$guardian_national->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$guardian_phone = new Zend_Dojo_Form_Element_ValidationTextBox('guardian_phone');
		$guardian_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		$id = new Zend_Form_Element_hidden('id');
		
		if($data!=null){
			$id->setValue($data['stu_id']);
			$name_kh->setValue($data['stu_khname']);
			$name_en->setValue($data['stu_enname']);
			$studen_national->setValue($data['nationality']);
			$_sex->setValue($data['sex']);
			if (!empty($data['dob'])){
			$date_of_birth->setValue(date("Y-m-d",strtotime($data['dob'])));
			}
			$pob->setValue($data['pob']);
			$phone->setValue($data['tel']);
			$home_note->setValue($data['home_num']);
			$way_note->setValue($data['street_num']);
			$village_note->setValue($data['village_name']);
			$commun_note->setValue($data['commune_name']);
			$distric_note->setValue($data['district_name']);
			$_student_province->setValue($data['province_id']);
			$student_id->setValue($data['stu_code']);
			$_academic_year->setValue($data['academic_year']);
			$session->setValue($data['session']);
			$degree->setValue($data['degree']);
			$room->setValue($data['room']);
			$status->setValue($data['status']);
			$remark->setValue($data['remark']);
			$fa_name_en->setValue($data['father_enname']);
			if (!empty($data['father_dob'])){
			$fa_dob->setValue(date("Y-m-d",strtotime($data['father_dob'])));
			}
			$fa_national->setValue($data['father_nation']);
			$fa_phone->setValue($data['father_phone']);
			$mom_name_en->setValue($data['mother_enname']);
			if (!empty($data['mother_dob'])){
			$mo_dob->setValue(date("Y-m-d",strtotime($data['mother_dob'])));
			}
			$mom_nation->setValue($data['mother_nation']);
			$mon_phone->setValue($data['mother_phone']);
			$guardian_name_en->setValue($data['guardian_enname']);
			if (!empty($data['guardian_dob'])){
			$guardian_dob->setValue(date("Y-m-d",strtotime($data['guardian_dob'])));
			}
			$guardian_national->setValue($data['guardian_nation']);
			$guardian_phone->setValue($data['guardian_tel']);
		}
	
		$this->addElements(
				array(
						$id,
						$name_kh,
						$name_en,
						$studen_national,
						$_sex,
						$date_of_birth,
						$pob,
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
						$room,
						$status,
						$remark,
						$fa_name_en,
						$fa_dob,
						$fa_national,
						$fa_phone,
						$mom_name_en,
						$mo_dob,
						$mom_nation,
						$mon_phone,
						$guardian_name_en,
						$guardian_dob,
						$guardian_national,
						$guardian_phone,
						
						)
				);
		
		return $this;
	}
	
	
}