<?php 
Class Accounting_Form_FrmDiscount extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	protected $tarea;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmDiscountsetting($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_discount = new Zend_Dojo_Form_Element_FilteringSelect('disname_id');
		$_discount->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$degree_opt = $db->getAllDegree();
		$_discount->setMultiOptions($degree_opt);
		
		$discountOption = new Zend_Dojo_Form_Element_FilteringSelect('discountOption');
		$discountOption->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$opt = array(
				'1'=>$this->tr->translate('ALL_STUDENT'),
				'2'=>$this->tr->translate('ONLY_STUDENT'),
				);
		$discountOption->setMultiOptions($opt);
		
		$DisValueType = new Zend_Dojo_Form_Element_FilteringSelect('DisValueType');
		$DisValueType->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*'
				));
		$opt = array(
				'1'=>$this->tr->translate('PERCENTAGE'),
				'2'=>$this->tr->translate('FIXED_VALUE'),
		);
		$DisValueType->setMultiOptions($opt);
		
		
		
		$_discount->setValue($request->getParam("disname_id"));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllDiscount();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_DISCOUNT")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_discount->setMultiOptions($opt);
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$start_date->setValue(date('Y-m-d'));
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$end_date->setValue(date('Y-m-d'));
		
		$_dismax = new Zend_Dojo_Form_Element_NumberTextBox('discountValue');
		$_dismax->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside','required'=>'true',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		if (!empty($data)){
			$_discount->setValue($data['discountType']);
			$_dismax->setValue($data['discountValue']);
			$_branch_id->setValue($data['branch_id']);
			$start_date->setValue($data['start_date']);
			$end_date->setValue($data['end_date']);
			$_status->setValue($data['status']);
			$discountOption->setValue($data['discountOption']);
			
		}
		$this->addElements(array(
				$discountOption,$DisValueType,$_dismax,$_discount,$_branch_id,$start_date,$end_date,$_status));
		return $this;
		
	}
}