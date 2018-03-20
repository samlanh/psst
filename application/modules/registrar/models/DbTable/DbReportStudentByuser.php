<?php

class Registrar_Model_DbTable_DbReportStudentByuser extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getUserType(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->level;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    public function getType(){
    	$db=$this->getAdapter();
		$sql=" select type from rms_student_paymentdetail";
    	return $db->fetchAll($sql);
    }
	function getAllStudentPayment($search=null){
		try{
	    	$_db = new Application_Model_DbTable_DbGlobal();
	    	$branch_id = $_db->getAccessPermission('sp.branch_id');
	    	$user_level = $_db->getUserAccessPermission('sp.user_id');
	    	
	    	$db=$this->getAdapter();

	    	$type=$this->getType();
        	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
	    	$sql=" SELECT 
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
					  (SELECT pg.title FROM rms_program_name AS pg WHERE pg.service_id=spd.service_id) AS service_id,
					  (SELECT CONCAT(last_name,' - ',first_name) FROM rms_users WHERE rms_users.id = sp.user_id) AS user_id,
					  (SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term) AS payment_term,
					  (select name_en from rms_view where type=10 and key_code=sp.is_void) as void_status,
					  (SELECT CONCAT(last_name,' - ',first_name) FROM rms_users WHERE rms_users.id = sp.void_by) AS void_by
					FROM
					  rms_student AS s,
					  rms_student_payment AS sp,
					  rms_student_paymentdetail AS spd 
					WHERE sp.id = spd.payment_id 
					  AND s.stu_id = sp.student_id  $branch_id  $user_level ";
	    	
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
	    	if(($search['service']>0)){
	    		$where.= " AND spd.service_id = ".$search['service'];
	    	}
	    	if(($search['branch_id']>0)){
	    		$where.= " AND sp.branch_id = ".$search['branch_id'];
	    	}
	    	if(($search['study_year']>0)){
	    		$where.= " AND sp.year = ".$search['study_year'];
	    	}
	    	if(!empty($search['user'])){
	    		$where.= " AND sp.user_id = ".$search['user'];
	    	}
	    	$order=" ORDER By sp.id DESC ";
	    	echo $sql.$where.$order;exit();
	    	return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	  }
	    
	    
	  public function getServices($service_id){
	   	    $db=$this->getAdapter();
	   	    $sql="SELECT pn.service_id,pn.title FROM  rms_program_name AS pn,rms_student_paymentdetail AS spd 
						WHERE pn.service_id=spd.service_id AND pn.type=2 AND spd.service_id=$service_id";
	   	    return $db->fetchOne($sql);
	   }
	   
	function getAllService(){
		$db = $this->getAdapter();
		$sql="SELECT 
				  p.service_id ,
				  p.`title`
				FROM
				  `rms_servicefee_detail` as sfd,
				  `rms_servicefee`  as sf,
				  `rms_program_name` as p
				WHERE `sf`.id = `sfd`.`service_feeid` 
				  AND sf.`branch_id` = ".$this->getBranchId()."
				  AND p.`service_id`=sfd.`service_id`
				  or type=1
				GROUP BY service_id ";
		return $db->fetchAll($sql);		
	}
	   
	function getDailyReport($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('sp.branch_id');
			$user_level = $_db->getUserAccessPermission('sp.user_id');
	
			$db=$this->getAdapter();
	
			$type=$this->getType();
			$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
			            sp.id,
						sp.receipt_number,
						s.stu_code,
						s.stu_khname,
						s.stu_enname,
						(select en_name from rms_dept where dept_id = s.degree) as degree,
						(select major_enname from rms_major where major_id = s.grade) as grade,
						(select name_en from rms_view where rms_view.type = 4 and key_code=s.session) as session,
						sp.create_date,
						sp.is_void,
						(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.year LIMIT 1) AS year,
						(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id) AS user_id,
						(select name_en from rms_view where type=10 and key_code=sp.is_void) as void_status,
						sp.grand_total as total_payment,
						sp.fine,
						sp.credit_memo,
						sp.deduct,
						sp.net_amount,
						sp.note,
						(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.void_by) AS void_by
				  FROM
						rms_student AS s,
						rms_student_payment AS sp
				  WHERE s.stu_id = sp.student_id  $branch_id  ";
	
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
			if(!empty($search['degree'])){
				$where.= " AND s.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$where.= " AND s.grade = ".$search['grade_all'];
			}
			if(!empty($search['session'])){
				$where.= " AND s.session = ".$search['session'];
			}
			if(!empty($search['stu_code'])){
				$where.= " AND sp.student_id = ".$search['stu_code'];
			}
			if(!empty($search['stu_name'])){
				$where.= " AND sp.student_id = ".$search['stu_name'];
			}
			$order=" ORDER By sp.id DESC ";
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
		}
	}   
	
	function getAllStudentTest($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
	
			$db=$this->getAdapter();
	
			$from_date =(empty($search['start_date']))? '1': "st.paid_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "st.paid_date <= '".$search['end_date']." 23:59:59'";
			
			$sql="SELECT
					st.*,
					st.receipt_no,
					st.paid_date,
					st.kh_name,
					st.en_name,
					(select name_en from rms_view where type=2 and key_code=st.sex) as sex,
					st.dob,
					st.phone,
					(select en_name from rms_dept where dept_id = st.degree) as degree,
					st.create_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = st.account_userid) AS user,
					st.serial,
					st.register,
					st.old_school,
					st.old_grade,
					st.total_price,
					st.price,
					st.note
				FROM
					rms_student_test AS st
				WHERE 
				total_price>0
				AND status=1  
					$branch_id ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search= addslashes(trim($search['adv_search']));
				$s_where[]= " st.receipt_no LIKE '%{$s_search}%'";
				$s_where[]= " st.kh_name LIKE '%{$s_search}%'";
				$s_where[]= " st.en_name LIKE '%{$s_search}%'";
				$s_where[]= " st.serial LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['degree'])){
				$where.= " AND st.degree = ".$search['degree'];
			}
			if(!empty($search['user'])){
				$where.=" AND account_userid = ".$search['user'] ;
			}
			$order=" ORDER By st.id DESC ";
			return $db->fetchAll($sql.$where.$order);
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}	
	
	
	function getAllChangeProduct($search){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
		
			$db=$this->getAdapter();
		
			$from_date =(empty($search['start_date']))? '1': "cp.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "cp.create_date <= '".$search['end_date']." 23:59:59'";
				
			$sql="select
					cp.id,
					receipt_no,					
					(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS name,	
					total_payment,
					credit_memo,
					cp.create_date,
					cp.is_void,
					(select first_name from rms_users where rms_users.id=cp.user_id) as user,
					(select name_en from rms_view where type=10 and key_code=cp.is_void) as status
				from
					rms_change_product cp,
					rms_student as s
				where 
					cp.stu_id=s.stu_id
			";
		
			$where = " AND ".$from_date." AND ".$to_date;
		
			if(!empty($search['adv_search'])){
					$s_where=array();
					$s_search= addslashes(trim($search['adv_search']));
					$s_where[]= " receipt_no LIKE '%{$s_search}%'";
					$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
					$s_where[]= " s.stu_enname LIKE '%{$s_search}%'";
					$where.=' AND ('.implode(' OR ', $s_where).')';
			}
				
			if(!empty($search['user'])){
				$where.=" AND cp.user_id = ".$search['user'] ;
			}
			
			$order=" ORDER By cp.id DESC ";
				
			// 				    	echo $sql.$where.$order;exit();
				
			return $db->fetchAll($sql.$where.$order);
				
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	function getStudentTestPaymentById($id){
		$db=$this->getAdapter();
		$sql="select 
					*,
					(select name_en from rms_view where type=2 and key_code=sex) as sex,
					(select en_name from rms_dept where dept_id = degree) as degree, 
					(select CONCAT(first_name,'-',last_name) from rms_users as u where u.id = account_userid) as user
				from 
					rms_student_test 
				where 
					id = $id 
				limit 1
			";
		return $db->fetchRow($sql);
	}
	
	function getChangeProductById($id){
		$db=$this->getAdapter();
		$sql="select
					*,
					(select name_en from rms_view where type=2 and key_code=s.sex) as sex,
					(select CONCAT(first_name,'-',last_name) from rms_users as u where u.id = cp.user_id) as user
				from
					rms_student as s,
					rms_change_product as cp
				where
					cp.stu_id = s.stu_id
					and cp.id = $id
					limit 1
			";
		return $db->fetchRow($sql);
	}
	
	function getChangeProductDetailById($id){
		$db=$this->getAdapter();
		$sql="select
					*,
					(select title from rms_program_name as p where p.service_id = cpd.service_id_old) as old_product,
					(select title from rms_program_name as p where p.service_id = cpd.service_id_new) as new_product
				from
					rms_change_product_detail as cpd
				where
					cpd.change_id = $id
			";
		return $db->fetchAll($sql);
	}
}









