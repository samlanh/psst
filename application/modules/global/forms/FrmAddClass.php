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
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'class'=>'fullside height-text',));
		
		$_goup = new Zend_Dojo_Form_Element_TextBox('group_code');
		$_goup->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_time = new Zend_Dojo_Form_Element_TextBox('time');
		$_time->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
	
		$_note = new Zend_Dojo_Form_Element_Textarea('notes');
		$_note->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:80px;',));
		
		$_reason = new Zend_Dojo_Form_Element_Textarea('reason');
		$_reason->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:80px;',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$degree->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getallGrade();getStudentNo()',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		$rs_degree = $_dbgb->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
		
		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false,
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_academic->setValue($request->getParam("academic_year"));
		$db = new Global_Model_DbTable_DbGroup();
		$rows= $db->getAllYears(1);
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false,
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
		
		$_type->setValue($request->getParam("type"));
		$db = new Foundation_Model_DbTable_DbStudentDrop();
		$rows= $db->getAllDropType();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_TYPE")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)
			{
				$opt[$row['id']]=$row['name'];
			}
		$_type->setMultiOptions($opt);
		
		$room =  new Zend_Dojo_Form_Element_FilteringSelect('room');
		$room->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		
		));
		$rs_roow = $_dbgb->getAllRoom();
		$arr_room = array(-1=>$tr->translate("SELECT_ROOM"));
		if(!empty($rs_roow))foreach($rs_roow AS $row) $arr_room[$row['id']]=$row['name'];
		$room->setMultiOptions($arr_room);
		
		$session = new Zend_Dojo_Form_Element_FilteringSelect("session");
		$session->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				));
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
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('gender');
		$_sex->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
		
		
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
		
		$calture_opt = array(""=>$this->tr->translate("PLEASE_SELECT_EDUCATION_LEVEL"));
		$optionDegree = $_dbgb->getAllDegreeMent(21);//Education Level
		if(!empty($optionDegree))foreach($optionDegree AS $row) $calture_opt[$row['id']]=$row['name'];
		$_calture->setMultiOptions($calture_opt);
		
		$is_pass = new Zend_Dojo_Form_Element_FilteringSelect('is_pass');
		$is_pass->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'required'=>false
		));
		$opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$rs = $db->getViewById(9);
		if(!empty($rs))foreach($rs AS $row) $opt[$row['id']]=$row['name'];
		$is_pass->setMultiOptions($opt);
		
			
		$id = new Zend_Form_Element_hidden('id');
		if($data!=null){
			$id->setValue($data['id']);
			$_branch_id->setValue($data['branch_id']);
 			$_goup->setValue($data['group_code']);
			$session->setValue($data['session']);
			$_calture->setValue($data['calture']);
			$_time->setValue($data['time']);
			$_note->setValue($data['note']);
		}
		$this->addElements(array($is_pass,$id,$degree,$_status,$_sex,$_sex,$_reason,$_type,$room,$_branch_id,$_academic,$_time,$_note,$session,$_calture,$_goup));
		return $this;
	}
	
	public function FrmAddDrup($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array('readOnly'=>'readOnly',
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
	
		$_reason = new Zend_Dojo_Form_Element_Textarea('reason');
		$_reason->setAttribs(array('dojoType'=>$this->textarea,'class'=>'fullside','style'=>'min-height:80px;',));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
	
		$degree =  new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$degree->setAttribs(array('readOnly'=>'readOnly',
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getallGrade();getStudentNo()',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
	
		));
		$rs_degree = $_dbgb->getAllFecultyName();
		$arr_opt = array();
		if(!empty($rs_degree))foreach($rs_degree AS $row) $arr_opt[$row['id']]=$row['name'];
		$degree->setMultiOptions($arr_opt);
	
		$_academic = new Zend_Dojo_Form_Element_FilteringSelect('academic_year');
		$_academic->setAttribs(array('dojoType'=>$this->filter,'readOnly'=>'readOnly',
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false,
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
	
		$_academic->setValue($request->getParam("academic_year"));
		$db = new Global_Model_DbTable_DbGroup();
		$rows= $db->getAllYears();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_YEAR")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_academic->setMultiOptions($opt);
	
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_type->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false,
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
		));
	
		$_type->setValue($request->getParam("type"));
		$db = new Foundation_Model_DbTable_DbStudentDrop();
		$rows= $db->getAllDropType();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_TYPE")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_type->setMultiOptions($opt);
	
		$room =  new Zend_Dojo_Form_Element_FilteringSelect('room');
		$room->setAttribs(array('readOnly'=>'readOnly',
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
	
		));
		$rs_roow = $_dbgb->getAllRoom();
		$arr_room = array(-1=>$tr->translate("SELECT_ROOM"));
		if(!empty($rs_roow))foreach($rs_roow AS $row) $arr_room[$row['id']]=$row['name'];
		$room->setMultiOptions($arr_room);
	
		$session = new Zend_Dojo_Form_Element_FilteringSelect("session");
		$session->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside','readOnly'=>'readOnly',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',));
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
		$_sex =  new Zend_Dojo_Form_Element_FilteringSelect('gender');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				'readOnly'=>'readOnly'));
		$sex_opt = array(
				1=>$tr->translate("MALE"),
				2=>$tr->translate("FEMALE"));
		$_sex->setMultiOptions($sex_opt);
	
	
		$_calture = new Zend_Dojo_Form_Element_FilteringSelect('calture');
		$_calture->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside','readOnly'=>'readOnly',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'required'=>false
		));
		$db = new Application_Model_DbTable_DbGlobal();
	
		$calture_opt = array(""=>$this->tr->translate("PLEASE_SELECT_EDUCATION_LEVEL"));
		$optionDegree = $_dbgb->getAllDegreeMent(21);//Education Level
		if(!empty($optionDegree))foreach($optionDegree AS $row) $calture_opt[$row['id']]=$row['name'];
		$_calture->setMultiOptions($calture_opt);
			
		$id = new Zend_Form_Element_hidden('id');
		if($data!=null){
			$id->setValue($data['id']);
			$_branch_id->setValue($data['branch_id']);
			$_type->setValue($data['type']);
			$_branch_id->setValue($data['branch_id']);
			$_academic->setValue($data['academic_year']);
			$session->setValue($data['session']);
			$_calture->setValue($data['calture']);
			$degree->setValue($data['degree']);
			$degree->setValue($data['degree']);
			$_sex->setValue($data['gender']);
			$room->setValue($data['room']);
			$_reason->setValue($data['reason']);
		}
		$this->addElements(array($id,$degree,$_status,$_sex,$_reason,$_type,$room,$_branch_id,$_academic,$_time,$_note,$session,$_calture,$_goup));
		return $this;
	}
}