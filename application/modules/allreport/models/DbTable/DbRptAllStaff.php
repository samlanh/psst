<?php

class Allreport_Model_DbTable_DbRptAllStaff extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStaff($search){

    	$db = $this->getAdapter();
    	$sql =" select 
    				*,
    				(select name_en from rms_view where type=2 and key_code=sex) as staff_sex,
    				(select branch_nameen from rms_branch where br_id = branch_id) as branch_name,
    				(select title from rms_staff_position where rms_staff_position.id = position ) as staff_position
    			from
    				rms_teacher
    			where 
    				status = 1
    		";
    	
    	$where=' ';
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}

    	return $db->fetchAll($sql.$where);
    }
    
    public function getAllStaffSelected($staff_id){
    	//print_r($search);
    	//echo $search['stu_type'];
    	$db = $this->getAdapter();
    	$sql ="select 
    				*,
    				(select name_en from rms_view where type=2 and key_code=sex) as staff_sex,
    				(select branch_nameen from rms_branch where br_id = branch_id) as branch_name,
    				(select title from rms_staff_position where rms_staff_position.id = position ) as staff_position
    			from
    				rms_teacher
    			where 
    				status = 1
    				and id = $staff_id ";

    	return $db->fetchAll($sql);
    }
    
    
}



