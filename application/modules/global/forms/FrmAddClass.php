<?php 
Class Global_Form_FrmAddClass extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	//protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		//$this->check='dijit.form.CheckBox';
	}
	public function FrmAddClass($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_classname = new Zend_Dojo_Form_Element_TextBox('classname');
		$_classname->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_floor = new Zend_Dojo_Form_Element_TextBox('floor');
		$_floor->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				2=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array('dojoType'=>$this->filter,
			//	'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$_branch_id->setValue($request->getParam("branch_id"));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('br_id'=>'','branch_namekh'=>$this->tr->translate("SELECT_LOCATION")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['br_id']]=$row['branch_namekh'];
		$_branch_id->setMultiOptions($opt);
			
		$_submit = new Zend_Dojo_Form_Element_SubmitButton('submit');
		$_submit->setLabel("save"); 
		if(!empty($data)){
			$_classname->setValue($data['room_name']);
			$_branch_id->setValue($data['branch_id']);
			$_floor->setValue($data['floor']);
			$_status->setValue($data['is_active']);
		}
		$this->addElements(array($_branch_id,$_floor,$_classname,$_status,$_submit));		
		return $this;		
	}
	
}