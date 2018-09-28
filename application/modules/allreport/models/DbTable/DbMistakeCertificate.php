<?php

class Allreport_Model_DbTable_DbMistakeCertificate extends Zend_Db_Table_Abstract
{
	public function getStudentInfo($group_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT 
					s.stu_id,
					s.`stu_khname`,
					s.`stu_enname`,
					(select name_kh from rms_view where type=2 and key_code=s.sex) as sex,
					s.`dob`,
					s.`pob`,
					s.`home_num`,
					s.`street_num`,
					s.`village_name`,
					s.`commune_name`,
					s.`district_name`,
					s.`province_id`,
					(select province_kh_name from rms_province as p where p.province_id = s.province_id) as province_name,
					
					s.`father_enname`,
					s.`father_job`,
					(select occu_name from rms_occupation as oc where oc.occupation_id = s.father_job) as fa_job,
					s.`father_phone`,
					
					s.`mother_enname`,
					s.`mother_job`,
					(select occu_name from rms_occupation as oc where oc.occupation_id = s.mother_job) as mo_job,
					s.`mother_phone`,
					
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=s.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`s`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`s`.`grade`) AND  (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
    				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `s`.`room`) LIMIT 1) AS `room_name`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`))LIMIT 1) AS `session`
				FROM
					`rms_student` AS s
				WHERE 
					s.`stu_id` = $stu_id
			";
		return $db->fetchRow($sql);
	}
	
	public function getMistakeRecord($search,$group_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT
					sdd.`stu_id`,
					s.`stu_khname`,
					s.`stu_enname`,
					s.sex,
					s.`dob`,
					s.`pob`,
					s.`home_num`,
					s.`street_num`,
					s.`village_name`,
					s.`commune_name`,
					s.`district_name`,
					s.`province_id`,
						
					s.`father_enname`,
					s.`father_job`,
					s.`father_phone`,
						
					s.`mother_enname`,
					s.`mother_job`,
					s.`mother_phone`,
						
					g.`academic_year`,
					g.`degree`,
					g.`grade`,
					g.`session`,
					sd.`mistake_date`,
					sdd.`mistake_type`,
					sdd.`description`
				FROM
					`rms_student_discipline` AS sd,
					`rms_student_discipline_detail` AS sdd,
					`rms_student` AS s,
					`rms_group` AS g
				WHERE
					sd.`id` = sdd.`discipline_id`
					AND sdd.`stu_id`=s.`stu_id`
					AND g.`id` = sd.`group_id`
					AND sdd.`stu_id` = $stu_id
					AND sd.`group_id` = $group_id
			";
		
		$where = " ";
		$from_date =(empty($search['start_date']))? '1': "sd.mistake_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sd.mistake_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
		return $db->fetchAll($sql.$where);
	}
	
	public function getMistakeRecordByDate($date,$group_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT
		sdd.`stu_id`,
		s.`stu_khname`,
		s.`stu_enname`,
		s.sex,
		s.`dob`,
		s.`pob`,
		s.`home_num`,
		s.`street_num`,
		s.`village_name`,
		s.`commune_name`,
		s.`district_name`,
		s.`province_id`,
	
		s.`father_enname`,
		s.`father_job`,
		s.`father_phone`,
	
		s.`mother_enname`,
		s.`mother_job`,
		s.`mother_phone`,
	
		g.`academic_year`,
		g.`degree`,
		g.`grade`,
		g.`session`,
		sd.`for_session`,
		sd.`mistake_date`,
		sdd.`mistake_type`,
		sdd.`description`
		FROM
		`rms_student_discipline` AS sd,
		`rms_student_discipline_detail` AS sdd,
		`rms_student` AS s,
		`rms_group` AS g
		WHERE
		sd.`id` = sdd.`discipline_id`
		AND sdd.`stu_id`=s.`stu_id`
		AND g.`id` = sd.`group_id`
		AND sdd.`stu_id` = $stu_id
		AND sd.`group_id` = $group_id
		";
	
		$where = " ";
// 		$date = date("Y-m-d",strtotime($date));
// 		$where.=" AND sd.mistake_date = '$date'";
		$start_year = date("Y-01-01");
		$end_year = date("Y-12-31");
// 		$where.=" AND sd.mistake_date BETWEEN '$start_year' AND '$end_year'";new
// 		$from_date =(empty($search['start_date']))? '1': "sd.mistake_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': "sd.mistake_date <= '".$search['end_date']." 23:59:59'";
// 		$where .= " AND ".$from_date." AND ".$to_date;
		 
		return $db->fetchAll($sql.$where);
	}
    function getAttendenceBydate($date,$group_id,$stu_id){
    	$db = $this->getAdapter();
    	$sql="SELECT sade.*,sta.`date_attendence`,sta.`group_id`,
			(SELECT st.`type` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `type`,
			(SELECT st.`for_session` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `for_session`
			FROM rms_student_attendence_detail AS sade,
			`rms_student_attendence` AS sta
			WHERE sta.`id` = sade.`attendence_id`";
    	//$date = date("Y-m-d",strtotime($date));
    	$where = "";
    	//$where.=" AND sta.`date_attendence` = '$date' AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id LIMIT 1";
//     	$start_year = date("Y-01-01");
//     	$end_year = date("Y-12-31");
//     	$where.=" AND sta.`date_attendence` BETWEEN '$start_year' AND '$end_year'";
    	$where.=" AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id ORDER BY sta.`date_attendence` ASC";
//     	return $db->fetchRow($sql.$where);
    	return $db->fetchAll($sql.$where);
    }
}