<?php

class Foundation_Model_DbTable_DbTeacher extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_teacher';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	public function getTeacherinfoById($data){//use
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		
		$colunmname='title_en';
		$vill = 'village_name';
		$comm = 'commune_name';
		$dist = 'district_name';
		$prov = 'province_en_name';
		$view = 'name_en';
		$brannch = 'branch_nameen';
		$department = 'depart_nameen';
		if ($currentLang==1){
			$colunmname='title';
			$vill = 'village_namekh';
			$comm = 'commune_namekh';
			$dist = 'district_namekh';
			$prov = 'province_kh_name';
			$view = 'name_kh';
			$brannch = 'branch_namekh';
			$department = 'depart_namekh';
		}
		
		$db = $this->getAdapter();
		$sql = "
			SELECT g.*, 
				(SELECT b.$brannch FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,	
				(SELECT p.$prov FROM rms_province AS p WHERE p.code=g.province_id LIMIT 1) AS province_name,	
				(SELECT d.$dist FROM ln_district AS d WHERE d.dis_id=g.district_name LIMIT 1) AS dis_name,	
				(SELECT c.$comm FROM ln_commune AS c WHERE c.com_id=g.commune_name LIMIT 1) AS com_name,	
				(SELECT v.$vill FROM ln_village AS v WHERE v.vill_id=g.village_name LIMIT 1) AS Village_name,			
				
				DATE_FORMAT(g.dob,'%d-%m-%Y') aS dob,
				DATE_FORMAT(g.start_date,'%d-%m-%Y') aS start_date,
				DATE_FORMAT(g.end_date,'%d-%m-%Y') aS end_date,
				
				(SELECT v.$view FROM rms_view v WHERE v.type=2  AND v.key_code=g.sex LIMIT 1) AS sex,
				(SELECT v.$view FROM rms_view v WHERE v.type=24 AND v.key_code=g.teacher_type LIMIT 1) AS teacher_type, 
				(SELECT v.$view FROM rms_view v WHERE v.type=21 AND v.key_code=g.nationality LIMIT 1) AS nationality, 
				(SELECT v.$view FROM rms_view v WHERE v.type=21 AND v.key_code=g.nation LIMIT 1) AS nation, 
				(SELECT v.$view FROM rms_view v WHERE v.type=3  AND v.key_code=g.degree LIMIT 1) AS degree,
				(SELECT d.$department FROM `rms_department`  AS d WHERE d.depart_id=g.department LIMIT 1) AS department,
				(SELECT GROUP_CONCAT( DISTINCT (SELECT p.group_code FROM `rms_group` AS p WHERE p.id = b.group_id ) ) AS fgh FROM `rms_group_reschedule` AS b WHERE b.techer_id= g.id  LIMIT 1) as teachingGroup,
				(SELECT GROUP_CONCAT( DISTINCT (SELECT p.degree FROM `rms_group` AS p WHERE p.id = b.group_id ) ) AS fgh FROM `rms_group_reschedule` AS b WHERE b.techer_id= g.id  LIMIT 1) AS degreeTye,
				(SELECT sch.setting_id FROM `rms_schedulesetting_detail` AS sch WHERE sch.id = (SELECT b.schedule_setting_id FROM  rms_group_reschedule AS b WHERE b.techer_id=g.id LIMIT 1  )  LIMIT 1) AS schSettingId
			FROM rms_teacher AS g 
				WHERE  1 ";
		if(!empty($data['id'])){
			$sql.=" AND g.id=".$data['id'];
		}
		if(!empty($data['token'])){
			$sql.=" AND g.teacher_code='".addslashes($data['token'])."'";
		}
		if(!empty($data['id_select'])){
			$sql.=" AND g.id IN (".$data['id_select'].")";
			$row=$db->fetchAll($sql);
			return $row;
		}else{
			$sql.=" LIMIT 1";
			$row=$db->fetchRow($sql);
			return $row;

		}
	
		
	}
	
	function getAllStaffBybranch($branch_id){//use
    	$db = $this->getAdapter();
		
    	$sql = "SELECT id, CONCAT(teacher_name_kh,'-',teacher_name_en,'-',teacher_code) AS name
		FROM `rms_teacher` WHERE status = 1 AND branch_id = $branch_id ";
    	return $db->fetchAll($sql);
    }

	public function getStaffInfoById($staff_id){//use
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
    	if($currentlang==1){
    		$label="name_kh";
    	}
		
		$sql = "
			SELECT 
				g.*, 
				
				DATE_FORMAT(g.dob,'%d-%m-%Y') AS dob,
				DATE_FORMAT(g.start_date,'%d-%m-%Y') AS start_date,
				DATE_FORMAT(g.end_date,'%d-%m-%Y') AS end_date,
				(SELECT v.$label FROM rms_view AS V WHERE v.type=2  AND v.key_code=g.sex LIMIT 1) AS sex,
				(SELECT v.$label FROM rms_view AS V WHERE v.type=24 AND v.key_code=g.teacher_type LIMIT 1) AS teacher_type, 
				(SELECT v.$label FROM rms_view AS V WHERE v.type=21 AND v.key_code=g.nationality LIMIT 1) AS nationality, 
				(SELECT v.$label FROM rms_view AS V WHERE v.type=21 AND v.key_code=g.nation LIMIT 1) AS nation, 
				(SELECT v.$label FROM rms_view AS V WHERE v.type=3  AND v.key_code=g.degree LIMIT 1) AS degree		
			FROM rms_teacher AS g WHERE g.status=1 AND g.id =$staff_id ";
		
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
}