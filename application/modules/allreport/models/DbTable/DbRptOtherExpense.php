<?php

class Allreport_Model_DbTable_DbRptOtherExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllOtherExpense($search){//
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT *,
    			(select name_en from rms_view where rms_view.type=8 and key_code=payment_type) as payment_type,
    			(select CONCAT(first_name) from rms_users as u where u.id = user_id)  as name
    			 from ln_expense  WHERE 1 $branch_id  ";
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
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
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}

    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllExpensebycate($search){//
    	$db=$this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	 
    	$sql = "SELECT SUM(ed.total_amount) as total_expense,
    	(SELECT a.account_name FROM rms_account_name  as a WHERE a.id=ed.service_id) AS account_name
    	FROM 
    		ln_expense AS e,
    		ln_expense_detail AS ed
    	  WHERE 
			e.id=ed.expense_id
    	$branch_id  ";
    	$where= ' ';
    	$order=" GROUP BY ed.service_id ";
    	 
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
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
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
}    