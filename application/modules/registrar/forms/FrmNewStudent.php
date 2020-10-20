<?php 
Class Registrar_Form_FrmNewStudent extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
	protected $t_date=null;
	protected $t_num=null;
	protected $text=null;
	protected $textarea=null;
	protected $_degree=null;
	protected $_khname=null;
	protected $_enname=null;
	protected $_phone=null;
	protected $_batch=null;
	protected $_year=null;
	protected $_session=null;
	protected $_dob=null;
	protected $_pay_date=null;
	public function init()
	{
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
	}
	
	public function FrmAddNewStudent($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		
		$_arr_opt_branch = array(""=>$tr->translate("PLEASE_SELECT"));
		$optionBranch = $_db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		
		$stu_khname = new Zend_Dojo_Form_Element_ValidationTextBox('stu_khname');
		$stu_khname->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
 				'required'=>true
				));
				
		$name_en = new Zend_Dojo_Form_Element_ValidationTextBox('name_en');
		$name_en->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>OTHER_LANG_REQUIRED,
				'class'=>'fullside'));
		
		$last_name = new Zend_Dojo_Form_Element_ValidationTextBox('last_name');
		$last_name->setAttribs(array('dojoType'=>$this->tvalidate,
				'required'=>OTHER_LANG_REQUIRED,
				'class'=>'fullside'));
		
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
		
		$phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$phone->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'012 345 678',
				'class'=>'fullside'
			));
		
		$email = new Zend_Dojo_Form_Element_TextBox('email');
		$email->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'example@email.com',
				'class'=>'fullside'
			));
			
		$home_note = new Zend_Dojo_Form_Element_TextBox('home_note');
		$home_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$way_note = new Zend_Dojo_Form_Element_TextBox('way_note');
		$way_note->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
				
		$rs_province = $_db->getAllProvince();
		$province_opt = array();
		if(!empty($rs_province))foreach($rs_province AS $row) $province_opt[$row['id']]=$row['name'];
			
		$_student_province = new Zend_Dojo_Form_Element_FilteringSelect("student_province");
		$_student_province->setMultiOptions($province_opt);
		$_student_province->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
		$remark = new Zend_Dojo_Form_Element_Textarea('remark');
		$remark->setAttribs(array(
				'dojoType'=>$this->textarea,'class'=>'fullside',
				'style'=>'min-height: 65px !important;',
		));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
				1=>$tr->translate('ACTIVE'),
				0=>$tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		
		$id = new Zend_Form_Element_Hidden("id");
		
		$student_id = new Zend_Dojo_Form_Element_TextBox('student_id');
		$student_id->setAttribs(array('dojoType'=>$this->tvalidate,
				'class'=>'fullside',
				'required'=>'true',
				'style'=>'color: red;',
				'readOnly'=>'readOnly',
		));
		
		if($data!=null){
			
			$_branch_id->setValue($data['branch_id']);
			$student_id->setValue($data['stu_code']);
			
			$stu_khname->setValue($data['stu_khname']);
			$name_en->setValue($data['stu_enname']);
			$last_name->setValue($data['last_name']);
			$_sex->setValue($data['sex']);
			$phone->setValue($data['tel']);
			$email->setValue($data['email']);
			$_student_province->setValue($data['province_id']);
			
			$home_note->setValue($data['home_num']);
			$way_note->setValue($data['street_num']);
			
			$remark->setValue($data['remark']);
			$status->setValue($data['status']);
			
			$id->setValue($data['stu_id']);
		}
		
		$this->addElements(array(
			$_branch_id,
			$student_id,
				
				
			$stu_khname,
			$name_en,
			$last_name,
			$_sex,
			$phone,
			$email,
			
			$home_note,
			$way_note,
			$_student_province,
			
			$remark,
			$status,
		
			$id,
		));
		return $this;
		
	}	
}