<?php

class Registrar_Form_FrmStudentTest extends Zend_Dojo_Form
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
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getSerailCodeByBranch();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
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
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    			
    	));
    	
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$optionNation = $_dbgb->getViewByType(21);//Nation
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
    			'required'=>'true',
    			'onChange'=>'popupNation(2);',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("COMPLETED"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    		//$_date = date("Y-m-d");
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
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_branch_search->setValue($request->getParam("branch_search"));
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$kh_name->setValue($data["kh_name"]);
    		$_first_name->setValue($data["first_name"]);
    		$_last_name->setValue($data["en_name"]);
    		$_sex->setValue($data["sex"]);
    		
    		$_nationality->setValue($data["nationality"]);
    		$_nation->setValue($data["nation"]);
//     		$reason->setValue($data["reason"]);
//     		$_reference_name->setValue($data["parent_name"]);
//     		$_parent_tel->setValue($data["parent_tel"]);
//     		$_crm_status->setValue($data["crm_status"]);

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
    		$note->setValue($data["note"]);
    		$_emergency_name->setValue($data["emergency_name"]);
    		$_relationship_to_student->setValue($data["relationship_to_student"]);
    		$_emergency_tel->setValue($data["emergency_tel"]);
    		$_emergency_address->setValue($data["emergency_address"]);
    		$_degree->setValue($data["degree"]);
    		$_session->setValue($data["session_result"]);
    		$id->setValue($data["id"]);
    		
    		$_status->setValue($data["status"]);
    		
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
    			$result_date,
    			$_score,
    			$_status,
    			
    			$advance_search,
    			$_status_search,
    			$start_date,
    			$end_date,
    			$_branch_search
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
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
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    			 
    	));
    	 
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    
    	));
    	 
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));

    	
    	$_arr_opt_nation = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$optionNation = $_dbgb->getViewByType(21);//Nation
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
    			'required'=>'true',
    			'onChange'=>'popupNation(2);',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	 
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	 
    	 
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	 
    	$_arr = array(-1=>$this->tr->translate("ALL"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("COMPLETED"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	 
    	 
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	if(empty($_date)){
    		//$_date = date("Y-m-d");
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
    			$_status_search,
    			$start_date,
    			$end_date
    	));
    	return $this;
    }
    function FrmEnterResultTest($data=null,$detailscore=null){
    
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userinfo = $_dbgb->getUserInfo();
    
    	
    	$dbRegi = new Registrar_Model_DbTable_DbRegister();
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getStudntTestByBranch();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_arr_opt_stu = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$rows = $dbRegi->getAllStudentTested();
    	if(!empty($rows))foreach($rows AS $row) $_arr_opt_stu[$row['id']]=$row['name'];
    	$_stu_test_id = new Zend_Dojo_Form_Element_FilteringSelect("stu_test_id");
    	$_stu_test_id->setMultiOptions($_arr_opt_stu);
    	$_stu_test_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			//'onChange'=>'getStudentTestByBranch();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	$_score = new Zend_Dojo_Form_Element_NumberTextBox('score');
    	$_score->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("SCORE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Score")
    	
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
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	 
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_SESSION"));
    	$Option = $_dbgb->getAllSession();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_session = new Zend_Dojo_Form_Element_FilteringSelect("session");
    	$_session->setMultiOptions($_arr_opt);
    	$_session->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
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
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree_result = new Zend_Dojo_Form_Element_FilteringSelect("degree_result");
    	$_degree_result->setMultiOptions($_arr_opt);
    	$_degree_result->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getAllGradeResult();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	if (!empty($data)){
    		$_branch_id->setValue($data['branch_id']);
    		$_stu_test_id->setValue($data['id']);
    		
    		$_branch_id->setAttribs(array(
    				'readonly'=>'readonly',
    		));
    		$_stu_test_id->setAttribs(array(
    				'readonly'=>'readonly',
    				));
    	}
    	
    	if (!empty($detailscore)){
    		$_score->setValue($detailscore['score']);
    		$_degree->setValue($detailscore['degree']);
    		$_session->setValue($detailscore['session']);
    		if (!empty($detailscore['test_date'])){
    			$test_date->setValue(date("Y-m-d",strtotime($detailscore['test_date'])));
    		}
    		if (!empty($detailscore['result_date'])){
    			$result_date->setValue(date("Y-m-d",strtotime($detailscore['result_date'])));
    		}
    		$_degree_result->setValue($detailscore['degree_result']);
    		$note->setValue($detailscore['note']);
    		$id->setValue($detailscore['id']);
    		$_score->setAttribs(array(
    				'required'=>'true',
    		));
    		
    	}
    	$this->addElements(array(
    			$_branch_id,
    			$_stu_test_id,
    			$_score,
    			$_degree,
    			$_session,
    			$test_date,
    			$result_date,
    			$_degree_result,
    			$note,
    			$id
    	
    	));
    	return $this;
    }
}

