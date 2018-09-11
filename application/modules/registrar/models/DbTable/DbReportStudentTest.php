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

        	$from_date =(empty($search['start_date']))? '1': "str.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "str.create_date <= '".$search['end_date']." 23:59:59'";
	    	
	    	$sql=" SELECT 
					  st.*,
					  (SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code=st.sex LIMIT 1) AS sex,    
					  (SELECT i.title FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
					  (SELECT idd.title FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
					  (SELECT i.title FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
					  (SELECT idd.title FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,								 
					  (SELECT first_name FROM rms_users WHERE rms_users.id = str.user_id LIMIT 1) AS user_id,
					  (SELECT name_kh FROM rms_view WHERE TYPE=15 AND key_code = str.updated_result LIMIT 1) AS result_status,
					  (SELECT name_kh FROM rms_view WHERE TYPE=16 AND key_code = st.register LIMIT 1) AS register_status,
					  str.create_date as create_date_exam,
					  str.test_date as test_date_exam,
					  str.updated_result as updated_result_de
					  
					  
					FROM
					  rms_student_test AS st,
					  `rms_student_test_result` AS str
					WHERE 
					str.stu_test_id = st.id
					AND
						STATUS=1  
					AND st.kh_name!=''
					AND st.is_makestudenttest =1
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
	    		$where.= " AND str.user_id = ".$search['user'];
	    	}
	    	if(!empty($search['degree'])){
	    		$where .= " and str.degree_result = ".$search['degree'];
	    	}
	    	if($search['register_status']!=''){
	    		$where .= " and st.register = ".$search['register_status'];
	    	}
	    	if($search['result_status']!=''){
	    		$where .= " and str.updated_result = ".$search['result_status'];
	    	}
	    	$order=" ORDER By str.updated_result DESC,str.degree_result ASC,str.grade_result ASC ";
// 	    	echo $sql.$where.$order;exit();
	    	return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	    
	    
	  
	
	   
}









