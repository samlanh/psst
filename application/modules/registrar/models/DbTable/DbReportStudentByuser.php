<?php

class Registrar_Model_DbTable_DbReportStudentByuser extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getUserType(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->level;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('auth');
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
					  (select name_en from rms_view where type=10 and key_code=sp.is_void) as void_status
					FROM
					  rms_student AS s,
					  rms_student_payment AS sp,
					  rms_student_paymentdetail AS spd 
					WHERE sp.id = spd.payment_id 
					  AND s.stu_id = sp.student_id  $branch_id  $user_level 
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
// 	    	echo $sql.$where.$order;exit();
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
				GROUP BY service_id 
			";
		//echo $sql;
		return $db->fetchAll($sql);
		
// 		$i=0;
// 		$result=array('');
		
// 		if(!empty($service)){
// 			foreach ($service as $service_id){$i++;
// 				//echo $result['id']."<br />";
// 				$sql1 = "select service_id as id , title from rms_program_name where service_id=".$service_id['id'];
// 				$result = $db->fetchRow($sql1);
				
				
// 				//print_r($result);
// 				//return $result;
// 			}
// 			//return $result;
// 		}
		
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
						sp.note
			
				  FROM
						rms_student AS s,
						rms_student_payment AS sp
				  WHERE s.stu_id = sp.student_id  $branch_id  $user_level
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
		// 	    	echo $sql.$where.$order;exit();
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
				echo $e->getMessage();
		}
	}   
	
	
	function getAllStudentTest($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
	
			$db=$this->getAdapter();
	
			$from_date =(empty($search['start_date']))? '1': "st.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "st.create_date <= '".$search['end_date']." 23:59:59'";
			
			$sql="SELECT
					st.receipt,
					st.kh_name,
					st.en_name,
					(select name_en from rms_view where type=2 and key_code=st.sex) as sex,
					st.dob,
					st.phone,
					(select en_name from rms_dept where dept_id = st.degree) as degree,
					st.create_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = st.user_id) AS user,
					st.serial,
					st.register,
					st.old_school,
					st.old_grade,
					st.total_price,
					st.note
				FROM
					rms_student_test AS st
				WHERE 
					status=1  
					$branch_id 
			";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search= addslashes(trim($search['adv_search']));
			$s_where[]= " st.receipt LIKE '%{$s_search}%'";
			$s_where[]= " st.stu_khname LIKE '%{$s_search}%'";
			$s_where[]= " st.stu_enname LIKE '%{$s_search}%'";
			$s_where[]= " st.serial LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['degree'])){
				$where.= " AND st.degree = ".$search['degree'];
			}
			if(!empty($search['user'])){
				$where.=" AND user_id = ".$search['user'] ;
			}
			
			$order=" ORDER By st.id DESC ";
			
// 				    	echo $sql.$where.$order;exit();
			
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
					CONCAT(s.stu_khname,'-',s.stu_enname) as name,
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
				
			$order=" ORDER By cp.id DESC ";
				
			// 				    	echo $sql.$where.$order;exit();
				
			return $db->fetchAll($sql.$where.$order);
				
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	   
}









