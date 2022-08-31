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
				'placeholder'=>$this->tr->translate("INPUT_FACULTY_MAJOR")));
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
	
	public function FrmSetting($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("INPUT_LABEL_VALUE")));
		$_title->setValue($request->getParam("title"));
	
		$this->addElements(array($_title));
		return $this;
	}
	public function FrmAddSetting($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_keyname = new Zend_Dojo_Form_Element_TextBox('key_name');
		$_keyname->setAttribs(array('dojoType'=>$this->validate,
				'required'=>'true','class'=>'full',
				'placeholder'=>$this->tr->translate("INPUT_KEY_SETTING")));
	
		$_keyvalue = new Zend_Dojo_Form_Element_TextBox('key_value');
		$_keyvalue->setAttribs(array('dojoType'=>$this->validate,'class'=>'full',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("INPUT_VALUE_SETTING")));
		
		$_id = new Zend_Form_Element_Hidden('id');
	
		$this->addElements(array($_keyname,$_keyvalue,$_id));
		if(!empty($_data)){
			$_id->setValue($_data['code']);
			$_keyname->setValue($_data['keyName']);
			$_keyname->setAttrib("ReadOnly", true);
			$_keyvalue->setValue($_data['keyValue']);
		}
	
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
				'required'=>'true',
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
		
		
		$_arr_opt_nation = array(""=>$this->tr->translate("SELECT_NATION"),);
		$optionNation = $db->getViewByType(21);//Nation
		if(!empty($optionNation))foreach($optionNation AS $row) $_arr_opt_nation[$row['id']]=$row['name'];
		$_nationality = new Zend_Dojo_Form_Element_FilteringSelect("nationality");
		$_nationality->setMultiOptions($_arr_opt_nation);
		$_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text'
				,)
			);
		$_nationality->setValue($request->getParam("nationality"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$_staff=  new Zend_Dojo_Form_Element_FilteringSelect('staff_type');
		$_staff->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_staff_opt = array(
				0=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("TEACHER"),
				2=>$this->tr->translate("STAFF"));
		$_staff->setMultiOptions($_staff_opt);
		$_staff->setValue($request->getParam("staff_type"));
		
		$_teacher=  new Zend_Dojo_Form_Element_FilteringSelect('teacher_type');
		$_teacher->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_teacher_opt = array(
				-1=>$this->tr->translate("PLEASE_SELECT_TEACHER_TYPE"),
				1=>$this->tr->translate("TEACHER_KHMER"),
				0=>$this->tr->translate("TEACHER_FOREIGNER"));
		$_teacher->setMultiOptions($_teacher_opt);
		$_teacher->setValue($request->getParam("teacher_type"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		
		$_active_type=  new Zend_Dojo_Form_Element_FilteringSelect('active_type');
		$_active_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
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
		$this->addElements(array($_id,$_title,$_degree,$_teacher,$_staff,$_branch_id,$end_date,$_nationality,$_status,$_department,$_active_type));
		
		return $this;
	}
	public function searchRoom(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_ROOM_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
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
				'required'=>false
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
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside"));
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
				'required'=>'true',
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
	public function frmServiceType($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
	
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_status_type = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
	
		$this->addElements(array($_title,$_type,$_status));
		if(!empty($_data)){
		}
	
		return $this;
	}
	public function frmSearchServiceProgram($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_BY_TEACHER_NAME")));
		$_title->setValue($request->getParam('title'));
		
		$db =new Application_Model_DbTable_DbGlobal();
		$row = $db->getServiceType();
		$_cate_opt="";
		$_cate_name = new Zend_Dojo_Form_Element_FilteringSelect('cate_name');
		$action = $request->getActionName();
		if($action=='index'){
			$_cate_opt=array(-1=>$this->tr->translate("SELECT_CATEGORY"));
		}
		if(!empty($row)){
			foreach($row as $rs)$_cate_opt[$rs['id']]=$rs['title'];
			$_cate_name->setMultiOptions($_cate_opt);
		}
		$_cate_name->setValue($request->getParam("cate_name"));
		
		$_cate_name->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_status_type = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SEARCH_BY_TYPE")));
	
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
	
		$this->addElements(array($_cate_name,$_title,$_type,$_status));
		if(!empty($_data)){
		}
	
		return $this;
	}
	public function frmSearchTutionFee($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('fee_code');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_CODE")));
		$_title->setValue($request->getParam('title'));
		
		$_batch = new Zend_Dojo_Form_Element_TextBox('batch');
		$_batch->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_BATCH")));
		$_batch->setValue($request->getParam('title'));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllFecultyName();
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['dept_id']]=$row['en_name'];
		$_faculty=new Zend_Dojo_Form_Element_FilteringSelect('faculty');
		$_faculty->setAttribs(array('dojoType'=>$this->filter,));
		$_faculty->setMultiOptions($opt);
		$_faculty->setValue($request->getParam("faculty"));
		
		$row = $db ->getAllDegree();
		//$row=array(-1=>$this->tr->translate("CHOOSE"));
		$_degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,));
		$_degree->setMultiOptions($row);
		
		$row = $db->getAllMention();
		array_unshift($row, array(-1=>$this->tr->translate("CHOOSE")));
		$_metion =  new Zend_Dojo_Form_Element_FilteringSelect('metion');
		$_metion->setAttribs(array('dojoType'=>$this->filter,));
		$_metion->setMultiOptions($row);
	
	
		$this->addElements(array($_degree,$_batch,$_faculty,$_title,$_status,$_metion));
		if(!empty($_data)){
		}
		return $this;
	}
	public function frmSearchServiceChageFee($_data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_code = new Zend_Dojo_Form_Element_TextBox('service_code');
		$_code->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_BY_CODE")));
		$_code->setValue($request->getParam('service_code'));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getServiceType();
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['id']]=$row['title'];
		$_cate_type=new Zend_Dojo_Form_Element_FilteringSelect('cate_type');
		$_cate_type->setAttribs(array('dojoType'=>$this->filter,));
		$_cate_type->setMultiOptions($opt);
		$_cate_type->setValue($request->getParam("cate_type"));
	
		$rows = $db ->getAllServiceItemsName(0);
		$_service_name =  new Zend_Dojo_Form_Element_FilteringSelect('service_name');
		$_service_name->setAttribs(array('dojoType'=>$this->filter,));
		$opt = array(-1=>$this->tr->translate("CHOOSE"));
		if(!empty($rows))foreach ($rows as $row)$opt[$row['id']]=$row['title'];
		$_service_name->setMultiOptions($opt);
		$_service_name->setValue($request->getParam("service_name"));
	
		$this->addElements(array($_service_name,$_cate_type,$_code,$_status));
		if(!empty($_data)){
		}
		return $this;
	}
	public function FrmsearchSituation(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_SITUATION_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
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
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_OCCUPATION_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
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
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_DISCOUNT_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam('branch_id'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status,$_branch_id));
	
		return $this;
	}
	
	public function FrmsearchDocument(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH_DOCUMENT_TITLE")));
		$_title->setValue($request->getParam("title"));
		
		$_type=  new Zend_Dojo_Form_Element_FilteringSelect('type_search');
		$_type->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_type_opt = array(
				0=>$this->tr->translate("PLEASE_SELECT_CATEGORY"),
				1=>$this->tr->translate("STUDENT_DOCUMENT"),
				2=>$this->tr->translate("TEACHER_DOCUMENT"));
		$_type->setMultiOptions($_type_opt);
		$_type->setValue($request->getParam("type_search"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_type,$_status));
	
		return $this;
	}
	public function FrmsearchScholarship(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_SCHOLARSHIP_TITLE")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		$this->addElements(array($_title,$_status));
	
		return $this;
	}
}