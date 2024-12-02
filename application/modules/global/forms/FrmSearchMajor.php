<?php 
Class Global_Form_FrmSearchMajor extends Zend_Dojo_Form{
	protected $tr = null;
	protected $btn =null;//text validate
	protected $filter = null;
	protected $text =null;
	protected $validate = null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->btn = 'dijit.form.Button';
		$this->validate = 'dijit.form.ValidationTextBox';
	}
	public function FrmDepartment($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_FACULTY_NAME")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside"));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	
	public function FrmMajors($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
				
		$this->addElements(array($_title,$_status));		
		return $this;
	}
	
	public function frmSearchTeacher($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_dbd = new Global_Model_DbTable_DbTeacher();
		$_arr_opt_degree = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionDegree = $_dbd->getAllDegree();
		if(!empty($optionDegree))foreach($optionDegree AS $row) $_arr_opt_degree[$row['id']]=$row['name'];
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
		$_degree->setMultiOptions($_arr_opt_degree);
		$_degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT"),
				'class'=>'fullside height-text',));
		$_degree->setValue($request->getParam("degree"));
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_degree = array(""=>$this->tr->translate("PLEASE_SELECT_DEPARTMENT"));
		$_arr_department = $_db->getAllDepartment();
		if(!empty($_arr_department))foreach($_arr_department AS $row) $_arr_opt_degree[$row['id']]=$row['name'];
		$_department = new Zend_Dojo_Form_Element_FilteringSelect("department");
		$_department->setMultiOptions($_arr_opt_degree);
		$_department->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_DEPARTMENT"),
				'class'=>'fullside height-text',));
		$_department->setValue($request->getParam("department"));
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);

		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("ACADEMIC_YEAR"),
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_academic->setValue($request->getParam("academic_year"));
		$rows =  $db->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		
		
		$_arr_opt_nation = array(""=>$this->tr->translate("SELECT_NATION"),);
		$optionNation = $db->getViewByType(21);//Nation
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
		$_nationality->setValue($request->getParam("nationality"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_BRANCH"),
				'required'=>'false',
			));
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$_staff=  new Zend_Dojo_Form_Element_FilteringSelect('staff_type');
		$_staff->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("SELECT_TYPE"),
			'required'=>'false',
		));
		$_staff_opt = array(
				0=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("TEACHER"),
				2=>$this->tr->translate("STAFF"));
		$_staff->setMultiOptions($_staff_opt);
		$_staff->setValue($request->getParam("staff_type"));
		
		$_teacher=  new Zend_Dojo_Form_Element_FilteringSelect('teacher_type');
		$_teacher->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("PLEASE_SELECT_TEACHER_TYPE"),
			'required'=>'false',
		));
		$_teacher_opt = array(
				-1=>$this->tr->translate("PLEASE_SELECT_TEACHER_TYPE"),
				1=>$this->tr->translate("TEACHER_KHMER"),
				0=>$this->tr->translate("TEACHER_FOREIGNER"));
		$_teacher->setMultiOptions($_teacher_opt);
		$_teacher->setValue($request->getParam("teacher_type"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'placeholder'=>$this->tr->translate("STATUS"),
			'class'=>'fullside',
			'required'=>'false',
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		
		$_active_type=  new Zend_Dojo_Form_Element_FilteringSelect('active_type');
		$_active_type->setAttribs(array(
			'dojoType'=>$this->filter,
			'placeholder'=>$this->tr->translate("PLEASE_SELECT"),
			'class'=>'fullside',
			'required'=>'false',
		));
		$_active_type_opt = array(
				-1=>$this->tr->translate("PLEASE_SELECT"),
				0=>$this->tr->translate("ACTIVING"),
				1=>$this->tr->translate("STOP"));
		$_active_type->setMultiOptions($_active_type_opt);
		$_active_type->setValue($request->getParam("active_type"));
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($_data)){
			$_branch_id->setValue($_data['branch_id']);
			$_staff->setValue($_data['staff_type']);
			$_degree->setValue($_data['degree']);
			$_teacher->setValue($_data['teacher_type']);
			$_nationality->setValue($_data['nationality']);
		}
		$this->addElements(array($_id,$_title,$_degree,$_teacher,$_staff,$_branch_id,$end_date,$_nationality,$_status,$_department,$_active_type,$_academic ));
		
		return $this;
	}
	public function searchRoom(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_ROOM_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
				
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array('dojoType'=>$this->filter,
				//	'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_BRANCH")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_branch_id->setMultiOptions($opt);
		$_branch_id->setValue($request->getParam("branch_id"));
		
			
		$this->addElements(array($_branch_id,$_title,$_status));		
		return $this;
	}
	public function SubjectExam(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_SUBJECT_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			'placeholder'=>$this->tr->translate("STATUS"),
			"class"=>"fullside",
			'required'=>'false',
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$Option = $_dbgb->getAllSchoolOption($userinfo['branch_list']);
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$_schoolOption_search = new Zend_Dojo_Form_Element_FilteringSelect("schoolOption_search");
		$_schoolOption_search->setMultiOptions($_arr_opt);
		$_schoolOption_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_schoolOption_search->setValue($request->getParam("schoolOption_search"));
		
		$this->addElements(array($_title,$_status,$_schoolOption_search));
	
		return $this;
	}
	public function searchProvinnce(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH_PROVINCE_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	
	public function FrmsearchOccupation(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
			'dojoType'=>$this->text,
			"class"=>"fullside",
			'placeholder'=>$this->tr->translate("SEARCH_OCCUPATION_TITLE")
		));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'placeholder'=>$this->tr->translate("STATUS"),
			'required'=>'false',
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
	
	public function FrmsearchDiscount(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
			'dojoType'=>$this->text,
			"class"=>"fullside",
			'placeholder'=>$this->tr->translate("SEARCH_DISCOUNT_TITLE")
			));
		$_title->setValue($request->getParam("title"));

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
		$opt_degree = $db->getAllItems(1);//degree
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
		
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_BRANCH"),
				'required'=>'false',
			));
		$_branch_id->setValue($request->getParam('branch_id'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'required'=>'false',
			'placeholder'=>$this->tr->translate("STATUS"),
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));

		$discount_status=  new Zend_Dojo_Form_Element_FilteringSelect('discount_status');
		$discount_status->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'required'=>'false',
			'placeholder'=>$this->tr->translate("DISCOUNT_STATUS"),
		));
		$discount_status_opt = array(
				''=>$this->tr->translate("DISCOUNT_STATUS"),
				1=>$this->tr->translate("USING"),
				0=>$this->tr->translate("STOP_USED"));
		$discount_status->setMultiOptions($discount_status_opt);
		$discount_status->setValue($request->getParam("status_search"));

		$academicYearEnroll = new Zend_Dojo_Form_Element_FilteringSelect('academicYearEnroll');
		$academicYearEnroll->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SELECT_ENROLL_YEAR"),
				'class'=>'fullside',
				'required'=>'false',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		$academicYearEnroll->setValue($request->getParam("academicYearEnroll"));
		$rows =  $db->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_ENROLL_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$academicYearEnroll->setMultiOptions($opt);

		$studentType = new Zend_Dojo_Form_Element_FilteringSelect("studentType");	
		$studentType->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'false',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$studentType->setMultiOptions($db->getViewByType(40,1));

		$student_status=  new Zend_Dojo_Form_Element_FilteringSelect('student_status');
		$student_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$statusopt = array(
				''=>$this->tr->translate("STUDENT_STATUS"),
				1=>$this->tr->translate("LEARNING"),
				2=>$this->tr->translate("STOP_STUDY"));
		$student_status->setMultiOptions($statusopt);
		$student_status->setValue($request->getParam("student_status"));

		$frm = new Accounting_Form_FrmDiscount();
		$newfrm = $frm->FrmDiscountsetting();
		
		$discountFor = $newfrm->getElement('discountFor');
		$discountPeriod = $newfrm->getElement('discountPeriod');
		$academic_year = $newfrm->getElement('academic_year');

		$this->addElements(array($_title,$_status,$_branch_id,$discountFor,$discountPeriod,$academic_year,$academicYearEnroll,$studentType,$_degree,$discount_status,$student_status));
		
	
		return $this;
	}
	
	public function FrmsearchDocument(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_DOCUMENT_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_type=  new Zend_Dojo_Form_Element_FilteringSelect('type_search');
		$_type->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'placeholder'=>$this->tr->translate("PLEASE_SELECT_CATEGORY"),
			'required'=>'false',
		));
		$_type_opt = array(
				0=>$this->tr->translate("PLEASE_SELECT_CATEGORY"),
				1=>$this->tr->translate("STUDENT_DOCUMENT"),
				2=>$this->tr->translate("TEACHER_DOCUMENT"));
		$_type->setMultiOptions($_type_opt);
		$_type->setValue($request->getParam("type_search"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'placeholder'=>$this->tr->translate("STATUS"),
			'required'=>'false',
		));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_type,$_status));
	
		return $this;
	}
	
}