<?php
class Allreport_FoundationController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{	
		
	}
	
	
	public function rptamountstudentbyyearAction()
	{
		$db= new Allreport_Model_DbTable_DbRptAmountStudentByYear();
		$this->view->grade = $rs_rows = $db->getAllTitle();
		$this->view->session = $rs_row = $db->getAllSession();
		$this->view->rs = $db->getAllStu();
		$this->view->year = $db->getAllYearGeneration();
		//print_r($this->view->year);
		//$this->view->search=$search;
	}
	
	public function rptamountstudentAction()
	{
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{
			$search=array(
					'title' => '',
					'study_year' => '',
					'grade_bac' => '',
					'session' => '',
			);
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$db= new Allreport_Model_DbTable_DbRptAmountStudentByYear();
		$this->view->rs = $db->getAllStu($search);
		$this->view->search=$search;
	}
	public function rptGroupAction(){
		if($this->getRequest()->isPost()){
			$_data=$this->getRequest()->getPost();
			$search = array(
					'txtsearch' => $_data['txtsearch'],
					//'searchby' => $_data['searchby'],
			);
		}
		else{
			$search=array(
						'txtsearch' =>'',
				);
		}
	
		$group= new Allreport_Model_DbTable_DbRptGroup();
		$this->view->rs = $rs_rows = $group->getAllGroup($search);
		$this->view->search = $search;
	}
	
	public function rptStudentAction(){
	
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title'  	=>'',
					'study_year'=>'',
					'grade_bac' =>'',
					'session'  	=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'	=> date('Y-m-d'),
			);
		}
		
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$group= new Allreport_Model_DbTable_DbRptStudent();
		$this->view->rs = $rs_rows = $group->getAllStudent($search);
		$this->view->search=$search;
	}
	
	public function rptExamDegreeAction()
	{
	}
	public function rptFinalExamsAction()
	{
	
	}
	

	public function rptStuidAction()
	{
	
	}
	public function rptFoundationAction()
	{
	
	}
	public function rptDropOutAction()
	{
	
	}
	public function rptTotalAttendanceAction()
	{
	
	}
	
	
	public function rptScorelistAction()
	{
	
	}
	public function rptStudentAttendanceAction()
	{
	
	}
	public function rptFullResultAction()
	{
	
	}
	public function rptStudentListAction()
	{
		if($this->getRequest()->isPost()){
			$_data=$this->getRequest()->getPost();
			$search = array(
					'txtsearch' => $_data['txtsearch']
			);
		
		}
		else{
			$search = array(
					'txtsearch' => ""
			);
		}
		$db = new Allreport_Model_DbTable_DbStudent();
		$row = $db->getAllStudentre($search);
		$this->view->rs = $row;
	}
	public function rptStudentInfoAction()
	{
		if($this->getRequest()->isPost()){
			$_data=$this->getRequest()->getPost();
			$search = array(
					'txtsearch' => $_data['txtsearch']
			);
		
		}
		else{
			$search = array(
					'txtsearch' => ""
			);
		}
		$db = new Allreport_Model_DbTable_DbStudent();
		$row = $db->getStudentInfo($search);
		$this->view->rs = $row;
	}
	public function rptAllresultAction()
	{
		
	}
	public function rptCertificateAction()
	{
	
	}
	
	
//////////report student score 
//     function rptStudentScoreAction(){
// //     	if($this->getRequest()->isPost()){
// //     		$_data=$this->getRequest()->getPost();
// //     		$search = array(
// //     				'txtsearch' => $_data['txtsearch'],
// //     				//'searchby' => $_data['searchby'],
// //     		);
// //     	}
// //     	else{
// //     		$search=array(
// //     				'txtsearch' =>'',
// //     		);
// //     	}
//     	$group= new Allreport_Model_DbTable_DbRptGroup();
//     	$this->view->rs = $rs_rows = $group->getAllGroup($search=null);
//     	$this->view->search = $search;
//     	$parent=new Allreport_Model_DbTable_DbRptStudentScore();
//     	$this->view->row_parent=$parent->getParentName();
//     	$this->view->row_sub=$parent->getSubjectdByParent();
//     	$this->view->row_studet=$parent->getAllSubjectByStudent();
//     	$this->view->row_aca=$parent->getAcademic();
//     	$rows = $parent->getStudenetGroupSubject();
//     	$result = array();
//     	if(!empty($rows))foreach ($rows as $key =>$rs){
//     		$result[$key]=array(
//     				'stu_id'=>$rs['stu_id'],
//     				'stu_enname'=>$rs['stu_enname'],
//     				'stu_code'=>$rs['stu_code'],
//     				'id'=>$rs['id'],
//     				'group_code'=>$rs['group_code'],
//     				'group_id'=>$rs['group_id'],
//     				
//     				'room_id'=>$rs['room_id'],
//     				'from_academic'=>$rs['from_academic'],
//     				'to_academic'=>$rs['to_academic'],
//     				'semester'=>$rs['semester'],
//     				'session'=>$rs['session'],
//     				'degree'=>$rs['degree'],
//     				'grade'=>$rs['grade'],
//     			
//     				
//     				'start_date'=>$rs['start_date'],
//     				'expired_date'=>$rs['expired_date'],
//     				'user_id'=>$rs['user_id'],
//     				'academic_year'=>$rs['academic_year'],
//     				'subject_name'=>$rs['subject_name'],
//     				'subject_id'=>$rs['subject_id'],
//     				);
//     		$itemrs = $parent->getScoreByGroupId($rs['stu_id'],$rs['subject_id'],$rs['group_id']);
// //     		$itemrs = $parent->getScoreByGroupId($rs['subject_id'],$rs['group_id']);
//     		foreach ($itemrs as $index => $item){
//     			$result[$key]['subject_id'.$index]=$item['subject_name'];
//     			$result[$key]['total_score'.$index]=$item['total_score'];
//     		}
//     	}
//     	//$data= $this->view->row_group=$parent->getStudenetGroupSubject();
//     	$data= $this->view->row_group=$result;
// //     	print_r($data);exit();
//     }

    function rptStudentScoreTestAction(){
    	$group= new Allreport_Model_DbTable_DbRptGroup();
    	$this->view->rs = $rs_rows = $group->getAllGroup($search=null);
    	$this->view->search = $search;
    	$parent=new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->row_parent=$parent->getParentName();
    	$this->view->row_sub=$parent->getSubjectdByParent();
    	$this->view->row_studet=$parent->getAllSubjectByStudent();
    	$this->view->row_aca=$parent->getAcademic();
    	$rows = $parent->getStudenetGroupSubject();
    	$result = array();
    	if(!empty($rows))foreach ($rows as $key =>$rs){
    
    		$result[$key]=array(
    				'stu_id'=>$rs['stu_id'],
    				'stu_enname'=>$rs['stu_enname'],
    				'stu_code'=>$rs['stu_code'],
    				'id'=>$rs['id'],
    				'group_code'=>$rs['group_code'],
    				'group_id'=>$rs['group_id'],
    				'tuitionfee_id'=>$rs['tuitionfee_id'],
    				'room_id'=>$rs['room_id'],
    				'from_academic'=>$rs['from_academic'],
    				'to_academic'=>$rs['to_academic'],
    				'semester'=>$rs['semester'],
    				'session'=>$rs['session'],
    				'degree'=>$rs['degree'],
    				'grade'=>$rs['grade'],
    				
    				
    				'start_date'=>$rs['start_date'],
    				'expired_date'=>$rs['expired_date'],
    				'user_id'=>$rs['user_id'],
    				'academic_year'=>$rs['academic_year'],
    				'subject_name'=>$rs['subject_name'],
    				'subject_id'=>$rs['subject_id'],
    		);
    
    		$itemrs = $parent->getScoreByGroupId($rs['stu_id'],$rs['subject_id'],$rs['group_id']);
    		//     		$itemrs = $parent->getScoreByGroupId($rs['subject_id'],$rs['group_id']);
    		foreach ($itemrs as $index => $item){
    			$result[$key]['subject_id'.$index]=$item['subject_name'];
    			$result[$key]['total_score'.$index]=$item['total_score'];
    		}
    	}
    	//$data= $this->view->row_group=$parent->getStudenetGroupSubject();
    	 
    	$data= $this->view->row_group=$result;
    	//     	print_r($data);exit();
       
       
    }
////report attendent 
    public function rptAttendentAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$search=array(
    				'txtsearch' => $data['txtsearch'],
    		);
    	}else{
    		$search=array(
    				'txtsearch' => '',
    		);
    	}
        $att=new Allreport_Model_DbTable_DbAttendentList();
        $this->view->att_years=$att->getAttByYear();
        $this->view->stus_id=$att->getStudentByIds();
    	
    }
    
///////////////report score 
    function rptScoresAction(){
    	$group= new Allreport_Model_DbTable_DbRptGroup();
    	$this->view->rs = $rs_rows = $group->getAllGroup($search=null);
    	$this->view->search = $search;
    	$parent=new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->row_parent=$parent->getParentName();
    	$this->view->row_sub=$parent->getSubjectdByParent();
    	$this->view->row_studet=$parent->getAllSubjectByStudent();
    	$this->view->row_aca=$parent->getAcademic();
    	$rows = $parent->getStudenetGroupSubject();
    	$result = array();
    	if(!empty($rows))foreach ($rows as $key =>$rs){
    
    		$result[$key]=array(
    				'stu_id'=>$rs['stu_id'],
    				'stu_enname'=>$rs['stu_enname'],
    				'stu_code'=>$rs['stu_code'],
    				'id'=>$rs['id'],
    				'group_code'=>$rs['group_code'],
    				'group_id'=>$rs['group_id'],
    				'tuitionfee_id'=>$rs['tuitionfee_id'],
    				'room_id'=>$rs['room_id'],
    				'from_academic'=>$rs['from_academic'],
    				'to_academic'=>$rs['to_academic'],
    				'semester'=>$rs['semester'],
    				'session'=>$rs['session'],
    				'degree'=>$rs['degree'],
    				'grade'=>$rs['grade'],
    				
    				'start_date'=>$rs['start_date'],
    				'expired_date'=>$rs['expired_date'],
    				'user_id'=>$rs['user_id'],
    				'academic_year'=>$rs['academic_year'],
    				'subject_name'=>$rs['subject_name'],
    				'subject_id'=>$rs['subject_id'],
    		);
    
    		$itemrs = $parent->getScoreByGroupId($rs['stu_id'],$rs['subject_id'],$rs['group_id']);
    		
    		//     		$itemrs = $parent->getScoreByGroupId($rs['subject_id'],$rs['group_id']);
    		foreach ($itemrs as $index => $item){
    			$result[$key]['subject_id'.$index]=$item['subject_name'];
    			$result[$key]['total_score'.$index]=$item['total_score'];
    		}
    	}
    
    	$data= $this->view->row_group=$result;
    	$test = $this->array_2D_inverse($result);
    	print_r($test);
        
    }
    
    public function array_2D_inverse($arr) {
    	$out = array();
    	$ridx = 0;
    	foreach ($arr as $row) {
    		foreach ($row as $colidx => $val) {
    			while ($ridx > count($out[$colidx]))
    				$out[$colidx][] = null;
    			$out[$colidx][] = $val;
    		}
    		$ridx++;
    	}
    	$max_width = 0;
    	foreach($out as $v)
    		$max_width = ($max_width < count($v)) ? count($v) : $max_width;
    	foreach($out as $k => $v){
    		while(count($out[$k]) < $max_width)
    			$out[$k][] = null;
    	}
    	return $out;
    }
    function rptScoreAction(){
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->g_name=$search;
    	}
    	else{
    		$search = array(
    				'group_name' => '',
    				'study_year'=> '',
    				'grade_bac'=> '',
    				'degree_bac'=>'',
    				'session'=> '',
    				'time'=> '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'));
    	}
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	$this->view->studentgroup = $db->getStundetScoreGroup($search);
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    function rptGepMonthlyscoreAction(){
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
    
   
}

