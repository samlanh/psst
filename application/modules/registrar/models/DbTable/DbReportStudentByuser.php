<?php

class Registrar_Model_DbTable_DbReportStudentByuser extends Zend_Db_Table_Abstract
{
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
//     }
    
//     function getUserType(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->level;
//     }
    
//     public function getBranchId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->branch_id;
//     }
  
	function getDailyReport($search=null){//using
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
						s.stu_code,
						s.stu_khname,
						s.stu_enname,
						s.last_name,
						(SELECT title FROM `rms_items` WHERE rms_items.id=sp.degree LIMIT 1) AS degree,
						(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS grade,
						(SELECT name_en FROM rms_view WHERE rms_view.type = 4 AND key_code=sp.session LIMIT 1) AS session,
						(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS FeeYear,
						(SELECT name_en FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS void_status,
						(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = sp.payment_method LIMIT 1) AS paymentMethod,
						(SELECT bank_name FROM `rms_bank` WHERE rms_bank.id=sp.bank_id LIMIT 1) bank_name,
						sp.id,
						sp.receipt_number,
						sp.number,
						sp.grand_total AS total_payment,
						sp.credit_memo,
						sp.grand_total,
						sp.penalty,
						sp.paid_amount,
						sp.balance_due,
						sp.note,
						sp.is_closed,
						sp.payment_method,
						sp.create_date,
						sp.is_void,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS Byuser,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS voidBy
				  FROM
						rms_student AS s,
						rms_student_payment AS sp
				  WHERE 
						s.stu_id = sp.student_id  
						$branch_id ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(last_name,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
				$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$where.= " AND sp.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['userId'])){
					$where.= " AND sp.user_id = ".$search['userId'];
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
	
			if($search['receipt_order']==0){
				$order=" ORDER By sp.id ASC ";
			}else{
				$order=" ORDER By sp.id DESC ";
			}
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}   

	
	
	function getOtherIncomeById($id){//using
		$db=$this->getAdapter();
		$sql="SELECT
					*,
					(SELECT branch_namekh from rms_branch where br_id = branch_id LIMIT 1) as branch_name,
					(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) 
						FROM rms_student AS s
						WHERE s.stu_id=student_id LIMIT 1) AS studentName,
					(SELECT category_name FROM rms_cate_income_expense WHERE rms_cate_income_expense.id = cate_income LIMIT 1) AS cate_income,
					(SELECT name_kh FROM rms_view WHERE TYPE=8 AND key_code = payment_method LIMIT 1) AS payment_method,
					(SELECT bank_name FROM `rms_bank` b WHERE ln_income.bank_id = b.id LIMIT 1) AS bank_name,
					(SELECT CONCAT(last_name,' ',first_name) FROM rms_users AS u WHERE u.id = user_id LIMIT 1) AS userName
				FROM
					`ln_income`
				WHERE
					id =".$id;
		$_db = new Application_Model_DbTable_DbGlobal();
		$sql.= $_db->getAccessPermission();
		$sql.=" LIMIT 1 ";
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
		WHERE 	
				gds.itemType=1 
				AND gds.stu_id = s.stu_id
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
	function getBankTranReport($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('bt.branch_id');
				
			$lang = $_db->currentlang();
			if($lang==1){// khmer
				$label = "name_kh";
			}else{ // English
				$label = "name_en";
			}
	
			$db=$this->getAdapter();
			$from_date =(empty($search['start_date']))? '1': "bt.date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "bt.date <= '".$search['end_date']." 23:59:59'";
			
			$sql=" SELECT
					bt.id,
					bt.bank_transaction_id,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.last_name,
					
					CASE
						WHEN s.primary_phone = 1 THEN s.tel
						WHEN s.primary_phone = 2 THEN COALESCE(fam.fatherPhone,'')
						WHEN s.primary_phone = 3 THEN COALESCE(fam.motherPhone,'')
						ELSE COALESCE(fam.guardianPhone,'')
					END as tel,
						
					(SELECT name_en from rms_view where type=2 and key_code=s.sex) as sex,
					(SELECT title FROM `rms_items` WHERE rms_items.id=bt.degree LIMIT 1 ) AS degree,
					(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=bt.grade LIMIT 1) AS grade,
					bt.amount,
					bt.`date`,
					bt.bank_transaction_id,
					(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=bt.academic_year LIMIT 1) AS year
			FROM
				rms_student AS s JOIN rms_banktransaction AS bt ON s.stu_id = bt.stu_id 
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE
				1
			$branch_id  ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(last_name,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(bank_transaction_id,' ','') LIKE '%{$s_search}%'";
				$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				//$where.= " AND bt.branch_id = ".$search['branch_id'];
			}
			
			if(!empty($search['degree'])){
				$where.= " AND bt.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$where.= " AND bt.grade = ".$search['grade_all'];
			}
			
			if(!empty($search['stu_name'])){
				$where.= " AND bt.stu_id = ".$search['stu_name'];
			}
			$order=" ORDER By bt.id DESC ";

			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}