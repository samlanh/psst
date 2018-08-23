<?php 
Class Accounting_Form_FrmDiscount extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	protected $tarea;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		//$this->tarea = 'dijit.form.Textarea';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmDiscountsetting($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_discount = new Zend_Dojo_Form_Element_FilteringSelect('disname_id');
		$_discount->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
				 
		));
		$degree_opt = $db->getAllDegree();
		$_discount->setMultiOptions($degree_opt);
		
		$_discount->setValue($request->getParam("disname_id"));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllDiscount();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_DISCOUNT")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$_discount->setMultiOptions($opt);
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$start_date->setValue(date('Y-m-d'));
		
		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$end_date->setValue(date('Y-m-d'));
		
		$_dismax = new Zend_Dojo_Form_Element_NumberTextBox('dis_max');
		$_dismax->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside','required'=>'true',));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		if (!empty($data)){
			$_discount->setValue($data['disname_id']);
			$_dismax->setValue($data['dis_max']);
			$end_date->setValue($data['start_date']);
			$start_date->setValue($data['end_date']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($_dismax,$_discount,$start_date,$end_date,$_status));
		
		return $this;
		
	}
	
	
}