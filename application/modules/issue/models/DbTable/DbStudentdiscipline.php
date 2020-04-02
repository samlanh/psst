<?php

class Issue_Model_DbTable_DbStudentdiscipline extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
    function getAllDiscipline($search=null){
    	$db=$this->getAdapter();
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	if ($currentLang==1){
    		$colunmname='title';
    	}
    	
    	$sql="SELECT sa.`id`,
		    	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = sa.branch_id LIMIT 1) AS branch_name,
		    	g.group_code  AS group_name,
		    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academy,
		    	(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		    	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE `rms_itemsdetail`.`id`=`g`.`grade` AND `rms_itemsdetail`.`items_type`=1 LIMIT 1 ) AS grade,
		    	(SELECT g.semester FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS semester,
		    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS room,
		    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE (`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`) LIMIT 1) AS session,
		    	sa.`date_attendence` ";
    	$sql.=$dbp->caseStatusShowImage("sa.status");
    	$sql.=" FROM 
    				`rms_student_attendence` AS sa,
    				rms_group as g ";
    	
    	$where =' WHERE g.id=sa.group_id 
    					AND sa.`type` = 2 ';
    	$from_date =(empty($search['start_date']))? '1': " sa.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.= " AND sa.`branch_id` =".$search['branch_id'];
    	}
    	if(!empty($search['group_name'])){
    		$where.= " AND sa.`group_id` =".$search['group_name'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year  =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND g.grade =".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	if(!empty($search['room'])){
    		$where.=" AND `g`.`room_id` =".$search['room'];
    	}
    	$where.=$dbp->getAccessPermission('sa.`branch_id`');
    	$order=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addDiscipline($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch = $_data['branch_id'];
			$group = $_data['group'];
			$date = $_data['discipline_date'];
			$for_semester = $_data['for_semester'];
			$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group and for_semester = $for_semester and date_attendence = '$date' and type=2 limit 1";
			$id = $db->fetchOne($sql);
			if(empty($id)){
				$_arr = array(
						'branch_id'=>$_data['branch_id'],
						'group_id'=>$_data['group'],
						'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
						'date_create'=>date("Y-m-d"),
						'modify_date'=>date("Y-m-d"),			
						'for_semester'=> $_data['for_semester'],
						'note'=>$_data['note'],
						'status'=>1,
						'user_id'=>$this->getUserId(),
						'type'=>2, 
				);
				$id=$this->insert($_arr);
			}
			$dbpush = new  Application_Model_DbTable_DbGlobal();
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if(isset($_data['have_mistake'.$i])){
						if (!empty($_data['mistake_type'.$i])){
							$dbpush->getTokenUser($_data['student_id'.$i],null, 3);
							$arr = array(
									'attendence_id'=>$id,
									'stu_id'=>$_data['student_id'.$i],
									'attendence_status'=>$_data['mistake_type'.$i],
									'description'=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_attendence_detail';
							$this->insert($arr);
						}
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   public function updateStudentAttendence($_data){
		//print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
// 			$session_user=new Zend_Session_Namespace(SYSTEM_SES);
// 			$branch_id = $session_user->branch_id;
			$_arr = array(
					'branch_id'=>$_data['branch_id'],
					'group_id'=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
					'modify_date'=>date("Y-m-d"),
					'for_semester'=> $_data['for_semester'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'type'=>2, //for displince
			);
			$where="id=".$_data['id'];
			$db->getProfiler()->setEnabled(true);
			$this->update($_arr, $where);
			
		   $this->_name ='rms_student_attendence_detail';
		   $this->delete("attendence_id=".$_data['id']);
		  
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if(isset($_data['have_mistake'.$i])){
						if (!empty($_data['mistake_type'.$i])){
							$arr = array(
									'attendence_id'=>$_data['id'],
									'stu_id'=>$_data['student_id'.$i],
									'attendence_status'=>$_data['mistake_type'.$i],
									'description'=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_attendence_detail';
							$this->insert($arr);
						}
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
	

	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS name FROM rms_tuitionfee WHERE `status`=1
		GROUP BY from_academic,to_academic,generation";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}

	function getGroupName($academic,$session){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM  rms_group WHERE  `session`=$session AND academic_year=$academic  ";
		return $db->fetchAll($sql);
	}
	function getSubjectById($id){
		$db = $this->getAdapter();
		$sql =" SELECT 
				  sd.student_id,
				  (SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
				  (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
				  (SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
				  sd.subject_id,
				  (SELECT CONCAT(`subject_titlekh`,'-',`subject_titleen`) FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_name,
				  (SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
				  sd.score ,
  				  sd.`is_parent`
				FROM
				  rms_score_detail AS sd 
				WHERE sd.score_id =$id ";
		return $db->fetchAll($sql);
	}

	
	
	
	function getStudent($year,$grade,$session){
		$db=$this->getAdapter();
		$sql="SELECT stu_id,stu_code,CONCAT(stu_enname,' - ',stu_khname) AS stu_name,sex
	    	FROM rms_student AS s WHERE academic_year = $year and grade=$grade and session=$session";
		$order=" ORDER BY stu_code DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getStudentByGroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT 
			sgh.`stu_id`,
			(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_code,
			(SELECT CONCAT(s.stu_enname,' - ',s.stu_khname) FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name,
			(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex
			FROM `rms_group_detail_student` AS sgh
			WHERE sgh.`group_id`=$group_id and sgh.stop_type=0 ";
		$order=" ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) DESC";
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
// 		$sql="SELECT * FROM `rms_student_attendence` sd WHERE  sd.`id`=".$id;
		$sql="SELECT * FROM `rms_student_attendence` sd WHERE  sd.`id`=".$id." AND sd.type=2";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sd.`branch_id`');
		return $db->fetchRow($sql);
	}
	
	function getAttendeceStatus($attendence_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT sad.`attendence_status`,sad.`stu_id`,sad.`description`  FROM `rms_student_attendence_detail` AS sad WHERE sad.`attendence_id`=$attendence_id AND sad.`stu_id`=$stu_id";
		return $db->fetchRow($sql);
	}
	function getAllgroupStudy(){
		$db = $this->getAdapter();
		$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
			(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name 
		FROM `rms_group` AS `g` WHERE g.status=1 and g.is_pass!=1";
		return $db->fetchAll($sql);
	}  
}

