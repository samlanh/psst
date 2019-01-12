<?php

class Allreport_Model_DbTable_DbCertify extends Zend_Db_Table_Abstract{
    protected $_name = 'rms_student';
    
    function getStudentCertify($id){
    	$db = $this->getAdapter();
    	$sql ="select
    				s.*,
    				(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
					(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
					(SELECT b.branch_tel FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_tel,
					(SELECT b.br_address FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS br_address,
					(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS school_namekh,
					(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS school_nameen,
					(SELECT b.website FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS website,
					(SELECT b.email FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS email,
    				CONCAT(s.stu_enname,' ',s.last_name) AS name_englsih,
    				(select name_en from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				(SELECT name_kh FROM rms_view WHERE TYPE=2 AND key_code=s.sex LIMIT 1) AS sexkh,
    				(SELECT province_kh_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_khname,
    				(SELECT province_en_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_enname,
    				(SELECT commune_namekh FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS commune_name,
    				(SELECT commune_name FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS commune_nameeg,
    				(SELECT district_namekh FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS district_khmer,
    				(SELECT district_name FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS district_en,
    				(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) AS fa_job,
					(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) AS mo_job,
    				(SELECT rms_items.title FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degree,
    				(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS grade,
    				CONCAT(t.from_academic,' - ',t.to_academic) as academic_year,
    				t.generation
    			from 
    				rms_student as s,
    				rms_group as g,
    				rms_group_detail_student as gds,
    				rms_tuitionfee as t
    			where 
    				s.stu_id = gds.stu_id
    				and g.id=gds.group_id
    				and g.academic_year=t.id
    				and gds.gd_id=$id";
    	return $db->fetchRow($sql);
    }
}