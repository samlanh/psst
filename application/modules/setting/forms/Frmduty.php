<?php 
Class Setting_Form_Frmduty extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function Frmbranch($data=null){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				 1=>$this->tr->translate("ACTIVE"),
				 0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'value'=>' Search ',
		
		));
		
		$duty_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('duty_namekh');
		$duty_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'false',
				
				));

		$duty_nameen = new Zend_Dojo_Form_Element_TextBox('duty_nameen');
		$duty_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'false',
				));

		$position_kh = new Zend_Dojo_Form_Element_TextBox('positionkh');
		$position_kh->setAttribs(array(
			'dojoType'=>'dijit.form.ValidationTextBox',
			'class'=>'fullside',
			'required'=>'false',
			
			));

		$position_en = new Zend_Dojo_Form_Element_ValidationTextBox('positionen');
		$position_en->setAttribs(array(
			'dojoType'=>'dijit.form.ValidationTextBox',
			'class'=>'fullside',
			'required'=>'false',
		
		));

		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'true',
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree = $_dbgb->getAllItems(1);//degree
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);

		$_type=  new Zend_Dojo_Form_Element_FilteringSelect('type');
		$_type->setAttribs(array(
			'dojoType'=>$this->filter,
			"class"=>"fullside",
			'required'=>'true',
		));
		$_type_opt = array(
				''=>$this->tr->translate("PLEASE_SELECT_DEPARTMENT"),
				 1=>$this->tr->translate("PRINCIPAL_PART"),
				 2=>$this->tr->translate("ADMIN_PART"));
		$_type->setMultiOptions($_type_opt);
		$_type->setValue($request->getParam("type"));

		$old_prin_stamp = new Zend_Form_Element_Hidden('old_prin_stamp');
		$old_prin_sign = new Zend_Form_Element_Hidden('old_prin_sign');


		$id = new Zend_Form_Element_Hidden('id');
		$id->setAttribs(
				array('dojoType'=>'dijit.form.TextBox',)
		);
		
		if(!empty($data)){
			
			$duty_namekh->setValue($data['duty_namekh']);
			$duty_nameen->setValue($data['duty_nameen']);
			$position_kh->setValue($data['positionkh']);
			$position_en->setValue($data['positionen']);

			$old_prin_stamp->setValue($data['stamp']);
			$old_prin_sign->setValue($data['signature']);
			$_degree->setValue($data['degree']);
			$_type->setValue($data['type']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array(
			$id,
			$_title,
			$_btn_search ,
			$_status,
			$duty_namekh,
			$duty_nameen,
			$position_kh,
			$position_en,
			$_type,
			$old_prin_stamp,
			$old_prin_sign,
			$_degree 
		));
		
		return $this;
	}
}