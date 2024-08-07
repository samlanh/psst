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
		$branch = $_db->getBranchDisplay();
    	$label = "name_en";
		if($lang==1){ // khmer
    		$label = "name_kh";
    	}
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = "SELECT 
    				inc.*
					,CASE
						WHEN inc.optionType = 1 THEN '".$tr->translate("OTHER_INCOME")."'
						ELSE '".$tr->translate("OTHER_INCOME")."'
					END AS incomeType
					,CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentName
					,(SELECT b.bank_name FROM `rms_bank` b WHERE b.id=inc.bank_id LIMIT 1) AS bank_name
    				,payment_method AS payment_methodid
    				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = inc.branch_id LIMIT 1) AS branch_name
    				,(SELECT cate.category_name FROM rms_cate_income_expense AS cate WHERE cate.id = inc.cate_income LIMIT 1) AS cate_income
	    			,(select cate.category_name FROM rms_cate_income_expense AS cate where cate.id = inc.cate_income LIMIT 1) as income_category
	    			,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=8 and v.key_code = inc.payment_method LIMIT 1) AS payment_method
	    			,(SELECT CONCAT(u.first_name) FROM rms_users AS u WHERE u.id = inc.user_id LIMIT 1)  AS byUser
	    			,(SELECT u.first_name FROM rms_users AS u WHERE u.id = inc.voidBy LIMIT 1) AS voidBy
    			FROM 
    				ln_income  AS inc 
					LEFT JOIN rms_student AS s ON s.stu_id= inc.student_id
    			WHERE 
    				1 ";
    	
    	$where= ' ';
    	
    	$from_date =(empty($search['start_date']))? '1': " inc.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " inc.date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	if(!empty($search['cate_income'])){
    		$where.=" AND inc.cate_income = ".$search['cate_income'] ;
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND inc.branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND inc.branch_id = ".$search['branch_id'] ;
    	}
    	if(!empty($search['user']) AND $search['user']>0){
    		$where.=" AND inc.user_id = ".$search['user'] ;
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['txtsearch'])));
    		$s_where[] = " REPLACE(inc.title,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(inc.invoice,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission();
    	
    		if($search['receipt_order']==1){
    			$order=" ORDER BY inc.id DESC ";
    		}else{
    			$order=" ORDER BY inc.id ASC ";
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