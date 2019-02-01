<?php 
Class Library_Form_FrmBook extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function frmBook($data=null){
		$db = new Library_Model_DbTable_DbCategory();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$book_name = new Zend_Dojo_Form_Element_ValidationTextBox('book_name');
		$book_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'true',
				"autocomplete"=>'on',
				'placeholder'=>$this->tr->translate("BOOK_NAME")
		        ));
		
		
		$author_name = new Zend_Dojo_Form_Element_ValidationTextBox('author_name');
		$author_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("AUTHOR_NAME")
		         ));

		$serial_no = new Zend_Dojo_Form_Element_ValidationTextBox('serial_no');
		$serial_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SERIAL")
		));
		
		$barcode = new Zend_Dojo_Form_Element_ValidationTextBox('barcode');
		$barcode->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("BAR_CODE"),
		));
		
		$publisher = new Zend_Dojo_Form_Element_ValidationTextBox('publisher');
		$publisher->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("PUBLISHER")
		        ));
		
		$remark = new Zend_Dojo_Form_Element_TextBox('remarks');
		$remark->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Remark")
		        ));
		
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
		
		$note = new Zend_Dojo_Form_Element_TextBox('remark');
		$note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:98%;min-height:60px;'
				));
	 
		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("cat_id");
		$_cateory_parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'onChange'=>'showAddCat()'
				));
		$option = array("0"=>$this->tr->translate("SELECT_CATEGORY"),"-1"=>$this->tr->translate("ADD_NEW"));
		$result = $db->getCategory();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		$_cateory_parent->setMultiOptions($option);
		$_cateory_parent->setValue($request->getParam('cat_id'));
		
		$_block_id = new Zend_Dojo_Form_Element_FilteringSelect("block_id");
		$_block_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
				'onchange'=>'showAddBlock();'
		));
		$option = array("0"=>$this->tr->translate("SELECT_BLOCK"),"-1"=>$this->tr->translate("ADD_NEW"));
		$results = $db->getAllBlockName();
		if(!empty($results))foreach($results AS $row){
			$option[$row['id']]=$row['name'];
		}
		
		$_block_id->setMultiOptions($option);
		$_block_id->setValue($request->getParam('block_id'));
		
		$id = new Zend_Form_Element_Hidden("id");
		$_photo = new Zend_Form_Element_File('photo');
		$old_photo = new Zend_Form_Element_Hidden("old_photo");
		
		$note = new Zend_Dojo_Form_Element_ValidationTextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("NOTE")
		));
		
		if($data!=null){
			//print_r($data);exit();
			$book_name	->setValue($data['title']);
			$author_name->setValue($data['author']);
			$_cateory_parent->setValue($data['cat_id']);
			$publisher	->setValue($data['publisher']);
			$remark		->setValue($data['note']);
			$status		->setValue($data['status']);
			$old_photo	->setValue($data['photo']);
			$_block_id	->setValue($data['block_id']);
		}
		$this->addElements(array($barcode,$note,$_block_id,$old_photo,$id,$book_name,$author_name,$serial_no,$publisher,$_photo,
				                 $remark,$status,$note,$_cateory_parent));
		return $this;
	}	
	
}