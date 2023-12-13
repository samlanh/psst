<?php 
Class Application_Form_FrmSearchGlobal extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $date;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->date = 'dijit.form.DateTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmSearch($_data=null){
	
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$adv_search->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$adv_search->setValue($request->getParam("adv_search"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'onchange'=>'getallGrade();'
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree = $_dbgb->getAllItems(1);//degree
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
		
		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("ACADEMIC_YEAR"),
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_academic->setValue($request->getParam("academic_year"));
		$rows =  $_dbgb->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		
		$_room = new Zend_Dojo_Form_Element_FilteringSelect('room');
		$_room->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$_room->setValue($request->getParam("room"));
		$result = $_dbgb->getAllRoom();
		$opt_room = array(''=>$this->tr->translate("ROOM_NAME"));
		if(!empty($result))foreach ($result As $rs)$opt_room[$rs['id']]=$rs['name'];
		$_room->setMultiOptions($opt_room);
		
		$_session = new Zend_Dojo_Form_Element_FilteringSelect('session');
		$_session->setAttribs(array(
				'dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SESSION"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$_session->setValue($request->getParam("session"));
		$opt_sesion=$_dbgb->getViewById(4);
		$opt_session = array(''=>$this->tr->translate("SELECT_SESSION"));
		if(!empty($opt_sesion))foreach ($opt_sesion As $rs)$opt_session[$rs['key_code']]=$rs['view_name'];
		$_session->setMultiOptions($opt_session);
		
		$_teacher = new Zend_Dojo_Form_Element_FilteringSelect('teacher');
		$_teacher->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("SELECT_TEACHER"),
				'required'=>'false'
		));
		$_teacher->setValue($request->getParam("teacher"));
		$result = $_dbgb->getAllTeahcerName();
		$opt_group = array(''=>$this->tr->translate("SELECT_TEACHER"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_teacher->setMultiOptions($opt_group);
		
		$is_pass = new Zend_Dojo_Form_Element_FilteringSelect('is_pass');
		$is_pass->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'required'=>'false'
		));
		$opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$rs = $_dbgb->getViewById(9);
		if(!empty($rs))foreach($rs AS $row) $opt[$row['id']]=$row['name'];
		$is_pass->setMultiOptions($opt);
		$is_pass->setValue($request->getParam("is_pass"));
		
		$_startdate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_startdate->setAttribs(array('dojoType'=>$this->date,
				'class'=>'fullside',
				'required'=>'false',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("START_DATE"),
		));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
		}
		$_startdate->setValue($_date);
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>$this->date,
				'required'=>'false',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("END_DATE"),
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_enddate->setValue($_date);
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status_search->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status"));
		
		$_stu_type=  new Zend_Dojo_Form_Element_FilteringSelect('stu_type');
		$_stu_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_stu_opt = array(
				-1=>$this->tr->translate("ALL_STUDENT"),
				0=>$this->tr->translate("OLD_STUDENTS"),
				1=>$this->tr->translate("NEW_STUDENT"));
		$_stu_type->setMultiOptions($_stu_opt);
		$_stu_type->setValue($request->getParam("stu_type"));
		
		
		$_study_type=  new Zend_Dojo_Form_Element_FilteringSelect('study_type');
		$_study_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$optRs=$_dbgb->getViewById(5);
		$opt_study_type = array(''=>$this->tr->translate("STUDENT_TYPE"));
		if(!empty($optRs))foreach ($optRs As $rs)$opt_study_type[$rs['key_code']]=$rs['view_name'];
		$_study_type->setMultiOptions($opt_study_type);
		$_study_type->setValue($request->getParam("study_type"));
		
		$study_status = new Zend_Dojo_Form_Element_FilteringSelect('study_status');
		$study_status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("STUDENT_TYPE"),
		));
		$study_option = $_dbgb->getViewById(9,1);
		$study_option[-1]=$this->tr->translate("PLEASE_SELECT_STATUS");
		$study_status->setMultiOptions($study_option);
		$study_status->setValue($request->getParam("study_status"));
		
		
		$changegroup_id = new Zend_Dojo_Form_Element_FilteringSelect('changegroup_id');
		$changegroup_id->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_GROUP"),
		));
		$optRsChange = $_dbgb->getAllChangeGroup(1); // 1=ប្តូរក្រុម
		$changegrou_opt = array(''=>$this->tr->translate("SELECT_GROUP"));
		if(!empty($optRsChange))foreach ($optRsChange As $rs)$changegrou_opt[$rs['id']]=$rs['name'];
		$changegroup_id->setMultiOptions($changegrou_opt);
		$changegroup_id->setValue($request->getParam("changegroup_id"));
		
		
		$change_type = new Zend_Dojo_Form_Element_FilteringSelect('change_type');
		$change_type->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("CHANGE_TYPE"),
		));
		$optRs=$_dbgb->getViewById(17);
		$opt_change_type = array(''=>$this->tr->translate("CHANGE_TYPE"));
		if(!empty($optRs))foreach ($optRs As $rs)$opt_change_type[$rs['key_code']]=$rs['view_name'];
		$change_type->setMultiOptions($opt_change_type);
		$change_type->setValue($request->getParam("change_type"));
		
		/* START
		 * 
		 * For search score 
		 * */
		$_arr = array(0=>$this->tr->translate("SELECT_TYPE"),1=>$this->tr->translate("MONTHLY"),2=>$this->tr->translate("SEMESTER"));
		$_exam_type = new Zend_Dojo_Form_Element_FilteringSelect("exam_type");
		$_exam_type->setMultiOptions($_arr);
		$_exam_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_TYPE"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_exam_type->setValue($request->getParam("exam_type"));
		
		$_arr = array(0=>$this->tr->translate("SELECT_SEMESTER"),1=>$this->tr->translate("SEMESTER1"),2=>$this->tr->translate("SEMESTER2"));
		$_for_semester = new Zend_Dojo_Form_Element_FilteringSelect("for_semester");
		$_for_semester->setMultiOptions($_arr);
		$_for_semester->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_SEMESTER"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_for_semester->setValue($request->getParam("for_semester"));
		
		$_opt_month = array(0=>$this->tr->translate("CHOOSE_MONTH"));
		$_allMonth = $_dbgb->getAllMonth();
		if(!empty($_allMonth))foreach($_allMonth AS $row) $_opt_month[$row['id']]=$row['name'];
		$_for_month = new Zend_Dojo_Form_Element_FilteringSelect("for_month");
		$_for_month->setMultiOptions($_opt_month);
		$_for_month->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'autoComplete'=>'false',
				'placeholder'=>$this->tr->translate("CHOOSE_MONTH"),
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_for_month->setValue($request->getParam("for_month"));
		
		/* END
		 *
		* For search score
		* */
		
		/* START
		 *
		* For Issue Certificate/Letter Praise
		* */
		$_language_type=  new Zend_Dojo_Form_Element_FilteringSelect('language_type');
		$_language_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
		));
		$_language_opt = array(
				0=>$this->tr->translate("PLEASE_SELECT"),
				1=>$this->tr->translate("KHMER"),
				2=>$this->tr->translate("ENGLISH"));
		$_language_type->setMultiOptions($_language_opt);
		$_language_type->setValue($request->getParam("language_type"));
		/* END
		 *
		* For Issue Certificate/Letter Praise
		* */
		
		$_day= new Zend_Dojo_Form_Element_FilteringSelect('day');
		$_day->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$result = $_dbgb->getAllDayName();
		$opt_group = array(''=>$this->tr->translate("SELECT_DAY"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_day->setMultiOptions($opt_group);
		$_day->setValue($request->getParam("day"));
		
		
		$type_study = new Zend_Dojo_Form_Element_FilteringSelect('type_study');
		$type_study->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>"false"
		));
		$typestudy_opt = $_dbgb->getAllTermStudyTitle(1);
		$type_study->setMultiOptions($typestudy_opt);
		$type_study->setValue($request->getParam("type_study"));
		
		$generation = new Zend_Dojo_Form_Element_FilteringSelect('generation');
		$generation->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$generation->setValue($request->getParam("generation"));
		$generoption=$_dbgb->getAllGeneration(1,1);
		$generation->setMultiOptions($generoption);
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"));
		$Option = $_dbgb->getAllSchoolOption();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$school_option = new Zend_Dojo_Form_Element_FilteringSelect("school_option");
		$school_option->setMultiOptions($_arr_opt);
		$school_option->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$school_option->setValue($request->getParam("school_option"));
		
		$finished_status = new Zend_Dojo_Form_Element_FilteringSelect('finished_status');
		$finished_status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$finish_opt = new Accounting_Model_DbTable_DbTuitionFee();
		$fin_row=$finish_opt->getProcessTypeView();
		$opt = array('-1'=>$this->tr->translate("PROCESS_TYPE"));
		if(!empty($fin_row))foreach($fin_row AS $row) $opt[$row['id']]=$row['name'];
		$finished_status->setMultiOptions($opt);
		$finished_status->setValue($request->getParam("finished_status"));
		
		$_arr_opt_user = array(""=>$this->tr->translate("PLEASE_SELECT_USER"),);
		$userinfo = $_dbgb->getUserInfo();
		$optionUser = $_dbgb->getAllUserGlobal();
		if(!empty($optionUser))foreach($optionUser AS $row) $_arr_opt_user[$row['id']]=$row['name'];
		$_user_id = new Zend_Dojo_Form_Element_FilteringSelect("user_id");
		$_user_id->setMultiOptions($_arr_opt_user);
		$_user_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_USER"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		if ($userinfo['level']!=1){
			$_user_id->setAttribs(array(
					'readonly'=>true,
			));
			$_user_id->setValue($userinfo['user_id']);
		}
		$_user_id->setValue($request->getParam("user_id"));
		
		
		/* START
		 *
		* For Student Test
		* */
		$_arr = array(
			""=>$this->tr->translate("TYPE_TEST"),
			1=>$this->tr->translate("KHMER_KNOWLEDGE"),
			2=>$this->tr->translate("ENGLISH"),
		//	3=>$this->tr->translate("UNIVERSITY")
		);
		$_type_exam = new Zend_Dojo_Form_Element_FilteringSelect("type_exam");
		$_type_exam->setMultiOptions($_arr);
		$_type_exam->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("TYPE_TEST"),
				'required'=>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_type_exam->setValue($request->getParam("type_exam"));
		
		if($userinfo['level']!=1){
			$_type_exam->setAttribs(array('readonly'=>'readonly'));
			if(!empty($userinfo['schoolOption'])){
				$_type_exam->setValue($userinfo['schoolOption']);
			}
		}
		
		$_arr = array(""=>$this->tr->translate("OCCUPATION"),1=>
				$this->tr->translate("STUDENT"),
				2=>$this->tr->translate("STAFF"),
				3=>$this->tr->translate("OWN_BUSSINESS"));
		$_student_option_search = new Zend_Dojo_Form_Element_FilteringSelect("student_option_search");
		$_student_option_search->setMultiOptions($_arr);
		$_student_option_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("OCCUPATION"),
				'required'=>'false',
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
				'required'=>'false',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SELECT_PROVINCE"),
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$_province_search->setValue($request->getParam("province_search"));
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_TYPE"),
		));
		
		$db = new Foundation_Model_DbTable_DbStudentDrop();
		$rows= $db->getAllDropType();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_TYPE")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)
		{
			$opt[$row['id']]=$row['name'];
		}
		$_type->setMultiOptions($opt);
		$_type->setValue($request->getParam("type"));

		$_result_status=  new Zend_Dojo_Form_Element_FilteringSelect('result_status');
		$_result_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("RESULT_TEST_TYPE"),
		));
		$_result_status_opt = array(
				''=>$this->tr->translate("RESULT_TEST_TYPE"),
				1=>$this->tr->translate("GET_RESULT"),
				0=>$this->tr->translate("NO_RESULT"));
		$_result_status->setMultiOptions($_result_status_opt);
		$_result_status->setValue($request->getParam("result_status"));
		
		$_register_status=  new Zend_Dojo_Form_Element_FilteringSelect('register_status');
		$_register_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("ENROLL_STATUS"),
		));
		$_register_status_opt = array(
				''=>$this->tr->translate("ENROLL_STATUS"),
				1=>$this->tr->translate("ENROLLED"),
				0=>$this->tr->translate("NOT_YET_ENROLL")
			);
		$_register_status->setMultiOptions($_register_status_opt);
		$_register_status->setValue($request->getParam("register_status"));

		$_student_group_status=  new Zend_Dojo_Form_Element_FilteringSelect('student_group_status');
		$_student_group_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_group_status_opt = array(
				-1=>$this->tr->translate("ALL_STUDENT"),
				1=>$this->tr->translate("IS_GROUP"),
				0=>$this->tr->translate("NOT_YET_GROUP")
			);
		$_student_group_status->setMultiOptions($_group_status_opt);
		$_student_group_status->setValue($request->getParam("student_group_status"));

		$_cut_stock_type = new Zend_Dojo_Form_Element_FilteringSelect('cut_stock_type');
		$_cut_stock_type->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("TYPE"),
		));
		$_stock_opt = array(
				''=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("USAGE_STOCK"),
				2=>$this->tr->translate("DEBT_STOCK"));
		$_cut_stock_type->setMultiOptions($_stock_opt);
		$_cut_stock_type->setValue($request->getParam("cut_stock_type"));

		
		$_sort_degree = new Zend_Dojo_Form_Element_FilteringSelect('sort_degree');
		$_sort_degree->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("DEGREE"),
		));
		$_sort_dg_opt = array(

				''=>$this->tr->translate("ALL"),
				'2,3'=>$this->tr->translate("Junior-Senior"),
				
			);
		$_sort_degree->setMultiOptions($_sort_dg_opt);
		$_sort_degree->setValue($request->getParam("sort_degree"));

		$_arr_opt_exam = array(""=>$this->tr->translate("PLEASE_SELECT_CRITERIAL"));
    	$optionExametype = $_dbgb->getExamTypeEngItems();
    	if(!empty($optionExametype))foreach($optionExametype AS $row) $_arr_opt_exam[$row['id']]=$row['name'];
    	$_criteriaId = new Zend_Dojo_Form_Element_FilteringSelect("criteriaId");
    	$_criteriaId->setMultiOptions($_arr_opt_exam);
    	$_criteriaId->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'onChange'=>'addRow()',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));

		$_stuOrderBy=  new Zend_Dojo_Form_Element_FilteringSelect('stuOrderBy');
		$_stuOrderBy->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("ORDER_BY"),
		));
		$_order_opt = array(
				0=>$this->tr->translate("DEFAULT"),
				1=>$this->tr->translate("BY_STU_CODE_ASC"),
				2=>$this->tr->translate("BY_STU_KHNAME_ASC"),
				3=>$this->tr->translate("BY_STU_ENNAME_ASC")
			);
		$_stuOrderBy->setMultiOptions($_order_opt);
		$_stuOrderBy->setValue($request->getParam("stuOrderBy"));
		
		
		$this->addElements(array(
				$_type,
				$adv_search,
				$_branch_id,
				$_degree,
				$_academic,
				$_session,
				$_teacher,
				$is_pass,
				$_startdate,
				$_enddate,
				$_status_search,
				$_user_id,
				$_room,
				$_stu_type,
				$_study_type,
				$study_status,
				$changegroup_id,
				$change_type,
				$_exam_type,
				$_for_semester,
				$_for_month,
				$_language_type,
				$_day,
				$type_study,
				$generation,
				$school_option,
				$finished_status,
				$_type_exam,
				$_student_option_search,
				$_province_search,
				$_result_status,
				$_register_status,
				$_student_group_status,
				$_cut_stock_type,
				$_sort_degree,
				$_criteriaId,
				$_stuOrderBy
				)
			);
		return $this;
	}
	
}