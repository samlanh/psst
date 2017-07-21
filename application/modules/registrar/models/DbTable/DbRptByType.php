<?php

class Registrar_Model_DbTable_DbRptByType extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_paymentdetail';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    
	function getAllStudentPaymentByType($search=null,$order_type){
		$db=$this->getAdapter();
		try{
	    	$_db = new Application_Model_DbTable_DbGlobal();
	    	$branch_id = $_db->getAccessPermission('sp.branch_id');
	    	$user_level = $_db->getUserAccessPermission('sp.user_id');

        	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
	    	
	    	$sql=" SELECT 
					  spd.id,
					  spd.`payment_id`,
					  spd.type,
					  spd.paidamount,
					  sp.fine,
					  sp.credit_memo,
					  sp.deduct
					FROM
					  rms_student_paymentdetail AS spd,
					  rms_student_payment AS sp
					WHERE 
					  sp.id=spd.`payment_id`
					  AND sp.`is_void` = 0
	    		";
	    	
	    	$where = " AND ".$from_date." AND ".$to_date;
	    	
	    	if(!empty($search['adv_search'])){
	    		$s_where=array();
	    		$s_search= addslashes(trim($search['adv_search']));
	    		$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
	    		$s_where[]=" sp.receipt_number LIKE '%{$s_search}%'";
	    		$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
	    		$s_where[]= " s.stu_enname LIKE '%{$s_search}%'";
	    		$s_where[]= " s.grade LIKE '%{$s_search}%'";
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	
	    	if(!empty($search['user'])){
	    		$where.= " AND sp.user_id = ".$search['user'];
	    	}
	    	if($order_type==1){
	    		$order=" ORDER By spd.type ASC ";
	    	}else{
	    		$order=" ORDER By spd.payment_id ASC ";
	    	}
// 	    	echo $sql.$where.$order;exit();

	    	return $db->fetchAll($sql.$where.$order);
	    	
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	    
	    
}





