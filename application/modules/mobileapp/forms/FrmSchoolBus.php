<?php 
Class Mobileapp_Form_FrmSchoolBus extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $date;
	protected $tarea=null;
	protected $texarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->date = 'dijit.form.DateTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
		$this->texarea = 'dijit.form.Textarea';
	}
	public function FrmAddSchoolBus($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_arr_opt_branch = array(""=>$tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'onChange'=>'getAllAcademicByBranch();',
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

		$_busCode= new Zend_Dojo_Form_Element_TextBox('busCode');
		$_busCode->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));

		$_password= new Zend_Dojo_Form_Element_PasswordTextBox('password');
		$_password->setAttribs(array(
			'dojoType'		=>$this->tvalidate,
			'required'		=>'true',
			'class'			=>'fullside',
			'invalidMessage'=>"ពាក្យ​សំងាត់យ៉ាង​តិច មាន 6 តួអក្សរ",
			'regExp'       => "\w{6,}"
		));

		$_confirm_password= new Zend_Dojo_Form_Element_PasswordTextBox('confirm_password');
		$_confirm_password->setAttribs(array(
			'dojoType'		=>$this->tvalidate,
			'required'		=>'true',
			'class'			=>'fullside',
			'invalidMessage'=>"ពាក្យ​សំងាត់យ៉ាង​តិច មាន 6 តួអក្សរ",
			'regExp'       => "\w{6,}"
		));

		$_busType= new Zend_Dojo_Form_Element_FilteringSelect('busType');
		$_busType->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_busType_pot = array(
				1=>$this->tr->translate("ឡានក្រុង"),
				2=>$this->tr->translate("ឡានតូរីស"),
			);
		$_busType->setMultiOptions($_busType_pot);

		$_driverId=  new Zend_Dojo_Form_Element_FilteringSelect('driverId');
		$_driverId->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
		));
		$_driver_pot = array(
				1=>$this->tr->translate("Sokha"),
				2=>$this->tr->translate("DaWin"));
		$_driverId->setMultiOptions($_driver_pot);

		$_busPlateNo= new Zend_Dojo_Form_Element_TextBox('busPlateNo');
		$_busPlateNo->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));

		$_note= new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'required'=>'false',
			'class'=>'fullside',
		));

		$id = new Zend_Form_Element_Hidden("id");

		$create_date = new Zend_Dojo_Form_Element_DateTextBox('create_date');
		$create_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$create_date->setValue($_date);
		
		if($data!=null){
			$_branch_id		->setValue($data['branchId']);
			$id				->setValue($data['id']);
			$create_date	->setValue($data['createDate']);
			$_busCode		->setValue($data['busCode']);
			$_busPlateNo	->setValue($data['busPlateNo']);
			$_busType		->setValue($data['busType']);
			$_driverId		->setValue($data['driverId']);
			$_note			->setValue($data['note']);
		}
		
		$this->addElements(array(
		$id,
		$_branch_id,
		$create_date,
		$_busPlateNo,
		$_driverId,
		$_busType,
		$_busCode,
		$_note,
		$_password,
		$_confirm_password
		));
		return $this;
	}	

	public function FrmSchoolBusSchedule($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_arr_opt_branch = array(""=>$tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'onChange'=>'getAllStudenBus();',
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

		$_arr_opt_bus = array(""=>$tr->translate("PLEASE_SELECT"));
		$db = new Mobileapp_Model_DbTable_DbStudentBus();
		$optBus = $db->getStudentBus();
		if(!empty($optBus))foreach($optBus AS $row) $_arr_opt_bus[$row['id']]=$row['name'];
		$_school_bus = new Zend_Dojo_Form_Element_FilteringSelect("school_bus");
		$_school_bus->setMultiOptions($_arr_opt_bus);
		$_school_bus->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'onChange'=>'getAllAcademicByBranch();',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false'));
		if (count($optBus)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}

		$id = new Zend_Form_Element_Hidden("id");

		$create_date = new Zend_Dojo_Form_Element_DateTextBox('create_date');
		$create_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$create_date->setValue($_date);
		
		if($data!=null){
		
			if(!empty($data['branch_id'])){
				$_branch_id	->setValue($data['branch_id']);
				$_branch_id->setAttribs(array('readonly'=>'readonly'));
			}
		}
		
		$this->addElements(array(
		$id,
		$_branch_id,
		$create_date,
	
		));
		return $this;
	}	
	
}