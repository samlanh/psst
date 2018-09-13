<?php 
Class Registrar_Form_Frmexpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddExpense($data=null){
		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
 				'required'=>true
				));
		
		$receiver = new Zend_Dojo_Form_Element_ValidationTextBox('receiver');
		$receiver->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		
		$cheque_num = new Zend_Dojo_Form_Element_ValidationTextBox('cheque_num');
		$cheque_num->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$options= array(1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10",11=>"11",12=>"12");
		$for_date->setMultiOptions($options);
		
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();'
		));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
		
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),2=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
		
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly'
		));
		
		
		$total_income=new Zend_Dojo_Form_Element_NumberTextBox('total_income');
		$total_income->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		
		
		$convert_to_dollar=new Zend_Dojo_Form_Element_NumberTextBox('convert_to_dollar');
		$convert_to_dollar->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				//'required'=>true
		));
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red;',
		));
// 		$_db = new Application_Model_DbTable_DbGlobal();
// 		$invoice_no= $_db->getExpenseRecieptNo();
// 		$db = new Registrar_Model_DbTable_DbIncome();
// 		$invoice_no = $db->getInvoiceNo();
// 		$invoice->setValue($invoice_no);
		
		$id = new Zend_Form_Element_Hidden("id");
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				
		));
		$opt = $db->getViewById(8,1);
		$payment_method->setMultiOptions($opt);
	
		if($data!=null){
			$payment_method->setValue($data['payment_type']);
			$_branch_id->setValue($data['branch_id']);
			$title->setValue($data['title']);
			$receiver->setValue($data['receiver']);
			$total_amount->setValue($data['total_amount']);
			$total_income->setValue($data['total_amount']);
			$cheque_num->setValue($data['cheque_no']);
			$_Description->setValue($data['description']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array($cheque_num,$invoice,$payment_method,$title,$_Date ,$receiver,$_stutas,$_Description,$total_income,
				$total_amount,$convert_to_dollar,$_branch_id,$for_date,$id,));
		return $this;
		
	}	
}