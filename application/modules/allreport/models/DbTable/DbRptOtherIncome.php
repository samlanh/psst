<?php

class Allreport_Model_DbTable_DbRptOtherIncome extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllOtherIncome($search){//using 2-6-23
    	$db=$this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		
    	}
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = "SELECT 
    				*,
    				CASE
						WHEN optionType = 1 THEN '".$tr->translate("OTHER_INCOME")."'
						ELSE '".$tr->translate("OTHER_INCOME")."'
					END as incomeType,
					(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) 
					FROM rms_student AS s
					WHERE s.stu_id=student_id LIMIT 1) AS studentName,
					(SELECT bank_name FROM `rms_bank` b WHERE b.id=bank_id LIMIT 1) AS bank_name,
    				 payment_method AS payment_methodid,
    				(SELECT $branch from rms_branch where br_id = branch_id LIMIT 1) as branch_name,
    				(SELECT category_name FROM rms_cate_income_expense WHERE rms_cate_income_expense.id = cate_income LIMIT 1) AS cate_income, 
	    			(select category_name from rms_cate_income_expense where rms_cate_income_expense.id = cate_income LIMIT 1) as income_category,
	    			(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = payment_method LIMIT 1) AS payment_method,
	    			(SELECT CONCAT(first_name) from rms_users as u where u.id = user_id LIMIT 1)  as byUser,
	    			(SELECT first_name FROM rms_users WHERE rms_users.id = voidBy LIMIT 1) AS voidBy
    			 FROM 
    				ln_income  
    			WHERE 
    				1 ";
    	
    	$where= ' ';
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	if(!empty($search['cate_income'])){
    		$where.=" AND cate_income = ".$search['cate_income'] ;
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND ln_income.branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	if(!empty($search['user']) AND $search['user']>0){
    		$where.=" AND user_id = ".$search['user'] ;
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['txtsearch'])));
    		$s_where[] = " REPLACE(title,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(invoice,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission();
    	
    		if($search['receipt_order']==1){
    			$order=" ORDER BY id DESC ";
    		}else{
    			$order=" ORDER BY id ASC ";
    		}
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllOtherIncomebyCate($search){
    	$db=$this->getAdapter();
    	 
    	$sql = "SELECT 
    				i.*,
		    		SUM(total_amount) AS total_income, 
			    	cate.category_name AS income_category,
			    	cate.parent,
			    	(SELECT a.category_name FROM rms_cate_income_expense AS a WHERE a.id=cate.parent) AS parent_name,
			    	(SELECT first_name FROM rms_users AS u WHERE u.id = i.user_id)  AS user_name
		    	FROM
		    		ln_income AS i,
		    		rms_cate_income_expense AS cate
		    	WHERE
		    		i.cate_income = cate.id
		    		AND i.status=1 
		    		AND i.total_amount>0
    		";
    	
    	$where= ' ';
    	$order=" GROUP BY cate_income ORDER BY cate.parent DESC ";
    	$from_date =(empty($search['start_date']))? '1': " i.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " i.date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND i.branch_id = ".$search['branch_id'] ;
    	}
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
}  