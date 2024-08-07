<?php 
Class Registrar_Form_FrmSearchexpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_status_opt = array(
				-1=>$this->tr->translate("PLEASE_SELECT_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_transfer=  new Zend_Dojo_Form_Element_FilteringSelect('paid_transfer');
		$_transfer->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_status_opt = array(
				-1=>$this->tr->translate("PLEASE_SELECT_TRANSFER"),
				1=>$this->tr->translate("TRANSFER_OTHER"),
				0=>$this->tr->translate("CREATE_NEW"));
		$_transfer->setMultiOptions($_status_opt);
		$_transfer->setValue($request->getParam("paid_transfer"));
		
		$_bydate=  new Zend_Dojo_Form_Element_FilteringSelect('by_date');
		$_bydate->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_date_opt = array(
				0=>$this->tr->translate("PLEASE_SELECT_DATE"),
				1=>$this->tr->translate("START_DATE"),
				2=>$this->tr->translate("END_DATE"));
				
		$_bydate->setMultiOptions($_date_opt);
		$_bydate->setValue($request->getParam("by_date"));
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $db->getViewById(8,1);
		$payment_method->setMultiOptions($opt);
		$payment_method->setValue($request->getParam("payment_type"));
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'onchange'=>'CalculateDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("START_DATE"),
				'class'=>'fullside'));
		$_date = $request->getParam("start_date");
		
		if(!empty($_date)){
			$_releasedate->setValue($_date);
		}
		
		$_db = new Registrar_Model_DbTable_DbIncome();
		$_arr_opt_cate = array(""=>$this->tr->translate("PLEASE_SELECT_CATEGORY_INCOME"));
		$optionCate = $_db->getCateIncome();
		if(!empty($optionCate))foreach($optionCate AS $row) $_arr_opt_cate[$row['id']]=$row['name'];
		$_cate = new Zend_Dojo_Form_Element_FilteringSelect("cate_income");
		$_cate->setMultiOptions($_arr_opt_cate);
		$_cate->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',));
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'required'=>'true','class'=>'fullside',
				'placeholder'=>$this->tr->translate("END_DATE"),
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_dateline->setValue($_date);
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $db->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside height-text',));
		if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
		
		
		$this->addElements(array($_title,$_branch_id,$_cate,$_bydate,$payment_method,$_transfer,$_releasedate
				,$_dateline,$_status));
		return $this;
		
	}	
	
}