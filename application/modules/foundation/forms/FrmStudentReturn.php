<?php 
Class Foundation_Form_FrmStudentReturn extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $textarea=null;
	//protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
		//$this->check='dijit.form.CheckBox';
	}
	
	public function FrmStudentReturn($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
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
		
		
	
		$_note = new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:80px;',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false,
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));

		$_return_type=  new Zend_Dojo_Form_Element_FilteringSelect('return_type');
		$_return_type->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>true,
		));
		$_type_opt = array(
				2=>$this->tr->translate("RETURN_TO_GROUP"),
				1=>$this->tr->translate("RETURN_TO_TEST"),
			);
		$_return_type->setMultiOptions($_type_opt);
		
		$_academic->setValue($request->getParam("academic_year"));
		$rows =  $_dbgb->getAllAcademicYear();

		$opt=array();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		
		
		$return_date = new Zend_Dojo_Form_Element_DateTextBox('return_date');
    	$date = date("Y-m-d");
    	$return_date->setAttribs(array(
    			'data-dojo-Type'=>"dijit.form.DateTextBox",
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',
    			'required'=>true));
    	$return_date->setValue($date);
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'onchange'=>'getallGrade();'
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree = $_dbgb->getAllItems(1);//degree
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
	
		$stu_id = new Zend_Form_Element_hidden('stu_id');
		$stu_id->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
		
		$groupId = new Zend_Form_Element_hidden('groupId');
		$groupId->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
		
		$degreeId = new Zend_Form_Element_hidden('degreeId');
		$degreeId->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
		
		$gradeId = new Zend_Form_Element_hidden('gradeId');
		$gradeId->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
			
		$id = new Zend_Form_Element_hidden('id');
		$id->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside'));
		
		if($data!=null){
			$id->setValue($data['id']);
			$_branch_id->setValue($data['branchId']);
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			$_degree->setAttribs(array('readonly'=>'readonly'));
			
			$_degree->setValue($data['degree']);
			$return_date->setValue($data['returnDate']);
			$_note->setValue($data['note']);
			$_academic->setValue($data['academicYear']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array(
			$_branch_id,
			$id,
			$return_date,
			$_degree,$_status,$_academic,$_note
			,$stu_id
			,$groupId
			,$degreeId
			,$gradeId
			,$_return_type
			));
		return $this;
	}
	
	
}