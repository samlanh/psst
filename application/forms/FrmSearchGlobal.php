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
				'required'=>'true',
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
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false,
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
				'required'=>false
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
				'required'=>false
		));
		$_session->setValue($request->getParam("session"));
		$opt_sesion=$_dbgb->getSession();
		$opt_session = array(''=>$this->tr->translate("SELECT_SESSION"));
		if(!empty($opt_sesion))foreach ($opt_sesion As $rs)$opt_session[$rs['key_code']]=$rs['view_name'];
		$_session->setMultiOptions($opt_session);
		
		$_teacher = new Zend_Dojo_Form_Element_FilteringSelect('teacher');
		$_teacher->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_teacher->setValue($request->getParam("teacher"));
		$result = $_dbgb->getAllTeahcerName();
		$opt_group = array(''=>$this->tr->translate("SELECT_TEACHER"));
		if(!empty($result))foreach ($result As $rs)$opt_group[$rs['id']]=$rs['name'];
		$_teacher->setMultiOptions($opt_group);
		
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
		$rs = $_dbgb->getViewById(9);
		if(!empty($rs))foreach($rs AS $row) $opt[$row['id']]=$row['name'];
		$is_pass->setMultiOptions($opt);
		$is_pass->setValue($request->getParam("is_pass"));
		
		$_startdate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_startdate->setAttribs(array('dojoType'=>$this->date,
				'class'=>'fullside',
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
				'required'=>'true',
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
		$_status_search->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
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
		$study_status->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$study_option = $_dbgb->getViewById(9,1);
		$study_option[-1]=$this->tr->translate("PLEASE_SELECT_STATUS");
		$study_status->setMultiOptions($study_option);
		
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
		if(!empty($_data)){
		
		}
		
		
		$this->addElements(array(
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
				$study_status
				)
			);
		return $this;
	}
	
}