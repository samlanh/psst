<?php
class Test_Form_FrmStudentTest extends Zend_Dojo_Form
{
	protected  $tr;
    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddStudentTest($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userinfo = $_dbgb->getUserInfo();
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getSerailCodeByBranch();',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
				
		if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	$kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
    	$kh_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Name In Khmer")
    	));
    	
    	$_first_name = new Zend_Dojo_Form_Element_TextBox('first_name');
    	$_first_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    	));
    	
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$optionNation = $_dbgb->getViewByType(21);//Nation
    	if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
    	$_nationality = new Zend_Dojo_Form_Element_FilteringSelect("nationality");
    	$_nationality->setMultiOptions($_arr_opt_nation);
    	$_nationality->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'popupNation(1);',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$_nation = new Zend_Dojo_Form_Element_FilteringSelect("nation");
    	$_nation->setMultiOptions($_arr_opt_nation);
    	$_nation->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'popupNation(2);',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$dob= new Zend_Dojo_Form_Element_DateTextBox('dob');
    	$dob->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d",strtotime("-10 year"));
    	$dob->setValue($_date);
    	
    	$_pob = new Zend_Dojo_Form_Element_TextBox('pob');
    	$_pob->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("POB"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
    	$_phone->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("PHONE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$_email = new Zend_Dojo_Form_Element_TextBox('email');
    	$_email->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("EMAIL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$_arr = array(1=>$this->tr->translate("SINGLE"),2=>$this->tr->translate("MARRIED"),3=>$this->tr->translate("MONK"));
    	$_student_status = new Zend_Dojo_Form_Element_FilteringSelect("student_status");
    	$_student_status->setMultiOptions($_arr);
    	$_student_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_if_employed_where = new Zend_Dojo_Form_Element_TextBox('if_employed_where');
    	$_if_employed_where->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("IF_EMPLOYED_WHERE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$_position = new Zend_Dojo_Form_Element_TextBox('position');
    	$_position->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("POSITION"),
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$_parent_name = new Zend_Dojo_Form_Element_TextBox('parent_name');
    	$_parent_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'onKeyup'=>'typethisamename();',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Parent Name")
    	));
    	
    	$_parent_tel = new Zend_Dojo_Form_Element_TextBox('parent_tel');
    	$_parent_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'onKeyup'=>'typethisamephone();',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Tel")
    	));
    	
    	$_from_school = new Zend_Dojo_Form_Element_TextBox('old_school');
    	$_from_school->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("FROM_SCHOOL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$_old_grade = new Zend_Dojo_Form_Element_TextBox('old_grade');
    	$_old_grade->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$home_num = new Zend_Dojo_Form_Element_TextBox('home_num');
    	$home_num->setAttribs(array('dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside'));
    	
    	$street_num = new Zend_Dojo_Form_Element_TextBox('street_num');
    	$street_num->setAttribs(array('dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside'));
    	
    	$village_name = new Zend_Dojo_Form_Element_TextBox('village_name');
    	$village_name->setAttribs(array('dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside'));
    	
    	$commune_name = new Zend_Dojo_Form_Element_TextBox('commune_name');
    	$commune_name->setAttribs(array('dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside'));
    	
    	$district_name = new Zend_Dojo_Form_Element_TextBox('district_name');
    	$district_name->setAttribs(array('dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside'));
    	
    	$rs_province = $_dbgb->getProvince();
    	$opt = array();
    	if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['province_id']]=$row['province_en_name'];
    		
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
    	$_province_id->setValue(12);
    	
    	$_stu_code = new Zend_Dojo_Form_Element_TextBox('stu_code');
    	$_stu_code->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'readonly'=>'readonly',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$serialType = STU_SERIAL_TYPE;
    	$arrSerialAttribute = array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    			);
    	if ($serialType!=2){
    		$arrSerialAttribute['readonly']="readonly";
    	}
    	$_serial = new Zend_Dojo_Form_Element_TextBox('serial');
    	$_serial->setAttribs(
    			$arrSerialAttribute
    	);
    	$newSerial = $_dbgb->getTestStudentId();
    	$_serial->setValue($newSerial);
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree_result = new Zend_Dojo_Form_Element_FilteringSelect("degree_result");
    	$_degree_result->setMultiOptions($_arr_opt);
    	$_degree_result->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>false,
    			'onChange'=>'getAllGradeResult();',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$test_date= new Zend_Dojo_Form_Element_DateTextBox('test_date');
    	$test_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    		$_date = date("Y-m-d");
    	$test_date->setValue($_date);
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_emergency_name = new Zend_Dojo_Form_Element_TextBox('emergency_name');
    	$_emergency_name->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Emergency Name")
    	));
    	
    	$_relationship_to_student = new Zend_Dojo_Form_Element_TextBox('relationship_to_student');
    	$_relationship_to_student->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$_emergency_tel = new Zend_Dojo_Form_Element_TextBox('emergency_tel');
    	$_emergency_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	
    	$_arr = array(
    			1=>$this->tr->translate("STUDENT"),
    			2=>$this->tr->translate("STAFF"),
    			3=>$this->tr->translate("OWN_BUSSINESS"));
    	$_student_option = new Zend_Dojo_Form_Element_FilteringSelect("student_option");
    	$_student_option->setMultiOptions($_arr);
    	$_student_option->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
    	$_degree->setMultiOptions($_arr_opt);
    	$_degree->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGrade();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_degree->setValue($request->getParam('degree'));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	$result_date= new Zend_Dojo_Form_Element_DateTextBox('result_date');
    	$date = date("Y-m-d");
    	$result_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$result_date->setValue($_date);
    	
    	$_score = new Zend_Dojo_Form_Element_NumberTextBox('score');
    	$_score->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("SCORE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Score")
    	));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree_search = new Zend_Dojo_Form_Element_FilteringSelect("degree_search");
    	$_degree_search->setMultiOptions($_arr_opt);
    	$_degree_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGrade();',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_degree_search->setValue($request->getParam("degree_search"));
    	
    	$_arr_opt_nation = array(""=>$this->tr->translate("NATIONALITY"));
    	$optionNation = $_dbgb->getViewByType(21);//Nation
    	if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
    	$_nation_search = new Zend_Dojo_Form_Element_FilteringSelect("nation_search");
    	$_nation_search->setMultiOptions($_arr_opt_nation);
    	$_nation_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_nation_search->setValue($request->getParam("nation_search"));
    	
    	$_arr = array(""=>$this->tr->translate("OCCUPATION"),1=>
    			$this->tr->translate("STUDENT"),
    			2=>$this->tr->translate("STAFF"),
    			3=>$this->tr->translate("OWN_BUSSINESS"));
    	$_student_option_search = new Zend_Dojo_Form_Element_FilteringSelect("student_option_search");
    	$_student_option_search->setMultiOptions($_arr);
    	$_student_option_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_student_option_search->setValue($request->getParam("student_option_search"));
    	
    	$rs_province = $_dbgb->getProvince();
    	$opt = array(""=>$this->tr->translate("SELECT_PROVINCE"));
    	if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['province_id']]=$row['province_en_name'];
    	$_province_search = new Zend_Dojo_Form_Element_FilteringSelect("province_search");
    	$_province_search->setMultiOptions($opt);
    	$_province_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside',
    			'onChange'=>'filterDistrict();',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    	));
    	$_province_search->setValue($request->getParam("province_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("COMPLETED"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("START_DATE"),
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	$start_date->setValue($_date);
    		
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("END_DATE"),
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_branch_search->setValue($request->getParam("branch_search"));
		if (count($optionBranch)==1){
    		$_branch_search->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_search->setValue($row['id']);
    		}
    	}
    	
		$_arr = array(
			""=>$this->tr->translate("TYPE_TEST"),
			1=>$this->tr->translate("CREATE_TEST_EXAM_KH"),
			2=>$this->tr->translate("CREATE_TEST_EXAM_EN"),
			3=>$this->tr->translate("CREATE_TEST_EXAM_UNIV")
		);
    	$_type_exam = new Zend_Dojo_Form_Element_FilteringSelect("type_exam");
    	$_type_exam->setMultiOptions($_arr);
    	$_type_exam->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_type_exam->setValue($request->getParam("type_exam"));
		if($userinfo['level']!=1){
			$_type_exam->setAttribs(array('readonly'=>'readonly'));
			if(!empty($userinfo['schoolOption'])){
				$_type_exam->setValue($userinfo['schoolOption']);
			}
		}
		
		$_arr_opt = array(''=>$this->tr->translate("TEST_TYPE"));
		$rows = $_dbgb->getPlacementTestType();
		if(!empty($rows))foreach($rows AS $row) $_arr_opt[$row['id']]= preg_replace( "/\r|\n/", "", strip_tags(htmlspecialchars($row['name'])));
		$_test_type = new Zend_Dojo_Form_Element_FilteringSelect("test_type");
		$_test_type->setMultiOptions($_arr_opt);
		$_test_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside height-text',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_test_type->setValue($request->getParam("test_type"));
		
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$kh_name->setValue($data["stu_khname"]);
    		$_first_name->setValue($data["stu_enname"]);
    		$_last_name->setValue($data["last_name"]);
    		$_sex->setValue($data["sex"]);
    		$_nationality->setValue($data["nationality"]);
    		$_nation->setValue($data["nation"]);
    		if (!empty($data["dob"])){
    			$dob->setValue(date("Y-m-d",strtotime($data["dob"])));
    		}
    		$_pob->setValue($data["pob"]);
    		$_phone->setValue($data["tel"]);
    		$_email->setValue($data["email"]);
    		$_student_status->setValue($data["student_status"]);
    		$_parent_name->setValue($data["guardian_khname"]);
    		$_parent_tel->setValue($data["guardian_tel"]);
    		$_from_school->setValue($data["from_school"]);
    		$_old_grade->setValue($data["old_grade"]);
    		$_student_option->setValue($data["student_option"]);
    		$_stu_code->setValue($data["stu_code"]);
    		$_serial->setValue($data["serial"]);
    		$_emergency_name->setValue($data["emergency_name"]);
    		$_relationship_to_student->setValue($data["relationship_to_student"]);
    		$_emergency_tel->setValue($data["emergency_tel"]);
    		$_status->setValue($data["status"]);
    		$home_num->setValue($data["home_num"]);
    		$street_num->setValue($data["street_num"]);
    		$village_name->setValue($data["village_name"]);
    		$commune_name->setValue($data["commune_name"]);
    		$district_name->setValue($data["district_name"]);
    		$_province_id->setValue($data["province_id"]);
    		$id->setValue($data["stu_id"]);
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		
    		$_test_type->setValue($data["test_type"]);
    		if (!empty($data["customer_type"])){
    			if ($data["customer_type"]==1){
    				$kh_name->setAttribs(array('readonly'=>'readonly'));
    				$_first_name->setAttribs(array('readonly'=>'readonly'));
    				$_last_name->setAttribs(array('readonly'=>'readonly'));
    				$_sex->setAttribs(array('readonly'=>'readonly'));
    				
    				$_phone->setAttribs(array('readonly'=>'readonly'));
    				$_email->setAttribs(array('readonly'=>'readonly'));
    				
    				$home_num->setAttribs(array('readonly'=>'readonly'));
    				$street_num->setAttribs(array('readonly'=>'readonly'));
    				$village_name->setAttribs(array('readonly'=>'readonly'));
    				$commune_name->setAttribs(array('readonly'=>'readonly'));
    				$district_name->setAttribs(array('readonly'=>'readonly'));
    				$_province_id->setAttribs(array('readonly'=>'readonly'));
    				
    			}
    			
    		}
    	}
    	
    	$this->addElements(array(
    			$_branch_id,
				$kh_name,
				$_first_name,
				$_last_name,
				$_sex,
    			$_nationality,
    			$_nation,
				$dob,
				$_pob,
				$_phone,
				$_email,
				$_student_status,
				$_if_employed_where,
				$_position,
				$_parent_name,
				$_parent_tel,
				$_from_school,
				$_old_grade,
    			$_student_option,
				$_stu_code,
				$_serial,
				$_degree_result,
				$test_date,
				$note,
				$_emergency_name,
				$_relationship_to_student,
				$_emergency_tel,
				$_degree,
    			$_session,
				$id,
    			$result_date,
    			$_score,
    			$_status,
    			$home_num,
    			$street_num,
    			$village_name,
    			$commune_name,
    			$district_name,
    			$_province_id,
    			$advance_search,
    			$_degree_search,
    			$_nation_search,
    			$_student_option_search,
    			$_province_search,
    			$_status_search,
    			$start_date,
    			$end_date,
    			$_branch_search,
				$_type_exam,
    			$_test_type
    			));
    	return $this;
    }
    
    function FrmAddCRMToTest($data){
    	 
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userinfo = $_dbgb->getUserInfo();
    	 
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getSerailCodeByBranch();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
    	$kh_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Name In Khmer")
    	));
    	 
    	$_first_name = new Zend_Dojo_Form_Element_TextBox('first_name');
    	$_first_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    	));
    	 
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	 
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));

    	
    	$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$optionNation = $_dbgb->getViewByType(21);//Nation
    	if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
    	$_nationality = new Zend_Dojo_Form_Element_FilteringSelect("nationality");
    	$_nationality->setMultiOptions($_arr_opt_nation);
    	$_nationality->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'popupNation(1);',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_nation = new Zend_Dojo_Form_Element_FilteringSelect("nation");
    	$_nation->setMultiOptions($_arr_opt_nation);
    	$_nation->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'popupNation(2);',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$dob= new Zend_Dojo_Form_Element_DateTextBox('dob');
    	$dob->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d",strtotime("-10 year"));
    	$dob->setValue($_date);
    	 
    	$_pob = new Zend_Dojo_Form_Element_TextBox('pob');
    	$_pob->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("POB"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	 
    	$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
    	$_phone->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("PHONE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	 
    	$_email = new Zend_Dojo_Form_Element_TextBox('email');
    	$_email->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("EMAIL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	 
    	$_arr = array(1=>$this->tr->translate("SINGLE"),2=>$this->tr->translate("MARRIED"),3=>$this->tr->translate("MONK"));
    	$_student_status = new Zend_Dojo_Form_Element_FilteringSelect("student_status");
    	$_student_status->setMultiOptions($_arr);
    	$_student_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$_if_employed_where = new Zend_Dojo_Form_Element_TextBox('if_employed_where');
    	$_if_employed_where->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("IF_EMPLOYED_WHERE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_position = new Zend_Dojo_Form_Element_TextBox('position');
    	$_position->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("POSITION"),
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_parent_name = new Zend_Dojo_Form_Element_TextBox('parent_name');
    	$_parent_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Parent Name")
    	));
    	 
    	$_parent_tel = new Zend_Dojo_Form_Element_TextBox('parent_tel');
    	$_parent_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Tel")
    	));
    	 
    	$_from_school = new Zend_Dojo_Form_Element_TextBox('old_school');
    	$_from_school->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("FROM_SCHOOL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	 
    	$_old_grade = new Zend_Dojo_Form_Element_TextBox('old_grade');
    	$_old_grade->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$address=  new Zend_Form_Element_Textarea('address');
    	$address->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	 
    	$_stu_code = new Zend_Dojo_Form_Element_TextBox('stu_code');
    	$_stu_code->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'readonly'=>'readonly',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_serial = new Zend_Dojo_Form_Element_TextBox('serial');
    	$_serial->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	$newSerial = $_dbgb->getTestStudentId();
    	$_serial->setValue($newSerial);
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree_result = new Zend_Dojo_Form_Element_FilteringSelect("degree_result");
    	$_degree_result->setMultiOptions($_arr_opt);
    	$_degree_result->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGradeResult();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$test_date= new Zend_Dojo_Form_Element_DateTextBox('test_date');
    	$test_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d");
    	$test_date->setValue($_date);
    	 
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	 
    	$_emergency_name = new Zend_Dojo_Form_Element_TextBox('emergency_name');
    	$_emergency_name->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Emergency Name")
    	));
    	 
    	$_relationship_to_student = new Zend_Dojo_Form_Element_TextBox('relationship_to_student');
    	$_relationship_to_student->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_emergency_tel = new Zend_Dojo_Form_Element_TextBox('emergency_tel');
    	$_emergency_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_emergency_address = new Zend_Dojo_Form_Element_TextBox('emergency_address');
    	$_emergency_address->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter")
    	));
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
    	$_degree->setMultiOptions($_arr_opt);
    	$_degree->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGrade();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    	));
    	
    	
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	 
    	$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$_nation_search = new Zend_Dojo_Form_Element_FilteringSelect("nation_search");
    	$_nation_search->setMultiOptions($_arr_opt_nation);
    	$_nation_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_nation_search->setValue($request->getParam("nation_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("COMPLETED"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_status_search->setValue($request->getParam("status_search"));
    	 
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    	}
    	$start_date->setValue($_date);
    
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	 
    	if ($userinfo['level']!=1){
    		$_branch_id->setAttribs(array(
    			'readonly'=>"readonly"));
    		$kh_name->setAttribs(array(
    				'readonly'=>"readonly"));
    		$_first_name->setAttribs(array(
    				'readonly'=>"readonly"));
    		$_last_name->setAttribs(array(
    				'readonly'=>"readonly"));
    		$_sex->setAttribs(array(
    				'readonly'=>"readonly"));
    		$_parent_name->setAttribs(array(
    				'readonly'=>"readonly"));
    		$_parent_tel->setAttribs(array(
    				'readonly'=>"readonly"));
    	}
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$kh_name->setValue($data["kh_name"]);
    		$_first_name->setValue($data["first_name"]);
    		$_last_name->setValue($data["en_name"]);
    		$_sex->setValue($data["sex"]);
    
    		if (!empty($data["dob"])){
    			$dob->setValue(date("Y-m-d",strtotime($data["dob"])));
    		}
    		$_pob->setValue($data["pob"]);
    		$_phone->setValue($data["phone"]);
    		$_email->setValue($data["email"]);
    		$_student_status->setValue($data["student_status"]);
    		$_if_employed_where->setValue($data["if_employed_where"]);
    		$_position->setValue($data["position"]);
    		$_parent_name->setValue($data["parent_name"]);
    		$_parent_tel->setValue($data["parent_tel"]);
    		$_from_school->setValue($data["old_school"]);
    		$_old_grade->setValue($data["old_grade"]);
    		$address->setValue($data["address"]);
    		$_stu_code->setValue($data["stu_code"]);
    		if (!empty($data["serial"])){
    			$_serial->setValue($data["serial"]);
    		}
    		$_degree_result->setValue($data["degree_result"]);
    		if (!empty($data["test_date"])){
    			$test_date->setValue(date("Y-m-d",strtotime($data["test_date"])));
    		}
    		$_nationality->setValue($data["nationality"]);
    		$_nation->setValue($data["nation"]);
    		$note->setValue($data["note"]);
    		$_emergency_name->setValue($data["emergency_name"]);
    		$_relationship_to_student->setValue($data["relationship_to_student"]);
    		$_emergency_tel->setValue($data["emergency_tel"]);
    		$_emergency_address->setValue($data["emergency_address"]);
    		$_degree->setValue($data["degree"]);
    		$_session->setValue($data["session_result"]);
    		$id->setValue($data["id"]);
    	}
    	 
    	$this->addElements(array(
    			$_branch_id,
    			$kh_name,
    			$_first_name,
    			$_last_name,
    			$_sex,
    			$_nationality,
    			$_nation,
    			$dob,
    			$_pob,
    			$_phone,
    			$_email,
    			$_student_status,
    			$_if_employed_where,
    			$_position,
    			$_parent_name,
    			$_parent_tel,
    			$_from_school,
    			$_old_grade,
    			$address,
    			$_stu_code,
    			$_serial,
    			$_degree_result,
    			$test_date,
    			$note,
    			$_emergency_name,
    			$_relationship_to_student,
    			$_emergency_tel,
    			$_emergency_address,
    			$_degree,
    			$_session,
    			$id,
    			$advance_search,
    			$_nation_search,
    			$_status_search,
    			$start_date,
    			$end_date,
    			
    	));
    	return $this;
    }
    function FrmEnterResultTest($data=null,$detailscore=null,$type=null){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userinfo = $_dbgb->getUserInfo();
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getStudntTestByBranch();',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_arr_opt_stu = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$rows = $_dbgb->getAllstudentTestForFrmRestult();
    	if(!empty($rows))foreach($rows AS $row) $_arr_opt_stu[$row['id']]=$row['name'];
    	$_stu_test_id = new Zend_Dojo_Form_Element_FilteringSelect("stu_test_id");
    	$_stu_test_id->setMultiOptions($_arr_opt_stu);
    	$_stu_test_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$_score = new Zend_Dojo_Form_Element_NumberTextBox('score');
    	$_score->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("SCORE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Score")
    	));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1,null,$type);//get degree type=1 and schooloption =1,2,3
    	if(!empty($Option))foreach($Option AS $row){$_arr_opt[$row['id']]=$row['name'];}
    	$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
    	$_degree->setMultiOptions($_arr_opt);
    	$_degree->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGrade();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$_date = date("Y-m-d");
    	
    	$test_date= new Zend_Dojo_Form_Element_DateTextBox('test_date');
    	$test_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$test_date->setValue($_date);
    	
    	$result_date= new Zend_Dojo_Form_Element_DateTextBox('result_date');
    	$date = date("Y-m-d");
    	$result_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'required'=>false));
    	$result_date->setValue($_date);
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1,null,$type);//get degree type=1 and schooloption =1,2,3
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree_result = new Zend_Dojo_Form_Element_FilteringSelect("degree_result");
    	$_degree_result->setMultiOptions($_arr_opt);
    	$_degree_result->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGradeResult();',
    			
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_arr = array(""=>$this->tr->translate("PLEASE_SELECT"),1=>$this->tr->translate("GOOD"),2=>$this->tr->translate("GOOD_FAIR"),3=>$this->tr->translate("FAIR"),4=>$this->tr->translate("WEAK"));
    	$_comment = new Zend_Dojo_Form_Element_FilteringSelect("comment");
    	$_comment->setMultiOptions($_arr);
    	$_comment->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	if (!empty($data)){
    		$_branch_id->setValue($data['branch_id']);
    		$_stu_test_id->setValue($data['stu_id']);
    		$_degree->setValue($data['crm_degree']);
    		$_branch_id->setAttribs(array(
    				'readonly'=>'readonly',
    		));
    		
    		$_arr_opt_stu=array();
    		$rows = $_dbgb->getAllstudentTestForFrmRestult($data['branch_id']);
    		if(!empty($rows))foreach($rows AS $row) $_arr_opt_stu[$row['id']]=$row['name'];
    		$_stu_test_id->setMultiOptions($_arr_opt_stu);
    		$_stu_test_id->setAttribs(array(
    				'readonly'=>'readonly',
    				));
    	}
    	
    	$_arr_opt_term = array(""=>$this->tr->translate("SELECT_TERM"));
//     	$optionBranch = $_dbgb->getAllBranch();
//     	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_term[$row['id']]=$row['name'];
    	$term = new Zend_Dojo_Form_Element_FilteringSelect("term_test");
    	$term->setMultiOptions($_arr_opt_term);
    	$term->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	$term->setValue($request->getParam("term_test"));
//     	if (count($optionBranch)==1){
//     		$term->setAttribs(array('readonly'=>'readonly'));
//     		if(!empty($optionBranch))foreach($optionBranch AS $row){
//     			$term->setValue($row['id']);
//     		}
//     	}
    	
    	if (!empty($detailscore)){
    		$_score->setValue($detailscore['score']);
    		$_degree->setValue($detailscore['degree']);
    		$_session->setValue($detailscore['session']);
    		if (!empty($detailscore['test_date'])){
    			$test_date->setValue(date("Y-m-d",strtotime($detailscore['test_date'])));
    		}
    		
    		
    		$term->setValue($detailscore['study_term']);
    		$term->setValue($detailscore['academic_year']);
    		
    		if (!empty($detailscore['result_date'])){
    			$result_date->setValue(date("Y-m-d",strtotime($detailscore['result_date'])));
    		}
    		$_degree_result->setValue($detailscore['degree_result']);
    		$note->setValue($detailscore['note']);
    		$id->setValue($detailscore['id']);
    		$_score->setAttribs(array(
    				'required'=>'true',
    		));
    		$_comment->setValue($detailscore['comment']);
    		
    	}
    	$this->addElements(array(
    			$_branch_id,
    			$term,
    			$_stu_test_id,
    			$_score,
    			$_degree,
    			$_session,
    			$test_date,
    			$result_date,
    			$_degree_result,
    			$_comment,
    			$note,
    			$id
    	));
    	return $this;
    }
}