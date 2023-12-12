<?php

class Allreport_Model_DbTable_DbRptStudentBalance extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
   
    
    
    public function getStudentBalance($search){
    	    	$db = $this->getAdapter();
    	    	$where=' ';
    	    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    		   	$sql=" SELECT 
				   (SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id = sp.branch_id LIMIT 1) AS branch_name,
				   sp.receipt_number,
				   sp.`penalty`,
				   sp.`grand_total`,
				   sp.`credit_memo`,
				   sp.`paid_amount`,
				   sp.`balance_due`,
				   s.stu_code,
				   s.stu_khname,
				   s.stu_enname,
				   s.last_name,
				   sp.is_current,
				   (SELECT title FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS grade_name,	
				   sp.note,		  
				   (SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS USER ,
				   sp.create_date                    
			   FROM  rms_student_payment AS sp, rms_student AS s WHERE s.stu_id = sp.student_id AND
				  is_void=0 AND sp.status=1 AND sp.`balance_due` > 0";
    	    	$order=" ORDER BY id DESC";
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		
    		$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
    		$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " and sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['grade']>0){
    		$where .= " and sp.grade = ".$search['grade'];
    	}
		if($search['is_current']>-1 AND $search['is_current'] !=''){
    		$where .= " and sp.is_current  = ".$search['is_current'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }

	public function getStudentPaymentDetail($search,$order_no){//
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$title = "title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$title = "title_en";
    	}
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" SELECT 
					spd.id,
					spd.fee,
					spd.qty,
					spd.subtotal,
					spd.extra_fee,
					(SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
					spd.discount_percent,
					spd.discount_amount,
					spd.paidamount,
					spd.note,
					spd.start_date,
					spd.validate,
					spd.is_start,
					spd.is_onepayment,
					sp.student_id,
					sp.receipt_number,
					sp.create_date,
					sp.is_void,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.last_name,
					s.create_date AS date_start_study,				  
					(SELECT $label FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
					spd.payment_term AS payment_id,
					(SELECT $label FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status,
					(SELECT generation FROM rms_tuitionfee WHERE rms_tuitionfee.id = sp.academic_year LIMIT 1) AS academic_type,
					d.items_id,
					d.$title AS service_name,
					(SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS major_name,
					(SELECT $degree FROM rms_items  WHERE rms_items.id = d.items_id LIMIT 1 ) AS category                             
					FROM 
					    rms_student_payment AS sp,
					    rms_student_paymentdetail AS spd,
					    rms_itemsdetail AS d,
					    rms_student AS s
				    WHERE 
				    	s.stu_id = sp.student_id
				    	AND sp.id=spd.payment_id 
				    	AND d.id = spd.itemdetail_id ";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		
    		$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(last_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
    		$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " and sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['service_type']>0){
    		$where .= " and d.items_type = ".$search['service_type'];
    	}
    	if(!empty($search['group'])){
    		$where .= " AND sp.group_id = ".$search['group'];
    	}
    	if(!empty($search['item'])){
    		if($search['item']>0){
    		$where .= " AND d.items_id = ".$search['item'];
    		}
    	}
    	if($search['pay_term']!=''){
    		$where .= " and spd.payment_term = ".$search['pay_term'];
    	}
    	if(!empty($search['service'])){
    		$where .= " AND spd.itemdetail_id = ".$search['service'];
    	}
    	if($search['study_year']>0){
    		$where .= " and sp.academic_year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " and sp.degree = ".$search['degree'];
    	}
    	if($search['grade_all']>0){
    		$where .= " AND sp.grade = ".$search['grade_all'];
    	}
//     	if($search['user']>0){
//     		$where .= " and sp.user_id = ".$search['user'];
//     	}
    	if($order_no==1){
    		$order=" ORDER BY sp.branch_id ASC, sp.id ASC ";
    	}elseif($order_no==2){//used order by student 
    		$order=" ORDER BY sp.branch_id ASC, sp.student_id DESC ";
    	}else{
//     		$order=" ORDER BY sp.branch_id ASC, d.items_id ";
    		$order=" ORDER BY sp.branch_id ASC,d.items_type ASC,d.items_id ASC,sp.id DESC  ";
    	}
    	
    	if (!empty($search['action'])){
    		if ($search['action']=="paymentHistorty"){
    			$order=" ORDER BY sp.branch_id ASC,sp.student_id ASC,sp.id DESC, d.items_type ASC,d.items_id ASC  ";
    		}
    	
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$where.= $_db->getAccessPermission('sp.branch_id');
    	
    	return $db->fetchAll($sql.$where.$order);
    }
 
}   