<?php

class Foundation_Model_DbTable_DbStudentdiscipline extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_discipline';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	
    function getAllDiscipline($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT sa.`id`,
    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS group_name,
    	(SELECT (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS academy,
    	(SELECT (SELECT en_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS degree,
    	(SELECT (SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS grade,
    	(SELECT g.semester FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS semester,
    	(SELECT (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS room,
    	(SELECT
    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS session,
    	sa.`mistake_date`,
    	sa.`status` FROM `rms_student_discipline` AS sa ";
    	$where =' WHERE 1 ';
    	$from_date =(empty($search['start_date']))? '1': " sa.date_create >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.date_create <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['group_name'])){
    		$where.= " AND sa.`group_id` =".$search['group_name'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND (SELECT g.academic_year FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND (SELECT g.grade FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND (SELECT  `g`.`session` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['session'];
    	}
   		if(!empty($search['room'])){
			$where.=" AND (select `g`.`room_id` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1 )=".$search['room'];
		}
    	$order=" ORDER BY id DESC ";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addDiscipline($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_sub = new Global_Model_DbTable_DbHomeWorkScore();
		try{
			$session_user=new Zend_Session_Namespace('authstu');
			$branch_id = $session_user->branch_id;
			$_arr = array(
					'branch_id'=>$branch_id,
					'group_id'=>$_data['group'],
					'mistake_date'=>date("Y-m-d",strtotime($_data['discipline_date'])),
					'date_create'=>date("Y-m-d"),
					'modify_date'=>date("Y-m-d"),			
					'for_semester'=> $_data['for_semester'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId()
			);
			$id=$this->insert($_arr);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if(isset($_data['have_mistake'.$i])){
						if (!empty($_data['mistake_type'.$i])){
							$arr = array(
									'discipline_id'=>$id,
									'stu_id'=>$_data['student_id'.$i],
									'mistake_type'=>$_data['mistake_type'.$i],
									'description'=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_discipline_detail';
							$this->insert($arr);
						}
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
		}
   }
   public function updateStudentAttendence($_data){
		//print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('authstu');
			$branch_id = $session_user->branch_id;
			$_arr = array(
					'branch_id'=>$branch_id,
					'group_id'=>$_data['group'],
					'mistake_date'=>date("Y-m-d",strtotime($_data['discipline_date'])),
					'modify_date'=>date("Y-m-d"),
					'for_semester'=> $_data['for_semester'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId()
			);
			$where="id=".$_data['id'];
			$db->getProfiler()->setEnabled(true);
			$this->update($_arr, $where);
			
		   $this->_name='rms_student_discipline_detail';
		   $this->delete("discipline_id=".$_data['id']);
		  
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if(isset($_data['have_mistake'.$i])){
						if (!empty($_data['mistake_type'.$i])){
							$arr = array(
									'discipline_id'=>$_data['id'],
									'stu_id'=>$_data['student_id'.$i],
									'mistake_type'=>$_data['mistake_type'.$i],
									'description'=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_discipline_detail';
							$this->insert($arr);
						}
					}
				}
			}
// 			exit();
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();
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
		$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS name FROM rms_tuitionfee WHERE `status`=1
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

	function getAllGrade($degree){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,CONCAT(major_enname) As name FROM rms_major WHERE is_active=1 and dept_id=".$degree;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
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
			WHERE sgh.`group_id`=".$group_id;
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
		$sql="SELECT * FROM `rms_student_discipline` sd WHERE  sd.`id`=".$id;
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
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name 
		FROM `rms_group` AS `g` WHERE g.status=1 and g.is_pass!=1";

		return $db->fetchAll($sql);
	}
}

