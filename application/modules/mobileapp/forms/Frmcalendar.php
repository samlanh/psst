<?php 
Class Mobileapp_Form_Frmcalendar extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $date;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->date = 'dijit.form.DateTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmAddHoliday($_data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH_HILIDAY_INFO")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('search_status');
		$_status_search->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("search_status"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
		
		));
		
		$_holiday_name = new Zend_Dojo_Form_Element_TextBox('holiday_name');
		$_holiday_name->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_title_en = new Zend_Dojo_Form_Element_TextBox('title_en');
		$_title_en->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_startdate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_startdate->setAttribs(array('dojoType'=>$this->date,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");

		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$_startdate->setValue($_date);
		
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array('dojoType'=>$this->date,'required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_enddate->setValue($_date);
		
		$_amount_day = new Zend_Dojo_Form_Element_NumberTextBox('amount_day');
		$_amount_day->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','required'=>'true',
				'class'=>'fullside',
				'onkeyup'=>'CalculateDate();',
				));
		$_amount_day->setValue(1);
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		
		$_type_holiday=  new Zend_Dojo_Form_Element_FilteringSelect('type_holiday');
		$_type_holiday->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_type_holiday_opt = array(
				1=>$this->tr->translate("Every Year"),
				2=>$this->tr->translate("One Time Only"));
		$_type_holiday->setMultiOptions($_type_holiday_opt);
		
		$rows = $_db->getAllFecultyNamess(1);
		$opt = '' ;
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['name'];
		 
		$_dept = new Zend_Dojo_Form_Element_FilteringSelect("dept");
		$_dept->setMultiOptions($opt);
		$_dept->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
			));
		$calendarType=  new Zend_Dojo_Form_Element_FilteringSelect('calendarType');
		$calendarType->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("HOLIDAY"),
				2=>$this->tr->translate("SCHOOL_EVENT"),
				3=>$this->tr->translate("EXAMINATION"),
				4=>$this->tr->translate("COMPETITION"),
				);
		$calendarType->setMultiOptions($_status_opt);
		$calendarType->setValue($request->getParam("calendarType"));
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($_data)){
			$_holiday_name->setValue($_data['title']);
			$_title_en->setValue($_data['title_en']);
			$_startdate->setValue($_data['start_date']);
			$_amount_day->setValue($_data['amount_day']);
			$_enddate->setValue($_data['end_date']);
			$_status->setValue($_data['active']);
// 			$_id->setValue($_data['id']);
			$_id->setValue($_data['keycode']);
			$_note->setValue($_data['description']);
			$_dept->setValue($_data['dept']);
			$_type_holiday->setValue($_data['type_holiday']);
			$calendarType->setValue($_data['calendarType']);
		}
		$this->addElements(
				array(
					$_btn_search
					,$_status_search
					,$_title,$_id
					,$_holiday_name
					,$_title_en
					,$_note
					,$_startdate
					,$_enddate
					,$_amount_day
					,$_status
					,$_dept
					,$_type_holiday
					,$calendarType
				)
			);
		return $this;
	}
	
}