<?php 
class Registrar_Form_FrmSearchInfor extends Zend_Dojo_Form {
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
		//$this->validate = 'dijit.form.TextBox';
	}
	public function FrmSearchRegister($type=1){ 
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$adv_search->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$adv_search->setValue($request->getParam("adv_search"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$_title->setValue($request->getParam("title"));
	    
		$study_year = new Zend_Dojo_Form_Element_FilteringSelect('study_year');
		$study_year->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false,
				
		));
		$study_year->setValue($request->getParam("study_year"));
		$db_years=new Registrar_Model_DbTable_DbRegister();
		$years=$db_years->getAllYears($type);
		$opt = array(''=>$this->tr->translate("SELECT_YEAR"));
		if(!empty($years))foreach($years AS $row) $opt[$row['id']]=$row['name'];
		$study_year->setMultiOptions($opt);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$_status_type = new Zend_Dojo_Form_Element_FilteringSelect("status_type");
		$_status_type->setMultiOptions($_arr_opt);
		$_status_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$status_opt = $db->AllStatusRe();
		$_status_type->setMultiOptions($status_opt);
		
		$item = new Zend_Dojo_Form_Element_FilteringSelect('item');
		$item->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$item->setValue($request->getParam("item"));
		$items = $_dbgb->getAllItems($type);
		$opt = array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($items))foreach($items AS $row) $opt[$row['id']]=$row['name'];
		$item->setMultiOptions($opt);
		
		$model = new Application_Model_DbTable_DbGlobal();
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT_DISTYPE"));
		$Option = $model->getAllDiscount();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$_dis_type = new Zend_Dojo_Form_Element_FilteringSelect("dis_type");
		$_dis_type->setMultiOptions($_arr_opt);
		$_dis_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$academic_year = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$academic_year->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$academic_year->setValue($request->getParam("academic_year"));
		$db_yeartran = new Global_Model_DbTable_DbGroup();
		$yeartran = $db_yeartran->getAllYears();
		$opt = array(''=>$this->tr->translate("SELECT_YEAR"));
		if(!empty($yeartran))foreach($yeartran AS $row) $opt[$row['id']]=$row['years'];
		$academic_year->setMultiOptions($opt);
		
		$finished_status = new Zend_Dojo_Form_Element_FilteringSelect('finished_status');
		$finished_status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$finish_opt = new Accounting_Model_DbTable_DbTuitionFee();
    	$fin_row=$finish_opt->getProcessTypeView();
		$opt = array('-1'=>$this->tr->translate("PROCESS_TYPE"));
		if(!empty($fin_row))foreach($fin_row AS $row) $opt[$row['id']]=$row['name'];
		$finished_status->setMultiOptions($opt);
		$finished_status->setValue($request->getParam("finished_status"));
		
		$_group = new Zend_Dojo_Form_Element_FilteringSelect('group');
		$_group->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_group->setValue($request->getParam("group"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllGroup();
		$opt_group = array(''=>$this->tr->translate("SELECT_GROUP"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_group->setMultiOptions($opt_group);
		
		$_cate = new Zend_Dojo_Form_Element_FilteringSelect('cate_income');
		$_cate->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_cate->setValue($request->getParam("cate_income"));
		$_db = new Registrar_Model_DbTable_DbIncome();	
		$result = $_db->getCateIncome();
		$opt_cate = array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($result))foreach ($result As $rs)$opt_cate[$rs['id']]=$rs['name'];
		$_cate->setMultiOptions($opt_cate);
		
		$term_test = new Zend_Dojo_Form_Element_FilteringSelect('term_test');
		$term_test->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$result = $db->getallTermtest();
		$optterm = array(''=>$this->tr->translate("SELECT_TERM"));
		if(!empty($result))foreach ($result As $rs){
			$optterm[$rs['id']]=$rs['name'];
		}
		$term_test->setMultiOptions($optterm);
		$term_test->setValue($request->getParam("term_test"));

		$_session = new Zend_Dojo_Form_Element_FilteringSelect('session');
		$_session->setAttribs(array(
				'dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SESSION"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
				));
		$_session->setValue($request->getParam("session"));
		$opt_ses=new Application_Model_DbTable_DbGlobal();
		$opt_sesion=$opt_ses->getSession();
		$opt_session = array(''=>$this->tr->translate("SESSION"));
		if(!empty($opt_sesion))foreach ($opt_sesion As $rs)$opt_session[$rs['key_code']]=$rs['view_name'];
		$_session->setMultiOptions($opt_session);
		
		$_room = new Zend_Dojo_Form_Element_FilteringSelect('room');
		$_room->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_room->setValue($request->getParam("room"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllRoom();
		$opt_room = array(''=>$this->tr->translate("ROOM_NAME"));
		if(!empty($result))foreach ($result As $rs)$opt_room[$rs['id']]=$rs['name'];
		$_room->setMultiOptions($opt_room);
		
		$_time = new Zend_Dojo_Form_Element_FilteringSelect('time');
		$_time->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("TIME"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
				));
		$_time->setValue($request->getParam("time"));
		$opt_time = array(''=>$this->tr->translate("TIME"),
				 1=>$this->tr->translate("PART_TIME"),
				 2=>$this->tr->translate("FULL_TIME"),
				);
		$_time->setMultiOptions($opt_time);
		
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
// 		$opt_degree=$db_years->getAllDegree();
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
		
		
		$_degree_bac = new Zend_Dojo_Form_Element_FilteringSelect('degree_bac');
		$_degree_bac->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_degree_bac->setValue($request->getParam('degree_bac'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		if($type==2){
			$opt_deg = array(''=>$this->tr->translate("CATE_TYPE"));
		}
		$opt_degree=$db_years->getAllDegreeBac($type);
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree_bac->setMultiOptions($opt_deg);
		
		$_grade = new Zend_Dojo_Form_Element_FilteringSelect('grade');
		$_grade->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
				));
		$_grade->setValue($request->getParam('grade'));
		$opt_g = array(''=>$this->tr->translate("GRADE"));
		$opt_grade= $_dbgb->getAllGradeStudy($type);
		if(!empty($opt_grade))foreach ($opt_grade As $rows)$opt_g[$rows['id']]=$rows['name'];
		$_grade->setMultiOptions($opt_g);
		
		$_grade_all = new Zend_Dojo_Form_Element_FilteringSelect('grade_all');
		$_grade_all->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_grade_all->setValue($request->getParam('grade_all'));
		$opt_g_all = array(''=>$this->tr->translate("GRADE"));
		if($type==2){
			$opt_g_all = array(''=>$this->tr->translate("SELECT_SERVICE"));
		}
		$opt_grade_all=$db_years->getGradeAllDept($type);
		if(!empty($opt_grade_all))foreach ($opt_grade_all As $rows)$opt_g_all[$rows['id']]=$rows['name'];
		$_grade_all->setMultiOptions($opt_g_all);
		
		$user = new Zend_Dojo_Form_Element_FilteringSelect('user');
		$user->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("USER"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$user->setValue($request->getParam('user'));
		$opt_user = array(''=>$this->tr->translate("USER"));
		$opt_all_user=$db_years->getAllUser();
		if(!empty($opt_all_user))foreach ($opt_all_user As $row)$opt_user[$row['id']]=$row['name'];
		$user->setMultiOptions($opt_user);
		
		$sess_gep = new Zend_Dojo_Form_Element_FilteringSelect('sess_gep');
		$sess_gep->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("TIME"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$sess_gep->setValue($request->getParam("sess_gep"));
		$ses_gep = array(''=>$this->tr->translate("TIME"),
				1=>$this->tr->translate("PART_TIME"),
				2=>$this->tr->translate("FULL_TIME"),
		);
		$sess_gep->setMultiOptions($ses_gep);
		
		
		$service = new Zend_Dojo_Form_Element_FilteringSelect('service');
		$service->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$service->setValue($request->getParam("service"));
		$opt_ser = array(''=>$this->tr->translate("SERVICE_NAME"));
		$ser_rows = $db->getAllGradeStudy(2);
		if(!empty($ser_rows))foreach($ser_rows As $row)$opt_ser[$row['id']]=$row['name'];
		$service->setMultiOptions($opt_ser);
		
		$pay_term = new Zend_Dojo_Form_Element_FilteringSelect('pay_term');
		$pay_term->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("PAYMENT_TERM"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$pay_term->setValue($request->getParam("pay_term"));
		$opt_term = array(
				''=>$this->tr->translate("PAYMENT_TERM"),
   				2=>$this->tr->translate('QUARTER'),
   				3=>$this->tr->translate('SEMESTER'),
   				4=>$this->tr->translate('YEAR'),
   		
   		);
		$pay_term->setMultiOptions($opt_term);
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		//date 
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$_date = $request->getParam("start_date");
		
		if(!empty($_date)){
			$start_date->setValue($_date);
		}
		
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
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false,
				'onchange'=>'getAllAcademicByBranch();'
		));
		$branch_id->setValue($request->getParam("branch_id"));
		$db = new Accounting_Model_DbTable_DbTuitionFee();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_LOCATION")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$branch_id->setMultiOptions($opt);
		if (count($rows)==1){
			$branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows))foreach($rows AS $row){
				$branch_id->setValue($row['id']);
			}
		}
		
		$_stu_code = new Zend_Dojo_Form_Element_FilteringSelect('stu_code');
		$_stu_code->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_stu_code->setValue($request->getParam("stu_code"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllStuCode();
		$opt_stu_code = array(''=>$this->tr->translate("SELECT_STUDENT_ID"));
		if(!empty($result))foreach ($result As $rs)$opt_stu_code[$rs['id']]=$rs['stu_code'];
		$_stu_code->setMultiOptions($opt_stu_code);
		
		$_stu_name = new Zend_Dojo_Form_Element_FilteringSelect('stu_name');
		$_stu_name->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_stu_name->setValue($request->getParam("stu_name"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllStuName();
		$opt_stu_name = array(''=>$this->tr->translate("SELECT_STUDENT_NAME"));
		if(!empty($result))foreach ($result As $rs)$opt_stu_name[$rs['id']]=$rs['name'];
		$_stu_name->setMultiOptions($opt_stu_name);
		
		//student id
		$stuname_con = new Zend_Dojo_Form_Element_FilteringSelect('stuname_con');
		$stuname_con->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$stuname_con->setValue($request->getParam("stuname_con"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllStudent();
		$opt_stu_name = array(''=>$this->tr->translate("SELECT_STUDENT_NAME"));
		if(!empty($result))foreach ($result As $rs)$opt_stu_name[$rs['id']]=$rs['name'];
		$stuname_con->setMultiOptions($opt_stu_name);
		
		//term 
		$term = new Zend_Dojo_Form_Element_FilteringSelect('term');
		$term->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$term->setValue($request->getParam("term"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllTerm();
		$opt_stu_name = array(''=>$this->tr->translate("SELECT_TERM"));
		if(!empty($result))foreach ($result As $rs)$opt_stu_name[$rs['id']]=$rs['name'];
		$term->setMultiOptions($opt_stu_name);
		
		$generation = new Zend_Dojo_Form_Element_FilteringSelect('generation');
		$generation->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$generation->setValue($request->getParam("generation"));
		$generoption=$db->getAllGeneration(1,1);
		$generation->setMultiOptions($generoption);
		
		$service_type = new Zend_Dojo_Form_Element_FilteringSelect('service_type');
		$service_type->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$db = new Accounting_Model_DbTable_DbService();
// 		$opt= $db->getServiceType(1,1);
		$opt = array(''=>$this->tr->translate("PLEASE_SELECT"),'1'=>$this->tr->translate("TUITION_FEE"),'2'=>$this->tr->translate("SERVICE"));
		$service_type->setMultiOptions($opt);
		$service_type->setValue($request->getParam("service_type"));
		
		$payment_by = new Zend_Dojo_Form_Element_FilteringSelect('payment_by');
		$payment_by->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false
		));
		
		$study_status = new Zend_Dojo_Form_Element_FilteringSelect('study_status');
		$study_status->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$study_option = $db->getViewById(9,1);
		$study_status->setMultiOptions($study_option);
		$study_status->setValue($request->getParam("study_status"));
		
		$opt=array(-1=>"Select Payment By",1=>"Tution Fee",3=>"Service",4=>"Product");
		$payment_by->setMultiOptions($opt);
		$payment_by->setValue($request->getParam("payment_by"));
		
		//day 
		$_subject = new Zend_Dojo_Form_Element_FilteringSelect('subject');
		$_subject->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_subject->setValue($request->getParam("subject"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllSubjectName();
		$opt_group = array(''=>$this->tr->translate("SELECT_SUBJECT"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_subject->setMultiOptions($opt_group);
		
		//teacher 
		$_teacher = new Zend_Dojo_Form_Element_FilteringSelect('teacher');
		$_teacher->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_teacher->setValue($request->getParam("teacher"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllTeahcerName();
		$opt_group = array(''=>$this->tr->translate("SELECT_TEACHER"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_teacher->setMultiOptions($opt_group);
		
		//day
		$_day= new Zend_Dojo_Form_Element_FilteringSelect('day');
		$_day->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_day->setValue($request->getParam("day"));
		$result = $db->getAllDayName();
		$opt_group = array(''=>$this->tr->translate("SELECT_DAY"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_day->setMultiOptions($opt_group);
		
		$_arr_opt_user = array(""=>$this->tr->translate("PLEASE_SELECT_USER"),);
		$userinfo = $_dbgb->getUserInfo();
		$optionUser = $_dbgb->getAllUser();
		if(!empty($optionUser))foreach($optionUser AS $row) $_arr_opt_user[$row['id']]=$row['name'];
		$_user_id = new Zend_Dojo_Form_Element_FilteringSelect("user_id");
		$_user_id->setMultiOptions($_arr_opt_user);
		$_user_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		if ($userinfo['level']!=1){
			$_user_id->setAttribs(array(
					'readonly'=>true,
			));
			$_user_id->setValue($userinfo['user_id']);
		}
		$_user_id->setValue($request->getParam("user_id"));
		
		
		$allacademicyear = new Zend_Dojo_Form_Element_FilteringSelect('allacademicyear');
		$allacademicyear->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$db_yeartran = new Allreport_Model_DbTable_DbRptAllStudent();
		$yeartran = $db_yeartran->getAllYearTuitionfee();
		$opt = array();
		if(!empty($yeartran))foreach($yeartran AS $row) $opt[$row['academicyear']]=$row['academicyear'];
		$allacademicyear->setMultiOptions($opt);
		$allacademicyear->setValue($request->getParam("allacademicyear"));
		
		$is_pass = new Zend_Dojo_Form_Element_FilteringSelect('is_pass');
		$is_pass->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'required'=>false
		));
		$opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$rs = $db->getViewById(9);
		if(!empty($rs))foreach($rs AS $row) $opt[$row['id']]=$row['name'];
		$is_pass->setMultiOptions($opt);
		$is_pass->setValue($request->getParam("is_pass"));
		
		$this->addElements(array($is_pass,$item,$finished_status,$term_test,$term,$stuname_con,
					$_day,$_cate,$_teacher,$_subject,$study_status,$_status_type,$_group,$payment_by,$study_year,$academic_year,
					$service_type,$_stu_name,$_stu_code,$_degree_bac,$_dis_type,$_room,$branch_id,$start_date,
					$user,$end_date,$sess_gep,$_title,$generation,
					$_session,$_time,$_degree,$_grade,$_grade_all,$adv_search,$_status,$service,$pay_term,$_user_id,
				
				$allacademicyear
				));
	
		return $this;
	} 
}