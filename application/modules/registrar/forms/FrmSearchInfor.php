<?php 

class Registrar_Form_FrmSearchInfor extends Zend_Dojo_Form
{
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
		//$this->validate = 'dijit.form.TextBox';
	}
	public function FrmSearchRegister($data=null){ 
		$request=Zend_Controller_Front::getInstance()->getRequest();
	
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array(
				'dojoType'=>$this->text,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")));
		$_title->setValue($request->getParam("title"));
	    
		$generation = new Zend_Dojo_Form_Element_FilteringSelect('study_year');
		$generation->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false
		));
		$generation->setValue($request->getParam("study_year"));
		$db_years=new Registrar_Model_DbTable_DbRegister();
		$years=$db_years->getAllYears();
		$opt = array(''=>$this->tr->translate("SELECT_YEAR"));
		if(!empty($years))foreach($years AS $row) $opt[$row['id']]=$row['years'];
		$generation->setMultiOptions($opt);
		
		
		$_session = new Zend_Dojo_Form_Element_FilteringSelect('session');
		$_session->setAttribs(array(
				'dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SESSION"),
				'class'=>'fullside',
				'required'=>false
				));
		$_session->setValue($request->getParam("session"));
		$opt_ses=new Application_Model_DbTable_DbGlobal();
		$opt_sesion=$opt_ses->getSession();
		$opt_session = array(''=>$this->tr->translate("SESSION"));
		if(!empty($opt_sesion))foreach ($opt_sesion As $rs)$opt_session[$rs['key_code']]=$rs['view_name'];
		$_session->setMultiOptions($opt_session);
		
		
		
		$_room = new Zend_Dojo_Form_Element_FilteringSelect('room');
		$_room->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>false
		));
		$_room->setValue($request->getParam("room"));
		$db = new Application_Model_DbTable_DbGlobal();
		$result = $db->getAllRoom();
		$opt_room = array(''=>$this->tr->translate("ROOM_NAME"));
		if(!empty($result))foreach ($result As $rs)$opt_room[$rs['id']]=$rs['name'];
		$_room->setMultiOptions($opt_room);
		
		
		
		$_time = new Zend_Dojo_Form_Element_FilteringSelect('time');
		$_time->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("TIME"),
				'class'=>'fullside',
				'required'=>false
				));
		$_time->setValue($request->getParam("time"));
		$opt_time = array(''=>$this->tr->translate("TIME"),
				 1=>$this->tr->translate("PART_TIME"),
				 2=>$this->tr->translate("FULL_TIME"),
				);
		$_time->setMultiOptions($opt_time);
		
		$_degree = new Zend_Dojo_Form_Element_FilteringSelect('degree');
		$_degree->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_degree->setValue($request->getParam('degree'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree=$db_years->getAllDegree();
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree->setMultiOptions($opt_deg);
		
		
		$_degree_bac = new Zend_Dojo_Form_Element_FilteringSelect('degree_bac');
		$_degree_bac->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_degree_bac->setValue($request->getParam('degree_bac'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree=$db_years->getAllDegreeBac();
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree_bac->setMultiOptions($opt_deg);
		
		
		$_degree_gep = new Zend_Dojo_Form_Element_FilteringSelect('degree_gep');
		$_degree_gep->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("DEGREE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_degree_gep->setValue($request->getParam('degree_gep'));
		$opt_deg = array(''=>$this->tr->translate("DEGREE"));
		$opt_degree=$db_years->getAllDegreeGEP();
		if(!empty($opt_degree))foreach ($opt_degree As $rows)$opt_deg[$rows['id']]=$rows['name'];
		$_degree_gep->setMultiOptions($opt_deg);
		
		
		$_grade = new Zend_Dojo_Form_Element_FilteringSelect('grade');
		$_grade->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'required'=>false
				));
		$_grade->setValue($request->getParam('grade'));
		$opt_g = array(''=>$this->tr->translate("GRADE"));
		$opt_grade=$db_years->getGradeAll();
		if(!empty($opt_grade))foreach ($opt_grade As $rows)$opt_g[$rows['id']]=$rows['name'];
		$_grade->setMultiOptions($opt_g);
		
		
		$_grade_bac = new Zend_Dojo_Form_Element_FilteringSelect('grade_bac');
		$_grade_bac->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_grade_bac->setValue($request->getParam('grade_bac')); // kid - grade 12
		$opt_g_bac = array(''=>$this->tr->translate("GRADE"));
		$opt_grade_bac=$db_years->getGradeAllBac();
		if(!empty($opt_grade_bac))foreach ($opt_grade_bac As $rows)$opt_g_bac[$rows['id']]=$rows['name'];
		$_grade_bac->setMultiOptions($opt_g_bac);
		
		
		$_grade_kid = new Zend_Dojo_Form_Element_FilteringSelect('grade_kid');
		$_grade_kid->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_grade_kid->setValue($request->getParam('grade_kid'));
		$opt_g_kid = array(''=>$this->tr->translate("GRADE"));
		$opt_grade_kid=$db_years->getGradeAllKid();
		if(!empty($opt_grade_kid))foreach ($opt_grade_kid As $rows)$opt_g_kid[$rows['id']]=$rows['name'];
		$_grade_kid->setMultiOptions($opt_g_kid);
		
		
		$_grade_all = new Zend_Dojo_Form_Element_FilteringSelect('grade_all');
		$_grade_all->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_grade_all->setValue($request->getParam('grade_all'));
		$opt_g_all = array(''=>$this->tr->translate("GRADE"));
		$opt_grade_all=$db_years->getGradeAllDept();
		if(!empty($opt_grade_all))foreach ($opt_grade_all As $rows)$opt_g_all[$rows['id']]=$rows['name'];
		$_grade_all->setMultiOptions($opt_g_all);
		
		
		//add gep controll 
		$_grade_gep = new Zend_Dojo_Form_Element_FilteringSelect('grade_gep');
		$_grade_gep->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("GRADE"),
				'class'=>'fullside',
				'required'=>false
		));
		$_grade_gep->setValue($request->getParam('grade_gep'));
		$opt_gep = array(''=>$this->tr->translate("GRADE"));
		$opt_grade_gep=$db_years->getGradeGepAll();
		if(!empty($opt_grade_gep))foreach ($opt_grade_gep As $row)$opt_gep[$row['id']]=$row['name'];
		$_grade_gep->setMultiOptions($opt_gep);
		
		
		$user = new Zend_Dojo_Form_Element_FilteringSelect('user');
		$user->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("USER"),
				'class'=>'fullside',
				'required'=>false
		));
		$user->setValue($request->getParam('user'));
		$opt_user = array(''=>$this->tr->translate("USER"));
		$opt_all_user=$db_years->getAllUser();
		if(!empty($opt_all_user))foreach ($opt_all_user As $row)$opt_user[$row['id']]=$row['name'];
		$user->setMultiOptions($opt_user);
		
		
		$sess_gep = new Zend_Dojo_Form_Element_FilteringSelect('sess_gep');
		$sess_gep->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("TIME"),
				'class'=>'fullside',
				'required'=>false
		));
		$sess_gep->setValue($request->getParam("sess_gep"));
		$ses_gep = array(''=>$this->tr->translate("TIME"),
				1=>$this->tr->translate("PART_TIME"),
				2=>$this->tr->translate("FULL_TIME"),
		);
		$sess_gep->setMultiOptions($ses_gep);
		
		
		$service = new Zend_Dojo_Form_Element_FilteringSelect('service');
		$service->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false
		));
		$service->setValue($request->getParam("service"));
		$opt_ser = array(''=>$this->tr->translate("SERVICE_NAME"));
		$ser_rows=$db_years->getServicesAll();
		if(!empty($ser_rows))foreach($ser_rows As $row)$opt_ser[$row['id']]=$row['title'];
		$service->setMultiOptions($opt_ser);
		
		$pay_term = new Zend_Dojo_Form_Element_FilteringSelect('pay_term');
		$pay_term->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("PAYMENT_TERM"),
				'class'=>'fullside',
				'required'=>false
		));
		$pay_term->setValue($request->getParam("pay_term"));
		$opt_term = array(
				''=>$this->tr->translate("PAYMENT_TERM"),
   				2=>$this->tr->translate('QUARTER'),
   				3=>$this->tr->translate('SEMESTER'),
   				4=>$this->tr->translate('YEAR'),
   		
   		);
		$pay_term->setMultiOptions($opt_term);
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		//date 
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$dates = date("Y-m-d");
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
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
				'class'=>'fullside',
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array('dojoType'=>$this->filter,
				'placeholder'=>$this->tr->translate("SERVIC"),
				'class'=>'fullside',
				'required'=>false
		));
		$branch_id->setValue($request->getParam("branch_id"));
		$db = new Accounting_Model_DbTable_DbTuitionFee();
		$rows= $db->getAllBranch();
		array_unshift($rows, array('id'=>'','name'=>"Select Branch"));
		$opt=array();
		if(!empty($rows))foreach($rows As $row)$opt[$row['id']]=$row['name'];
		$branch_id->setMultiOptions($opt);
	
		$this->addElements(array($_degree_bac,$_room,$branch_id,$start_date,$user,$end_date,$sess_gep,$_title,$_degree_gep,$generation,$_session,$_time,$_degree,$_grade,$_grade_all,$_grade_bac,$_grade_kid,$_status,$_grade_gep,$service,$pay_term));
		return $this;
	} 


}
