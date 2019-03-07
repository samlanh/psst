<?php

class Allreport_Model_DbTable_DbRptStudentBalance extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
    function getAllStudentBalance($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission("sp.branch_id");
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "school_namekh";
    		$stu_name="s.stu_khname ";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "school_nameen";
    		$stu_name="CONCAT(s.last_name,'-',s.stu_enname) ";
    	}
    	$sql="select 
    				sp.id,
    				(select $branch from rms_branch where br_id = sp.branch_id) as branch_name,
    				sp.receipt_number,
    				s.stu_code,
    				$stu_name as stu_name ,
    				(select $grade from rms_itemsdetail where rms_itemsdetail.id = sp.grade) as grade_name,
    				sp.penalty,
    				sp.grand_total,
    				sp.credit_memo,
    				sp.paid_amount,
    				sp.balance_due,
    				sp.note,
    				(SELECT CONCAT (`last_name`,' ', `first_name`) FROM `rms_users` WHERE `rms_users`.id = sp.user_id LIMIT 1) as user,
    				sp.create_date
				from 
					rms_student_payment AS sp,
					rms_student as s
				where 
					s.stu_id=sp.student_id
					and sp.balance_due>0  
					and sp.status=1
					and sp.is_void=0
					$branch_id 
    		";
    	
     	$order=" ORDER by sp.id ASC ";
    	$where = '';
     	
     	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
     	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
     	
     	$where = " AND ".$from_date." AND ".$to_date;
     	
     	if(!empty($search['adv_search'])){
     		$s_where = array();
     		$s_search = addslashes(trim($search['adv_search']));
     		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
     		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
     		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
     		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
     		$s_where[] = " s.last_name LIKE '%{$s_search}%'";
     		$where .=' AND ( '.implode(' OR ',$s_where).')';
     	}
     	
     	if(!empty($search['branch_id'])){
     		$where .=" and sp.branch_id = ".$search['branch_id'];
     	}
     	if(!empty($search['grade'])){
     		$where .=" and sp.grade = ".$search['grade'];
     	}
    		
//     		echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   