<?php

class Global_Form_FrmParttimeList extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
	}
	function FrmAddPartimeList($data)
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
			$title->setValue($data["title"]);
			$description->setValue($data["description"]);
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
			$_branch_search ,
			$_status_search,
		
		));
		return $this;
	}
}
