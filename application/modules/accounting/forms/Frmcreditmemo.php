<?php 
Class Accounting_Form_Frmcreditmemo extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function Frmcreditmemo($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$student_id = new Zend_Dojo_Form_Element_FilteringSelect('student_id');
		$student_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				//'readOnly'=>'readOnly',
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(1)'
				));
		$optstu = $db->getAllStudent(1,1);
		$student_id->setMultiOptions($optstu);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$stu_id = new Zend_Dojo_Form_Element_FilteringSelect('stu_idto');
		$stu_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(1)'
		));
		$optstu = $db->getAllStudent(1,1);
		$stu_id->setMultiOptions($optstu);
		
		$student_name = new Zend_Dojo_Form_Element_FilteringSelect('student_name');
		$student_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				//'readOnly'=>'readOnly',
				'autoComplete'=>"true",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$student_name->setMultiOptions($optstu);
		
		$stu_name = new Zend_Dojo_Form_Element_FilteringSelect('stu_name');
		$stu_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"true",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$stu_name->setMultiOptions($optstu);
		
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				//'readOnly'=>'readOnly',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				//'readOnly'=>'readOnly',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddate->setValue(date('Y-m-d'));
		
		$_enddates = new Zend_Dojo_Form_Element_DateTextBox('end_dates');
		$_enddates->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddates->setValue(date('Y-m-d'));
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$start_date->setValue(date('Y-m-d'));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				//'readOnly'=>'readOnly',
				'onchange'=>'filterClient();'
		));
		
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
		
		$prob=new Zend_Dojo_Form_Element_TextBox('prob');
		$prob->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'readOnly'=>'readOnly',
				'required'=>true,
		));
		
		$problem=new Zend_Dojo_Form_Element_TextBox('problem');
		$problem->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				//'readOnly'=>'readOnly',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
		
		$Description_s = new Zend_Dojo_Form_Element_Textarea('Descriptions');
		$Description_s ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				//'readOnly'=>'readOnly',
				'required'=>true,
		));
		
		$total_am=new Zend_Dojo_Form_Element_NumberTextBox('total_amountall');
		$total_am->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$student_id->setValue($data['student_id']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['note']);
			$prob->setValue($data['prob']);
			$_Date->setValue($data['date']);
			$_enddate->setValue($data['end_date']);
			$_stutas->setValue($data['status']);
			
			$problem->setValue($data['problem']);
			$Description_s->setValue($data['Descriptions']);
			$stu_id->setValue($data['stu_idto']);
			
			$id->setValue($data['id']);
		}
		$this->addElements(array($student_name,$student_id,$stu_name,$_enddates,$problem,$total_am,$Description_s,$start_date,$_Date,$stu_id,$_stutas,$prob,$_enddate,$_Description,
				$total_amount,$_branch_id,$for_date,$id,));
		return $this;
		
	}
	public function Frmcreditmemoadd($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$student_id = new Zend_Dojo_Form_Element_FilteringSelect('student_id');
		$student_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$student_id->setMultiOptions($optstu);
	
// 		$student_name = new Zend_Dojo_Form_Element_FilteringSelect('student_name');
// 		$student_name->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'autoComplete'=>"true",
// 				'queryExpr'=>'*${0}*',
// 				'onchange'=>'setSelected(2)'
// 		));
// 		$optstu = $db->getAllStudent(1,2);
// 		$student_name->setMultiOptions($optstu);
	
		$stu_name = new Zend_Dojo_Form_Element_FilteringSelect('stu_name');
		$stu_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"true",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$stu_name->setMultiOptions($optstu);
	
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
	
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddate->setValue(date('Y-m-d'));
		 
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();'
		));
	
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
	
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
	
		$prob=new Zend_Dojo_Form_Element_TextBox('prob');
		$prob->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true,
		));
	
		 
	
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
	
		$Description_s = new Zend_Dojo_Form_Element_Textarea('Descriptions');
		$Description_s ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
	
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
	
		$id = new Zend_Form_Element_Hidden("id");
	
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$student_id->setValue($data['student_id']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['note']);
			$prob->setValue($data['prob']);
			$_Date->setValue($data['date']);
			$_enddate->setValue($data['end_date']);
			$_stutas->setValue($data['status']);
			$id->setValue($data['id']);
		}
		$this->addElements(array($student_id,$Description_s,$_Date,$_stutas,$prob,$_enddate,$_Description,
				$total_amount,$_branch_id,$id,));
		return $this;
	
	}
	public function Frmcreditmemotran($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$student_id = new Zend_Dojo_Form_Element_FilteringSelect('student_id');
		$student_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'readOnly'=>'readOnly',
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(1)'
		));
		$optstu = $db->getAllStudent(1,1);
		$student_id->setMultiOptions($optstu);
	
		$db = new Application_Model_DbTable_DbGlobal();
		$stu_id = new Zend_Dojo_Form_Element_FilteringSelect('stu_idto');
		$stu_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(1)'
		));
		$optstu = $db->getAllStudent(1,1);
		$stu_id->setMultiOptions($optstu);
	
		$student_name = new Zend_Dojo_Form_Element_FilteringSelect('student_name');
		$student_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'autoComplete'=>"true",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$student_name->setMultiOptions($optstu);
	
		$stu_name = new Zend_Dojo_Form_Element_FilteringSelect('stu_name');
		$stu_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"true",
				'queryExpr'=>'*${0}*',
				'onchange'=>'setSelected(2)'
		));
		$optstu = $db->getAllStudent(1,2);
		$stu_name->setMultiOptions($optstu);
	
	
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
	
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'readOnly'=>'readOnly',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
	
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'readOnly'=>'readOnly',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddate->setValue(date('Y-m-d'));
	
		$_enddates = new Zend_Dojo_Form_Element_DateTextBox('end_dates');
		$_enddates->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddates->setValue(date('Y-m-d'));
	
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$start_date->setValue(date('Y-m-d'));
	
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'onchange'=>'filterClient();'
		));
	
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
	
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
	
		$prob=new Zend_Dojo_Form_Element_TextBox('prob');
		$prob->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>true,
		));
	
		$problem=new Zend_Dojo_Form_Element_TextBox('problem');
		$problem->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
	
		$Description_s = new Zend_Dojo_Form_Element_Textarea('Descriptions');
		$Description_s ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
	
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>true,
		));
	
		$total_am=new Zend_Dojo_Form_Element_NumberTextBox('total_amountall');
		$total_am->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$id = new Zend_Form_Element_Hidden("id");
	
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$student_id->setValue($data['student_id']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['note']);
			$prob->setValue($data['prob']);
			$_Date->setValue($data['date']);
			$_enddate->setValue($data['end_date']);
			$_stutas->setValue($data['status']);
			$id->setValue($data['id']);
		}
		$this->addElements(array($student_name,$student_id,$stu_name,$_enddates,$problem,$total_am,$Description_s,$start_date,$_Date,$stu_id,$_stutas,$prob,$_enddate,$_Description,
				$total_amount,$_branch_id,$for_date,$id,));
		return $this;
	
	}	
}