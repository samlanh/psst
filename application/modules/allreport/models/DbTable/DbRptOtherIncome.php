<?php

class Allreport_Model_DbTable_DbRptOtherIncome extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllOtherIncome($search){
    	$db=$this->getAdapter();
    	
    	$sql = "SELECT 
    				*,
	    			(select category_name from rms_cate_income_expense where rms_cate_income_expense.id = cate_income) as income_category,
	    			(SELECT name_en FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = payment_method) AS payment_method,
	    			(select CONCAT(first_name) from rms_users as u where u.id = user_id)  as name
    			 from 
    				ln_income  
    			WHERE 
    				status=1 ";
    	
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	if(!empty($search['user']) AND $search['user']>0){
    		$where.=" AND user_id = ".$search['user'] ;
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission();
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllOtherIncomebyCate($search){
    	$db=$this->getAdapter();
    	 
    	$sql = "SELECT *,
    		SUM(total_amount) AS total_income, 
	    	(SELECT category_name FROM rms_cate_income_expense WHERE rms_cate_income_expense.id = cate_income limit 1) as income_category,
	    	(SELECT first_name from rms_users as u where u.id = user_id)  as user_name
    	FROM
    		ln_income
    	WHERE
    		status=1 ";
    	
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
}  