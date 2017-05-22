<?php
class Allreport_Model_DbTable_DbRptProductsold extends Zend_Db_Table_Abstract
{
    function  getAllProductSold($search){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql=" SELECT 
				  (SELECT stu_code FROM `rms_student` WHERE stu_id = student_id) AS stu_code,
				  (SELECT stu_enname FROM `rms_student` WHERE stu_id = student_id) AS stu_name,
				  (SELECT title FROM `rms_program_name` WHERE `rms_program_name`.`service_id` = spd.service_id) AS pro_name,
				  spd.`qty`,
				  spd.`fee`,
				  spd.`subtotal`,
				  spd.`paidamount`,
				  sp.`create_date`,
				  (select first_name from rms_users where rms_users.id = sp.`user_id` ) as user
				FROM
				  `rms_student_payment` AS sp,
				  `rms_student_paymentdetail` AS spd 
				WHERE sp.id = spd.`payment_id` 
				  AND spd.type = 4  
				  AND sp.is_void!=1
				  $branch_id
			";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date  >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['txtsearch'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['txtsearch']));
    		$s_where[]= " (SELECT stu_code FROM `rms_student` WHERE stu_id = student_id) LIKE '%{$s_search}%'";
    		$s_where[]= " (SELECT stu_enname FROM `rms_student` WHERE stu_id = student_id) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT title FROM `rms_program_name` WHERE `rms_program_name`.`service_id` = spd.service_id) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND sp.branch_id=".$search['branch_id'];
    	}
    	
    	return $db->fetchAll($sql.$where);
    }
    
}



