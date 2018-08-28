<?php 
Class Accounting_Form_FrmFee extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmTutionfee($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$_from_academic = new Zend_Dojo_Form_Element_FilteringSelect('from_academic');
		$_from_academic->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();'
		));
		$from_academic_opt = array();
		 for($i=date('Y')-1;$i<=date('Y')+1;$i++){
			$from_academic_opt[$i]=$i;
		}
		$_from_academic->setMultiOptions($from_academic_opt);
		
		
		$_to_academic = new Zend_Dojo_Form_Element_FilteringSelect('to_academic');
		$_to_academic->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();'
		));
		$to_academic_opt = array();
		for($i=date('Y');$i<=date('Y')+2;$i++){
			$to_academic_opt[$i]=$i;
		}
		$_to_academic->setMultiOptions($to_academic_opt);
		
		$generation = new Zend_Dojo_Form_Element_TextBox('generation');
		$generation->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true',
				'class'=>'fullside height-text',
		));
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit;  min-height:100px !important;width:100%;'));
		
		
		$_is_finished = new Zend_Dojo_Form_Element_FilteringSelect('is_finished');
		$_is_finished->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		
		$rows = $db->getProcessTypeView();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_is_finished->setMultiOptions($options);
		
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($options);
		
		$id = new Zend_Form_Element_Hidden("id");
		
		//search form 
		$_year = new Zend_Dojo_Form_Element_FilteringSelect('year');
		$_year->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		$dbfee = new Accounting_Model_DbTable_DbFee();
		$rows = $dbfee->getAceYear();
		$options=array(""=>$this->tr->translate("SELECT_YEAR"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_year->setMultiOptions($options);
		$_year->setValue($request->getParam("year"));
		
		$_is_finished_search = new Zend_Dojo_Form_Element_FilteringSelect('is_finished_search');
		$_is_finished_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		
		$rows = $db->getProcessTypeView();
		$options=array(""=>$this->tr->translate("PROCESS_TYPE"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_is_finished_search->setMultiOptions($options);
		$_is_finished_search->setValue($request->getParam("is_finished_search"));
		
		
		$schooloption = new Zend_Dojo_Form_Element_FilteringSelect('school_option');
		$schooloption->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		
		$rsschool = $db->getAllSchoolOption();
		$options=array(-1=>"SELECT_SCHOOLOPTION");
		if(!empty($rsschool))foreach($rsschool AS $row){
			$options[$row['id']]=$row['name'];
		}
		$schooloption->setMultiOptions($options);
		$schooloption->setValue($request->getParam("school_option"));
		
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_from_academic->setValue($data['from_academic']);
			$_to_academic->setValue($data['to_academic']);
			$generation->setValue($data['generation']);
			$schooloption->setValue($data['school_option']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$_is_finished->setValue($data['is_finished']);
		}
		$this->addElements(array($_branch_id,
				$_from_academic,
				$_to_academic,
				$generation,
				$note,
				$_is_finished,
				$_status,$id,
				$schooloption,
				$_year,
				$_is_finished_search
				));
		return $this;
		
	}	
}