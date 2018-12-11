<?php

class Allreport_Model_DbTable_DbCertify extends Zend_Db_Table_Abstract{
    protected $_name = 'rms_student';
    
    function getStudentCertify($id){
    	$db = $this->getAdapter();
    	$sql ="select
    				s.stu_code,
    				s.stu_khname,
    				s.last_name,
    				s.stu_enname,
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
    				s.father_enname,
    				s.father_khname,
    				s.mother_khname,
    				s.mother_enname,
    				s.age,
    				s.nationality,
    				s.dob,
    				s.pob,
    				s.home_num,
    				s.street_num,
    				s.photo,
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