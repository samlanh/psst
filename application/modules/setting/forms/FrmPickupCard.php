<?php 
Class Setting_Form_FrmPickupCard extends Zend_Dojo_Form {
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
// 				'readonly'=>true
				));
		$options = array(1=>$this->tr->translate("ACTIVE"), 0=>$this->tr->translate("DEACTIVE"));
		$status->setMultiOptions($options);
		
		$display_by = new Zend_Dojo_Form_Element_FilteringSelect('display_by');
		$display_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				// 				'readonly'=>true
		));
		$options = array(1=>$this->tr->translate("ENGLISH"), 2=>$this->tr->translate("KHMER"));
		$display_by->setMultiOptions($options);
	
		
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
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$title->setValue($data['title']);
			$branch_id->setValue($data['branch_id']);
			$_schoolOption->setValue($data['schoolOption']);
			$note->setValue($data['note']);
			$status->setValue($data['status']);
			$_id->setValue($data['id']);
			$display_by->setValue($data['display_by']);
			$issue->setValue($data['issue']);
			$valid->setValue($data['validate']);
		}
		
		$this->addElements(array(
				$title,
				$branch_id,
				$_schoolOption,
				$note,
				$issue,
				$valid,
				$_adv_search,
				$status_search,
				$_id,
				$display_by,
		));
		
		return $this;
		
	}
	
}