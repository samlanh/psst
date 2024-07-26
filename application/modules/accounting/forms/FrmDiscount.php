<?php
class Accounting_Form_FrmDiscount extends Zend_Dojo_Form
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
	public function FrmDiscountsetting($data = null)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();

		$db = new Application_Model_DbTable_DbGlobal();

		$acadmicyear = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$acadmicyear->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
			)
		);
		$year_opt = $db->getAllAcademicYear(1);
		$acadmicyear->setMultiOptions($year_opt);

		$discountTitle = new Zend_Dojo_Form_Element_ValidationTextBox('discountTitle');
		$discountTitle->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'class' => 'fullside',
				'required' => true
			)
		);

		$_discount = new Zend_Dojo_Form_Element_FilteringSelect('discount_id');
		$_discount->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
			)
		);
		$degree_opt = $db->getAllDegree();
		$_discount->setMultiOptions($degree_opt);

		$_discount->setValue($request->getParam("discount_id"));
		$rows = $db->getAllDiscount();
		array_unshift($rows, array('id' => '', 'name' => $this->tr->translate("SELECT_DISCOUNT")));
		$opt = array();
		if (!empty($rows))
			foreach ($rows as $row)
				$opt[$row['id']] = $row['name'];
		$_discount->setMultiOptions($opt);

		$discountFor = new Zend_Dojo_Form_Element_FilteringSelect('discountFor');
		$discountFor->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
				'onchange'=>'discountFor();'
			)
		);
		$opt = $db->getViewById(37, 1,1);
		unset($opt[-1]);

		if ($request->getActionName() == 'index' OR $request->getModuleName() == 'allreport') {
			$opt['0']=$this->tr->translate("PLEASE_SELECT");
			ksort($opt);
			$discountFor->setAttrib('required', 'false');
			$acadmicyear->setAttrib('required', 'false');
		}
		$discountFor->setMultiOptions($opt);
		$discountFor->setValue($request->getParam('discountFor'));

		$discountType = new Zend_Dojo_Form_Element_FilteringSelect('DisValueType');
		$discountType->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*'
			)
		);
		$opt = array(
			'1' => $this->tr->translate('PERCENTAGE'),
			'2' => $this->tr->translate('FIXED_VALUE'),
		);
		$discountType->setMultiOptions($opt);

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

		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(
			array(
				'dojoType' => 'dijit.form.DateTextBox',
				'class' => 'fullside',
				'constraints' => "{datePattern:'dd/MM/yyyy'}"
			)
		);
		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$end_date->setAttribs(
			array(
				'dojoType' => 'dijit.form.DateTextBox',
				'class' => 'fullside',
				'constraints' => "{datePattern:'dd/MM/yyyy'}"
			)
		);

		$discountPeriod = new Zend_Dojo_Form_Element_FilteringSelect('discountPeriod');
		$discountPeriod->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
				'placeHolder'=>$this->tr->translate('DISCOUNT_PERIOD'),
				'onchange' => 'checkDiscountPeriod();'
			)
		);
		// $opt = array(
		// 	'1' => $this->tr->translate('ONETIME_ONLY'),
		// 	'2' => $this->tr->translate('BY_PERIOD'),
		// 	'3' => $this->tr->translate('LIFT_TIME'),
		// );
		$opt = $db->getViewById(39, 1,1);
		unset($opt[-1]);
		
		if ($request->getActionName() == 'index' OR $request->getModuleName() == 'allreport') {
			$opt['0']=$this->tr->translate("SELECT").$this->tr->translate("DISCOUNT_PERIOD");
			ksort($opt);
			$discountPeriod->setAttrib('required', 'false');
		}

		$discountPeriod->setMultiOptions($opt);
		$discountPeriod->setValue($request->getParam('discountPeriod'));

		$discountforType = new Zend_Dojo_Form_Element_FilteringSelect('discountforType');
		$discountforType->setAttribs(
			array(
				'dojoType' => $this->filter,
				'class' => 'fullside',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
				'onchange' => 'checkDiscountForType();'
			)
		);
		// $opt = array(
		// 	'1' => $this->tr->translate('SCHOOL_FEE'),
		// 	'2' => $this->tr->translate('SERVICE'),
		// );

		$opt = $db->getViewById(38, 1,1);
		unset($opt[-1]);

		$discountforType->setMultiOptions($opt);

		$_dismax = new Zend_Dojo_Form_Element_NumberTextBox('discountValue');
		$_dismax->setAttribs(
			array(
				'dojoType' => $this->t_num,
				'class' => 'fullside',
				'required' => 'true',
			)
		);

		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType' => $this->filter,'class' =>'fullside'));
		$_status_opt = array(
			1 => $this->tr->translate("ACTIVE"),
			0 => $this->tr->translate("DACTIVE")
		);
		$_status->setMultiOptions($_status_opt);

		if (!empty($data)) {
			$_branch_id->setValue($data['branchId']);
			$acadmicyear->setValue($data['academicYear']);
			$discountTitle->setValue($data['discountTitle']);
			$discountFor->setValue($data['discountFor']);
			$discountforType->setValue($data['discountForType']);

			$_discount->setValue($data['discountId']);
			$discountType->setValue($data['DisValueType']);
			$_dismax->setValue($data['discountValue']);

			$discountPeriod->setValue($data['discountPeriod']);
			$start_date->setValue($data['startDate']);
			$end_date->setValue($data['endDate']);

			$_status->setValue($data['status']);
		}
		$this->addElements(
			array(
				$discountFor,
				$discountType,
				$_dismax,
				$_discount,
				$_branch_id,
				$start_date,
				$end_date,
				$_status,
				$acadmicyear,
				$discountPeriod,
				$discountTitle,
				$discountforType
			)
		);
		return $this;

	}
}