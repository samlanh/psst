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
    	
    	$sql="SELECT 
					sd.id,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branch_name,
					sd.`pro_id`,
					pn.title as pro_name,
					sd.qty,
					sd.`cost`,
					sd.`price`,
					(sd.qty*sd.price) AS total
				FROM 
					`rms_saledetail` AS sd,
					rms_program_name AS pn,
					rms_student_payment AS sp
				WHERE 
					sd.pro_id = pn.service_id
					AND sp.id = sd.payment_id
					AND sp.is_void=0 
    		";
    	
    	$order_by = " ORDER BY sd.`pro_id` ASC";
    	
    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search= addslashes(trim($search['adv_search']));
    		//$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['pro_name'])){
    		$where.= " AND sd.pro_id = ".$search['pro_name'];
    	}
//     	echo $sql.$where.$order_by;
    	return $db->fetchAll($sql.$where.$order_by);
    }	
    
    function getAllProductInProgramName(){
    	$db = $this->getAdapter();
    	$sql="select service_id as id,title from rms_program_name where type=1 and status=1 ";
    	return $db->fetchAll($sql);
    }
    
    
    
    
}



