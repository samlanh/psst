<?php 
Class Library_Form_FrmBookreturn extends Zend_Dojo_Form {
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
				'placeholder'=>$this->tr->translate("Book Name")
		        ));
		
		$book_id = new Zend_Dojo_Form_Element_ValidationTextBox('book_id');
		$book_id->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Book Name")
				));
		$db_book=new Library_Model_DbTable_DbBook();
		$b_id=$db_book->getBookNo();
		$book_id->setValue($b_id);
		
		$author_name = new Zend_Dojo_Form_Element_ValidationTextBox('author_name');
		$author_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Author Name")
		         ));

		$serial_no = new Zend_Dojo_Form_Element_ValidationTextBox('serial_no');
		$serial_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Serial Number")
		        ));
		
		$publisher = new Zend_Dojo_Form_Element_ValidationTextBox('publisher');
		$publisher->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Publisher Number")
		        ));
		
		$qty = new Zend_Dojo_Form_Element_NumberTextBox('qty');
		$qty->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true','class'=>'fullside',
		        ));
		
		$unit_price = new Zend_Dojo_Form_Element_NumberTextBox('unit_price');
		$unit_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				));
		
		$remark = new Zend_Dojo_Form_Element_TextBox('remarks');
		$remark->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Remark")
		        ));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("statuss");
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
		
		$tel = new Zend_Dojo_Form_Element_TextBox('tel');
		$tel->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:98%;min-height:60px;'
		));
	 
		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("parent_id");
		$_cateory_parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
				'onChange'=>'showAddCat()'
				));
		$option = array("0"=>$this->tr->translate("SELECT_CATEGORY"),"-1"=>$this->tr->translate("ADD_NEW"));
		$result = $db->getCategory();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		if (!empty($data)){unset($option[$data['id']]);}
		$_cateory_parent->setMultiOptions($option);
		$_cateory_parent->setValue($request->getParam('parent'));
		
		$id = new Zend_Form_Element_Hidden("id");
		$_photo = new Zend_Form_Element_File('photo');
		$old_photo = new Zend_Form_Element_Hidden("old_photo");
		
		if($data!=null){
			
			$book_name	->setValue($data['title']);
			$book_id	->setValue($data['book_no']);
			$author_name->setValue($data['author']);
			$serial_no	->setValue($data['serial_no']);
			$_cateory_parent->setValue($data['cat_id']);
			$publisher	->setValue($data['publisher']);
			$qty		->setValue($data['qty']);
			$unit_price	->setValue($data['unit_price']);
			$remark		->setValue($data['note']);
			$status		->setValue($data['status']);
			$note		->setValue($data['note']);
			$old_photo	->setValue($data['photo']);
		}
		$this->addElements(array($old_photo,$id,$book_name,$book_id,$author_name,$serial_no,$publisher,$_photo,
				                 $tel,$qty,$unit_price,$remark,$status,$note,$_cateory_parent));
		return $this;
	}	
	
}