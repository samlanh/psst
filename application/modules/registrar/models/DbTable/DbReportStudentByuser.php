<?php

class Registrar_Model_DbTable_DbReportStudentByuser extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    function getUserType(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->level;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->branch_id;
    }
    public function getType(){
    	$db=$this->getAdapter();
		$sql=" select type from rms_student_paymentdetail";
    	return $db->fetchAll($sql);
    }
	   
	function getDailyReport($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('sp.branch_id');
			
			$lang = $_db->currentlang();
			if($lang==1){// khmer
				$label = "name_kh";
			}else{ // English
				$label = "name_en";
			}
	
			$db=$this->getAdapter();
			$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
			            sp.id,
						sp.receipt_number,
						s.stu_code,
						s.stu_khname,
						s.stu_enname,
						s.last_name,
						(SELECT title FROM `rms_items` WHERE rms_items.id=sp.degree LIMIT 1 ) AS degree,
						(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS grade,
						(SELECT name_en FROM rms_view WHERE rms_view.type = 4 AND key_code=sp.session LIMIT 1) AS session,
						sp.create_date,
						sp.is_void,
						(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user_id,
						(SELECT name_en FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS void_status,
						(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = sp.payment_method LIMIT 1) AS paymentMethod,
						sp.grand_total AS total_payment,
						sp.credit_memo,
						sp.grand_total,
						sp.penalty,
						sp.paid_amount,
						sp.balance_due,
						sp.note,
						sp.is_closed,
						sp.payment_method,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by
				  FROM
						rms_student AS s,
						rms_student_payment AS sp
				  WHERE 
						s.stu_id = sp.student_id  
						$branch_id  ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				
				$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(last_name,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
				$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$where.= " AND sp.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['user'])){
					$where.= " AND sp.user_id = ".$search['user'];
			}
			if(!empty($search['degree'])){
				$where.= " AND sp.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$where.= " AND sp.grade = ".$search['grade_all'];
			}
			if(!empty($search['session'])){
				$where.= " AND sp.session = ".$search['session'];
			}
			if(!empty($search['stu_name'])){
				$where.= " AND sp.student_id = ".$search['stu_name'];
			}
			//$where.=" AND paystudent_type=3 AND revenue_type=2 AND data_from=4 ";
// 			$where.=" AND  data_from=3 ";
// 			$where.=" AND  paystudent_type=3 AND data_from=4 ";
			$order=" ORDER By sp.id DESC ";
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
	function getAllStudentClearBalance($search){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('sp.branch_id');
	
			$db=$this->getAdapter();
	
			$from_date =(empty($search['start_date']))? '1': "scb.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "scb.create_date <= '".$search['end_date']." 23:59:59'";
	
			$sql="SELECT
						scb.id,
						s.stu_code,
						s.stu_khname,
						s.stu_enname,
						sp.receipt_number,
						sp.create_date,
						scb.receipt_no,
						scb.total_balance,
						scb.paid_amount,
						scb.balance,
						scb.note,
						scb.create_date,
						scb.is_void,
						(SELECT name_en FROM rms_view AS v WHERE v.type=10 AND v.key_code=scb.is_void) AS status,
						(SELECT first_name FROM rms_users AS u WHERE u.id=scb.user_id) AS user,
						(SELECT first_name FROM rms_users AS u WHERE u.id=scb.void_by) AS void_by
					FROM
						rms_student_clear_balance scb,
						rms_student_payment AS sp,
						rms_student AS s
					WHERE
						sp.id = scb.payment_id
						AND s.stu_id = scb.stu_id
						$branch_id
				";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search= addslashes(trim($search['adv_search']));
				$s_where[]= " scb.receipt_no LIKE '%{$s_search}%'";
				$s_where[]= " sp.receipt_number LIKE '%{$s_search}%'";
				$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
				$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
				$s_where[]= " s.stu_enname LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$where.= " AND sp.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['user'])){
				$where.=" AND scb.user_id = ".$search['user'] ;
			}
			$order=" ORDER By scb.id DESC ";
			//echo $sql.$where.$order;exit();
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	function getClearBalanceById($id){
		$db=$this->getAdapter();
		$sql="SELECT
					scb.id,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					sp.receipt_number,
					sp.create_date as date_balance,
					scb.receipt_no,
					scb.total_balance,
					scb.paid_amount,
					scb.balance,
					scb.note,
					scb.create_date,
					scb.is_void,
					(SELECT name_en FROM rms_view AS v WHERE v.type=10 AND v.key_code=scb.is_void) AS status,
					(SELECT first_name FROM rms_users AS u WHERE u.id=scb.user_id) AS user,
					(SELECT first_name FROM rms_users AS u WHERE u.id=scb.void_by) AS void_by,
					(SELECT first_name FROM rms_users AS u WHERE u.id = scb.user_id) AS first_name,
					(SELECT last_name FROM rms_users AS u WHERE u.id = scb.user_id) AS last_name
				FROM
					rms_student_clear_balance scb,
					rms_student_payment AS sp,
					rms_student AS s
				WHERE
					sp.id = scb.payment_id
					AND s.stu_id = scb.stu_id
					and scb.id = $id
				LIMIT 
					1	
			";
		return $db->fetchRow($sql);
	}
	
	function getOtherIncomeById($id){
		$db=$this->getAdapter();
		$sql="SELECT
					*,
					(SELECT category_name FROM rms_cate_income_expense WHERE rms_cate_income_expense.id = cate_income) AS cate_income,
					(SELECT name_kh FROM rms_view WHERE TYPE=8 AND key_code = payment_method) AS payment_method,
					(SELECT first_name FROM rms_users AS u WHERE u.id = user_id) AS first_name,
					(SELECT last_name FROM rms_users AS u WHERE u.id = user_id) AS last_name
				FROM
					`ln_income`
				WHERE
					id = $id
				LIMIT 1
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
	
	function submitClosingEngry($data){
		$db = $this->getAdapter();
		if(!empty($data['id_selected'])){
			$ids = explode(',', $data['id_selected']);
			$arr = array(
					"is_closed"=>1,
			);
			foreach ($ids as $i){
				if ($data['type_record'.$i]==1){ //1= Student Payment
					if (!empty($data['id_'.$i])){
						$this->_name="rms_student_payment";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}else if ($data['type_record'.$i]==2){ //2= Other Income
					if (!empty($data['id_'.$i])){
						$this->_name="ln_income";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}else if ($data['type_record'.$i]==3){ //3= Other Expense
					if (!empty($data['id_'.$i])){
						$this->_name="ln_expense";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}
				
			}
		}
	}
	
	
	public function getAllStudentUnpaid($search){
		$_db = $this->getAdapter();
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
	
		$where = " ";
		$sql = "
		SELECT
			s.stu_id,
			s.branch_id,
			(SELECT $branch FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
			s.stu_code,
			s.stu_khname,
			s.stu_enname,
			s.last_name,
				
			CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gds.academic_year LIMIT 1) AS academicTitle,
			(SELECT group_code FROM `rms_group` WHERE rms_group.id=gds.group_id LIMIT 1) AS group_name,
			(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
			(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = gds.`degree` LIMIT 1) AS degreeTitle,
			(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = gds.`grade` LIMIT 1) AS gradeTitle,
			gds.gd_id,
			gds.degree,
			gds.grade,
			gds.group_id,
			gds.academic_year
		FROM `rms_student` AS s,
			`rms_group_detail_student` AS gds
		WHERE gds.stu_id = s.stu_id
				AND gds.is_current=1
				AND gds.is_maingrade=1
				AND gds.is_setgroup=1
				AND s.customer_type=1
				AND s.status=1
				AND gds.stop_type=0
				AND
				(SELECT
					sp.id
				FROM `rms_student_payment` AS sp,
					`rms_student_paymentdetail` AS spd
				WHERE
					sp.id = spd.payment_id
					AND spd.service_type =1 AND spd.itemdetail_id = gds.grade AND sp.student_id=s.stu_id ORDER BY sp.id DESC LIMIT 1) IS NULL
		";
	
	
		$orderby = " ORDER BY gds.degree DESC,gds.grade DESC, s.stu_id DESC ";
	
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  	LIKE '%{$s_search}%'";
					
				$where .=' AND ( '.implode(' OR ',$s_where).')';
			}
			if(!empty($search['group'])){
					$where.=" AND gds.group_id =".$search['group'];
			}
			if(!empty($search['degree'])){
				$where.=" AND gds.degree =".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$where.=" AND gds.grade =".$search['grade_all'];
			}
			if(!empty($search['branch_id'])){
				$where.=" AND s.branch_id=".$search['branch_id'];
			}
			$where.=$dbp->getAccessPermission('s.branch_id');
			return $_db->fetchAll($sql.$where.$orderby);
	}
}









