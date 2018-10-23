<?php 
Class Global_Form_FrmAddClass extends Zend_Dojo_Form {
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
	public function FrmAddClass($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_classname = new Zend_Dojo_Form_Element_TextBox('classname');
		$_classname->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_floor = new Zend_Dojo_Form_Element_TextBox('floor');
		$_floor->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		$_student = new Zend_Dojo_Form_Element_TextBox('max_student');
		$_student->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				 1=>$this->tr->translate("ACTIVE"),
				 0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array('dojoType'=>$this->filter,
			//	'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_branch_id->setValue($request->getParam("branch_id"));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_BRANCH")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_branch_id->setMultiOptions($opt);
			
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		if(!empty($data)){
			$_classname->setValue($data['room_name']);
			$_branch_id->setValue($data['branch_id']);
			$_student->setValue($data['max_std']);
			$_floor->setValue($data['floor']);			
			$_status->setValue($data['is_active']);
		}
		$this->addElements(array($_branch_id,$_floor,$_classname,$_status,$_submit,$_student));		
		return $this;		
	}
	public function FrmAddGroup($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$_goup = new Zend_Dojo_Form_Element_TextBox('group_code');
		$_goup->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_time = new Zend_Dojo_Form_Element_TextBox('time');
		$_time->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
	
		$_note = new Zend_Dojo_Form_Element_Textarea('notes');
		$_note->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:80px;',));
		
		
		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false
		));
		$_academic->setValue($request->getParam("academic_year"));
		$db = new Global_Model_DbTable_DbGroup();
		$rows= $db->getAllYears();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		
		$session = new Zend_Dojo_Form_Element_FilteringSelect("session");
		$opt_session = array(
				1=>$tr->translate('MORNING'),
				2=>$tr->translate('AFTERNOON'),
				3=>$tr->translate('EVERNING'),
				4=>$tr->translate('WEEKEND'),
		);
		$session->setMultiOptions($opt_session);
		$session->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',));
		
		$_calture = new Zend_Dojo_Form_Element_FilteringSelect('calture');
		$_calture->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'required'=>false
		));
		$db = new Application_Model_DbTable_DbGlobal();
// 		$calture_opt = $db->getAllDegree();
		
		$calture_opt = array(""=>$this->tr->translate("PLEASE_SELECT_EDUCATION_LEVEL"));
		$optionDegree = $_dbgb->getAllDegreeMent(21);//Education Level
		if(!empty($optionDegree))foreach($optionDegree AS $row) $calture_opt[$row['id']]=$row['name'];
		$_calture->setMultiOptions($calture_opt);
			
		$id = new Zend_Form_Element_hidden('id');
		if($data!=null){
			$id->setValue($data['id']);
			$_branch_id->setValue($data['branch_id']);
			$_goup->setValue($data['group_code']);
			$_academic->setValue($data['academic_year']);
			$session->setValue($data['session']);
			$_calture->setValue($data['calture']);
			$_time->setValue($data['time']);
			//$_note->setValue($data['notes']);
		}
		$this->addElements(array($id,$_branch_id,$_academic,$_time,$_note,$session,$_calture,$_goup));
		return $this;
	}
	
	
}