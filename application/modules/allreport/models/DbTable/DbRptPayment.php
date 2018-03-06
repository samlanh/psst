<?php

class Allreport_Model_DbTable_DbRptPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    public function getStudentPaymentByid($id){
    	$db = $this->getAdapter();
    	$sql = "select 
    				s.stu_code,
    				s.stu_khname,
    				s.stu_enname,
    				s.tel,
    				s.dob,
    				(select name_kh from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				sp.receipt_number,
    				sp.create_date,
    				(select first_name from rms_users where id=sp.user_id LIMIT 1) as user,
    				sp.grand_total,
    				sp.credit_memo,
    				sp.deduct,
    				sp.fine,
    				sp.net_amount,
    				(SELECT CONCAT(from_academic,'-',to_academic) 
						FROM rms_tuitionfee where rms_tuitionfee.id=sp.year LIMIT 1) AS academic_year,
    				(select en_name from rms_dept where dept_id = sp.degree LIMIT 1) as degree,
					(select major_enname from rms_major where major_id = sp.grade LIMIT 1) as grade,
					(select name_en from rms_view where rms_view.type = 4 and key_code=sp.session LIMIT 1) as session,
					spd.type
    			from
    				rms_student_payment as sp,
					rms_student as s,
    				rms_student_paymentdetail as spd
    			WHERE 
    				sp.student_id=s.stu_id 
    				and sp.id=$id ";
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
	   				s.stu_id,
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
	   				sp.is_void,
	   				(select name_en from rms_view where type=10 and key_code = is_void) as vois_status 
	   			FROM 
	   				rms_student_payment as sp,
					rms_student as s
	   			WHERE 
	   				sp.student_id=s.stu_id ";
	   	
    	$order=" ORDER BY id DESC";
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
	 		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " AND sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['study_year']>0){
    		$where .= " AND sp.year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " AND sp.degree = ".$search['degree'];
    	}
    	if($search['grade_all']>0){
    		$where .= " AND sp.grade = ".$search['grade_all'];
    	}
    	if($search['session']>0){
    		$where .= " AND sp.session = ".$search['session'];
    	}
    	if($search['user']>0){
    		$where .= " AND sp.user_id = ".$search['user'];
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
    
    
    public function getStudentPaymentDetail($search,$order_no){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		
    	$sql=" Select 
    			  spd.id,
    			  spd.type,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.late_fee,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_fix,
				  spd.paidamount,
				  spd.balance,
				  spd.note,
				  spd.start_date,
				  spd.validate,
				  spd.is_start,
				  spd.is_parent ,
				  spd.is_complete,
				  sp.scholarship_percent,
				  sp.scholarship_amount,
				  sp.tuition_fee,
				  sp.student_id,
				  sp.receipt_number,
				  sp.create_date,
				  sp.is_void,
				  s.stu_code,
				  s.stu_khname,
				  s.stu_enname,
				  p.title AS service_name,
				  (SELECT pg.name_kh FROM `rms_pro_category` AS pg WHERE pg.id = (SELECT pp.cat_id FROM `rms_product` AS pp WHERE pp.id = p.ser_cate_id LIMIT 1) LIMIT 1) AS product_category,
				  (SELECT major_enname FROM `rms_major` WHERE major_id=sp.grade LIMIT 1) As major_name,
				  (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
				  (SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
				  (select name_en from rms_view where type=10 and key_code=sp.is_void LIMIT 1) as void_status,
				  (select title from rms_program_type where rms_program_type.id=p.ser_cate_id AND p.type=2 LIMIT 1) service_cate                             
    			FROM 
    				rms_student_payment as sp,
    				rms_student_paymentdetail as spd,
    				rms_student as s,
    				rms_program_name as p
    			where 
    				s.stu_id = sp.student_id
    				AND sp.id=spd.payment_id 
    				AND p.service_id=spd.service_id ";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " p.title LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " and sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['payment_by']>0){
    		$where .= " and spd.type = ".$search['payment_by'];
    	}
    	if(!empty($search['service'])){
    		$where .= " AND spd.type!=1 AND spd.service_id = ".$search['service'];
    	}
    	if($search['study_year']>0){
    		$where .= " and sp.year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " and sp.degree = ".$search['degree'];
    	}
    	if($search['grade_all']>0){
    		$where .= " AND spd.type=1 AND sp.grade = ".$search['grade_all'];
    	}
    	if($search['user']>0){
    		$where .= " and sp.user_id = ".$search['user'];
    	}
    	if($order_no==1){
    		$order=" ORDER BY payment_id DESC, spd.type DESC";
    	}elseif($order_no==2){//used order by student 
    		$order=" ORDER BY sp.student_id DESC ";
    	}else{
    		$order=" ORDER BY spd.type DESC, p.ser_cate_id DESC ";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getPaymentReciptDetail($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT 
    	spd.id,
    	spd.payment_id,
    	spd.is_onepayment,
    	(SELECT `rms_student`.`stu_khname` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) AS kh_name,
    	(SELECT `rms_student`.`stu_enname` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) AS en_name,
    	(SELECT `rms_view`.`name_kh` FROM `rms_view` WHERE ((`rms_view`.`type` = 2) AND (`rms_view`.`key_code` =(SELECT `rms_student`.`sex` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) )))  as sex,
    	spd.type,spd.fee,spd.qty,spd.subtotal,
    	(SELECT title FROM `rms_program_name` WHERE `rms_program_name`.`service_id`= spd.service_id LIMIT 1) as service,
    	(SELECT `name_en` FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS payment_term,
    	(SELECT major_enname from rms_major where major_id = sp.grade LIMIT 1) as grade,
    	spd.subtotal,
    	spd.paidamount,
    	sp.receipt_number as receipt_number,
    	sp.`total_payment` AS total_payment,
    	sp.`paid_amount` as paid_amount,
    	sp.`balance_due` as balance_due,
    	sp.`return_amount` as return_amount,
    	sp.`amount_in_khmer` as amount_in_khmer,
    	(SELECT CONCAT (`last_name`,' ', `first_name`) FROM `rms_users` WHERE `rms_users`.id = sp.user_id LIMIT 1) as user,
    	spd.note,
    	spd.start_date,
    	spd.validate,
    	spd.late_fee,
    	spd.extra_fee, 
    	spd.discount_fix,
    	spd.discount_percent
    	FROM 
    	rms_student_payment as sp,
    	rms_student_paymentdetail AS spd ";
    	$sql.='WHERE sp.id=spd.payment_id 
    		AND spd.payment_id = '.$id;
		return $db->fetchAll($sql);    	
    }
    
    function submitPaidDate($data){
    	$db=$this->getAdapter();
		$this->_name='rms_student_payment';
		if(!empty($data['identity'])){
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$arr = array(
						'create_date'=>$data['create_date_'.$i]
						);
				$where=" id = ".$data['payment_id_'.$i];
				$this->update($arr, $where);
			}
		}
    }
    
    
    
    
}
   
    
   