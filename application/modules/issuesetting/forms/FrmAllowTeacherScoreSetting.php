<?php

class Issuesetting_Form_FrmAllowTeacherScoreSetting extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
	}
	function FrmAddScoreEntrySetting($data)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$typeItems = empty($typeItems) ? 1 : $typeItems;
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);

		$_arr_opt_branch = array("" => $this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		if (count($optionBranch) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_id->setValue($row['id']);
			}
		}

		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array(
				'dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("ACADEMIC_YEAR"),
				'class'=>'fullside',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_academic->setValue($request->getParam("academic_year"));
		$rows =  $_dbgb->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);

		$_subject_id = new Zend_Dojo_Form_Element_FilteringSelect('subject_id');
		$_subject_id->setAttribs(array(
				'dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SUBJECT"),
				'class'=>'fullside',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_subject_id->setValue($request->getParam("subject_id"));
		$rows =  $_dbgb->getAllSubjectName();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_SUBJECT")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_subject_id->setMultiOptions($opt);

		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array(
			'dojoType' => $this->filter,
			'placeholder' => $this->tr->translate("DEGREE"),
			'class' => 'fullside',
			'autoComplete' => "false",
			'queryExpr' => '*${0}*',
			'required' => 'true',
			//'onchange' => 'getallGrade();'
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array('' => $this->tr->translate("DEGREE"));
		$opt_degree = $_dbgb->getAllItems(1); //degree
		if (!empty($opt_degree)) foreach ($opt_degree as $rows) $opt_deg[$rows['id']] = $rows['name'];
		$_degree->setMultiOptions($opt_deg);

		$title = new Zend_Dojo_Form_Element_TextBox('title');
		$title->setAttribs(array(
			'dojoType' => 'dijit.form.ValidationTextBox',
			'required' => 'true',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("TITLE"),
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => $this->tr->translate("Forget Enter Title")
		));

		$description =  new Zend_Form_Element_Textarea('description');
		$description->setAttribs(array(
			'dojoType' => 'dijit.form.Textarea',
			'class' => 'fullside',
			'style' => 'font-family: inherit;  min-height:100px !important;'
		));

		$_arr = array(1 => $this->tr->translate("ACTIVE"), 0 => $this->tr->translate("DEACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));


		$id = new Zend_Form_Element_Hidden('id');

		$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
		$advance_search->setAttribs(array(
			'dojoType' => 'dijit.form.TextBox',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("SEARCH_HERE"),
			'missingMessage' => $this->tr->translate("SEARCH_HERE")
		));
		$advance_search->setValue($request->getParam("advance_search"));

		$_arr = array(-1 => $this->tr->translate("ALL"), 1 => $this->tr->translate("ACTIVE"), 0 => $this->tr->translate("DEACTIVE"));
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_status_search->setMultiOptions($_arr);
		$_status_search->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		$_status_search->setValue($request->getParam("status_search"));

		$from_date = new Zend_Dojo_Form_Element_DateTextBox('from_date');
		$from_date->setAttribs(array(
			'placeholder' => $this->tr->translate("FROM_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$from_date->setValue($date);

		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
			'placeholder' => $this->tr->translate("START_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$_date = $request->getParam("start_date");
		$start_date->setValue($_date);

		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
			'placeholder' => $this->tr->translate("END_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'class' => 'fullside',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'required' => false
		));
		$_date = $request->getParam("end_date");
		if (empty($_date)) {
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);

		$_arr_opt_branch = array("" => $this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
		$_branch_search->setMultiOptions($_arr_opt_branch);
		$_branch_search->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		if (count($optionBranch) == 1) {
			$_branch_search->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_search->setValue($row['id']);
			}
		}
		$_branch_search->setValue($request->getParam("branch_search"));

		if (!empty($data)) {
			$_branch_id->setValue($data["branchId"]);
			$_academic->setValue($data["academicYear"]);
			$_degree->setValue($data["degree"]);
			$end_date->setValue($data["endDate"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
		}
		$this->addElements(array(
			$_branch_id,
			$title,
			$description,
			$_status,
			$id,
			$advance_search,
			$_status_search,
			$from_date,
			$start_date,
			$end_date,
			$_branch_search,
			$_degree,
			$_academic,
			$_subject_id
		));
		return $this;
	}
}
