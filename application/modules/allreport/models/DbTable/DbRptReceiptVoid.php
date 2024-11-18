<?php

class Allreport_Model_DbTable_DbRptReceiptVoid extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
    public function getAllStudentVoid($search){
		
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
    	$db = $this->getAdapter();
    	$sql =" SELECT 
    				(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name
					,s.stu_code
					,s.stu_khname
					,s.stu_enname
					,sp.*
					,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users as u WHERE u.id = sp.user_id) as user
    				,(SELECT name_en FROM rms_view WHERE type=10 and key_code = is_void LIMIT 1) as vois_status
    				,(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by 
    			FROM
    				rms_student as s,
    				rms_student_payment as sp
    			WHERE 
    				s.stu_id = sp.student_id
    				and s.status = 1
    				and sp.is_void=1 
				";
    	
    	$where=' ';
    	
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
    
    
    public function getAllChangeProductVoid($search){
    	$db = $this->getAdapter();
    	$sql =" SELECT
			    	s.stu_code,
			    	s.stu_khname,
			    	s.stu_enname,
    				cp.*,
			    	(SELECT CONCAT(last_name,' ',first_name) from rms_users as u where u.id = cp.user_id LIMIT 1) as user,
			    	(SELECT name_en from rms_view where type=10 and key_code = is_void LIMIT 1) as vois_status
    			FROM
    				rms_change_product as cp,
    				rms_student as s
    			WHERE
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
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$sql ="SELECT
			    	inc.*
			    	,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=inc.branch_id LIMIT 1) AS branch_name
			    	,(SELECT category_name FROM rms_cate_income_expense AS ci where ci.id = inc.cate_income LIMIT 1) as cate_income
			    	,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users AS u where u.id = inc.user_id LIMIT 1) as user
    			FROM
			    	ln_income AS inc
    			WHERE
			    	inc.status = 0 ";
    	$where=' ';
    	
    	$where.=$dbp->getAccessPermission();
    
    	$from_date =(empty($search['start_date']))? '1': " inc.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " inc.date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " inc.title LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
    
    public function getAllExpenseVoid($search){
    
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$sql =" SELECT
			    	exp.*
			    	,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=exp.branch_id LIMIT 1) AS branch_name
			    	,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = exp.user_id LIMIT 1) AS user
    			FROM
    				ln_expense AS exp
    			WHERE
    				exp.status = 0 ";
    	$where=' ';
    	
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " exp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " exp.date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " exp.title LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where);
    }
}