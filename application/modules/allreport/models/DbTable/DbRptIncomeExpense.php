<?php

class Allreport_Model_DbTable_DbRptIncomeExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllIncomeExpense($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT *,
    			(select curr_nameen from ln_currency where ln_currency.id=ln_income_expense.curr_type) as curr_name,
    			(select CONCAT(last_name,' - ',first_name) from rms_users as u where u.id = user_id)  as name
    			 from ln_income_expense  WHERE 1 $branch_id  ";
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	
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
    		$s_where[] = " account_id LIKE '%{$s_search}%'";
    		$s_where[] = " invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
	public function getAllexspan($search){//report expense
	   $db=$this->getAdapter();
	   $sql="SELECT e.* ,
				(SELECT b.branch_nameen FROM `rms_branch` AS b WHERE b.br_id =e.branch_id LIMIT 1) AS branch_name,
				u.user_name ,u.first_name,e.receiver,
				(select name_en from rms_view where rms_view.type=8 and key_code=payment_type) as payment_type,
				(SELECT v.name_kh FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type LIMIT 1) AS pay
				
			FROM ln_expense AS e ,
				rms_branch AS b ,
				 rms_users AS u 
			WHERE b.br_id=e.branch_id 
	   			AND e.user_id=u.id
	   			AND e.status=1 ";
	   
	   $where="";
	   $from_date =(empty($search['start_date']))? '1': " e.date  >= '".$search['start_date']." 00:00:00'";
	   $to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
	   $where = " AND ".$from_date." AND ".$to_date;
	   
	   if(!empty($search['txtsearch'])){
	   	$s_where = array();
	   	$s_search = addslashes(trim($search['txtsearch']));
	   	$s_where[] = " e.invoice LIKE '%{$s_search}%'";
	   	$s_where[] = " e.receiver LIKE '%{$s_search}%'";
	   	$s_where[] = " e.title LIKE '%{$s_search}%'";
	   	$s_where[] = " e.description LIKE '%{$s_search}%'";
	   	$where .=' AND ( '.implode(' OR ',$s_where).')';
	   }
	   if($search['branch_id']){
	   	$where.= " AND e.branch_id = ".$search['branch_id'];
	   }
	   return $db->fetchAll($sql.$where);
	}
	public function getAllexspanByid($id){
	    $db=$this->getAdapter();
	    $sql="SELECT 
	      			e.* ,
					b.branch_namekh AS branch ,
					u.user_name ,
					(SELECT v.name_kh FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type) AS pay	
				FROM ln_expense AS e ,
    				 rms_branch AS b ,
					 rms_users AS u 
			    WHERE 
	      			b.br_id=e.branch_id 
	      			AND e.user_id=u.id 
	      			and e.id=$id
	      		";
	    return $db->fetchrow($sql);
    }
	public function getAllexspandetailByid($id){
	    $db=$this->getAdapter();
	      $sql="SELECT 
	      			e.* ,
	      			s.account_name as service
	      		 FROM 
	      		 	ln_expense_detail AS e,
	      			rms_account_name AS s 
	      		WHERE 
	      			e.service_id=s.id 
	      			and e.expense_id=$id
	      	";
	    return $db->fetchAll($sql);
    }
}
   
    
   