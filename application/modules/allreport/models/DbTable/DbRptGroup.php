<?php

class Allreport_Model_DbTable_DbRptGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
	function submitDateList($data){
		$db=$this->getAdapter();
		$this->_name='rms_student';
		
		if(!empty($data['identity'])){
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$arr = array(
						'dob'=>$data['dob_'.$i]
						);
				$where=" stu_id = ".$data['stu_id'.$i];
				$this->update($arr, $where);
			}
		}
	} 
    public function getAllGroup($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT `g`.`id`,`g`.`group_code` AS `group_code`,`g`.`semester` AS `semester`,
    	
    	(select CONCAT(from_academic,'-',to_academic,' (',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) as academic_year,
    	
		(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`))AS degree,
		(SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`)) AS grade,`g`.`amount_month`,
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
		`g`.`start_date`,`g`.`expired_date`,`g`.`note`,
		(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1)
		AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`
		FROM `rms_group` as `g`  ";	
    	
    	$where= " where 1";
    	$order=" order by id DESC";
   		if(empty($search)){
	   		return $db->fetchAll($sql.$order);
	   	}
	   	if(!empty($search['title'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['title']));
		   		$s_where[] = " group_code LIKE '%{$s_search}%'";
		   		$s_where[] = " (SELECT rms_room.room_name FROM rms_room	WHERE (rms_room.room_id = g.room_id)) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT rms_view.name_en	FROM rms_view WHERE ((rms_view.type = 4)
								AND (rms_view.key_code = g.session))LIMIT 1) LIKE '%{$s_search}%'";
		   		//$s_where[] = " (select CONCAT(from_academic,'-',to_academic)) LIKE '%{$s_search}%'";
	    		$s_where[] = " (SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`)) LIKE '%{$s_search}%'";
	   			$s_where[] = " (select kh_name from rms_dept where rms_dept.dept_id=g.degree) LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['study_year'])){
	   		$where.=' AND g.academic_year='.$search['study_year'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if(!empty($search['session'])){
	   		$where.=' AND g.session='.$search['session'];
	   	}
	   	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   public function getStudentGroup($id,$search,$type){
   	$db = $this->getAdapter();
		$sql="SELECT
					 g.gd_id,
					 `g`.`group_id` AS `group_id`,
					 `g`.`stu_id`   AS `stu_id`,
				  	 `s`.`stu_code` AS `stu_code`,
				     `s`.`stu_khname` AS `kh_name`,
				     `s`.`stu_enname` AS `en_name`,
				     `s`.`nationality` AS `nation`,
				     `s`.`address` AS `address`,
				     s.pob,
				     `s`.`tel` AS `tel`,
				     `s`.`sex` AS `gender`,
				     `s`.`dob` AS `dob`,
				     s.father_enname AS father_name,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job,
					 s.mother_enname AS mother_name,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job,

				    (SELECT
				        `rms_view`.`name_kh`
				      FROM `rms_view`
				      WHERE ((`rms_view`.`type` = 2)
				             AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`,
				  `g`.`status`   AS `status`
				FROM 
					`rms_group_detail_student` AS g,
					rms_student as s
				WHERE 
					g.stu_id = s.stu_id
		   			and `g`.`status` = 1 ";
			$sql.=' AND g.group_id='.$id;
			if($type == 0){
				$sql.=' and g.type=1 ';
			}  
			
			$order= ' ORDER BY s.stu_khname ASC,s.stu_enname ASC ';
		   	if(empty($search)){
		   		return $db->fetchAll($sql.$order);
		   	}
		   	if(!empty($search['txtsearch'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['txtsearch']));
			   		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			   		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
		   			$s_where[] = " stu_code LIKE '%{$s_search}%'";
		   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
		   	if(!empty($search['study_type'])){
		   		$sql.=' and g.type = '.$search['study_type'];
		   	}
		 return $db->fetchAll($sql.$order);
	}
	 
	public function getGroupDetail($search){
	   	$db = $this->getAdapter();
	   	$sql = 'SELECT
				   	`g`.`id`,
				   	`g`.`group_code`    AS `group_code`,
				   	(SELECT CONCAT(from_academic," - ",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	(SELECT en_name FROM `rms_dept`	WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) as degree,
				   	(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1) as grade,
				   	(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)LIMIT 1) AS `room_name`,
				   	 g.amount_month,
				   	`g`.`start_date`,
				   	`g`.`expired_date`,
				   	`g`.`note`,
				   	(SELECT `rms_view`.`name_kh` FROM `rms_view` WHERE `rms_view`.`type` = 9 AND `rms_view`.`key_code` = `g`.`is_pass` LIMIT 1) AS `status`,
				   	(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE `group_id`=`g`.`id` LIMIT 1) AS Num_Student
				FROM 
	   				`rms_group` `g`
	   			WHERE 
	   				 group_code != "" ';
	   	
	   	$where=" ";
	   	
	   	if(!empty($search['title'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['title']));
	   		$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
	   		$s_where[] = " 	`g`.`semester` LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = g.session LIMIT 1) LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 9 AND rms_view.key_code = g.is_pass LIMIT 1) LIKE '%{$s_search}%'";
	   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['study_year'])){
	   		$where.=' AND g.academic_year='.$search['study_year'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if($search['room']>0){
	   		$where.=' AND `g`.`room_id`='.$search['room'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `g`.`degree`='.$search['degree'];
	   	}
	   	if(!empty($search['session'])){
	   		$where.=' AND g.session='.$search['session'];
	   	}
	   	if(!empty($search['group'])){
	   		$where.=' AND g.id='.$search['group'];
	   	}
	   	if($search['study_status']>=0){
	   		$where.=' AND g.is_pass='.$search['study_status'];
	   	}
	   	
	   	$order = ' ORDER BY `g`.`is_pass` ASC ,`g`.`id` DESC ';
	   	return $db->fetchAll($sql.$where.$order);
	}
   
	public function getGroupDetailByID($id){
	   	$db = $this->getAdapter();
	   	$sql = 'SELECT
				   	`g`.`id`,
				   	`g`.`group_code`    AS `group_code`,
				   	(SELECT CONCAT(from_academic," - ",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	(SELECT en_name	FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) as degree,
				   	(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`)) as grade,
				   	(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
				   	`g`.`start_date`,
				   	`g`.`expired_date`,
				   	`g`.`note`,
				   	(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1) AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`,
				   	(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE `group_id`=`g`.`id`)AS Num_Student
			   	FROM 
		   			`rms_group` `g` 
		   		WHERE 
		   			`g`.`id`='.$id;
	   	
	   	return $db->fetchRow($sql);
	}
	
	function getAllTeacherByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					t.id,
					CONCAT(t.`teacher_name_kh`,'-',t.`teacher_name_en`) AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_teacher AS t
				WHERE 
					gsd.teacher = t.id
					AND gsd.group_id =  $group_id	
		
			";
		return $db->fetchAll($sql);
	}
	
	function getAllSubjectByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT
					s.id,
					CONCAT(s.`subject_titlekh`,'-',s.`subject_titleen`) AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_subject AS s
				WHERE
					gsd.subject_id = s.id
					AND gsd.group_id =  $group_id
			";
		return $db->fetchAll($sql);
	}
	
	
	
       
}