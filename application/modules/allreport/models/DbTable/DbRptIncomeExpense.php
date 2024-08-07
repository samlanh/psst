<?php

class Allreport_Model_DbTable_DbRptIncomeExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	public function getAllexspan($search){//report expense
	   $db=$this->getAdapter();
	   
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
    	$label = "name_en";
		if($currentLang==1){ 
    		$label = "name_kh";
    	}
		
	   $sql="SELECT 
				e.*
				 ,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id =e.branch_id LIMIT 1) AS branch_name
				 ,u.user_name 
				 ,u.first_name
				 ,e.receiver
				 ,e.cheque_no
				 ,e.external_invoice
				 ,(select v.$label FROM rms_view AS v WHERE v.type=8 and v.key_code= e.payment_type LIMIT 1) as payment_type
				 ,(SELECT v.$label FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type LIMIT 1) AS pay
			FROM 
				ln_expense AS e JOIN rms_branch AS b ON b.br_id=e.branch_id 
				LEFT JOIN rms_users AS u  ON e.user_id=u.id
			WHERE 
	   			1  
		";
	   
	   $where="";
	   $from_date =(empty($search['start_date']))? '1': " e.date  >= '".$search['start_date']." 00:00:00'";
	   $to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
	   $where = " AND ".$from_date." AND ".$to_date;
	   
	   if(!empty($search['txtsearch'])){
	   	$s_where = array();
	   	$s_search = addslashes(trim($search['txtsearch']));
	   	$s_where[] = " e.invoice LIKE '%{$s_search}%'";
	   	$s_where[] = " e.external_invoice LIKE '%{$s_search}%'";
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
	
	public function getAmountExpest(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql =' SELECT SUM(total_amount) FROM ln_expense ';
    	$where=' WHERE status=1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }
    public function getTotalIncome(){//count to dashboard
    	$db = $this->getAdapter();
    	$total=0;
    	$sql =' SELECT SUM(paid_amount) FROM rms_student_payment ';
    	$where=' WHERE status=1 and is_void=0 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$student_payment = $db->fetchOne($sql.$where);
    	
    	
    	$sql1 =' SELECT SUM(total_amount) FROM ln_income  ';
    	$where1=' WHERE status=1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where1.=$dbp->getAccessPermission();
    	$other_payment = $db->fetchOne($sql1.$where1);
    	
    	$total = $student_payment + $other_payment;
    	return $total;
    }
    
	public function getAllexspanByid($id){//using
	    $db=$this->getAdapter();
	    $sql="SELECT 
	      			e.* ,
					u.user_name ,
					DATE_FORMAT(date,'%d-%m-%Y' ) date,
					(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =e.branch_id LIMIT 1) AS branch_name,
					(SELECT name_kh FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = payment_type limit 1) AS payment_type,
					(SELECT bank_name FROM `rms_bank` b WHERE b.id=e.bank_id LIMIT 1) AS bank_name,
					(SELECT v.name_kh FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type LIMIT 1	) AS pay	
				FROM ln_expense AS e ,
					 rms_users AS u 
			    WHERE 
	      			e.user_id=u.id 
	      			AND e.id=$id
	      		";
	    return $db->fetchrow($sql);
    }
	public function getAllexspandetailByid($id){//using
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
   
    
   