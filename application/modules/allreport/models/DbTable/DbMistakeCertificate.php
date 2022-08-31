<?php

class Allreport_Model_DbTable_DbMistakeCertificate extends Zend_Db_Table_Abstract
{
	public function getStudentInfo($group_id,$stu_id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
		$village_name="village_name";
		$commune_name="commune_name";
		$district_name="district_name";
		$province_name="province_en_name";
		if ($currentLang==1){
			$stuname="s.stu_khname";
			$village_name="village_namekh";
			$commune_name="commune_namekh";
			$district_name="district_namekh";
			$province_name="province_kh_name";
		}
		$sql="SELECT 
					s.stu_id,
					s.branch_id,
					$stuname as student_name,
					s.`stu_khname`,
					s.`stu_enname`,
					s.`last_name`,
					(select name_kh from rms_view where type=2 and key_code=s.sex) as sex,
					s.`dob`,
					s.`pob`,
					s.`home_num`,
					s.`street_num`,
					s.`province_id`,
					s.photo,
					(select $province_name from rms_province as p where p.province_id = s.province_id) as province_name,
					(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
					s.`father_enname`,
					s.`father_job`,
					(select occu_name from rms_occupation as oc where oc.occupation_id = s.father_job) as fa_job,
					s.`father_phone`,
					
					s.`mother_enname`,
					s.`mother_job`,
					(select occu_name from rms_occupation as oc where oc.occupation_id = s.mother_job) as mo_job,
					s.`mother_phone`,
					
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gsd.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`gsd`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`gsd`.`grade`) AND  (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = (SELECT g.room_id FROM rms_group AS g WHERE g.id =gsd.`group_id` LIMIT 1 )) LIMIT 1) AS `room_name`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `gsd`.`session`))LIMIT 1) AS `session`
				FROM
					`rms_student` AS s,
					 `rms_group_detail_student` AS gsd
				WHERE 
					gsd.itemType=1 AND
					s.`stu_id` =gsd.stu_id
					AND s.`stu_id` = $stu_id
					AND gsd.`group_id` = $group_id
			";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("s.branch_id");
		return $db->fetchRow($sql);
		
	}


    function getAttendenceBydate($date,$group_id,$stu_id){
    	$db = $this->getAdapter();
    	$sql="SELECT sade.*,sta.`date_attendence`,sta.`group_id`,
			(SELECT st.`type` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `type`,
			(SELECT st.`for_session` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `for_session`
			FROM rms_student_attendence_detail AS sade,
			`rms_student_attendence` AS sta
			WHERE sta.`id` = sade.`attendence_id`";

    	$where = "";
    	$where.=" AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id ORDER BY sta.`date_attendence` ASC";
    	return $db->fetchAll($sql.$where);
    }
}