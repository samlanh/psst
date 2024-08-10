<?php 
Class Setting_Form_FrmCardMg extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmCardmg($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				));
		
		$card_prefix = new Zend_Dojo_Form_Element_ValidationTextBox('card_prefix');
		$card_prefix->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		
		$colorcode = new Zend_Dojo_Form_Element_ValidationTextBox('colorcode');
		$colorcode->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		$colorcode->setValue("000000");
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("BRANCH"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>false
		));
		$branch_id->setValue($request->getParam("branch_id"));
		
		$rows= $_dbgb->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>$this->tr->translate("SELECT_BRANCH")));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$branch_id->setMultiOptions($opt);
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllSchoolOption($userinfo['branch_list']);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_schoolOption = new Zend_Dojo_Form_Element_FilteringSelect("schoolOption");
    	$_schoolOption->setMultiOptions($_arr_opt);
    	$_schoolOption->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
				
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$options = array(1=>$this->tr->translate("ACTIVE"), 0=>$this->tr->translate("DEACTIVE"));
		$status->setMultiOptions($options);
		
		$display_by = new Zend_Dojo_Form_Element_FilteringSelect('display_by');
		$display_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$options = array(1=>$this->tr->translate("ENGLISH"), 2=>$this->tr->translate("KHMER"));
		$display_by->setMultiOptions($options);
		
		$card_type = new Zend_Dojo_Form_Element_FilteringSelect('card_type');
		$card_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'checkCardType()',
		));
		$options = array(1=>$this->tr->translate("STUDENT"),2=>$this->tr->translate("TEACHER"),3=>$this->tr->translate("STAFF"));
		$card_type->setMultiOptions($options);
		
		$issue = new Zend_Dojo_Form_Element_DateTextBox('issue');
		$start_date = date("Y-m-d");
		$issue->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				'required'=>true));
		$issue->setValue($start_date);
		
		$valid = new Zend_Dojo_Form_Element_DateTextBox('valid');
		$date = date("Y-m-d",strtotime("+1 Year"));
		$valid->setAttribs(array(
				'data-dojo-Type'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				'required'=>true));
		$valid->setValue($date);
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$status_search->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				 1=>$this->tr->translate("ACTIVE"),
				 0=>$this->tr->translate("DACTIVE"));
		$status_search->setMultiOptions($_status_opt);
		$status_search->setValue($request->getParam("status_search"));


		$name_left=new Zend_Dojo_Form_Element_NumberTextBox('name_left');
		$name_left->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$name_top=new Zend_Dojo_Form_Element_NumberTextBox('name_top');
		$name_top->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));

		$studentcode_left = new Zend_Dojo_Form_Element_NumberTextBox('studentcode_left');
		$studentcode_left->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true,
		));

		$studentcode_top = new Zend_Dojo_Form_Element_NumberTextBox('studentcode_top');
		$studentcode_top->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true,
		));
		
		$photo_left = new Zend_Dojo_Form_Element_NumberTextBox('photo_left');
		$photo_left->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$photo_top = new Zend_Dojo_Form_Element_NumberTextBox('photo_top');
		$photo_top->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));

		$group_left = new Zend_Dojo_Form_Element_NumberTextBox('group_left');
		$group_left->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));

		$group_top = new Zend_Dojo_Form_Element_NumberTextBox('group_top');
		$group_top->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));

		$code_left = new Zend_Dojo_Form_Element_NumberTextBox('code_left');
		$code_left->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$code_top = new Zend_Dojo_Form_Element_NumberTextBox('code_top');
		$code_top->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));

		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$title->setValue($data['title']);
			$branch_id->setValue($data['branch_id']);
			$_schoolOption->setValue($data['schoolOption']);
			$note->setValue($data['note']);
			$status->setValue($data['status']);
			$_id->setValue($data['id']);
			$display_by->setValue($data['display_by']);
			
			$card_prefix->setValue($data['card_prefix']);
			$colorcode->setValue($data['colorcode']);
			$card_type->setValue($data['card_type']);
			$valid->setValue($data['valid']);
			$issue->setValue($data['issue']);
			$name_left->setValue($data['issue']);

			$name_left->setValue($data['stunameleft']);
			$name_top->setValue($data['stunametop']);
			$studentcode_left->setValue($data['studentcodeleft']);
			$studentcode_top->setValue($data['studentcodetop']);
			$photo_left->setValue($data['photo_left']);
			$photo_top->setValue($data['photo_top']);
			$group_left->setValue($data['groupleft']);
			$group_top->setValue($data['grouptop']);
			$code_left->setValue($data['qrcodeleft']);
			$code_top->setValue($data['qrcodetop']);

		}
		
		$this->addElements(array(
				$title,
				$branch_id,
				$_schoolOption,
				$note,
				$card_prefix,
				$colorcode,
				$card_type,
				$issue,
				$valid,
				$status,
				$display_by,
				$_adv_search,
				$status_search,
				$_id,
				$name_left,
				$name_top,
				$studentcode_left,
				$studentcode_top,
				$photo_left,
				$photo_top,
				
				$group_left,
				$group_top,
				$code_left,
				$code_top
		));
		
		return $this;
	}
}