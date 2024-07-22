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
		
		
		$familyCode = new Zend_Dojo_Form_Element_ValidationTextBox('familyCode');
		$familyCode->setAttribs(
			array(
				'dojoType'=>$this->tvalidate
				,'class'=>'fullside text-primary'
				,'required'=>'true'
				,'readOnly'=>'true'
				,'style'=>'color:blue;'
				
		));
		
		$familyType = new Zend_Dojo_Form_Element_FilteringSelect("familyType");	
		$familyType->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$condictionArr = array("addonsItem"=>"");
		$familyType->setMultiOptions($_db->getViewByType(41,1,$condictionArr));
		
		$laonNumber = new Zend_Dojo_Form_Element_ValidationTextBox('laonNumber');
		$laonNumber->setAttribs(
			array(
				'dojoType'=>$this->text
				,'class'=>'fullside'
		));
		
		
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
				'dojoType'=>$this->tvalidate
				,'placeHolder'=>'012345678'
				,'class'=>'fullside'
				,'required'=>'true'
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
				
		$rs_province = $_db->getAllProvince();
		$province_opt =array() ;
		if(!empty($rs_province))foreach($rs_province AS $row) $province_opt[$row['id']]=$row['name'];
			
		$provinceId = new Zend_Dojo_Form_Element_FilteringSelect("provinceId");
		$provinceId->setMultiOptions($province_opt);
		$provinceId->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		
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
			
			$familyCode->setValue($data['familyCode']);
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
			$provinceId->setValue($data['provinceId']);
			
			$familyType->setValue($data['familyType']);
			$laonNumber->setValue($data['laonNumber']);
			
		}
	
		$this->addElements(
				array(	
						$familyCode
						,$fatherName
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
						
						,$provinceId
						,$familyType
						,$laonNumber
						
						)
				);
		
		return $this;
	}	
	function FrmSearchFamily($_data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$adv_search->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$tr->translate("SEARCH")));
		$adv_search->setValue($request->getParam("adv_search"));
		
		$familyType = new Zend_Dojo_Form_Element_FilteringSelect("familyType");	
		$familyType->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$condictionArr = array("addonsItem"=>"");
		$familyType->setMultiOptions($_db->getViewByType(41,1,$condictionArr));
		$familyType->setValue($request->getParam("familyType"));
		
		$rs_province = $_db->getAllProvince();
		$province_opt =array(
		""=>$tr->translate("PLEASE_SELECT")
		);
		if(!empty($rs_province))foreach($rs_province AS $row) $province_opt[$row['id']]=$row['name'];
			
		$provinceId = new Zend_Dojo_Form_Element_FilteringSelect("provinceId");
		$provinceId->setMultiOptions($province_opt);
		$provinceId->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'filterDistrict();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$provinceId->setValue($request->getParam("provinceId"));
		
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$tr->translate("START_DATE")
				));
		$_date = $request->getParam("start_date");
		
		if(!empty($_date)){
			$start_date->setValue($_date);
		}
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
			'dojoType'=>"dijit.form.DateTextBox",
			'class'=>'fullside',
			'required'=>'true',
			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
			'placeholder'=>$tr->translate("END_DATE")
				));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>'false',
				'placeholder'=>$tr->translate("ALL_STATUS"),
				));
		$_status_opt = array(
				-1=>$tr->translate("ALL_STATUS"),
				1=>$tr->translate("ACTIVE"),
				0=>$tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$this->addElements(
				array(	
						$adv_search
						,$familyType
						,$provinceId
						,$start_date
						,$end_date
						,$_status
						
						
						)
				);
		
		return $this;
	}	
}