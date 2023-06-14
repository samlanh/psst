<?php

class Allreport_Model_DbTable_DbMistakeCertificate extends Zend_Db_Table_Abstract
{
	public function getStudentInfoMistake($group_id,$stu_id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
		$village_name="village_name";
		$commune_name="commune_name";
		$district_name="district_name";
		$province_name="province_en_name";
		$occuTitle="occu_enname";
		if ($currentLang==1){
			$stuname="s.stu_khname";
			$village_name="village_namekh";
			$commune_name="commune_namekh";
			$district_name="district_namekh";
			$province_name="province_kh_name";
			$occuTitle="occu_name";
		}
		$sql="SELECT 
					s.stu_id,
					s.branch_id,
					$stuname as student_name,
					s.`stu_khname`,
					s.`stu_enname`,
					s.`last_name`,
					(SELECT name_kh from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
					s.`dob`,
					s.`pob`,
					s.`home_num`,
					s.`street_num`,
					s.`province_id`,
					s.photo,
					(SELECT $province_name from rms_province as p where p.province_id = s.province_id LIMIT 1) as province_name,
					(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
					s.father_khname,
					s.`father_job`,
					(SELECT occu_name from rms_occupation as oc where oc.occupation_id = s.father_job LIMIT 1) as fa_job,
					(SELECT occu_enname from rms_occupation as oc where oc.occupation_id = s.father_job LIMIT 1) as faJobEng,
					(SELECT $occuTitle from rms_occupation as oc where oc.occupation_id = s.father_job LIMIT 1) as faJobTitle,
					s.`father_phone`,
					s.mother_khname,
					s.`mother_job`,
					(SELECT occu_name from rms_occupation as oc where oc.occupation_id = s.mother_job LIMIT 1) as mo_job,
					(SELECT occu_enname from rms_occupation as oc where oc.occupation_id = s.mother_job LIMIT 1) as moJobEng,
					(SELECT $occuTitle from rms_occupation as oc where oc.occupation_id = s.mother_job LIMIT 1) as moJobTitle,
					s.`mother_phone`,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gsd.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`gsd`.`grade`) AND  (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
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


    function getAttendenceBydate($semester,$group_id,$stu_id){
    	$db = $this->getAdapter();
    	$sql="SELECT sade.*,
    				sta.`date_attendence`,sta.`group_id`,
					(SELECT st.`type` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `type`,
					(SELECT st.`for_session` FROM `rms_student_attendence` AS st WHERE st.id = sade.`attendence_id` LIMIT 1) AS `for_session`
				FROM rms_student_attendence_detail AS sade,
				`rms_student_attendence` AS sta
					WHERE sta.`id` = sade.`attendence_id` ";
    	$where = " AND sta.for_semester=".$semester;
    	$where.=" AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id ORDER BY sta.`date_attendence` ASC";
    	return $db->fetchAll($sql.$where);
    }
}