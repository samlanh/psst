<?php
class Registrar_Form_FrmInitial extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_num = 'dijit.form.NumberTextBox';
	}
	public function FrmInitialItem($data = null)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();

		$db = new Application_Model_DbTable_DbGlobal();

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
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
		$_branch_id->setValue($request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}

		if (!empty($data)) {
			$_branch_id->setValue($data['branchId']);
			
		}
		$this->addElements(
			array(
				
				$_branch_id,
				
			)
		);
		return $this;

	}
}