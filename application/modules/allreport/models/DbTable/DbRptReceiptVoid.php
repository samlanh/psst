<?php

class Allreport_Model_DbTable_DbRptReceiptVoid extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
    public function getAllStudentVoid($search){
    	$db = $this->getAdapter();
    	$sql =" SELECT 
    				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branch_name,
    				s.stu_code,
    				s.stu_khname,
    				s.stu_enname,
    				sp.*,
    				(select CONCAT(last_name,' ',first_name) from rms_users as u where u.id = sp.user_id) as user,
    				(SELECT name_en from rms_view where type=10 and key_code = is_void LIMIT 1) as vois_status,
    				(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by 
    			from
    				rms_student as s,
    				rms_student_payment as sp
    			where 
    				s.stu_id = sp.student_id
    				and s.status = 1
    				and sp.is_void=1 ";
    	
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " and ".$from_date." AND ".$to_date;
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
    
    
    public function getAllChangeProductVoid($search){
    	$db = $this->getAdapter();
    	$sql =" select
			    	s.stu_code,
			    	s.stu_khname,
			    	s.stu_enname,
    				cp.*,
			    	(select CONCAT(last_name,' ',first_name) from rms_users as u where u.id = cp.user_id) as user,
			    	(SELECT name_en from rms_view where type=10 and key_code = is_void LIMIT 1) as vois_status
    			from
    				rms_change_product as cp,
    				rms_student as s
    			where
    				cp.stu_id = s.stu_id
			    	and s.status = 1
			    	and cp.is_void = 1
    		";
    	 
    	$where=' ';
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	 
    	$from_date =(empty($search['start_date']))? '1': " cp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " cp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " and ".$from_date." AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    
    	return $db->fetchAll($sql.$where);
    }
    
    public function getAllIncomeVoid($search){
    	$db = $this->getAdapter();
    	$sql ="SELECT
			    	*,
			    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=ln_income.branch_id LIMIT 1) AS branch_name,
			    	(SELECT category_name from rms_cate_income_expense as ci where ci.id = cate_income LIMIT 1) as cate_income,
			    	(SELECT CONCAT(last_name,' ',first_name) from rms_users as u where u.id = user_id LIMIT 1) as user
    			FROM
			    	ln_income
    			WHERE
			    	status = 0 ";
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= " and ".$from_date." AND ".$to_date;
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
    
    public function getAllExpenseVoid($search){
    
    	$db = $this->getAdapter();
    	$sql =" SELECT
			    	*,
			    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=ln_expense.branch_id LIMIT 1) AS branch_name,
			    	(select CONCAT(last_name,' ',first_name) from rms_users as u where u.id = user_id) as user
    			FROM
    				ln_expense
    			WHERE
    				status = 0 ";
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= " and ".$from_date." AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
}