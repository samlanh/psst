<?php

class Accounting_Form_FrmSpecail extends Zend_Dojo_Form
{
	protected  $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $textarea=null;
	protected $text;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->tvalidate = 'dijit.form.ValidationTextBox';
    	$this->filter = 'dijit.form.FilteringSelect';
    	$this->t_date = 'dijit.form.DateTextBox';
    	$this->t_num = 'dijit.form.NumberTextBox';
    	$this->text = 'dijit.form.TextBox';
    	$this->textarea = 'dijit.form.Textarea';
    }
    function FrmAddSpecailDocument($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$model = new Application_Model_DbTable_DbGlobal();
    	
    	$re_name = new Zend_Dojo_Form_Element_TextBox('request_name');
    	$re_name->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
    	
    	$phone = new Zend_Dojo_Form_Element_TextBox('phone');
    	$phone->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
    	
    	$stu_name = new Zend_Dojo_Form_Element_TextBox('stu_name');
    	$stu_name->setAttribs(array('dojoType'=>$this->tvalidate, 'class'=>'fullside','required'=>'true'));
    	
    	$expired_date = new Zend_Dojo_Form_Element_DateTextBox('expired_date');
    	$expired_date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
    	));
    	$expired_date->setValue(date('Y-m-d'));
    	
    	$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $model->getAllDiscount();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_dis_type = new Zend_Dojo_Form_Element_FilteringSelect("dis_type");
    	$_dis_type->setMultiOptions($_arr_opt);
    	$_dis_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	
    	$_duration = new Zend_Dojo_Form_Element_TextBox('duration');
    	$_duration->setAttribs(array('dojoType'=>$this->t_num,
					'placeholder'=>$this->tr->translate("PERIOD"),
    			'class'=>'fullside','required'=>'true'));
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$_duration_type = new Zend_Dojo_Form_Element_FilteringSelect("duration_type");
    	$_duration_type->setMultiOptions($_arr_opt);
    	$_duration_type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$duration_opt = $db->getAllPaymentTerm(null,null);
    	array_unshift($duration_opt, array ('0' =>$this->tr->translate("PLEASE_SELECT")));
    	$_duration_type->setMultiOptions($duration_opt);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr_opt);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$status_opt = $db->AllStatusRe(null);
    	$_status->setMultiOptions($status_opt);
    	
    	$note=  new Zend_Form_Element_Textarea('notes');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:70px !important;'));
    	
    	
    	$_save = new Zend_Dojo_Form_Element_Button('save');
    	$_save->setAttribs(array(
    			'dojoType'=>'dijit.form.Button',
    			'onclick'=>"dijit.byId('frm_add_tran').submit();",
    			'class'=>'dijitEditorIcon',
    	));
    	$_save->setLabel($this->tr->translate("ADD_NEW"));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	if(!empty($data)){
    		$re_name->setValue($data["request_name"]);
//     		if ($data["type"]==1){
//     			$_dis_type->setValue($data["schoolOption"]);
//     		}
    		$_dis_type->setValue($data["dis_type"]);
    		$stu_name->setValue($data["stu_name"]);
    		$_duration->setValue($data["duration"]);
    		$_duration_type->setValue($data["duration_type"]);
    		$expired_date->setValue($data["expired_date"]);
    		$phone->setValue($data["phone"]);
    		$note->setValue($data["notes"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    		
    	}
    	
    	$this->addElements(array(
    			$re_name,
    			$expired_date,
    			$_dis_type,
    			$note,
    			$_status,
    			$_duration,
    			$_duration_type,
    			$_save,
    			$phone,
    			$stu_name,
    			$id,
    			$advance_search,
    			$_status_search,
    			));
    	return $this;
    }
}

