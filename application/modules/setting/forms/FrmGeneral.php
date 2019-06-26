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
		
		$_show_header_receipt=  new Zend_Dojo_Form_Element_FilteringSelect('show_header_receipt');
		$_show_header_receipt->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_show_header_receipt_opt = array(
				1=>$this->tr->translate("SHOW"),
				0=>$this->tr->translate("HIDE"));
		$_show_header_receipt->setMultiOptions($_show_header_receipt_opt);
		
		
		if($data!=null){
			
			$_branch_add->setValue($data['branch_add']['keyValue']);
			$_branch_email->setValue($data['branch_email']['keyValue']);
			$_branch_tel->setValue($data['branch_tel']['keyValue']);
			$_label_animation->setValue($data['label_animation']['keyValue']);
			$_show_header_receipt->setValue($data['show_header_receipt']['keyValue']);
			
			
		}
		$this->addElements(array(
				$_label_animation,
				$_branch_tel,
				$_branch_email,
				$_branch_add,
				$_show_header_receipt,
				
				));
		
		return $this;
		
	}
	
}