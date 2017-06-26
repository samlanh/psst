<?php 
Class Library_Form_FrmSearchMajor extends Zend_Dojo_Form{
	protected $tr = null;
	protected $btn =null;//text validate
	protected $filter = null;
	protected $text =null;
	protected $validate = null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->btn = 'dijit.form.Button';
		$this->validate = 'dijit.form.ValidationTextBox';
	}
	public function FrmMajors($_data=null){
		$db = new Library_Model_DbTable_DbCategory();
		$db_s=new Library_Model_DbTable_DbNeardayreturnbook();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_INFO")));
		$_title->setValue($request->getParam("title"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$is_full=  new Zend_Dojo_Form_Element_FilteringSelect('is_full');
		$is_full->setAttribs(array('dojoType'=>$this->filter,));
		$_opt = array(
				-1=>$this->tr->translate("SELECT_IS_FULL"),
				1=>$this->tr->translate("COMPLETE"),
				0=>$this->tr->translate("NOT_COMPLETE"));
		$is_full->setMultiOptions($_opt);
		$is_full->setValue($request->getParam("is_full"));
		
// 		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("parent");
// 		$_cateory_parent->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required'=>'true',
// 				 ));
// 		$option = array("0"=>$this->tr->translate("SELECT_CATEGORY"));
// 		$result = $db->getCategory();
		 
// 		if(!empty($result))foreach($result AS $row){
// 			$option[$row['id']]=$row['name'];
// 		}
// 		if (!empty($_data)){
// 			unset($option[$data['id']]);
// 		}
        
		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("parent");
		$_cateory_parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				));
		$option = array("0"=>$this->tr->translate("SELECT_CATEGORY"));
		$result = $db->getCategory();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		if (!empty($_data)){
			unset($option[$data['id']]);
		}
		$_cateory_parent->setMultiOptions($option);
		$_cateory_parent->setValue($request->getParam('parent'));
		
		$cood_book = new Zend_Dojo_Form_Element_FilteringSelect("cood_book");
		$cood_book->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$option = array("0"=>$this->tr->translate("SELECT_BOOK"));
		$result = $db_s->getBookIdName();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		$cood_book->setMultiOptions($option);
		$cood_book->setValue($request->getParam('cood_book'));
		
		$stu_name = new Zend_Dojo_Form_Element_FilteringSelect("stu_name");
		$stu_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$option = array("0"=>$this->tr->translate("SELECT_STUDEN_NAME"));
		$db_stu=new Library_Model_DbTable_DbNeardayreturnbook();
		$result = $db_stu->getAllStudentId(2);
		//print_r($result);exit();
		if(!empty($result))foreach($result AS $row){
			$option[$row['stu_id']]=$row['name'];
		}
		$stu_name->setMultiOptions($option);
		$stu_name->setValue($request->getParam('stu_name'));
		
		//date
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$dates = date("Y-m-d");
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'required'=>false));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$start_date->setValue($_date);
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		$this->addElements(array($stu_name,$is_full,$cood_book,$start_date,$end_date,$_cateory_parent,$_title,$_status));
		
		return $this;
	}
	
	public function frmSearchReturn($_data=null){
		$db = new Library_Model_DbTable_DbCategory();
		$db_s=new Library_Model_DbTable_DbNeardayreturnbook();
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'placeholder'=>$this->tr->translate("SEARCH_INFO")));
		$_title->setValue($request->getParam("title"));
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
	
		$_cateory_parent = new Zend_Dojo_Form_Element_FilteringSelect("parent");
		$_cateory_parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$option = array("0"=>$this->tr->translate("SELECT_CATEGORY"));
		$result = $db->getCategory();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		if (!empty($_data)){
			unset($option[$data['id']]);
		}
		$_cateory_parent->setMultiOptions($option);
		$_cateory_parent->setValue($request->getParam('parent'));
	
		$cood_book = new Zend_Dojo_Form_Element_FilteringSelect("cood_book");
		$cood_book->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$option = array("0"=>$this->tr->translate("SELECT_BOOK"));
		$result = $db_s->getBookIdName();
		if(!empty($result))foreach($result AS $row){
			$option[$row['id']]=$row['name'];
		}
		$cood_book->setMultiOptions($option);
		$cood_book->setValue($request->getParam('cood_book'));
		
		$stu_name = new Zend_Dojo_Form_Element_FilteringSelect("stu_name");
		$stu_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$option = array("0"=>$this->tr->translate("SELECT_STUDEN_NAME"));
		$db_stu=new Library_Model_DbTable_DbNeardayreturnbook();
		$result = $db_stu->getAllStudentId(2);
		//print_r($result);exit();
		if(!empty($result))foreach($result AS $row){
			$option[$row['stu_id']]=$row['name'];
		}
		$stu_name->setMultiOptions($option);
		$stu_name->setValue($request->getParam('stu_name'));
	
		//date
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$dates = date("Y-m-d");
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'required'=>false));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$start_date->setValue($_date);
	
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
	
		$this->addElements(array($stu_name,$cood_book,$start_date,$end_date,$_cateory_parent,$_title,$_status));
	
		return $this;
	}
}