<?php

class Issuesetting_Form_FrmScoreEntrySetting extends Zend_Dojo_Form
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
		
		$academicYear = new Zend_Dojo_Form_Element_FilteringSelect('academicYear');
		$academicYear->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'placeholder'=>$this->tr->translate("ACADEMIC_YEAR"),
			'class'=>'fullside',
			'required'=>'false',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>'false',
		));
		$rows =  $_dbgb->getAllAcademicYear();
		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$academicYear->setMultiOptions($opt);
		$academicYear->setValue($request->getParam("academicYear"));

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

		$exam_from_date = new Zend_Dojo_Form_Element_DateTextBox('exam_from_date');
		$exam_from_date->setAttribs(array(
			'placeholder' => $this->tr->translate("FROM_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$exam_from_date->setValue($date);

		$exam_end_date = new Zend_Dojo_Form_Element_DateTextBox('exam_end_date');
		$exam_end_date->setAttribs(array(
			'placeholder' => $this->tr->translate("END_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$exam_end_date->setValue($date);

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

		$_arr = array(0=>$this->tr->translate("SELECT_TYPE"),1=>$this->tr->translate("MONTHLY"),2=>$this->tr->translate("SEMESTER"));
		$examType = new Zend_Dojo_Form_Element_FilteringSelect("examType");
		$examType->setMultiOptions($_arr);
		$examType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_TYPE"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$examType->setValue($request->getParam("examType"));

		$_arr = array(0=>$this->tr->translate("SELECT_SEMESTER"),1=>$this->tr->translate("SEMESTER1"),2=>$this->tr->translate("SEMESTER2"));
		$forSemester = new Zend_Dojo_Form_Element_FilteringSelect("forSemester");
		$forSemester->setMultiOptions($_arr);
		$forSemester->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_SEMESTER"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$forSemester->setValue($request->getParam("forSemester"));
		
		$_opt_month = array(0=>$this->tr->translate("CHOOSE_MONTH"));
		$_allMonth = $_dbgb->getAllMonth();
		if(!empty($_allMonth))foreach($_allMonth AS $row) $_opt_month[$row['id']]=$row['name'];
		$forMonth = new Zend_Dojo_Form_Element_FilteringSelect("forMonth");
		$forMonth->setMultiOptions($_opt_month);
		$forMonth->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'autoComplete'=>'false',
				'placeholder'=>$this->tr->translate("CHOOSE_MONTH"),
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$forMonth->setValue($request->getParam("forMonth"));

		if (!empty($data)) {
			$_branch_id->setValue($data["branchId"]);
			$title->setValue($data["title"]);
			$description->setValue($data["description"]);
			$from_date->setValue($data["fromDate"]);
			$end_date->setValue($data["endDate"]);
			$exam_from_date->setValue($data["examFromDate"]);
			$exam_end_date->setValue($data["examEndDate"]);
			$examType->setValue($data["examType"]);
			$forSemester->setValue($data["forSemester"]);
			$forMonth->setValue($data["forMonth"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
			$academicYear->setValue($data["academicYear"]);
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
			$exam_from_date,
			$exam_end_date,
			$examType,
			$forSemester,
			$forMonth,
			$academicYear
		));
		return $this;
	}
}
