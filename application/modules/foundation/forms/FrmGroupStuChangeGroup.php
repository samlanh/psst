<?php

class Foundation_Form_FrmGroupStuChangeGroup extends Zend_Dojo_Form
{
	protected  $tr;
	protected $textarea;
	protected $filter;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->textarea = 'dijit.form.Textarea';
	}
	function FrmAddGroupChangeGroup($data = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_dbgb = new Application_Model_DbTable_DbGlobal();

		$_arr_opt_branch = array("" => $tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
			'queryExpr' => '*${0}*',
			'autoComplete' => 'false'
		));
		if (count($optionBranch) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_id->setValue($row['id']);
			}
		}

		$moving_date = new Zend_Dojo_Form_Element_DateTextBox('moving_date');
		$date = date("Y-m-d");
		$moving_date->setAttribs(array(
			'data-dojo-Type' => "dijit.form.DateTextBox",
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
			'required' => true
		));
		$moving_date->setValue($date);

		$note = new Zend_Dojo_Form_Element_Textarea('note');
		$note->setAttribs(array(
			'dojoType' => $this->textarea, 'class' => 'fullside',
			'style' => 'min-height: 65px !important;',
		));

		$change_type = new Zend_Dojo_Form_Element_FilteringSelect('change_type');
		$change_type->setAttribs(array(
			'dojoType' => $this->filter,
			'class' => 'fullside',
			'autoComplete' => "false",
			'queryExpr' => '*${0}*',
			'required' => false
		));
		$optRs = $_dbgb->getViewById(17);
		$opt_change_type = array();
		if (!empty($optRs)) foreach ($optRs as $rs) $opt_change_type[$rs['key_code']] = $rs['view_name'];
		$change_type->setMultiOptions($opt_change_type);

		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
			1 => $tr->translate('ACTIVE'),
			0 => $tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
			'dojoType' => $this->filter,
			'class' => 'fullside',
		));

		$id = new Zend_Form_Element_hidden('id');
		$id->setAttribs(array(
			'dojoType' => "dijit.form.TextBox",
			'class' => 'fullside'
		));

		$from_degree = new Zend_Form_Element_hidden('from_degree');
		$from_degree->setAttribs(array(
			'dojoType' => "dijit.form.TextBox",
			'class' => 'fullside'
		));

		if (!empty($data)) {
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			$moving_date->setValue($data['moving_date']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
			$status->setValue($data['status']);
			$change_type->setValue($data['change_type']);
		}
		$this->addElements(array(
			$_branch_id,
			$moving_date,
			$note,
			$id,
			$from_degree,
			$status,
			$change_type
		));
		return $this;
	}
}
