<?php

class Issue_Model_DbTable_DbStudentAttendanceOne extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllAttendence($search=null){
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	$label="name_en";
    	if ($currentLang==1){
    		$colunmname='title';
    		$label="name_kh";
    	}
    	$sql="SELECT 
			sad.`id`,
			(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = sad.branchId LIMIT 1) AS branch_name,
			(SELECT stu_code FROM rms_student AS s WHERE s.stu_id = sad.stu_id LIMIT 1) AS stu_code,
			(SELECT stu_khname FROM rms_student AS s WHERE s.stu_id = sad.stu_id LIMIT 1) AS stu_name,
			g.group_code AS group_name,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id,
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE `rms_items`.`id`=`g`.`degree` AND `rms_items`.`type`=1 LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE `rms_itemsdetail`.`id`=`g`.`grade` AND `rms_itemsdetail`.`items_type`=1 LIMIT 1) AS grade,
			CASE    
				WHEN  sad.forSemester = 1 THEN '".$tr->translate("Semester 1")."'
				WHEN  sad.forSemester = 2 THEN '".$tr->translate("Semester 2")."'
			END AS forSemester ,
			CASE    
				WHEN  sad.forSession = 1 THEN '".$tr->translate("MORNING")."'
				WHEN  sad.forSession = 2 THEN '".$tr->translate("AFTERNOON")."'
				WHEN  sad.forSession = 3 THEN '".$tr->translate("FULL_DAY")."'
			END AS forSession ,
			sad.`attendanceDate`,
			CASE    
			WHEN  sad.isCompleted = 0 THEN '".$tr->translate("PENDING")."'
			WHEN  sad.isCompleted = 1 THEN '".$tr->translate("COMPLETED")."'
			END AS isCompleted 
			";
	$sql.=$dbp->caseStatusShowImage("sad.`status`");
	$sql.="	 FROM rms_student_attendence_detail AS sad 
			LEFT JOIN `rms_group` AS g ON sad.`groupId`=g.`id` 
			WHERE sad.`type`=2 ";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " sad.`attendanceDate` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sad.`attendanceDate` <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['branch_id'])){
    		$where.= " AND sad.branchId  =".$search['branch_id'];
    	}
    	if(!empty($search['group'])){
    		$where.= " AND sad.`groupId` =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year  =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND  g.grade =".$search['grade'];
    	}
    	if(!empty($search['session_type'])){
    		$where.=" AND sad.forSession =".$search['session_type'];
    	}
   		if(!empty($search['for_semester'])){
			$where.=" AND sad.forSemester =".$search['for_semester'];
		}
    	$order=" ORDER BY sad.id DESC ";
    	$where.=$dbp->getAccessPermission('sad.branchId');
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addStudentAttendeceOne($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		
			if ($_data['attedence']!=1){
				$arr = array(
					'attendence_id'	=>0,
					'stu_id'		=>$_data['stu_name'],
					'attendence_status'=>$_data['attedence'],
					'description'	=>$_data['comment'],
					'type'			=>2, //from one student 
					'branchId'		=>$_data['branch_id'],
					'groupId'		=>$_data['group'],
					'forSemester'	=>$_data['for_semester'],
					'forSession'	=>$_data['session_type'],
					'attendanceDate'=>$_data['attendence_date'],
					'createDate'	=>date("Y-m-d"),
					'modifyDate'	=>date("Y-m-d"),
				);
				$this->_name ='rms_student_attendence_detail';
				$this->insert($arr);
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			$db->rollBack();
		}
   	}
   	public function editStudentAttendeceOne($_data){
   		$db = $this->getAdapter();
   		$db->beginTransaction();
   		try{
   			$_arr = array(
			
				'stu_id'		=>$_data['stu_name'],
				'attendence_status'=>$_data['attedence'],
				'description'	=>$_data['comment'],
				'type'			=>2, //from one student 
				'branchId'		=>$_data['branch_id'],
				'groupId'		=>$_data['group'],
				'forSemester'	=>$_data['for_semester'],
				'forSession'	=>$_data['session_type'],
				'attendanceDate'=>$_data['attendence_date'],
				'modifyDate'	=>date("Y-m-d"),
				'status'		=>$_data['status'],
   			);
   			$this->_name = "rms_student_attendence_detail";
   			$where = " id = ".$_data['id'];
   			$this->update($_arr,$where);
   			$db->commit();
   		}catch (Exception $e){
   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   			Application_Form_FrmMessage::message("INSERT_FAIL");
   			$db->rollBack();
   		}
   	}
	function getStudyYears(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(from_academic,'-',to_academic) AS name FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	function getGroupAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getSubjectBygroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT gsd.`subject_id` as id,
		(SELECT CONCAT(sj.subject_titleen,' - ',sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsd.`subject_id` LIMIT 1) AS name
		FROM `rms_group_subject_detail` AS gsd
		WHERE gsd.`group_id`= $group_id";
		return $db->fetchAll($sql);
	}
	function getAttendencetByID($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					sad.*,
					sa.*
				FROM 
					`rms_student_attendence` sa ,
					rms_student_attendence_detail as sad 
				WHERE  
					sa.id = sad.attendence_id
					and sad.`id` = $id
					AND sa.type=1
					and sad.type=2 
			";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sa.branch_id');
		return $db->fetchRow($sql);
	}
	function getAttendenceDetailByID($id){
		$db=$this->getAdapter();
		$sql="SELECT sad.* FROM rms_student_attendence_detail as sad WHERE sad.type=2 and sad.`id` = $id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sad.branchId');
		return $db->fetchRow($sql);
	}
	function getDisciplineStatus($discipline_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT sdd.`attendence_status`,sdd.`stu_id`,sdd.`description` FROM `rms_student_attendence_detail` AS sdd WHERE sdd.`attendence_id`=$discipline_id AND sdd.`stu_id`=$stu_id";
		return $db->fetchRow($sql);
	}
	
	function getAttendeceStatus($att_id , $stu_id){
		$db = $this->getAdapter();
		$sql = "select 
					attendence_status,
					description 
				from 
					rms_student_attendence_detail as sad,
					rms_student_attendence as sa 
				where 
					sad.attendence_id = sa.id
					and sa.type = 1
					and sa.id = $att_id
					and sad.stu_id = $stu_id ";
		return $db->fetchRow($sql);
	}
	
	function getStudentByGroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT
					sgh.`stu_id` as id,
					s.stu_code as name
				FROM
					`rms_group_detail_student` AS sgh,
					rms_student as s
				WHERE
					sgh.itemType=1 
					AND s.stu_id = sgh.stu_id
					and sgh.is_pass = 0
					and sgh.group_id = $group_id
			";
		$order=" ORDER BY s.stu_code ASC ";
		$stu_code = $db->fetchAll($sql.$order);
		
		$sql1="SELECT
					sgh.`stu_id` as id,
					CONCAT(COALESCE(s.stu_khname,''),' - ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) as name
				FROM
					`rms_group_detail_student` AS sgh,
					rms_student as s
				WHERE
					sgh.itemType=1 
					AND s.stu_id = sgh.stu_id
					
					and sgh.is_pass = 0
					and sgh.group_id = $group_id
			";
		$order=" ORDER BY s.stu_code ASC ";
		$stu_name = $db->fetchAll($sql1.$order);
		
		$result=array(
					'stu_name'=>$stu_name,
					'stu_code'=>$stu_code
				);
		return $result;
	}
	
	
}