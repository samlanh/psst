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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>'false'
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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false",
			'onchange'=>'filterClient();'
		));
		$from_academic_opt = $db->getAllAcademicYear(1);
		$_from_academic->setMultiOptions($from_academic_opt);
		
		$type_study = new Zend_Dojo_Form_Element_FilteringSelect('type_study');
		$type_study->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'onchange'=>'filterClient();',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
		));
		$typestudy_opt = $db->getAllTermStudyTitle(1);
		$type_study->setMultiOptions($typestudy_opt);
		$type_study->setValue($request->getParam("type_study"));
		
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
			'style'=>'font-family: inherit;  min-height:100px !important;width:100%;'
		));
		
		$_is_finished = new Zend_Dojo_Form_Element_FilteringSelect('is_finished');
		$_is_finished->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
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
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
		));
		
		$rsschool = $db->getAllSchoolOption();
		$options=array(-1=>$this->tr->translate("SELECT_SCHOOL_OPTIONS"));
		if(!empty($rsschool))foreach($rsschool AS $row){
			$options[$row['id']]=$row['name'];
		}
		$schooloption->setMultiOptions($options);
		$schooloption->setValue($request->getParam("school_option"));
		
		$ismulty_study = new Zend_Dojo_Form_Element_FilteringSelect('ismulty_study');
		$ismulty_study ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>"false"
		));
		$options= array(0=>$this->tr->translate("ONE_PROGRAM_ONLY"),1=>$this->tr->translate("MULTY_PROGRAM"),);
		$ismulty_study->setMultiOptions($options);
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'required'=>'false',
				'onchange'=>'getallGrade();'
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree = $db->getAllItems(1);//degree
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
		
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_from_academic->setValue($data['academic_year']);
			$type_study->setValue($data['term_study']);
			$ismulty_study->setValue($data['is_multi_study']);
			$generation->setValue($data['generation']);
			$schooloption->setValue($data['school_option']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$_is_finished->setValue($data['is_finished']);
		}
		$this->addElements(array($_branch_id,
			$_degree,
			$ismulty_study,
			$_from_academic,
			$type_study,
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
	public function FrmSearchTutionfee($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $db->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
    			'required'=>'false',
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

		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$_title->setValue($request->getParam("title"));
		
		$type_study = new Zend_Dojo_Form_Element_FilteringSelect('type_study');
		$type_study->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'false',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("TIME"),
			'onchange'=>'filterClient();',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
		));
		$typestudy_opt = $db->getAllTermStudyTitle(1);
		$type_study->setMultiOptions($typestudy_opt);
		$type_study->setValue($request->getParam("type_study"));

		$schooloption = new Zend_Dojo_Form_Element_FilteringSelect('school_option');
		$schooloption->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'false',
			'placeholder'=>$this->tr->translate("SELECT_SCHOOL_OPTIONS"),
			'class'=>'fullside',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
		));
		
		$rsschool = $db->getAllSchoolOption();
		$options=array(-1=>$this->tr->translate("SELECT_SCHOOL_OPTIONS"));
		if(!empty($rsschool))foreach($rsschool AS $row){
			$options[$row['id']]=$row['name'];
		}
		$schooloption->setMultiOptions($options);
		$schooloption->setValue($request->getParam("school_option"));

		$_is_finished_search = new Zend_Dojo_Form_Element_FilteringSelect('is_finished_search');
		$_is_finished_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'false',
			'placeholder'=>$this->tr->translate("PROCESS_TYPE"),
			'class'=>'fullside',
			'queryExpr'=>'*${0}*',
			'autoComplete'=>"false"
		));
		
		$rows = $db->getProcessTypeView();
		$options=array(""=>$this->tr->translate("PROCESS_TYPE"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_is_finished_search->setMultiOptions($options);
		$_is_finished_search->setValue($request->getParam("is_finished_search"));

		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("ALL_STATUS"),
				));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$this->addElements(array(
			$type_study
			,$schooloption 
			,$_is_finished_search
			,$_status
			,$_title 
			,$_branch_id
		));
		return $this;		
	}	
}