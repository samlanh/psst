<?php

class Allreport_Model_DbTable_DbRptPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    public function getStudentPaymentByid($id){
    	$db = $this->getAdapter();
    	$sql = "select 
    				s.stu_code,
    				s.stu_khname,
    				s.stu_enname,
    				(select name_kh from rms_view where type=2 and key_code=s.sex) as sex,
    				sp.receipt_number,
    				sp.create_date,
    				(select first_name from rms_users where id=sp.user_id) as user,
    				
    				sp.grand_total,
    				sp.credit_memo,
    				sp.deduct,
    				sp.fine,
    				sp.net_amount
    			from
    				rms_student_payment as sp,
					rms_student as s,
    				rms_student_paymentdetail as spd
    			WHERE 
    				sp.student_id=s.stu_id 
    				and sp.id=$id
    				
    	";
    	return $db->fetchRow($sql);
    }
    
    public function getStudentPayment($search){
    	$db = $this->getAdapter();
    	$where=' ';
    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
	   	$sql="SELECT 
	   				sp.id,
	   				s.stu_code,
	   				s.stu_khname,
	   				s.stu_enname,
	   				sp.receipt_number,
	   				sp.grand_total as total_payment,
	   				sp.fine,
	   				sp.credit_memo,
	   				sp.deduct,
	   				sp.net_amount,
	   				sp.create_date,
	   				(select first_name from rms_users where rms_users.id=sp.user_id) as user,
	   				sp.note,
	   				sp.is_void 
	   			FROM 
	   				rms_student_payment as sp,
					rms_student as s
	   			WHERE sp.student_id=s.stu_id ";
	   	
    	$order=" ORDER BY id DESC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
	 		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getService(){
    	$db = $this->getAdapter();
    	$sql="SELECT `service_id`,title FROM `rms_program_name` WHERE `type`=2  AND `status`=1";
    	return $db->fetchAll($sql);
    }
    
    public function getPaymentDetailByType($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$where = ' ' ;
    	
    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = "SELECT * FROM v_getstudentpaymentdetail WHERE 1 $branch_id ";
    	$order=" ORDER BY service_id DESC ";
    	
    	if($search['service_type']>0){
    		$where.=" AND service_id =".$search['service_type'];
    	} 
    	
    	if(!empty($search['branch_id'])){
    		$where .= " and branch_id = ".$search['branch_id'];
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$s_where[] = " service LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " payment_term LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getPaymentDetailByTypeSumup($search){
    	$db = $this->getAdapter();
    	 
    	$where = ' ' ;
    	 
    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$sql = 'SELECT * FROM v_test WHERE 1 ';
    	$order=" ORDER BY service_categoryid DESC ";
    	 
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$s_where[] = " service LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " payment_term LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    
    public function getStudentPaymentDetail($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		
    	$sql = "SELECT * FROM v_getstudentpaymentdetail WHERE 1  $branch_id  ";
    	
    	$sql=" select 
    			  spd.id,
				  sp.receipt_number,
				  s.stu_code,
				  s.stu_khname,
				  s.stu_enname,
				  spd.type,
				  sp.tuition_fee,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.late_fee,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_fix,
				  
				  spd.paidamount,
				  spd.balance,
				  sp.create_date,
				  sp.is_void,
				  spd.note,
				  spd.start_date,
				  spd.validate,
				  spd.is_start,
				  spd.is_parent ,
				  spd.is_complete,
				  (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.year LIMIT 1) AS year,
				  (SELECT pg.title FROM rms_program_name AS pg WHERE pg.service_id=spd.service_id) AS service_name,
				  (SELECT CONCAT(last_name,' - ',first_name) FROM rms_users WHERE rms_users.id = sp.user_id) AS user,
				  (SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term) AS payment_term,
				  (select name_en from rms_view where type=10 and key_code=sp.is_void) as void_status
    			from 
    				rms_student_payment as sp,
    				rms_student_paymentdetail as spd,
    				rms_student as s
    			where 
    				s.stu_id = sp.student_id
    				and sp.id=spd.payment_id
    		 ";
    	
    	
    	$order=" ORDER BY payment_id DESC , receipt_number DESC ";
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$s_where[] = " service LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where .= " and branch_id = ".$search['branch_id'];
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getPaymentReciptDetail($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
    	payment_id,
    	(SELECT receipt_number FROM `rms_student_payment` WHERE id=payment_id LIMIT 1) as receipt_number,
    	(SELECT (SELECT `rms_student`.`stu_khname`FROM `rms_student` WHERE (`rms_student`.`stu_id` = `rms_student_payment`.`student_id`) LIMIT 1)FROM `rms_student_payment` WHERE id=payment_id LIMIT 1) as kh_name,
    	(SELECT (SELECT `rms_student`.`stu_enname`FROM `rms_student` WHERE (`rms_student`.`stu_id` = `rms_student_payment`.`student_id`) LIMIT 1)FROM `rms_student_payment` WHERE id=payment_id LIMIT 1) as en_name,
    	(SELECT (SELECT (SELECT `rms_view`.`name_kh`FROM `rms_view` WHERE ((`rms_view`.`type` = 2) AND (`rms_view`.`key_code` = `rms_student`.`sex`))) FROM `rms_student` WHERE (`rms_student`.`stu_id` = `rms_student_payment`.`student_id`)LIMIT 1)FROM `rms_student_payment` WHERE id=payment_id LIMIT 1) as sex,
    	type,fee,qty,subtotal,
    	(SELECT title FROM `rms_program_name` WHERE `rms_program_name`.`service_id`= rms_student_paymentdetail.service_id LIMIT 1) as service,
    	(SELECT `name_en` FROM `rms_view` WHERE  `type`=6 AND key_code= payment_term LIMIT 1)as payment_term,
    	subtotal,paidamount,
    	(SELECT `total_payment` FROM `rms_student_payment` WHERE id= payment_id LIMIT 1) as total_payment,
    	(SELECT `paid_amount` FROM `rms_student_payment` WHERE id= payment_id LIMIT 1) as paid_amount,
    	(SELECT `balance_due` FROM `rms_student_payment` WHERE id= payment_id LIMIT 1) as balance_due,
    	(SELECT `return_amount` FROM `rms_student_payment` WHERE id= payment_id LIMIT 1) as return_amount,
    	(SELECT `amount_in_khmer` FROM `rms_student_payment` WHERE id= payment_id LIMIT 1) as amount_in_khmer,
    	discount_fix,discount_percent,
    	(SELECT CONCAT (`last_name`,' ', `first_name`) FROM `rms_users` WHERE `rms_users`.id = user_id LIMIT 1) as user,
    	note,validate
    	FROM rms_student_paymentdetail
    	";
    	$sql.='WHERE payment_id = '.$id;
    	//echo $sql;
		return $db->fetchAll($sql);    	
    }
}
   
    
   