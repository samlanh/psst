<?php

class Registrar_Model_DbTable_DbReportStudentTest extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    
	function getAllStudentTest($search=null){
		try{
	    	$_db = new Application_Model_DbTable_DbGlobal();
	    	$branch_id = $_db->getAccessPermission('st.branch_id');
	    	
	    	$db=$this->getAdapter();

        	$from_date =(empty($search['start_date']))? '1': "st.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "st.create_date <= '".$search['end_date']." 23:59:59'";
	    	
	    	$sql=" SELECT 
					  st.id,
					  st.serial,
					  st.stu_code,
					  st.kh_name,
					  st.en_name,
					  (SELECT name_en from rms_view where type=2 and key_code=st.sex LIMIT 1) as sex,
					  st.dob,
					  st.phone,
					  st.create_date,					  
					  st.degree_result,
					  st.grade_result,
					  st.session_result,					  
					  (SELECT en_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=st.degree_result) LIMIT 1) AS degree,
		   			  (SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=st.grade_result) LIMIT 1 )AS grade,
		   			  (SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = st.session_result))LIMIT 1) AS `session`,
					  st.note,
					  (SELECT first_name FROM rms_users WHERE rms_users.id = st.user_id limit 1) AS user_id,
					  (select name_en from rms_view where type=15 and key_code = updated_result LIMIT 1) as updated_result
					  
					FROM
					  rms_student_test AS st
					WHERE 
						status=1  
						and st.kh_name!=''
						and register=0
						$branch_id  
	    		";
	    	
	    	$where = " AND ".$from_date." AND ".$to_date;
	    	
	    	if(!empty($search['adv_search'])){
	    		$s_where=array();
	    		$s_search= addslashes(trim($search['adv_search']));
	    		$s_where[]= " st.serial LIKE '%{$s_search}%'";
	    		$s_where[]= " st.stu_code LIKE '%{$s_search}%'";
	    		$s_where[]= " st.kh_name LIKE '%{$s_search}%'";
	    		$s_where[]= " st.en_name LIKE '%{$s_search}%'";
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(($search['branch_id']>0)){
	    		$where.= " AND st.branch_id = ".$search['branch_id'];
	    	}
	    	if(!empty($search['user'])){
	    		$where.= " AND st.user_id = ".$search['user'];
	    	}
	    	if(!empty($search['degree'])){
	    		$where .= " and degree = ".$search['degree'];
	    	}
	    	if(!empty($search['result_status'])){
	    		$where .= " and updated_result = ".$search['result_status'];
	    	}
	    	$order=" ORDER By st.id DESC ";
// 	    	echo $sql.$where.$order;exit();
	    	return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	  }
	    
	    
	  
	
	   
}









