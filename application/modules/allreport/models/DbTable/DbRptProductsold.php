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
				  p.title AS pro_name,
				  spd.`qty`,
				  spd.`fee`,
				  spd.`subtotal`,
				  spd.`paidamount`,
				  sp.`create_date`,
				  (SELECT name_kh FROM `rms_pro_category` WHERE rms_pro_category.id=p.ser_cate_id ) as category_name,
				  (select first_name from rms_users where rms_users.id = sp.`user_id` LIMIT 1) as user
				FROM
				  `rms_student_payment` AS sp,
				  `rms_student_paymentdetail` AS spd ,
				  rms_program_name as p
				WHERE sp.id = spd.`payment_id` 
				  AND spd.type = 4  
				  AND sp.is_void!=1
				  AND `p`.`service_id` = spd.service_id
				  $branch_id ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date  >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " (SELECT stu_code FROM `rms_student` WHERE stu_id = student_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]= " (SELECT stu_enname FROM `rms_student` WHERE stu_id = student_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT title FROM `rms_program_name` WHERE `rms_program_name`.`service_id` = spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
//     	if(!empty($search['branch_id'])){
//     		$where.=" AND sp.branch_id=".$search['branch_id'];
//     	}
    	if(!empty($search['study_year'])){
    		$where.=" AND sp.year=".$search['study_year'];
    	}
    	if(!empty($search['user'])){
    		$where.=" AND sp.user_id=".$search['user'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND spd.service_id=".$search['product'];
    	}
    	if(!empty($search['category_id'])){
    		$where.=" AND p.ser_cate_id=".$search['category_id'];
    	}
    	return $db->fetchAll($sql.$where);
    }
    
}



