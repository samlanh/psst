<?php 
Class Global_Form_FrmCal extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmCalculator($data=null){
		$_hundred = new Zend_Dojo_Form_Element_TextBox('d_hundred');
		$_hundred->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calcuhundred()'));
		
		$_fifty = new Zend_Dojo_Form_Element_TextBox('d_fifty');
		$_fifty->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfifty()'));

		$_tweenty = new Zend_Dojo_Form_Element_TextBox('d_tweenty');
		$_tweenty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltweenty()'));
		
		$_ten = new Zend_Dojo_Form_Element_TextBox('d_ten');
		$_ten->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calten()'));
		
		$_five = new Zend_Dojo_Form_Element_TextBox('d_five');
		$_five->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfive()'));
		
		$_one = new Zend_Dojo_Form_Element_TextBox('d_one');
		$_one->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calone()'));
		
		///*** result of calculator ///***
		$rs_hundred = new Zend_Dojo_Form_Element_TextBox('rs_hundred');
		$rs_hundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fifty = new Zend_Dojo_Form_Element_TextBox('rs_fifty');
		$rs_fifty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tweenty = new Zend_Dojo_Form_Element_TextBox('rs_tweenty');
		$rs_tweenty->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_ten = new Zend_Dojo_Form_Element_TextBox('rs_ten');
		$rs_ten->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		$rs_five = new Zend_Dojo_Form_Element_TextBox('rs_five');
		$rs_five->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		$rs_one = new Zend_Dojo_Form_Element_TextBox('rs_one');
		$rs_one->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside','readonly'=>true));
		
		//**control khmer currency**//
		$_fiftyhousend = new Zend_Dojo_Form_Element_TextBox('r_fiftyhousend');
		$_fiftyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfiftyhousend()'));
		
		$_tenthousend = new Zend_Dojo_Form_Element_TextBox('r_tenthousend');
		$_tenthousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltenthousend()'));
		
		$_fivehousend = new Zend_Dojo_Form_Element_TextBox('r_fivehousend');
		$_fivehousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfivehousend()'));
		
		$_tweentyhousend = new Zend_Dojo_Form_Element_TextBox('r_tweentyhousend');
		$_tweentyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Caltweentyhousend()'));
		
		$_thousend = new Zend_Dojo_Form_Element_TextBox('thousend');
		$_thousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calthousend()'));
		
		$_fivehundred = new Zend_Dojo_Form_Element_TextBox('r_fivehundred');
		$_fivehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfivehundred()'));
		
		$_onehundred = new Zend_Dojo_Form_Element_TextBox('r_onehundred');
		$_onehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calonehundred()'));
		//**rs khmer currency**//
		
		$rs_fiftyhousend = new Zend_Dojo_Form_Element_TextBox('rs_fiftyhousend');
		$rs_fiftyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tenthousend = new Zend_Dojo_Form_Element_TextBox('rs_tenthousend');
		$rs_tenthousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fivehousend = new Zend_Dojo_Form_Element_TextBox('rs_fivehousend');
		$rs_fivehousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_tweentyhousend = new Zend_Dojo_Form_Element_TextBox('rs_tweentyhousend');
		$rs_tweentyhousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_thousend = new Zend_Dojo_Form_Element_TextBox('rs_thousend');
		$rs_thousend->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_fivehundred = new Zend_Dojo_Form_Element_TextBox('rs_fivehundred');
		$rs_fivehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_onehundred = new Zend_Dojo_Form_Element_TextBox('rs_onehundred');
		$rs_onehundred->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_totalkh = new Zend_Dojo_Form_Element_TextBox('rs_kh_total');
		$rs_totalkh->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$rs_dollar_total = new Zend_Dojo_Form_Element_TextBox('rs_dollar_total');
		$rs_dollar_total->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'required'=>1,
				'readonly'=>true));
		
		$reil_to_dollar = new Zend_Dojo_Form_Element_TextBox('total_reil_dollar');
		$reil_to_dollar->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'readonly'=>true));
		
		$_rate = new Zend_Dojo_Form_Element_TextBox('rate');
		$_rate->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside',
				'onkeyup'=>'Calfiftyhousend()'
				));
		$db = new Accounting_Model_DbTable_DbExchangeRate();
		$datavalue = $db->getExchangeRate();
		$_rate->setValue($datavalue['reil']);		
		
		$amount_total = new Zend_Dojo_Form_Element_TextBox('total_amount');
		$amount_total->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','class'=>'fullside black',
				'readonly'=>true,'required'=>1,));
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside'));
		
		$date = new Zend_Dojo_Form_Element_TextBox('date');
		$date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}"));
		$id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_rate->setValue($data['exchange_rate']);
			$_hundred->setValue($data['dollar_100']);
			$_fifty->setValue($data['dollar_50']);
			$_tweenty->setValue($data['dollar_20']);
			$_ten->setValue($data['dollar_10']);
			$_five->setValue($data['dollar_5']);
			$_one->setValue($data['dollar_1']);
			
			$rs_hundred->setValue($data['dollar_100']*100);
			$rs_fifty->setValue($data['dollar_50']*50);
			$rs_tweenty->setValue($data['dollar_20']);
			$rs_ten->setValue($data['dollar_10']*10);
			$rs_five->setValue($data['dollar_5']*5);
			$rs_one->setValue($data['dollar_1']*1);
			
			$_fiftyhousend->setValue($data['reil_50000']);
			$_tenthousend->setValue($data['reil_10000']);
		    $_fivehousend->setValue($data['reil_5000']);
			$_tweentyhousend->setValue($data['reil_2000']);
			$_thousend->setValue($data['reil_1000']);
			$_fivehundred->setValue($data['reil_500']);
			$_onehundred->setValue($data['reil_100']);
			
			$rs_fiftyhousend->setValue($data['reil_50000']*50000);
			$rs_tenthousend->setValue($data['reil_10000']*10000);
			$rs_fivehousend->setValue($data['reil_5000']*5000);
			$rs_tweentyhousend->setValue($data['reil_2000']*2000);
			$rs_thousend->setValue($data['reil_1000']*1000);
			$rs_fivehundred->setValue($data['reil_500']*500);
			$rs_onehundred->setValue($data['reil_100']*100);
			
			$rs_totalkh->setValue($data['total_reil']);
			$rs_dollar_total->setValue($data['total_dollar']);
			$reil_to_dollar->setValue($data['dollar_fromreil']);
			$_rate->setValue($data['reil_100']);
			$amount_total->setValue($data['tototal_all']);
			$date->setValue($data['input_date']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
		}
		$this->addElements(array($date,$note,$id,
			  $_hundred, $_fifty, $_tweenty, $_ten,$_five, $_one,
			  $rs_hundred, $rs_fifty, $rs_tweenty, $rs_ten,$rs_five, $rs_one,
			  $_fiftyhousend,$_tenthousend, $_fivehousend, $_tweentyhousend, $_thousend, $_fivehundred, $_onehundred,
			  $rs_fiftyhousend,$rs_tenthousend, $rs_fivehousend, $rs_tweentyhousend, $rs_thousend, $rs_fivehundred, $rs_onehundred,
			  $rs_totalkh, $rs_dollar_total,$reil_to_dollar,$_rate,$amount_total
			  ));
		return $this;
	}
}