<?php 
Class Library_Form_FrmCategory extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmCategory($data=null){
		$db = new Library_Model_DbTable_DbCategory();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$cat_name = new Zend_Dojo_Form_Element_ValidationTextBox('cat_name');
		$cat_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("CATEGORY_NAME")
		));
		$cat_name->setValue($request->getParam("cat_name"));
		
		$remark = new Zend_Dojo_Form_Element_TextBox('remark');
		$remark->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Remark")
		));
		$remark->setValue($request->getParam("remark"));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt=array(
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DEACTIVE"),
		);
		$status->setMultiOptions($opt);
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$status->setValue($request->getParam('status'));
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:98%;min-height:60px;'));
		$note->setValue($request->getParam('note'));
		
		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("parent");
		$_cateory_parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside'));
		$option = array("0"=>$this->tr->translate("IS_PARENT"));
		$result = $db->getCategory();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		if (!empty($data)){unset($option[$data['id']]);}
		$_cateory_parent->setMultiOptions($option);
		$_cateory_parent->setValue($request->getParam('parent'));
		
		$id = new Zend_Form_Element_Hidden("id");
		if($data!=null){
			//print_r($data);exit();
			$cat_name->setValue($data['name']);
			$_cateory_parent->setValue($data['parent_id']);
			$note->setValue($data['remark']);
			$status->setValue($data['status']);
		}
		$this->addElements(array($id,$cat_name,$remark,$status,$note,$_cateory_parent));
		return $this;
	}

	public function FrmBlock($data=null){
		$db = new Library_Model_DbTable_DbCategory();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$cat_name = new Zend_Dojo_Form_Element_ValidationTextBox('block_name');
		$cat_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("BLOCK_NAME")
		));
		$cat_name->setValue($request->getParam("cat_name"));
	
		$remark = new Zend_Dojo_Form_Element_TextBox('remark');
		$remark->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Remark")
		));
		$remark->setValue($request->getParam("remark"));
	
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt=array(
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DEACTIVE"),
		);
		$status->setMultiOptions($opt);
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$status->setValue($request->getParam('status'));
	
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:98%;min-height:60px;'));
		$note->setValue($request->getParam('note'));
	
		 
	
		$id = new Zend_Form_Element_Hidden("id");
		if($data!=null){
			//print_r($data);exit();
			$cat_name->setValue($data['block_name']);
			$note->setValue($data['remark']);
			$status->setValue($data['status']);
		}
		$this->addElements(array($id,$cat_name,$remark,$status,$note));
		return $this;
	}
	
}