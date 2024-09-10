<?php 
Class Mobileapp_Form_FrmVideo extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddVideo($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_arr=array(
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DACTIVE"),
		);
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside'));
		$_status->setValue($request->getParam('status'));
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_ordering = new Zend_Dojo_Form_Element_NumberTextBox('ordering');
		$_ordering->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ORDERING")
		));
		$_ordering->setValue(0);
		
		$dbCate = new Mobileapp_Model_DbTable_DbCategory();
		$_arr_opt_cate = array(""=>$this->tr->translate("SELECT_CATEGORY"));
		$optionBranch = $dbCate->getAllCategoryList();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_cate[$row['id']]=$row['name'];
		$_category = new Zend_Dojo_Form_Element_FilteringSelect("category");
		$_category->setMultiOptions($_arr_opt_cate);
		$_category->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_category->setValue($request->getParam('category'));
		
		$video_link=  new Zend_Form_Element_Textarea('video_link');
		$video_link->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
		
		$public_date = new Zend_Dojo_Form_Element_DateTextBox('public_date');
		$public_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$public_date->setValue(date("Y-m-d"));
		$id = new Zend_Form_Element_Hidden("id");
		
		$_arrTypeOfVideo=array(
				"1"=>$this->tr->translate("FOR_STUDENT"),
				"2"=>$this->tr->translate("FOR_TEACHER"),
		);
		$typeOfVideo = new Zend_Dojo_Form_Element_FilteringSelect("typeOfVideo");
		$typeOfVideo->setMultiOptions($_arrTypeOfVideo);
		$typeOfVideo->setAttribs(array(
				'class'=>'fullside',
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		
		
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_arrsearch=array(
				"-1"=>$this->tr->translate("SELECT_STATUS"),
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DACTIVE"),
		);
		$_status_search->setMultiOptions($_arrsearch);
		$_status_search->setAttribs(array(
				'class'=>'fullside',
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
		));
		$_status_search->setValue($request->getParam('status_search'));
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$_date = $request->getParam("start_date");
		$from_date->setValue($_date);
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		if($data!=null){
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$video_link->setValue($data['video_link']);
			$_category->setValue($data['category']);
			$public_date->setValue($data['publish_date']);
			$_ordering->setValue($data['ordering']);
			$data['typeOfVideo'] = empty($data['typeOfVideo']) ? 1 : $data['typeOfVideo'];
			$typeOfVideo->setValue($data['typeOfVideo']);
		}
		
		$this->addElements(array($id,
				$_category,
				$video_link,
				$public_date,
				$_ordering,
				$typeOfVideo,
				$_btn_search,$_status,$_adv_search,$_status_search,$from_date,$to_date));
		return $this;
	}	
	
}