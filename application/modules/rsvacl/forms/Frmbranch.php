<?php 
Class RsvAcl_Form_Frmbranch extends Zend_Dojo_Form {
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
		
		$br_id = new Zend_Dojo_Form_Element_TextBox('br_id');
		$br_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$br_code=Global_Model_DbTable_DbBranch::getBranchCode();
		$br_id->setValue($br_code);
		
		$branch_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('branch_namekh');
		$branch_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Calfifty()'
				));

		$branch_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('branch_nameen');
		$branch_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Caltweenty()'
				));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('main_branch_id');
		$branch_id->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$branch_id->setValue($request->getParam("main_branch_id"));
		$db = new Accounting_Model_DbTable_DbTuitionFee();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_BRANCH")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$branch_id->setMultiOptions($opt);
		
		$card_type = new Zend_Dojo_Form_Element_FilteringSelect('card_type');
		$card_type->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("CARD_TYPE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$card_type->setValue($request->getParam("card_type"));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllCardFormat();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_CARD_TYPE")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$card_type->setMultiOptions($opt);
		
		$branch_code = new Zend_Dojo_Form_Element_NumberTextBox('branch_code');
		$branch_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$db_code=Global_Model_DbTable_DbBranch::getBranchCode();
		$branch_code->setValue($db_code);
		
		$branch_tel = new Zend_Dojo_Form_Element_NumberTextBox('branch_tel');
		$branch_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
		
		$branch_tel1 = new Zend_Dojo_Form_Element_NumberTextBox('branch_tel1');
		$branch_tel1->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$email = new Zend_Dojo_Form_Element_NumberTextBox('email');
		$email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
		));
		
		$website = new Zend_Dojo_Form_Element_NumberTextBox('website');
		$website->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
		));
		
		$_fax = new Zend_Dojo_Form_Element_TextBox('fax ');
		$_fax->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calone()'
				));
		
		///*** result of calculator ///***
		$branch_note = new Zend_Dojo_Form_Element_TextBox('branch_note');
		$branch_note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		
		$prefix_code = new Zend_Dojo_Form_Element_TextBox('prefix_code');
		$prefix_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
		$branch_status = new Zend_Dojo_Form_Element_FilteringSelect('branch_status');
		$branch_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		$options = array( -1=>$this->tr->translate("ALL_STATUS"), 1=>$this->tr->translate("ACTIVE"), 0=>$this->tr->translate("DEACTIVE"));
		$branch_status->setMultiOptions($options);
		
		$branch_display = new Zend_Dojo_Form_Element_FilteringSelect('branch_display');
		$branch_display->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_EN"));
		$branch_display->setMultiOptions($_display_opt);
		
		$br_address = new Zend_Dojo_Form_Element_TextBox('br_address');
		$br_address->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$school_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('school_namekh');
		$school_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		
		$school_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('school_nameen');
		$school_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
	
		
		$color = new Zend_Dojo_Form_Element_TextBox('color');//Color Letter head
		$color->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		$color->setValue("2e3192");
		
		$centereys = new Zend_Dojo_Form_Element_TextBox('centereys');//Color Letter head
		$centereys->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		
		$officeeys = new Zend_Dojo_Form_Element_TextBox('officeeys');//Color Letter head
		$officeeys->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		
		$principal = new Zend_Dojo_Form_Element_TextBox('principal');
		$principal->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		
		$workat = new Zend_Dojo_Form_Element_TextBox('workat');//Color Letter head
		$workat->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		
		$id = new Zend_Form_Element_Hidden('id');
		$id->setAttribs(
				array('dojoType'=>'dijit.form.TextBox',)
		);
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$school_namekh->setValue($data['school_namekh']);
			$school_nameen->setValue($data['school_nameen']);
			
			$branch_id->setValue($data['parent']);
			$br_id->setValue($data['br_id']);
			$prefix_code->setValue($data['prefix']);
			$branch_namekh->setValue($data['branch_namekh']);
			$branch_nameen->setValue($data['branch_nameen']);
			
			$br_address->setValue($data['br_address']);
			$branch_tel->setValue($data['branch_tel']);
			$branch_tel1->setValue($data['branch_tel1']);
			$branch_code->setValue($data['branch_code']);
			$_fax->setValue($data['fax']);
			$email->setValue($data['email']);
			$website->setValue($data['website']);
			$branch_note->setValue($data['other']);
			$branch_status->setValue($data['status']);
			$branch_display->setValue($data['displayby']);
			$color->setValue($data['color']);
			$card_type->setValue($data['card_type']);
			
			$principal->setValue($data['principal']);
			$workat->setValue($data['workat']);
			$officeeys->setValue($data['officeeys']);
			$centereys->setValue($data['centereys']);
			
			$id->setValue($data['br_id']);
		}
		
		$this->addElements(array($principal,$workat,$officeeys,$centereys,$branch_tel1,$school_nameen,$school_namekh,$branch_id,$prefix_code,$_btn_search,$_title,$_status,$br_id,$branch_namekh,$website,$email,$branch_nameen,$br_address,$branch_code,$branch_tel,$_fax ,$branch_note,
				$branch_status,$branch_display,$card_type,
				$color,$id));
		
		return $this;
	}
}