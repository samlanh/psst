<?php 
Class Setting_Form_FrmGeneral extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->check='dijit.form.CheckBox';
	}
	public function FrmGeneral($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		
		$_branch_tel = new Zend_Dojo_Form_Element_TextBox('branch_tel');
		$_branch_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Tel Client")
		));
		$_branch_email = new Zend_Dojo_Form_Element_TextBox('branch_email');
		$_branch_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Email Client")
		));
		$_branch_add = new Zend_Dojo_Form_Element_TextBox('branch_add');
		$_branch_add->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Address")
		));
		
		$_label_animation = new Zend_Dojo_Form_Element_TextBox('label_animation');
		$_label_animation->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("label_animation")
		));
		
		$_receipt_print = new Zend_Dojo_Form_Element_NumberTextBox('receipt_print');
		$_receipt_print->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("RECEIPT_PRINT")
		));
		
		$_show_header_receipt=  new Zend_Dojo_Form_Element_FilteringSelect('show_header_receipt');
		$_show_header_receipt->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_show_header_receipt_opt = array(
				1=>$this->tr->translate("SHOW"),
				0=>$this->tr->translate("HIDE"));
		$_show_header_receipt->setMultiOptions($_show_header_receipt_opt);
		
		$_payment_day_alert = new Zend_Dojo_Form_Element_NumberTextBox('payment_day_alert');
		$_payment_day_alert->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$_payment_day_alert->setValue(1);

		$_test_period = new Zend_Dojo_Form_Element_NumberTextBox('test_period');
		$_test_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		
		
		$_trasfer_st_cut=  new Zend_Dojo_Form_Element_FilteringSelect('trasfer_st_cut');
		$_trasfer_st_cut->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_trasfer_st_cut_opt = array(
				0=>$this->tr->translate("CUTSTOCK_DIRECT"),
				1=>$this->tr->translate("CUTSTOCK_WITH_RECEIVED_NOTE"));
		$_trasfer_st_cut->setMultiOptions($_trasfer_st_cut_opt);
		
		$_sale_stock=  new Zend_Dojo_Form_Element_FilteringSelect('sale_stock');
		$_sale_stock->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_salestock_opt = array(
				0=>$this->tr->translate("SALE_STOCK_DIRECT"),
				1=>$this->tr->translate("CUTSTOCK_WITH_STORE"));
		$_sale_stock->setMultiOptions($_salestock_opt);
		
		$settingStuID=  new Zend_Dojo_Form_Element_FilteringSelect('settingStuID');
		$settingStuID->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$settingStuIDOpt = array(
				1=>$this->tr->translate("COUNT_BY_STUDENT"),
				2=>$this->tr->translate("COUNT_BY_DEGREE"),
				3=>$this->tr->translate("COUNT_BY_SCHOOL_OPT"),
				);
		$settingStuID->setMultiOptions($settingStuIDOpt);
		
		$schooolNameKh = new Zend_Dojo_Form_Element_TextBox('schooolNameKh');
		$schooolNameKh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SCHOOL_NAMEKH")
		));
		
		$schooolNameEng = new Zend_Dojo_Form_Element_TextBox('schooolNameEng');
		$schooolNameEng->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SCHOOL_NAMEEN")
		));
		
		$hornorTableSetting=  new Zend_Dojo_Form_Element_FilteringSelect('hornorTableSetting');
		$hornorTableSetting->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$hornorTableSettingOpt = array(
				1=>$this->tr->translate("SHOW_3_STUDENTS"),
				2=>$this->tr->translate("SHOW_5_STUDENTS"),
				);
		$hornorTableSetting->setMultiOptions($hornorTableSettingOpt);

		$_discount_percent = new Zend_Dojo_Form_Element_NumberTextBox('discount_percent');
		$_discount_percent->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("PERCENT"),
				'onKeyup' => 'CompareA()'
		));
		
		$_discount_amount = new Zend_Dojo_Form_Element_NumberTextBox('discount_amount');
		$_discount_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("DISCOUNT_AMOUNT"),
				'onKeyup' => 'CompareDis()'
		));
		
		$_other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$_other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("other fee"),
				'onKeyup' => 'CompareOtherFee()'
		));

		$newStuIdTest=  new Zend_Dojo_Form_Element_FilteringSelect('new_stuid_test');
		$newStuIdTest->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$newStuIDOpt = array(
				0=>$this->tr->translate("Default"),
				1=>$this->tr->translate("show stu_id payment to enter"),
				);
		$newStuIdTest->setMultiOptions($newStuIDOpt);

		$docDisplay=  new Zend_Dojo_Form_Element_FilteringSelect('doc_display');
		$docDisplay->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$docDOpt = array(
				0=>$this->tr->translate("HIDE"),
				1=>$this->tr->translate("SHOW"),
				);
		$docDisplay->setMultiOptions($docDOpt);
		
		$nameRequired=  new Zend_Dojo_Form_Element_FilteringSelect('name_required');
		$nameRequired->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$reOPt = array(
				0=>$this->tr->translate("NOT_REQUIRED"),
				1=>$this->tr->translate("REQUIRED"),
				);
		$nameRequired->setMultiOptions($reOPt);

		$entryStuId=  new Zend_Dojo_Form_Element_FilteringSelect('entry_stuid');
		$entryStuId->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$entryStuIDOpt = array(
				0=>$this->tr->translate("Disable"),
				1=>$this->tr->translate("Can Entry Student Id"),
				);
		$entryStuId->setMultiOptions($entryStuIDOpt);

		$groupPayment=  new Zend_Dojo_Form_Element_FilteringSelect('pay_as_group');
		$groupPayment->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$gOpt = array(
				0=>$this->tr->translate("DEFAULT"),
				1=>$this->tr->translate("PAYMENT_AS_GROUP"),
				);
		$groupPayment->setMultiOptions($gOpt);

		$picDisplay=  new Zend_Dojo_Form_Element_FilteringSelect('show_pic_receipt');
		$picDisplay->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$picOpt = array(
				0=>$this->tr->translate("HIDE"),
				1=>$this->tr->translate("SHOW"),
				);
		$picDisplay->setMultiOptions($picOpt);

		$testOnline=  new Zend_Dojo_Form_Element_FilteringSelect('test_online');
		$testOnline->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$testOpt = array(
				0=>$this->tr->translate("HIDE"),
				1=>$this->tr->translate("SHOW"),
				);
		$testOnline->setMultiOptions($testOpt);
		
		$show_groupin_payment=  new Zend_Dojo_Form_Element_FilteringSelect('show_groupin_payment');
		$show_groupin_payment->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$show_groupin_payment->setMultiOptions($docDOpt);
		
		$paddingTopReceipt = new Zend_Dojo_Form_Element_NumberTextBox('receipt_paddingtop');
		$paddingTopReceipt->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
		));
		
		
		$StudentIdLength = new Zend_Dojo_Form_Element_NumberTextBox('studentIdLength');
		$StudentIdLength->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>'Length'
		));
		
		$studentIPrefix = new Zend_Dojo_Form_Element_TextBox('studentIPrefix');
		$studentIPrefix->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true',
				'placeholder'=>'Prefix'
		));
		
		$studentPrefixOpt = new Zend_Dojo_Form_Element_FilteringSelect('studentPrefixOpt');
		$studentPrefixOpt->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$countStuIDOpt = array(
				1=>$this->tr->translate("BY_BRANCH"),
				2=>$this->tr->translate("BY_DEGREE"),
				3=>$this->tr->translate("BY_SCHOOL_OPTION"),
				4=>$this->tr->translate("BY_ENTRY"),
		);
		$studentPrefixOpt->setMultiOptions($countStuIDOpt);

		$docTeacher=  new Zend_Dojo_Form_Element_FilteringSelect('teacher_doc');
		$docTeacher->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$teachOpt = array(
				0=>$this->tr->translate("HIDE"),
				1=>$this->tr->translate("SHOW"),
				);
		$docTeacher->setMultiOptions($teachOpt);

		$paymentDate=  new Zend_Dojo_Form_Element_FilteringSelect('payment_date');
		$paymentDate->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$paydateOpt = array(
				0=>$this->tr->translate("DISABLE"),
				1=>$this->tr->translate("ENABLE"),
				);
		$paymentDate->setMultiOptions($paydateOpt);

		$studydaySchedule=  new Zend_Dojo_Form_Element_FilteringSelect('studyday_schedule');
		$studydaySchedule->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$dayOpt = array(
				0=>$this->tr->translate("DEFAULT"),
				1=>$this->tr->translate("MON_SAT"),
				2=>$this->tr->translate("MON_FRI"),
				);
		$studydaySchedule->setMultiOptions($dayOpt);
		
		
		if($data!=null){
			$_sale_stock->setValue($data['sale_cut_stock']['keyValue']);
			$_branch_add->setValue($data['branch_add']['keyValue']);
			$_branch_email->setValue($data['branch_email']['keyValue']);
			$_branch_tel->setValue($data['branch_tel']['keyValue']);
			$_label_animation->setValue($data['label_animation']['keyValue']);
			$_receipt_print->setValue($data['receipt_print']['keyValue']);
			$_show_header_receipt->setValue($data['show_header_receipt']['keyValue']);
			$_payment_day_alert->setValue($data['payment_day_alert']['keyValue']);
			$_test_period->setValue($data['test_period']['keyValue']);
			$_discount_percent->setValue($data['discount_percent']['keyValue']);
			$_discount_amount->setValue($data['discount_amount']['keyValue']);
			$_other_fee->setValue($data['other_fee']['keyValue']);
			$_trasfer_st_cut->setValue($data['trasfer_st_cut']['keyValue']);
			$settingStuID->setValue($data['settingStuID']['keyValue']);
			
			$schooolNameKh->setValue($data['schooolNameKh']['keyValue']);
			$schooolNameEng->setValue($data['schooolNameEng']['keyValue']);
			$hornorTableSetting->setValue($data['hornorTableSetting']['keyValue']);

			$newStuIdTest->setValue($data['new_stuid_test']['keyValue']);
			$docDisplay->setValue($data['doc_display']['keyValue']);
			$nameRequired->setValue($data['name_required']['keyValue']);
			$entryStuId->setValue($data['entry_stuid']['keyValue']);
			$groupPayment->setValue($data['pay_as_group']['keyValue']);
			$picDisplay->setValue($data['show_pic_receipt']['keyValue']);
			$testOnline->setValue($data['test_online']['keyValue']);
			$show_groupin_payment->setValue($data['show_groupin_payment']['keyValue']);
			$paddingTopReceipt->setValue($data['receipt_paddingtop']['keyValue']);
			
			$studentPrefixOpt->setValue($data['studentPrefixOpt']['keyValue']);
			$studentIPrefix->setValue($data['studentIPrefix']['keyValue']);
			$StudentIdLength->setValue($data['studentIdLength']['keyValue']);
			$docTeacher->setValue($data['teacher_doc']['keyValue']);
			$paymentDate->setValue($data['payment_date']['keyValue']);
			$studydaySchedule->setValue($data['studyday_schedule']['keyValue']);

		}
		$this->addElements(array(
				$studentPrefixOpt,
				$studentIPrefix,
				$StudentIdLength,
				$paddingTopReceipt,
				$show_groupin_payment,
				$_sale_stock
				,$_label_animation
				,$_branch_tel
				,$_branch_email
				,$_branch_add
				,$_receipt_print
				,$_show_header_receipt
				,$_payment_day_alert
				,$_trasfer_st_cut
				,$settingStuID
				,$schooolNameKh
				,$schooolNameEng
				,$hornorTableSetting
				,$_test_period
				,$_discount_percent
				,$_discount_amount
				,$_other_fee
				,$newStuIdTest
				,$docDisplay
				,$nameRequired
				,$entryStuId
				,$groupPayment
				,$picDisplay
				,$testOnline
				,$docTeacher
				,$paymentDate
				,$studydaySchedule
				));
		
		return $this;
	}
	
}