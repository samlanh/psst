<?php 
Class Foundation_Form_FrmFamily extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
	protected $t_date=null;
	protected $t_num=null;
	protected $text=null;
	protected $textarea=null;
	protected $_degree=null;
	protected $_khname=null;
	protected $_enname=null;
	protected $_phone=null;
	protected $_batch=null;
	protected $_year=null;
	protected $_session=null;
	protected $_dob=null;
	protected $_pay_date=null;
	public function init()
	{
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
	}
	public function FrmFrmFamily($data=null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$fatherName = new Zend_Dojo_Form_Element_ValidationTextBox('fatherName');
		$fatherName->setAttribs(
			array(
				'dojoType'=>$this->tvalidate
				,'class'=>'fullside'
				,'required'=>'true'
		));
		
		$fatherNameKh = new Zend_Dojo_Form_Element_ValidationTextBox('fatherNameKh');
		$fatherNameKh->setAttribs(
			array(
				'dojoType'=>$this->tvalidate
				,'class'=>'fullside'
				,'required'=>'true'
		));
		
		$fatherPhone = new Zend_Dojo_Form_Element_TextBox('fatherPhone');
		$fatherPhone->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'012345678',
				'class'=>'fullside'
			));
		
		$fatherDob = new Zend_Dojo_Form_Element_DateTextBox('fatherDob');
		$date = date("1990-m-d");
		$fatherDob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				//'required'=>true
				));
		//$fatherDob->setValue($date);
		
		
		$motherName = new Zend_Dojo_Form_Element_ValidationTextBox('motherName');
		$motherName->setAttribs(
			array(
				'dojoType'=>$this->text
				,'class'=>'fullside'
		));
		
		$motherNameKh = new Zend_Dojo_Form_Element_ValidationTextBox('motherNameKh');
		$motherNameKh->setAttribs(
			array(
				'dojoType'=>$this->text
				,'class'=>'fullside'
		));
		
		$motherPhone = new Zend_Dojo_Form_Element_TextBox('motherPhone');
		$motherPhone->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'012345678',
				'class'=>'fullside'
			));
		
		$motherDob = new Zend_Dojo_Form_Element_DateTextBox('motherDob');
		$date = date("1990-m-d");
		$motherDob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				//'required'=>true
				));
		//$motherDob->setValue($date);
		
		
		$guardianName = new Zend_Dojo_Form_Element_ValidationTextBox('guardianName');
		$guardianName->setAttribs(
			array(
				'dojoType'=>$this->text
				,'class'=>'fullside'
		));
		
		$guardianNameKh = new Zend_Dojo_Form_Element_ValidationTextBox('guardianNameKh');
		$guardianNameKh->setAttribs(
			array(
				'dojoType'=>$this->text
				,'class'=>'fullside'
		));
		
		$guardianPhone = new Zend_Dojo_Form_Element_TextBox('guardianPhone');
		$guardianPhone->setAttribs(array(
				'dojoType'=>$this->text,
				'placeHolder'=>'012345678',
				'class'=>'fullside'
			));
		
		$guardianDob = new Zend_Dojo_Form_Element_DateTextBox('guardianDob');
		$date = date("1990-m-d");
		$guardianDob->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				//'required'=>true
				));
		//$motherDob->setValue($date);
		
		
		$street = new Zend_Dojo_Form_Element_TextBox('street');
		$street->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
				
		$houseNo = new Zend_Dojo_Form_Element_TextBox('houseNo');
		$houseNo->setAttribs(array('dojoType'=>$this->text,
				'class'=>'fullside'));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
				1=>$tr->translate('ACTIVE'),
				0=>$tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		
		
		
		$note = new Zend_Dojo_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>$this->textarea,'class'=>'fullside',
				'style'=>'min-height: 65px !important;',
		));
		
		$id = new Zend_Form_Element_hidden('id');
		$id->setAttribs(array('dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'));
		
		if($data!=null){
			
			$fatherName->setValue($data['fatherName']);
			$fatherNameKh->setValue($data['fatherNameKh']);
			$fatherPhone->setValue($data['fatherPhone']);
			
			if (!empty($data['fatherDob']) AND $data['fatherDob']!='0000-00-00'){
				$fatherDob->setValue($data['fatherDob']);
			}
			$motherName->setValue($data['motherName']);
			$motherNameKh->setValue($data['motherNameKh']);
			$motherPhone->setValue($data['motherPhone']);
			
			if (!empty($data['motherDob']) AND $data['motherDob']!='0000-00-00'){
				$motherDob->setValue($data['motherDob']);
			}
			
			$guardianName->setValue($data['guardianName']);
			$guardianNameKh->setValue($data['guardianNameKh']);
			$guardianPhone->setValue($data['guardianPhone']);
			
			if (!empty($data['guardianDob']) AND $data['guardianDob']!='0000-00-00'){
				$guardianDob->setValue($data['guardianDob']);
			}
			
			$street->setValue($data['street']);
			$houseNo->setValue($data['houseNo']);
			$status->setValue($data['status']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
			
		}
	
		$this->addElements(
				array(	
						$fatherName
						,$fatherNameKh
						,$fatherPhone
						,$fatherDob
						
						,$motherName
						,$motherNameKh
						,$motherPhone
						,$motherDob
						
						,$guardianName
						,$guardianNameKh
						,$guardianPhone
						,$guardianDob
						
						,$street
						,$houseNo
						,$status
						,$note
						,$id
						
						
						)
				);
		
		return $this;
	}	
}