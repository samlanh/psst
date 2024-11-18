<?php

class Allreport_Model_DbTable_DbRptOtherExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllOtherExpense($search){//using
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT *,
    				description as note,
	    			payment_type AS payment_methodid, 
	    			(SELECT name_en FROM rms_view WHERE rms_view.type=8 and key_code=payment_type LIMIT 1) AS payment_type,
	    			(SELECT name_en FROM rms_view WHERE type=10 AND key_code=isVoid LIMIT 1) AS voidStatus,
	    			(SELECT first_name FROM rms_users as u WHERE u.id = user_id LIMIT 1) AS byUser,
	    			(SELECT first_name FROM rms_users as u WHERE u.id = voidBy LIMIT 1) AS voidByUser
    			 FROM ln_expense  WHERE 1 $branch_id  ";
    	$where= ' ';
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	if(!empty($search['user'])){
    		$where.=" AND user_id = ".$search['user'] ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " invoice LIKE '%{$s_search}%'";
    		$s_where[] = " external_invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['receipt_order']==0){
    		$order=" ORDER By id ASC ";
    	}else{
    		$order=" ORDER By id DESC ";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllExpensebycate($search){//
    	$db=$this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	 
    	$sql = "SELECT 
    				SUM(ed.total) AS total_expense,
					ac.account_name,
					ac.parent_id,
					(SELECT a.account_name FROM rms_account_name AS a WHERE a.id=ac.parent_id) AS parent_name
    			FROM 
		    		ln_expense AS e,
		    		ln_expense_detail AS ed,
		    		rms_account_name as ac
		    	WHERE 
					e.id=ed.expense_id
					and ed.service_id = ac.id
    				$branch_id  
    		";
    	$where= ' ';
    	
    	$order=" GROUP BY ed.service_id order by ac.parent_id";
    	 
    	$from_date =(empty($search['start_date']))? '1': " e.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND e.branch_id = ".$search['branch_id'] ;
    	}
    	// if(!empty($search['user'])){
    	// 	$where.=" AND e.user_id = ".$search['user'] ;
    	// }
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " e.title LIKE '%{$s_search}%'";
    		$s_where[] = " e.invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
}    