<?php

class Allreport_Model_DbTable_DbCertify extends Zend_Db_Table_Abstract{

    protected $_name = 'rms_student';
    
    function getStudentCertify($id){
    	$db = $this->getAdapter();
    	$sql ="select
    				s.stu_code,
    				s.stu_khname,
    				s.stu_enname,
    				(select name_en from rms_view where type=2 and key_code=s.sex) as sex,
    				s.age,
    				s.nationality,
    				s.dob,
    				s.pob,
    				(select en_name from rms_dept where dept_id = g.degree) as degree,
    				(select major_enname from rms_major where major_id = g.grade) as grade,
    				CONCAT(t.from_academic,'-',to_academic) as academic_year,
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
    				and gds.gd_id=$id
    		";
    	return $db->fetchRow($sql);
    }
}