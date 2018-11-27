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
    				(select name_en from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				s.age,
    				s.nationality,
    				s.dob,
    				s.pob,
    				
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
    				and gds.gd_id=$id ";
    	return $db->fetchRow($sql);
    }
}