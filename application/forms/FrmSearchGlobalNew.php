<?php 
Class Application_Form_FrmSearchGlobalNew extends Zend_Dojo_Form {
	
	protected $tr;
	protected $db;
	protected $request;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->request=Zend_Controller_Front::getInstance()->getRequest();
		$this->db = new Application_Model_DbTable_DbGlobal();
	}

	public function controlTextSearch($_data=null){//used
		
		$adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$adv_search->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$adv_search->setValue($this->request->getParam("adv_search"));

		return $adv_search;
	}
	public function getBranchSearch($_data=null){//used

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $this->db->getAllBranch();
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
			
		$_branch_id->setValue($this->request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}

		return $_branch_id;
		
	}
	public function getDegreeSearch($_data=null){//used

		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'onchange'=>'getallGrade();'
		));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree = $this->db->getAllItems(1);//degree

		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);

		$_degree->setValue($this->request->getParam('degree'));

		return $_degree;
	}

	public function getacademicYearEnrollSearch($_data=null){//used

		$academicYearEnroll = new Zend_Dojo_Form_Element_FilteringSelect('academicYearEnroll');
		$academicYearEnroll->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("SELECT_ENROLL_YEAR"),
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$academicYearEnroll->setValue($this->request->getParam("academicYearEnroll"));
		$rows =  $this->db->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_ENROLL_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$academicYearEnroll->setMultiOptions($opt);

		return $academicYearEnroll;
	}
		
	public function getAcademicYearSearch($_data=null){//used

		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("ACADEMIC_YEAR"),
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$rows =  $this->db->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		$currentAcademic = $this->request->getParam("academic_year");
		if(empty($currentAcademic)){
			$result = $this->db->getLatestAcadmicYear();
			$currentAcademic = $result['id'];
		}

		$_academic->setValue($currentAcademic);

		return $_academic;
	}
	function getStatusSearch($_data=null){
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($this->request->getParam("status"));

		return $_status_search;
	}
	
	function getStartDateSearch($_data=null,$stringHolder=''){//used
		$_startdate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_startdate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>'false',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>($stringHolder=='')?$this->tr->translate("START_DATE"):$this->tr->translate($stringHolder),
		));
		$_date = $this->request->getParam("start_date");
		$_startdate->setValue($_date);

		return $_startdate;
	}
	function getPaymentDateSearch($_data=null){//used
		$_paymentdate = new Zend_Dojo_Form_Element_DateTextBox('payment_date');
		$_paymentdate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>'false',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("PAYMENT_DATE"),
		));
		$_date = $this->request->getParam("payment_date");
		$_paymentdate->setValue($_date);

		return $_paymentdate;
	}

	function getEndDateSearch($_data=null){//used
	
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>'false',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("END_DATE"),
		));
		$_date = $this->request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_enddate->setValue($_date);

		return $_enddate;
	}
		function getPaymentTermSearch($_data=null){
			$pay_term = new Zend_Dojo_Form_Element_FilteringSelect('pay_term');
			$pay_term->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
						'placeholder'=>$this->tr->translate("PAYMENT_TERM"),
						'class'=>'fullside',
						'autoComplete'=>"false",
						'queryExpr'=>'*${0}*',
						'required'=>false
				));
				
				$opt_term = array(
						''=>$this->tr->translate("PAYMENT_TERM"),
						1=>$this->tr->translate('MONTHLY'),
						2=>$this->tr->translate('TERM'),
						3=>$this->tr->translate('SEMESTER'),
						4=>$this->tr->translate('YEAR'),
						5=>$this->tr->translate('OTHER'),
				);
				$pay_term->setMultiOptions($opt_term);
				$pay_term->setValue($this->request->getParam("pay_term"));
			return $pay_term;
		}
		function getTermListSearch($_data=null){
				$termlist = new Zend_Dojo_Form_Element_FilteringSelect('termList');
				$termlist->setAttribs(array(
						'dojoType'=>'dijit.form.FilteringSelect',
						'placeholder'=>$this->tr->translate("Select Term"),
						'class'=>'fullside',
						'autoComplete'=>"false",
						'queryExpr'=>'*${0}*',
						'required'=>false
				));
				
				$opt_term = array(
						-1=>$this->tr->translate("Select Term"),
						1=>$this->tr->translate('Term1'),
						2=>$this->tr->translate('Term2'),
						3=>$this->tr->translate('Term3'),
						4=>$this->tr->translate('Term4'),
				);
				$termlist->setMultiOptions($opt_term);
				$termlist->setValue($this->request->getParam("termList"));
			return $termlist;
		}
	function getSessionSearch($_data=null){

		$_session = new Zend_Dojo_Form_Element_FilteringSelect('session');
		$_session->setAttribs(
			array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("SESSION"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		
		$opt_sesion=$this->db->getViewById(4);
		$opt_session = array(''=>$this->tr->translate("SELECT_SESSION"));
		if(!empty($opt_sesion))foreach ($opt_sesion As $rs)$opt_session[$rs['key_code']]=$rs['view_name'];
		$_session->setMultiOptions($opt_session);

		$_session->setValue($this->request->getParam("session"));

		return $_session;
	}
	
	function getStudentTypeSearch($_data=null){
		
		$_stu_type=  new Zend_Dojo_Form_Element_FilteringSelect('stu_type');
		$_stu_type->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_stu_opt = array(
				-1=>$this->tr->translate("ALL_STUDENT"),
				0=>$this->tr->translate("OLD_STUDENTS"),
				1=>$this->tr->translate("NEW_STUDENT"));
		$_stu_type->setMultiOptions($_stu_opt);
		$_stu_type->setValue($this->request->getParam("stu_type"));

		return $_stu_type;
	}
	
	function getStudyTypeSearch($_data=null){

		$_study_type=  new Zend_Dojo_Form_Element_FilteringSelect('study_type');
		$_study_type->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$optRs=$this->db->getViewById(5);
		$opt_study_type = array(''=>$this->tr->translate("STUDEN_STATUS"));
		if(!empty($optRs))foreach ($optRs As $rs)$opt_study_type[$rs['key_code']]=$rs['view_name'];
		$_study_type->setMultiOptions($opt_study_type);
		$_study_type->setValue($this->request->getParam("study_type"));
		return $_study_type;
	}

	function getPaymentStatusSearch($_data=null){

		$paymentstatus=  new Zend_Dojo_Form_Element_FilteringSelect('paymentstatus');
		$paymentstatus->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$optRs=$this->db->getViewById(42);
		$opt_study_type =array();
		if(!empty($optRs))foreach ($optRs As $rs)$opt_study_type[$rs['key_code']]=$rs['view_name'];
		$paymentstatus->setMultiOptions($opt_study_type);
		$paymentstatus->setValue($this->request->getParam("paymentstatus"));
		return $paymentstatus;
	}
	function getStudyStatusSearch($_data=null){
		$study_status = new Zend_Dojo_Form_Element_FilteringSelect('study_status');
		$study_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("STUDENT_TYPE"),
		));
		$study_option = $this->db->getViewById(9,1);
		$study_option[-1]=$this->tr->translate("PLEASE_SELECT_STATUS");
		$study_status->setMultiOptions($study_option);
		$study_status->setValue($this->request->getParam("study_status"));

		return $study_status;
	}

	//for score search
	function getExamTypeSearch($_data=null){
		
		$_exam_type = new Zend_Dojo_Form_Element_FilteringSelect("exam_type");
		$_arr = array(
			0=>$this->tr->translate("SELECT_TYPE"),
			1=>$this->tr->translate("MONTHLY"),
			2=>$this->tr->translate("SEMESTER"),
			3=>$this->tr->translate("YEARLY_RESULT"),
		);
		$_exam_type->setMultiOptions($_arr);
		$_exam_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_TYPE"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_exam_type->setValue($this->request->getParam("exam_type"));

		return $_exam_type;
	}
	function getForSemesterSearch($_data=null){
		
		$_for_semester = new Zend_Dojo_Form_Element_FilteringSelect("for_semester");
		$_arr = array(
			0=>$this->tr->translate("SELECT_SEMESTER"),
			1=>$this->tr->translate("SEMESTER1"),
			2=>$this->tr->translate("SEMESTER2"));
		$_for_semester->setMultiOptions($_arr);
		$_for_semester->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_SEMESTER"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_for_semester->setValue($this->request->getParam("for_semester"));

		return $_for_semester;
	}

	function getForMonthSearch($_data=null){

		$_for_month = new Zend_Dojo_Form_Element_FilteringSelect("for_month");
		$_opt_month = array(0=>$this->tr->translate("CHOOSE_MONTH"));
		$_allMonth = $this->db->getAllMonth();
		if(!empty($_allMonth))foreach($_allMonth AS $row) $_opt_month[$row['id']]=$row['name'];
		$_for_month->setMultiOptions($_opt_month);
		$_for_month->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'autoComplete'=>'false',
				'placeholder'=>$this->tr->translate("CHOOSE_MONTH"),
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_for_month->setValue($this->request->getParam("for_month"));

		return $_for_month;
	}

	//for other
	function getGetLanguageSearch($_data=null){
		
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
		$_language_type->setValue($this->request->getParam("language_type"));

		return $_language_type;
	}
	function getDaysSearch($_data=null){
		
		$_day= new Zend_Dojo_Form_Element_FilteringSelect('day');
		$_day->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$result = $this->db->getAllDayName();
		$opt_group = array(''=>$this->tr->translate("SELECT_DAY"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_day->setMultiOptions($opt_group);
		$_day->setValue($this->request->getParam("day"));

		return $_day;
	}
	function getGenerationSearch($_data=null){
		
		$generation = new Zend_Dojo_Form_Element_FilteringSelect('generation');
		$generation->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false'
		));
		$generation->setValue($this->request->getParam("generation"));
		$generoption=$this->db->getAllGeneration(1,1);
		$generation->setMultiOptions($generoption);

		return $generation;
	}
	function getSchoolOptionSearch($_data=null){
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"));
		$Option = $this->db->getAllSchoolOption();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$school_option = new Zend_Dojo_Form_Element_FilteringSelect("school_option");
		$school_option->setMultiOptions($_arr_opt);
		$school_option->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$school_option->setValue($this->request->getParam("school_option"));

		return $school_option;
	}
	function getFinishStatusSearch($_data=null){
			
		$finished_status = new Zend_Dojo_Form_Element_FilteringSelect('finished_status');
		$finished_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
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
		$finished_status->setValue($this->request->getParam("finished_status"));

		return $finished_status;
	}
	function getUserListSearch($_data=null){
		
		$_arr_opt_user = array(""=>$this->tr->translate("PLEASE_SELECT_USER"),);
		$userinfo = $this->db->getUserInfo();
		$optionUser = $this->db->getAllUserGlobal();
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
		$_user_id->setValue($this->request->getParam("user_id"));
		
		return $_user_id;
	}
	function getTypeExamSearch($_data=null){

		$_type_exam = new Zend_Dojo_Form_Element_FilteringSelect("type_exam");
		$_arr = array(
			""=>$this->tr->translate("TYPE_TEST"),
			1=>$this->tr->translate("KHMER_KNOWLEDGE"),
			2=>$this->tr->translate("ENGLISH"),
		//	3=>$this->tr->translate("UNIVERSITY")
		);
		$_type_exam->setMultiOptions($_arr);
		$_type_exam->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("TYPE_TEST"),
				'required'=>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_type_exam->setValue($this->request->getParam("type_exam"));

		$userinfo = $this->db->getUserInfo();
		if($userinfo['level']!=1){
			$_type_exam->setAttribs(array('readonly'=>'readonly'));
			if(!empty($userinfo['schoolOption'])){
				$_type_exam->setValue($userinfo['schoolOption']);
			}
		}

		return $_type_exam;
	}
	function getStudentOptionSearch($_data=null){

		$_student_option_search = new Zend_Dojo_Form_Element_FilteringSelect("student_option_search");
		$_arr = array(
		""=>$this->tr->translate("OCCUPATION"),1=>
		$this->tr->translate("STUDENT"),
		2=>$this->tr->translate("STAFF"),
		3=>$this->tr->translate("OWN_BUSSINESS"));
		$_student_option_search->setMultiOptions($_arr);
		$_student_option_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("OCCUPATION"),
				'required'=>'false',
				'class'=>'fullside height-text',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_student_option_search->setValue($this->request->getParam("student_option_search"));

		return $_student_option_search;
	}
	function getProviceSearch($_data=null){
		
		$_province_search = new Zend_Dojo_Form_Element_FilteringSelect("province_search");
		$rs_province = $this->db->getProvince();
		$opt = array(""=>$this->tr->translate("SELECT_PROVINCE"));
		if(!empty($rs_province))foreach($rs_province AS $row) $opt[$row['province_id']]=$row['province_en_name'];
		
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
		$_province_search->setValue($this->request->getParam("province_search"));
		return $_province_search;
	}
	function getStudentDropTypeSearch($_data=null){
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_type->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
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
		$_type->setValue($this->request->getParam("type"));

		return $_type;
	}
	function getResultTextStatusSearch($_data=null){
		
		$_result_status=  new Zend_Dojo_Form_Element_FilteringSelect('result_status');
		$_result_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("RESULT_TEST_TYPE"),
		));
		$_result_status_opt = array(
				''=>$this->tr->translate("RESULT_TEST_TYPE"),
				1=>$this->tr->translate("GET_RESULT"),
				0=>$this->tr->translate("NO_RESULT"));
		$_result_status->setMultiOptions($_result_status_opt);
		$_result_status->setValue($this->request->getParam("result_status"));

		return $_result_status;
	}
	function getRegisterStatusSearch($_data=null){
		
		$_register_status=  new Zend_Dojo_Form_Element_FilteringSelect('register_status');
		$_register_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
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
		$_register_status->setValue($this->request->getParam("register_status"));

		return $_register_status;
	}
	function getGroupStatusSearch($_data=null){

		$_student_group_status=  new Zend_Dojo_Form_Element_FilteringSelect('student_group_status');
		$_student_group_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
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
		$_student_group_status->setValue($this->request->getParam("student_group_status"));

		return $_student_group_status;
	}
	function getCutStockTypeSearch($_data=null){
		
		$_cut_stock_type = new Zend_Dojo_Form_Element_FilteringSelect('cut_stock_type');
		$_cut_stock_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("TYPE"),
		));
		$_stock_opt = array(
				''=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("USAGE_STOCK"),
				2=>$this->tr->translate("DEBT_STOCK"));
		$_cut_stock_type->setMultiOptions($_stock_opt);
		$_cut_stock_type->setValue($this->request->getParam("cut_stock_type"));

		return $_cut_stock_type;
	}
	function getShortDegreeSearch($_data=null){
		
		$_sort_degree = new Zend_Dojo_Form_Element_FilteringSelect('sort_degree');
		$_sort_degree->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("DEGREE"),
		));
		$_sort_dg_opt = array(

				''=>$this->tr->translate("ALL"),
				'2,3'=>$this->tr->translate("Junior-Senior"),
				
			);
		$_sort_degree->setMultiOptions($_sort_dg_opt);
		$_sort_degree->setValue($this->request->getParam("sort_degree"));

		return $_sort_degree;
	}

	function getCriteriaIDSearch($_data=null){
		
		$_arr_opt_exam = array(""=>$this->tr->translate("PLEASE_SELECT_CRITERIAL"));
    	$optionExametype = $this->db->getExamTypeEngItems();
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

		return $_criteriaId;
	}

	function getShortStudentByTypeSearch($_data=null){
		
		$_stuOrderBy=  new Zend_Dojo_Form_Element_FilteringSelect('stuOrderBy');
		$_stuOrderBy->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
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
		$_stuOrderBy->setValue($this->request->getParam("stuOrderBy"));

		return $_stuOrderBy;
	}

	function getScoreResultStatusSearch($_data=null){
		
		$_score_result_status=  new Zend_Dojo_Form_Element_FilteringSelect('score_result_status');
		$_score_result_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_rs_opt = array(
				0=>$this->tr->translate("Unpublished"),
				1=>$this->tr->translate("Published"),
			);
		$_score_result_status->setMultiOptions($_rs_opt);
		$_score_result_status->setValue($this->request->getParam("score_result_status"));

		return $_score_result_status;
	}
	function getDepartSearch($_data=null){
		
		$dbTeacher = new Global_Model_DbTable_DbTeacher();
		$rowDept = $dbTeacher->getAllDepartment();
		$optDeptpartment = array(''=>$this->tr->translate("PLEASE_SELECT_DEPARTMENT"));
		if(!empty($rowDept))foreach ($rowDept As $rs)$optDeptpartment[$rs['id']]=$rs['name'];
		$_department=  new Zend_Dojo_Form_Element_FilteringSelect('department');
		$_department->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("DEPARTMENT"),
		));
		$_department->setMultiOptions($optDeptpartment);
		$_department->setValue($this->request->getParam("department"));

		return $_department;
	}
	function getResultStatusSearch($_data=null){
		
    	$_resultStatus = new Zend_Dojo_Form_Element_FilteringSelect("resultStatus");
		$_resultStatus->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required'=>'true',
			'class'=>'fullside height-text',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',));
		$_arrResultStatus = array(
			""=>$this->tr->translate("PLEASE_SELECT"),
			1=>$this->tr->translate("Qualified"),
			2=>$this->tr->translate("Unqualified"));
    	$_resultStatus->setMultiOptions($_arrResultStatus);
    	
		$_resultStatus->setValue($this->request->getParam("resultStatus"));

		return $_resultStatus;
	}
	function getMentionGradeSearch($_data=null){
		
		$_mention=  new Zend_Dojo_Form_Element_FilteringSelect('mention');
		$_mention->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("SELCECT_MENTION"),
		));
		$_mention_opt = array(
			''=>$this->tr->translate("SELCECT_MENTION"),
			90=>$this->tr->translate("GRADE_A"),
			80=>$this->tr->translate("GRADE_B"),
			70=>$this->tr->translate("GRADE_C"),
			60=>$this->tr->translate("GRADE_D"),
			50=>$this->tr->translate("GRADE_E"),
			1=>$this->tr->translate("GRADE_F"),
		);
		$_mention->setMultiOptions($_mention_opt);

		$_mention->setValue($this->request->getParam("mention"));

		return $_mention;
	}
	function getStudentTypeStatusTypeSearch($_data=null){
		
		$studentType = new Zend_Dojo_Form_Element_FilteringSelect("studentType");	
		$studentType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt = $this->db->getViewByType(40, 1);
		$opt['']=$this->tr->translate('STUDENT_TYPE');
		$studentType->setMultiOptions($opt);

		return $studentType;
	}
	function getUpdateOptionSearch($_data=null){
		
		$updateOption=  new Zend_Dojo_Form_Element_FilteringSelect('updateOption');
		$updateOption->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$updateopt = array(
				''=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("ADD_TO_DISCOUNT"),
				2=>$this->tr->translate("UPGRADE_DISCOUNT"),
				3=>$this->tr->translate("CHANGE_DISCOUNT"),
			);
		$updateOption->setMultiOptions($updateopt);
		$updateOption->setValue($this->request->getParam("updateOption"));

		return $updateOption;
	}

	function getDiscountStatusSearch($_data=null){
		
		$discountStatus=  new Zend_Dojo_Form_Element_FilteringSelect('discountStatus');
		$discountStatus->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$discountstatusopt = array(
				1=>$this->tr->translate("USING"),
				0=>$this->tr->translate("STOP_USED"));
		$discountStatus->setMultiOptions($discountstatusopt);
		$discountStatus->setValue($this->request->getParam("discountStatus"));

		return $discountStatus;
	}
	function getLimitFilterSearch($_data=null){
		
		$limit=  new Zend_Dojo_Form_Element_FilteringSelect('limit');
		$limit->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$limitopt = array(
				25=>25,
				50=>50,
				100=>100,
				150=>150,
				200=>200,
			);
		$limit->setMultiOptions($limitopt);
		$limit->setValue($this->request->getParam("limit"));

		return $limit;
	}
	function getStudentStatusSearch($_data=null){
		
		$student_status=  new Zend_Dojo_Form_Element_FilteringSelect('student_status');
		$student_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$statusopt = array(
				1=>$this->tr->translate("LEARNING"),
				2=>$this->tr->translate("STOP_STUDY"));
		$student_status->setMultiOptions($statusopt);
		$student_status->setValue($this->request->getParam("student_status"));

		return $student_status;
	}
	function getStudentStudyStatus($_data=null){
		
		$student_study_status = new Zend_Dojo_Form_Element_FilteringSelect('student_study_status');
		$student_study_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$stud_option = $this->db->getViewById(5,1);
		$stud_option[-1]=$this->tr->translate("PLEASE_SELECT_STATUS");
		$student_study_status->setMultiOptions($stud_option);
		$student_study_status->setValue($this->request->getParam("student_status"));

		return $student_study_status;
	}
	
	function getTermOptionSearch($_data=null){
		
		$termOption=  new Zend_Dojo_Form_Element_FilteringSelect('termOption');
		$termOption->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$rowsOpt = $this->db->getAllPaymentTerm($id=null,$hidemonth=1);
		$optTerm = array(0=>$this->tr->translate("PLEASE_SELECT"));
		if(!empty($rowsOpt)) foreach($rowsOpt as $key => $opt){
			$optTerm[$key] =$opt;
		}
		$termOption->setMultiOptions($optTerm);
		$termOption->setValue($this->request->getParam("termOption"));

		return $termOption;
	}

	function getMainGradeTypeSearch($_data=null){
		$_isMainGradeType=  new Zend_Dojo_Form_Element_FilteringSelect('isMainGradeType');
		$_isMainGradeType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("ALL_STATUS"),
			));
			$_isMainGradeOpt = array(
					0=>$this->tr->translate("ALL"),
					1=>$this->tr->translate("MAIN_CLASS"),
					2=>$this->tr->translate("SUB_CLASS")
					);
			$_isMainGradeType->setMultiOptions($_isMainGradeOpt);
			$_isMainGradeType->setValue($this->request->getParam("isMainGradeType"));

		return $_isMainGradeType;
	}
	function getTypeStudySearch($_data=null){
		$type_study = new Zend_Dojo_Form_Element_FilteringSelect('type_study');
		$type_study->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>"false"
		));
		$typestudy_opt = $this->db->getAllTermStudyTitle(1);
		$type_study->setMultiOptions($typestudy_opt);
		$type_study->setValue($this->request->getParam("type_study"));

		return $typestudy_opt;
	}

	function getAskForSearch($_data=null){
		$_arr = array(
			""=>$this->tr->translate("ASK_FOR"),
			1=>$this->tr->translate("KHMER_KNOWLEDGE"),
			2=>$this->tr->translate("ENGLISH_KNOWLEDGE"),
			3=>$this->tr->translate("UNIVERSITY"),
			4=>$this->tr->translate("CHINESE_KNOWLEDGE"),
			5=>$this->tr->translate("OTHER")
		);
		$_ask_for_search = new Zend_Dojo_Form_Element_FilteringSelect("ask_for_search");
		$_ask_for_search->setMultiOptions($_arr);
		$_ask_for_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required'=>'false',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'placeholder'=>$this->tr->translate("ASK_FOR"),
			'class'=>'fullside height-text',));
		$_ask_for_search->setValue($this->request->getParam("ask_for_search"));
		return $_ask_for_search;
	}
	function getKnowBySearch($_data=null){
		$_arr_opt_know = array(""=>$this->tr->translate("KNOW_BY"));
    	$optionKnowBy = $this->db->getAllKnowBy();
    	if(!empty($optionKnowBy))foreach($optionKnowBy AS $row) $_arr_opt_know[$row['id']]=$row['name'];
    	$_know_by_search = new Zend_Dojo_Form_Element_FilteringSelect("know_by_search");
    	$_know_by_search->setMultiOptions($_arr_opt_know);
    	$_know_by_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required'=>'false',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'placeholder'=>$this->tr->translate("KNOW_BY"),
			'class'=>'fullside height-text',));
    	$_know_by_search->setValue($this->request->getParam("know_by_search"));
		return $_know_by_search;
	}

	function getFollowStatusSearch($_data=null){
		$followup = new Zend_Dojo_Form_Element_FilteringSelect("followup_status");
		$followup->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required'=>'false',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'placeholder'=>$this->tr->translate("FOLLOWU_STATUS"),
			'class'=>'fullside height-text',));
		
		$_arr =array(
			-1=>$this->tr->translate("FOLLOWU_STATUS"),
			1=>$this->tr->translate("FOLLOW_UP"),
			0=>$this->tr->translate("STOP_FOLLOW_UP"),
		);
		$followup->setMultiOptions($_arr);
		
		$followup->setValue($this->request->getParam('followup_status'));	
		return $followup;
	}
	function getCrmStatusSearch($_data=null){
		$_arr= $this->db->getcrmFollowupStatus();
		$crm_status = new Zend_Dojo_Form_Element_FilteringSelect("crm_status");
		$crm_status->setMultiOptions($_arr);
		$crm_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'required'=>'false',
			'queryExpr'=>'*${0}*',
			'placeholder'=>$this->tr->translate("STATUS"),
			'class'=>'fullside height-text',));
		$crm_status->setValue($this->request->getParam("crm_status"));
		return $crm_status;
	}
	function getTestTypeSearch($_data=null){
		$_arr_type_exam = array(
			""=>$this->tr->translate("TYPE_TEST"),
			1=>$this->tr->translate("KHMER_KNOWLEDGE"),
			2=>$this->tr->translate("ENGLISH"),
			3=>$this->tr->translate("UNIVERSITY")
		);
		$_type_exam_search = new Zend_Dojo_Form_Element_FilteringSelect("type_exam_search");
		$_type_exam_search->setMultiOptions($_arr_type_exam);
		$_type_exam_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside height-text',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',));
		$_type_exam_search->setValue($this->request->getParam("type_exam_search"));
		return $_type_exam_search;
	}
	function getTeacherSearch($_data=null){
		$_teacher = new Zend_Dojo_Form_Element_FilteringSelect('teacher');
		$_teacher->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("SELECT_TEACHER"),
				'required'=>'false'
		));
		$_teacher->setValue($this->request->getParam("teacher"));
		$result = $this->db->getAllTeahcerName();
		$opt_group = array(''=>$this->tr->translate("SELECT_TEACHER"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_teacher->setMultiOptions($opt_group);
		return $_teacher;
	}
	function getIsPassSearch($_data=null){
		$is_pass = new Zend_Dojo_Form_Element_FilteringSelect('is_pass');
		$is_pass->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("SERVIC"),
			'autoComplete'=>"false",
			'queryExpr'=>'*${0}*',
			'missingMessage'=>'Invalid Module!',
			'required'=>'false'
		));
		$opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$rs = $this->db->getViewById(9);
		if(!empty($rs))foreach($rs AS $row) $opt[$row['id']]=$row['name'];
		$is_pass->setMultiOptions($opt);
		$is_pass->setValue($this->request->getParam("is_pass"));
		return $is_pass;
	}

	function staffTypeSearch($_data=null){
		$_staff=  new Zend_Dojo_Form_Element_FilteringSelect('staff_type');
		$_staff->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("SELECT_TYPE"),
			'required'=>'false',
		));
		$_staff_opt = array(
			0=>$this->tr->translate("SELECT_TYPE"),
			1=>$this->tr->translate("TEACHER"),
			2=>$this->tr->translate("STAFF"));
		$_staff->setMultiOptions($_staff_opt);
		$_staff->setValue($this->request->getParam("staff_type"));
		return $_staff;
	}

	function getNationSearch($_data=null){
		$_arr_opt_nation = array(""=>$this->tr->translate("SELECT_NATION"),);
		$optionNation = $this->db->getViewByType(21);//Nation
		if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
		$_nationality = new Zend_Dojo_Form_Element_FilteringSelect("nationality");
		$_nationality->setMultiOptions($_arr_opt_nation);
		$_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("SELECT_NATION"),
				'required'=>'false',
				)
			);
		$_nationality->setValue($this->request->getParam("nationality"));	
		return $_nationality;
	}

	function getTeacherTypeSearch($_data=null){
		$teacher_type=  new Zend_Dojo_Form_Element_FilteringSelect('teacher_type');
		$teacher_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("PLEASE_SELECT_TEACHER_TYPE"),
			'required'=>'false',
		));
		$_teacher_opt = array(
			-1=>$this->tr->translate("PLEASE_SELECT_TEACHER_TYPE"),
			1=>$this->tr->translate("TEACHER_KHMER"),
			0=>$this->tr->translate("TEACHER_FOREIGNER"));
		$teacher_type->setMultiOptions($_teacher_opt);
		$teacher_type->setValue($this->request->getParam("teacher_type"));
		return $teacher_type;
	}

	function getActiveTypeSearch($_data=null){
		$_active_type=  new Zend_Dojo_Form_Element_FilteringSelect('active_type');
		$_active_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'placeholder'=>$this->tr->translate("PLEASE_SELECT"),
			'class'=>'fullside',
			'required'=>'false',
		));
		$_active_type_opt = array(
			-1=>$this->tr->translate("PLEASE_SELECT"),
			0=>$this->tr->translate("ACTIVING"),
			1=>$this->tr->translate("STOP"));
		$_active_type->setMultiOptions($_active_type_opt);
		$_active_type->setValue($this->request->getParam("active_type"));
		return $_active_type;
	}
	
	function getServiceTypeSearch($_data=null){
		$service_type = new Zend_Dojo_Form_Element_FilteringSelect("service_type");
		$service_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>"false",
			'queryExpr'=>'*${0}*',
			'required'=>false
			)
		);
		
		$_arr =array(
				''=>$this->tr->translate("PLEASE_SELECT"),
				1=>$this->tr->translate("TUITION_FEE"),
				2=>$this->tr->translate("SERVICE"),
				3=>$this->tr->translate("PRODUCT")
		);
		$service_type->setMultiOptions($_arr);
		
		$service_type->setValue($this->request->getParam('service_type'));	
		return $service_type;
	}
	function getNearlyPaymetySortSearch($_data=null){
		$nearlyPaymetySort = new Zend_Dojo_Form_Element_FilteringSelect("nearlyPaymetySort");
		$nearlyPaymetySort->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>"false",
			'queryExpr'=>'*${0}*',
			'required'=>false
			)
		);
		
		$_arr =array(
				1=>$this->tr->translate("STUDENT"),
				2=>$this->tr->translate("SERVICE"),
		);
		$nearlyPaymetySort->setMultiOptions($_arr);
		
		$nearlyPaymetySort->setValue($this->request->getParam('nearlyPaymetySort'));	
		return $nearlyPaymetySort;
	}
	
	function getPeriodDaySearch($_data=null){
		$periodDay = new Zend_Dojo_Form_Element_FilteringSelect("periodDay");
		$periodDay->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>"false",
			'queryExpr'=>'*${0}*',
			'required'=>false
			)
		);
		
		$_arr =array(
				0=>$this->tr->translate("BY_SELECTED_DATE"),
				7=>$this->tr->translate("BEFORE_ONE_WEEK"),
				15=>$this->tr->translate("BEFORE_HALF_MONTH"),
				30=>$this->tr->translate("BEFORE_ONE_MONTH"),
		);
		$periodDay->setMultiOptions($_arr);
		
		$periodDay->setValue($this->request->getParam('periodDay'));	
		return $periodDay;
	}

	
	
}