<?php

class Allreport_Model_DbTable_DbRptIncomeExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
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
	public function getAllexspan(){
	   $db=$this->getAdapter();
	   $sql="SELECT e.* ,
				b.branch_namekh AS branch ,
				u.user_name ,
				(SELECT v.name_kh FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type) AS pay
				
			FROM ln_expense AS e ,
			 rms_branch AS b ,
				 rms_users AS u 
			WHERE b.br_id=e.branch_id AND e.user_id=u.id";
	   return $db->fetchAll($sql);
	}
	public function getAllexspanByid($id){
	    $db=$this->getAdapter();
	      $sql="SELECT e.* ,
					b.branch_namekh AS branch ,
					u.user_name ,
					(SELECT v.name_kh FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type) AS pay	
				FROM ln_expense AS e ,
    				 rms_branch AS b ,
					 rms_users AS u 
			    WHERE b.br_id=e.branch_id AND e.user_id=u.id and e.id='".$id."'";
	    return $db->fetchrow($sql);
    }
	public function getAllexspandetailByid($id){
	    $db=$this->getAdapter();
	      $sql="SELECT e.* , s.account_name as service FROM ln_expense_detail AS e ,rms_account_name AS s WHERE e.expense_id=s.id and e.expense_id='".$id."'";
	    return $db->fetchAll($sql);
    }
}
   
    
   