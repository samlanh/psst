<?php 
Class Stock_Form_FrmCutStock extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
	}
	public function FrmAddCutStock($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		 
		$_dbcht = new Stock_Model_DbTable_DbCutStock();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		
		$_serailno = new Zend_Dojo_Form_Element_TextBox('serailno');
		$_serailno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'readonly'=>'readonly',
				'style'=>'color: red; font-weight: 600;',
				'missingMessage'=>$this->tr->translate("Forget Enter Receipt No")
		));
		$itemsCode = $_dbcht->getCutStockode();
		$_serailno->setValue($itemsCode);

		$_cut_stock_type = new Zend_Dojo_Form_Element_FilteringSelect('cut_stock_type');
		$_cut_stock_type->setAttribs(array(
			'dojoType'=>$this->filter,
			'class'=>'fullside',
			'required'=>'true',
			'placeholder'=>$this->tr->translate("SELECT_CUT_STOCK_TYPE"),
		));
		$_cut_stock = array(
				''=>$this->tr->translate("SELECT_TYPE"),
				1=>$this->tr->translate("USAGE_STOCK"),
				2=>$this->tr->translate("DEBT_STOCK"));
		$_cut_stock_type->setMultiOptions($_cut_stock);
		
		
		$_balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$total_received = new Zend_Dojo_Form_Element_NumberTextBox('total_received');
		$total_received->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("TOTAL_RECEIVED"),
				'missingMessage'=>$this->tr->translate("Forget Enter Total Paid")
		));
		
		$_total_discount = new Zend_Dojo_Form_Element_NumberTextBox('total_discount');
		$_total_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("TOTAL_DISCOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$_total_remain = new Zend_Dojo_Form_Element_NumberTextBox('total_remain');
		$_total_remain->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("total_remain"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		
		$_date_payment= new Zend_Dojo_Form_Element_DateTextBox('date_payment');
		$_date_payment->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date_payment->setValue(date("Y-m-d"));
		
		$_all_balance = new Zend_Dojo_Form_Element_NumberTextBox('all_balance');
		$_all_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		$_all_balance->setValue(0);
		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('amount');
		$_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'onKeyup'=>'checkAmout()',
				'placeholder'=>$this->tr->translate("AMOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Amount")
		));
		$_amount->setValue(0);
		
		
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit;  min-height:100px !important;'));
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("VOID"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$id = new Zend_Form_Element_Hidden('id');
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("SEARCH")."...",
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
		$_branch_search->setMultiOptions($_arr_opt_branch);
		$_branch_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_branch_search->setValue($request->getParam("branch_search"));
		
		$_bypuchase_no = new Zend_Dojo_Form_Element_TextBox('bypuchase_no');
		$_bypuchase_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("FILTER_BY_RECIEPT_NO"),
				'missingMessage'=>$this->tr->translate("Forget Enter Receipt No")
		));
		
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("START_DATE"),
				'class'=>'fullside',));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		 
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		$_arr_optview = array(""=>$this->tr->translate("ALL"));
		$optionView = $_dbgb->getViewById(8);
		if(!empty($optionView))foreach($optionView AS $row) $_arr_optview[$row['key_code']]=$row['view_name'];
		$_paid_by_search = new Zend_Dojo_Form_Element_FilteringSelect("paid_by_search");
		$_paid_by_search->setMultiOptions($_arr_optview);
		$_paid_by_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_paid_by_search->setValue($request->getParam("paid_by_search"));
		
		$_arr = array(""=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("VOID"));
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_status_search->setMultiOptions($_arr);
		$_status_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_status_search->setValue($request->getParam("status_search"));
		
		if(!empty($data)){
			$_branch_id->setValue($data["branch_id"]);
			$_cut_stock_type->setValue($data["cut_stock_type"]);
			$_serailno->setValue($data["serailno"]);
			$_balance->setValue($data["totalAmountDue"]);
			$total_received->setValue($data["totalQtyReceive"]);
			$_amount->setValue($data["totalQtyReceive"]);
			$_total_remain->setValue($data["totalQtyRemain"]);
			if (!empty($data["received_date"])){
				$_date_payment->setValue(date("Y-m-d",strtotime($data["received_date"])));
			}
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
			$note->setValue($data["note"]);
			$_branch_id->setAttribs(array(
					'readonly'=>'readonly',
					));
			$_cut_stock_type->setAttribs(array(
				'readonly'=>'readonly',
			));
		}
		$this->addElements(array(
				$_branch_id,
				$_serailno,
				$_all_balance,
				$_balance,
				$total_received,
				$_total_discount,
				$_total_remain,
				$_date_payment,
				$_status,
				$id,
				$_amount,
				$note,
				$_branch_search,
				$start_date,
				$end_date,
				$_bypuchase_no,
				$_adv_search,
				$_paid_by_search,
				$_status_search,
				$_cut_stock_type,
				));
		
		return $this;
		
	}
	
	
	
	
}