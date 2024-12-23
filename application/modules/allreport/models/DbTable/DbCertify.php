<?php

class Allreport_Model_DbTable_DbCertify extends Zend_Db_Table_Abstract{
    protected $_name = 'rms_student';
    
    function getStudentCertify($id){
    	$db = $this->getAdapter();
    	$sql ="SELECT
					
    				s.*
					,fam.fatherNameKh AS father_khname 
					,fam.fatherName AS father_enname  
					,fam.fatherNation AS father_nation
					,fam.fatherPhone AS father_phone
					
					,fam.motherNameKh AS mother_khname 
					,fam.motherName AS mother_enname  
					,fam.motherPhone AS mother_phone  
					
					,fam.guardianNameKh AS guardian_khname 
					,fam.guardianName AS guardian_enname 
					,fam.guardianPhone AS guardian_tel
					
					,DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`,
    				 DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob_en`,
    				(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
					(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
					(SELECT b.branch_tel FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_tel,
					(SELECT b.branch_tel1 FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_tel1,
					(SELECT b.br_address FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS br_address,
					(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS school_namekh,
					(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS school_nameen,
					(SELECT b.website FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS website,
					(SELECT b.email FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS email,
    				CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name_englsih,
    				(select name_en from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				(SELECT name_kh FROM rms_view WHERE TYPE=2 AND key_code=s.sex LIMIT 1) AS sexkh,
    				(SELECT province_kh_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_khname,
    				(SELECT province_en_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_enname,
    				(SELECT commune_namekh FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS commune_name,
    				(SELECT commune_name FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS commune_nameeg,
    				(SELECT district_namekh FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS district_khmer,
    				(SELECT district_name FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS district_en
					
				
    				,(SELECT occ.occu_name FROM rms_occupation AS occ 	WHERE occ.occupation_id=fam.fatherJob LIMIT 1) AS fa_job,
					(SELECT occ.occu_enname FROM rms_occupation AS occ 	WHERE occ.occupation_id=fam.fatherJob LIMIT 1) AS faJobEng,
					(SELECT occ.occu_name FROM rms_occupation AS occ 	WHERE occ.occupation_id=fam.motherJob LIMIT 1) AS mo_job,
					(SELECT occ.occu_enname FROM rms_occupation AS occ 	WHERE occ.occupation_id=fam.motherJob LIMIT 1) AS moJobEng,
					
    				(SELECT rms_items.title FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degree,
    				(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS grade,
    				(SELECT rms_itemsdetail.title_en FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS grade_eng,
    				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = t.academic_year LIMIT 1) AS academic_year,
    				t.generation
    			from 
    				rms_student AS s JOIN rms_group_detail_student AS gds ON gds.itemType=1 AND s.stu_id = gds.stu_id AND gds.is_maingrade=1
    				LEFT JOIN rms_group AS g ON g.id = gds.group_id
    				LEFT JOIN rms_tuitionfee AS t ON g.academic_year=t.id
    				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
    			WHERE 
    				1 AND s.stu_id=$id";
    	return $db->fetchRow($sql);
    }
}