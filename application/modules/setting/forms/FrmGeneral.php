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
				2=>$this->tr->translate("COUNT_BY_SCHOOL_OPT"),
				3=>$this->tr->translate("COUNT_BY_DEGREE"),
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
		
		if($data!=null){
			$_sale_stock->setValue($data['sale_cut_stock']['keyValue']);
			$_branch_add->setValue($data['branch_add']['keyValue']);
			$_branch_email->setValue($data['branch_email']['keyValue']);
			$_branch_tel->setValue($data['branch_tel']['keyValue']);
			$_label_animation->setValue($data['label_animation']['keyValue']);
			$_receipt_print->setValue($data['receipt_print']['keyValue']);
			$_show_header_receipt->setValue($data['show_header_receipt']['keyValue']);
			$_payment_day_alert->setValue($data['payment_day_alert']['keyValue']);
			$_trasfer_st_cut->setValue($data['trasfer_st_cut']['keyValue']);
			$settingStuID->setValue($data['settingStuID']['keyValue']);
			
			$schooolNameKh->setValue($data['schooolNameKh']['keyValue']);
			$schooolNameEng->setValue($data['schooolNameEng']['keyValue']);
		}
		$this->addElements(array(
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
				));
		
		return $this;
		
	}
	
}