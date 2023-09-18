<?php

class Allreport_Model_DbTable_DbRptMobileApp extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_student';
	public function getAllDisableAccountStudent($search)
	{ //for card list
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		$field = 'name_en';


		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "branch_namekh";

			$village_name = "village_namekh";
			$commune_name = "commune_namekh";
			$district_name = "district_namekh";
			$province = "province_kh_name";

			$stu_name = "s.stu_khname";
			$field = 'name_kh';
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "branch_nameen";

			$village_name = "village_name";
			$commune_name = "commune_name";
			$district_name = "district_name";
			$province = "province_en_name";

			$stu_name = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		}
		$sql = "SELECT 
    				*,
    				s.branch_id,
	    	      (SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
	    	       CONCAT(COALESCE(s.stu_khname,''),' - ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) as name,
	    	       s.stu_khname,
	    	       s.last_name,
	    	       s.stu_enname,
	    	       s.photo,
				   CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS en_name,
				   $stu_name as stu_name,
	    	      (SELECT $label FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
	    		  (SELECT $label FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
	    		  (SELECT $label from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS sex,
	    		  gds.degree as dept,
			      (SELECT $degree FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
			      (SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			      (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic_year,
			      (SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS start_year,
			      (SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS end_year,
			      (SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
	    		   (SELECT $label from rms_view where type=5 and key_code=gds.stop_type LIMIT 1) as status,
	    		   (SELECT g.group_code FROM rms_group AS g WHERE g.id = gds.group_id LIMIT 1) AS group_name,
	    		   (SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=(SELECT g.session FROM rms_group AS g WHERE g.id = gds.group_id LIMIT 1) LIMIT 1)AS session,
	    		   (SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			       (SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			       (SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
	    		   (SELECT $province from rms_province where rms_province.province_id = s.province_id LIMIT 1) AS province,
	    		   (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				   (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				   (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job
    		   	FROM 
    				rms_student AS s,
    				rms_group_detail_student AS gds
    			WHERE 
					gds.itemType=1 AND
    				s.stu_id = gds.stu_id
    				AND s.status=1 
    				AND gds.is_current =1
    				AND gds.is_maingrade =1
    				AND s.customer_type=1 
					AND s.isDisbleAccount=1";

		$where = ' ';

		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission('s.branch_id');
		$from_date = (empty($search['start_date'])) ? '1' : "s.disableDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "s.disableDate <= '" . $search['end_date'] . " 23:59:59'";
		$where .= " AND " . $from_date . " AND " . $to_date;

		$adv_search = empty($search['adv_search']) ? "" : $search['adv_search'];
		$search['title'] = empty($search['title']) ? $adv_search : $search['title'];
		if (!empty($search['title'])) {
			$s_where = array();
			//$s_search = addslashes(trim($search['title']));
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
			$s_where[] = " REPLACE(s.stu_code,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_khname,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_enname,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.last_name,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.tel,' ','') LIKE '%{$s_search}%'";

			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND gds.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['group'])) {
			$where .= ' AND gds.group_id=' . $search['group'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND gds.degree=' . $search['degree'];
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND s.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND gds.grade=' . $search['grade'];
		}
		if (!empty($search['session'])) {
			$where .= ' AND session=' . $search['session'];
		}
		return $db->fetchAll($sql . $where);
	}
	public function getAllPreRegister($search)
	{ //for card list
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();

		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		$sql = "SELECT *,
			CONCAT(COALESCE(p.lastName,''),' ',COALESCE(p.firstName,'')) AS en_name,
			(SELECT $label FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=p.gender LIMIT 1) AS sex,
			(SELECT $degree FROM rms_items WHERE rms_items.id=p.degree AND rms_items.type=1 LIMIT 1) AS degree,
		 	(SELECT $grade  FROM rms_itemsdetail WHERE rms_itemsdetail.id=p.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade
		
		   FROM `rms_mobile_pre_register` AS p WHERE 1";
		$where = ' ';
		$from_date = (empty($search['start_date'])) ? '1' : "p.createDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "p.createDate <= '" . $search['end_date'] . " 23:59:59'";
		$where .= " AND " . $from_date . " AND " . $to_date;

		$adv_search = empty($search['adv_search']) ? "" : $search['adv_search'];
		$search['title'] = empty($search['title']) ? $adv_search : $search['title'];
		if (!empty($search['title'])) {
			$s_where = array();
			//$s_search = addslashes(trim($search['title']));
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));

			$s_where[] = " REPLACE(p.fullKhName,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.firstName,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.lastName,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(CONCAT(p.firstName,p.lastName),' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(CONCAT(p.lastName,p.firstName),' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.phoneNumber,' ','') LIKE '%{$s_search}%'";

			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}

		if (!empty($search['degree'])) {
			$where .= ' AND p.degree=' . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND p.grade=' . $search['grade'];
		}

		return $db->fetchAll($sql . $where);
	}
}
