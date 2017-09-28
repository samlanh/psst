<?php 
Class Accounting_Form_FrmCustomerPayment extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function frmCustomer($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		
		$old_cus = new Zend_Dojo_Form_Element_CheckBox('old_cus');
		$old_cus->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				//'class'=>'fullside',
				));
		
		$cus_id = new Zend_Dojo_Form_Element_TextBox('cus_id');
		$cus_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				//'class'=>'fullside',
				));
		$cus_name = new Zend_Dojo_Form_Element_ValidationTextBox('cus_name');
		$cus_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				//'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Book No"),
				'required'=>'true',
		));
		
		$cus_sex = new Zend_Dojo_Form_Element_FilteringSelect('cus_sex');
		$cus_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				//'class'=>'fullside',
		));
		$opt_sex = array(1=>$this->tr->translate('Male'),2=>$this->tr->translate('Female'));
		$cus_sex->setMultiOptions($opt_sex);
		
		$cus_phone = new Zend_Dojo_Form_Element_TextBox('cus_phone');
		$cus_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$email = new Zend_Dojo_Form_Element_TextBox('email');
		$email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$address = new Zend_Dojo_Form_Element_Textarea('address');
		$address ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
		
		$cus_start_date= new Zend_Dojo_Form_Element_DateTextBox('cus_start_date');
		$cus_start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$cus_start_date->setValue(date('Y-m-d'));
		
		$cus_end_date= new Zend_Dojo_Form_Element_DateTextBox('cus_end_date');
		$cus_end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$cus_end_date->setValue(date('Y-m-d'));
		$id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
			
		}
		$this->addElements(array($old_cus,$cus_id,$cus_name ,$cus_phone,$cus_sex,
				$cus_start_date,$cus_end_date,$address,$email,));
		return $this;
		
	}	
}