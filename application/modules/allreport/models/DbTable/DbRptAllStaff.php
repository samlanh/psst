<?php

class Allreport_Model_DbTable_DbRptAllStaff extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
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
    		$s_where[] = " (select rms_items.title from rms_items where rms_items.id=rms_student.degree AND rms_items.type=1 limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select rms_itemsdetail.title from rms_itemsdetail where rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 limit 1) LIKE '%{$s_search}%'";
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



