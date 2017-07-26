<?php

class Registrar_Model_DbTable_DbProductsold extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getProductSold($search){
    	$db = $this->getAdapter();
    	
    	$sql="SELECT sd.id,
    	 	 (SELECT branch_nameen FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branch_name,
			 (SELECT CONCAT(stu_khname,'-',stu_enname) FROM `rms_student` WHERE stu_id=sp.student_id LIMIT 1) AS stu_enname,
			 (SELECT stu_code FROM `rms_student` WHERE stu_id=sp.student_id LIMIT 1) AS stu_code,
			 sp.receipt_number,
			 sd.qty,
			 (SELECT pn.title FROM `rms_program_name` AS pn WHERE pn.service_id=sd.pro_id) AS pro_name,
			 sp.note,
			 (SELECT u.first_name FROM `rms_users` as u WHERE sp.user_id =u.id ) as user_name
			  FROM `rms_saledetail` AS sd,rms_student_payment AS sp,rms_student as s
			 WHERE sp.id=sd.payment_id and s.stu_id = sp.student_id ";
    	
    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search= addslashes(trim($search['adv_search']));
    		$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[]= " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[]= " s.stu_enname LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['user'])){
    		$where.= " AND sp.user_id = ".$search['user'];
    	}
    	if(!empty($search['stu_code'])){
    		$where.= " AND sp.student_id = ".$search['stu_code'];
    	}
    	if(!empty($search['stu_name'])){
    		$where.= " AND sp.student_id = ".$search['stu_name'];
    	}
    	if(!empty($search['pro_name'])){
    		$where.= " AND sd.pro_id = ".$search['pro_name'];
    	}
    	
    	return $db->fetchAll($sql.$where);
    }	
    
    function getAllProductInProgramName(){
    	$db = $this->getAdapter();
    	$sql="select service_id as id,title from rms_program_name where type=1 and status=1 ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}



