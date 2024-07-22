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
	
		$required=(STU_EN_REQUIRED==1)?'true':'false';
		$name_en = new Zend_Dojo_Form_Element_ValidationTextBox('name_en');
		$name_en->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>$required,
				'class'=>'fullside'));
		
		$last_name = new Zend_Dojo_Form_Element_ValidationTextBox('last_name');
		$last_name->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>$required,
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
				'onChange'=>'getAllAcademicByBranch();getStudentNo();',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
	
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"),
				''=>$tr->translate("N/A")
			);
		$_sex->setMultiOptions($sex_opt);
		
		$date_of_birth = new Zend_Dojo_Form_Element_DateTextBox('date_of_birth');
		$date = date("2000-m-d");
		$date_of_birth->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				//'required'=>true
				));
		//$date_of_birth->setValue($date);
	
		$pob = new Zend_Dojo_Form_Element_Textarea('pob');
		$pob->setAttribs(array('dojoType'=>$this->textarea,
				'class'=>'fullside',
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
		$province_opt =array() ;
		if(!empty($rs_province))foreach($rs_province AS $row) $province_opt[$row['id']]=$row['name'];
			
		$_student_province = new Zend_Dojo_Form_Element_FilteringSelect("student_province");
		$_student_province->setMultiOptions($province_opt);
		$_student_province->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$entry_stuId=(ENTY_STUID==0)?'readOnly':'false';
		$student_id = new Zend_Dojo_Form_Element_TextBox('student_id');
		$student_id->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside',
				'required'=>'true',
				'style'=>'color: red;',
				'readOnly'=>$entry_stuId,
				));
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$level = $session_user->level;
		if($level==1){
			$student_id->setAttrib('readOnly', 'false');
		}
			
		
		$db = new Global_Model_DbTable_DbGroup();
		$rs_year = $db->getAllYears();
		$opt_year = array() ;
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
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'));
		
		$degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getallGrade();getStudentNo()',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'
		));
		$rs_degree = $_db->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
		
		$_arr_opt_degree = array(""=>$tr->translate("PLEASE_SELECT_EDUCATION_LEVEL"));
		$optionDegree = $_db->getAllDegreeMent(21);//Education Level
		if(!empty($optionDegree))foreach($optionDegree AS $row) $_arr_opt_degree[$row['id']]=$row['name'];
		$degree_stu = new Zend_Dojo_Form_Element_FilteringSelect("calture");
		$degree_stu->setMultiOptions($_arr_opt_degree);
		$degree_stu->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'required'=>'false'));
		
		
		$room =  new Zend_Dojo_Form_Element_FilteringSelect('room');
		$room->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'
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
				'style'=>'min-height: 65px !important;',
		));
		
		$fa_name_en = new Zend_Dojo_Form_Element_TextBox('fa_name_en');
		$fa_name_en->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
				
		$father_khname = new Zend_Dojo_Form_Element_TextBox('father_khname');
		$father_khname->setAttribs(array('dojoType'=>$this->text,
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
		
		$mother_khname = new Zend_Dojo_Form_Element_TextBox('mother_khname');
		$mother_khname->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
				
		$mo_dob = new Zend_Dojo_Form_Element_DateTextBox('mo_dob');
		$mo_dob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		
		$mon_phone = new Zend_Dojo_Form_Element_ValidationTextBox('mon_phone');
		$mon_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		
		$guardian_name_en = new Zend_Dojo_Form_Element_TextBox('guardian_name_en');
		$guardian_name_en->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$guardian_khname = new Zend_Dojo_Form_Element_TextBox('guardian_khname');
		$guardian_khname->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
				
		$guardian_dob = new Zend_Dojo_Form_Element_DateTextBox('guardian_dob');
		$guardian_dob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		
		$from_school = new Zend_Dojo_Form_Element_TextBox('from_school');
		$from_school->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$rs_province = $_db->getAllProvince();
		$province_opt = array('0' => $tr->translate('SELECT_PROVINCE'));
		if(!empty($rs_province))foreach($rs_province AS $row) $province_opt[$row['id']]=$row['name'];
		$school_province = new Zend_Dojo_Form_Element_FilteringSelect("school_province");
		$school_province->setMultiOptions($province_opt);
		$school_province->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$sponser = new Zend_Dojo_Form_Element_TextBox('sponser');
		$sponser->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$sponser_phone = new Zend_Dojo_Form_Element_TextBox('sponser_phone');
		$sponser_phone->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$guardian_phone = new Zend_Dojo_Form_Element_ValidationTextBox('guardian_phone');
		$guardian_phone->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside'));
		
		$date_baccexam = new Zend_Dojo_Form_Element_DateTextBox('date_baccexam');
		$date_baccexam->setAttribs(array('dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside'));
		
		$center_baccexam = new Zend_Dojo_Form_Element_TextBox('center_baccexam');
		$center_baccexam->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		$room_baccexam = new Zend_Dojo_Form_Element_TextBox('room_baccexam');
		$room_baccexam->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		$table_baccexam = new Zend_Dojo_Form_Element_TextBox('table_baccexam');
		$table_baccexam->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		$grade_baccexam = new Zend_Dojo_Form_Element_TextBox('grade_baccexam');
		$grade_baccexam->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		$score_baccexam = new Zend_Dojo_Form_Element_NumberTextBox('score_baccexam');
		$score_baccexam->setAttribs(array('dojoType'=>"dijit.form.NumberTextBox",
				'class'=>'fullside'));
		
		$certificate_baccexam = new Zend_Dojo_Form_Element_TextBox('certificate_baccexam');
		$certificate_baccexam->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		$discount_type = new Zend_Dojo_Form_Element_FilteringSelect("discount_type");
		$discount_type->setMultiOptions($province_opt);
		$discount_type->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$discount_type->setMultiOptions($_db->getAllDiscount(1));
		
		$scholarship_amount = new Zend_Dojo_Form_Element_NumberTextBox('scholarship_amount');
		$scholarship_amount->setAttribs(array('dojoType'=>"dijit.form.NumberTextBox",
				'class'=>'fullside'));
		
		$scholarship_fromdate = new Zend_Dojo_Form_Element_DateTextBox('scholarship_fromdate');
		$scholarship_fromdate->setAttribs(array('dojoType'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside'));
		
		$scholarship_todate = new Zend_Dojo_Form_Element_DateTextBox('scholarship_todate');
		$scholarship_todate->setAttribs(array('dojoType'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside'));
		
	
		$studentType = new Zend_Dojo_Form_Element_FilteringSelect("studentType");	
		$studentType->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$condictionArr = array("addonsItem"=>"addNEw");
		$studentType->setMultiOptions($_db->getViewByType(40,1,$condictionArr));
		
		$id = new Zend_Form_Element_hidden('id');
		$id->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		if($data!=null){
			
			
			
			
			$school_province->setValue($data['province_bacc']);
			$certificate_baccexam->setValue($data['certificate_bacc']);
			$score_baccexam->setValue($data['score_bacc']);
			$grade_baccexam->setValue($data['grade_bacc']);
			$table_baccexam->setValue($data['table_bacc']);
			$room_baccexam->setValue($data['room_bacc']);
			$center_baccexam->setValue($data['center_bacc']);
			$date_baccexam->setValue($data['date_bacc']);
			
			$id->setValue($data['stu_id']);
			$name_kh->setValue($data['stu_khname']);
			$name_en->setValue($data['stu_enname']);
			$last_name->setValue($data['last_name']);
			
			$_sex->setValue($data['sex']);
			
			if (!empty($data['dob']) AND $data['dob']!='0000-00-00'){
				$date_of_birth->setValue(date("Y-m-d",strtotime($data['dob'])));
			}
			$pob->setValue($data['pob']);
			$phone->setValue($data['tel']);
			$home_note->setValue($data['home_num']);
			$way_note->setValue($data['street_num']);
			$village_note->setValue($data['village_name']);
			$_branch_id->setValue($data['branch_id']);
			$commun_note->setValue($data['commune_name']);
			$distric_note->setValue($data['district_name']);
			$_student_province->setValue($data['province_id']);
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getActionName()=='edit'){
				$student_id->setValue($data['stu_code']);
			}			
			$degree_stu->setValue($data['calture']);
			$status->setValue($data['status']);
			$remark->setValue($data['remark']);
			$fa_name_en->setValue($data['father_enname']);
			if (!empty($data['father_dob']) AND $data['father_dob']!='0000-00-00'){
				$fa_dob->setValue(date("Y-m-d",strtotime($data['father_dob'])));
			}
			$fa_phone->setValue($data['father_phone']);
			$mom_name_en->setValue($data['mother_enname']);
			if (!empty($data['mother_dob']) AND $data['mother_dob']!='0000-00-00'){
			$mo_dob->setValue(date("Y-m-d",strtotime($data['mother_dob'])));
			}
			$mon_phone->setValue($data['mother_phone']);
			$guardian_name_en->setValue($data['guardian_enname']);
			if (!empty($data['guardian_dob']) AND $data['guardian_dob']!='0000-00-00'){
			$guardian_dob->setValue(date("Y-m-d",strtotime($data['guardian_dob'])));
			}
			$guardian_phone->setValue($data['guardian_tel']);
			
			$from_school->setValue($data['from_school']);
			$sponser->setValue($data['sponser']);
			$sponser_phone->setValue($data['sponser_phone']);
			
			$father_khname->setValue($data['father_khname']);
			$mother_khname->setValue($data['mother_khname']);
			$guardian_khname->setValue($data['guardian_khname']);
			$data['studentType'] = empty($data['studentType']) ? 0 : $data['studentType'];
			$studentType->setValue($data['studentType']);
			
		}
	
		$this->addElements(
				array(	
						$scholarship_amount,
						$scholarship_fromdate,
						$scholarship_todate,
						$discount_type,
						$school_province,
						$certificate_baccexam,
						$score_baccexam,
						$grade_baccexam,
						$table_baccexam,
						$room_baccexam,
						$center_baccexam,
						$date_baccexam,
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
						
						$father_khname,
						$mother_khname,
						$guardian_khname,
						$studentType
						
						)
				);
		
		return $this;
	}	
}