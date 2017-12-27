<?php
class Allreport_ScoreController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){	
		
	}
	
    function rptScoreBacMonthlyAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title',
    				'room'=>0,
    				'group_name' => 0,
    				'study_year'=> 0,
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    				);
    	}
    	
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreGroup($search);
    	
    	$group = $db->getAllgroupStudyNotPass();
    	array_unshift($group, array ( 'id' => 0,'name' => 'ជ្រើសរើស'));
    	
    	$this->view->g_all_name=$group;
    	$this->view->month = $db->getAllMonth();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    function rptScoreDetailAction(){
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,1);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    
    function monthlyOutstandingStudentAction(){
    	$id=$this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'for_month'=>'',
    		);
    	}
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,2);
    	$this->view->all_student = $db->getStundetScoreDetailGroup($search,$id,1);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    
    function rptResultbysemesterAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");
    	$search= array();
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester = $db->getStundetScorebySemester($group_id,$type);
    	$array_score = array();
    	if(!empty($result_semester)){
    		foreach ($result_semester as $key => $row){
    			$array_score[$key]['score_average'] = ($row['average']+$row['avg_exam'])/2;
    		}
    	}
    	if(empty($result_semester)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/allstudent/student-group");
    	}
    	array_multisort($array_score, SORT_DESC, $result_semester);
    	$this->view->studentgroup = $result_semester;
    }
    
    function semesterOutstandingStudentAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");
    	$search= array();
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester = $db->getStundetScorebySemester($group_id,$type);
    	$array_score = array();
    	if(!empty($result_semester)){
    		foreach ($result_semester as $key => $row){
    			$array_score[$key]['score_average'] = ($row['average']+$row['avg_exam'])/2;
    		}
    	}
    	if(empty($result_semester)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/allstudent/student-group");
    	}
    	array_multisort($array_score, SORT_DESC, $result_semester);
    	$this->view->studentgroup = $result_semester;
    }
    
    function rptResultbyyearAction(){
    	$group_id=$this->getRequest()->getParam("id");
    	$type=$this->getRequest()->getParam("type");

    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$result_semester1 = $db->getStundetScorebyYear($group_id,1);

    	$result_semester2 = $db->getStundetScorebyYear($group_id,2);
    	$main_semester = $result_semester2;
    	$array_score​= array();
    	if(!empty($result_semester2)){
    		   foreach ($result_semester2 as $key => $row){
    		   		$result_semester1[$key]['avage_semester1']= ($result_semester1[$key]['average']+$result_semester1[$key]['avg_exam'])/2;
    		    	$result_semester1[$key]['avage_semester2']= ($row['average']+$row['avg_exam'])/2;
    		    	$array_score[$key]['average_year'] = ($result_semester1[$key]['avage_semester1']+$result_semester1[$key]['avage_semester2'])/2;
    		    }
       }
       if(empty($result_semester2)){
       		Application_Form_FrmMessage::Sucessfull("NO_RECORD_FOUND","/allreport/allstudent/student-group");
       }
       array_multisort($array_score, SORT_DESC, $result_semester1);
       $this->view->studentgroup = $result_semester1;
    }
    
    function rptScoreGepAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->g_name=$search;
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_english'=> '',
    				'degree_english'=>'',
    				'session'=> '',
    				'time'=> '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    	}
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundentEnglishMonthlyScore($search);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    
    function rptSemesterEvaluationAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->g_name=$search;
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_english'=> '',
    				'degree_english'=>'',
    				'session'=> '',
    				'time'=> '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    	}
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundentEnglishSemesterScore($search);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    function veiwAction(){
    	
    }
    
    function rptScoreListMonthlyAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title',
    				'room'=>0,
    				'group_name' => 0,
    				'study_year'=> 0,
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    		);
    	}
    	 
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreList($search);
    	 
    	$group = $db->getAllgroupStudyNotPass();
    	array_unshift($group, array ( 'id' => 0,'name' => 'ជ្រើសរើស'));
    	 
    	$this->view->g_all_name=$group;
    	$this->view->month = $db->getAllMonth();
    	 
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    
    function rptSubjectScoredetailAction(){
    	
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    				'title',
    				'room'=>0,
    				'group_name' => 0,
    				'study_year'=> 0,
    				'grade'=> 0,
    				'degree'=>0,
    				'session'=> 0,
    				'for_month'=>date('m'),
    		);
    	}
    	
    	$this->view->search=$search;
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreList($search);
    	
    	$group = $db->getAllgroupStudyNotPass();
    	array_unshift($group, array ( 'id' => 0,'name' => 'ជ្រើសរើស'));
    	
    	$this->view->g_all_name=$group;
    	$this->view->month = $db->getAllMonth();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	
    	
    }
    
    
}





