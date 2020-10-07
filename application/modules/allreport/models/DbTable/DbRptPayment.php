<?php

class Allreport_Model_DbTable_DbRptPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getStudentPaymentByid($id){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title ";
    		$degree = "rms_items.title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en ";
    		$degree = "rms_items.title_en";
    	}
    	$sql = "SELECT 
    				s.stu_id,
    				sp.branch_id,
    				s.stu_code,
    				s.serial,
    				s.stu_khname,
    				s.stu_enname,
    				s.last_name,
    				s.photo,
    				s.tel,
    				s.dob,
    				(SELECT $label from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				sp.receipt_number,
    				sp.create_date,
    				(SELECT CONCAT(last_name,' ',first_name) FROM rms_users where id=sp.user_id LIMIT 1) as user,
    				sp.is_void,
    				sp.grand_total,
    				sp.credit_memo,
    				sp.penalty AS fine,
    				sp.paid_amount,
    				sp.balance_due,
    				sp.note,
    			   (SELECT $label FROM rms_view where rms_view.type = 8 and key_code=sp.payment_method LIMIT 1) AS payment_method,
    			   sp.number,
    				(SELECT (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = rms_tuitionfee.academic_year LIMIT 1)
					 FROM rms_tuitionfee where rms_tuitionfee.id=sp.academic_year LIMIT 1) AS academic_year,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT $degree FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1)AS degree,
					(SELECT $label from rms_view where rms_view.type = 4 AND key_code=sp.session LIMIT 1) as session,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT gds.grade FROM rms_group_detail_student AS gds WHERE gds.stu_id = s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle
    			FROM
    				rms_student_payment as sp,
					rms_student as s,
    				rms_student_paymentdetail as spd
    			WHERE 
    				sp.student_id=s.stu_id 
    				and sp.id=$id LIMIT 1 ";
    		return $db->fetchRow($sql);
    }
    
    public function getPaymentDetailByType($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$where = ' ' ;
    	
    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = "SELECT * FROM v_getstudentpaymentdetail WHERE 1 $branch_id ";
    	$order=" ORDER BY service_id DESC ";
    	
    	if($search['service_type']>0){
    		$where.=" AND service_id =".$search['service_type'];
    	} 
    	
    	if(!empty($search['branch_id'])){
    		$where .= " and branch_id = ".$search['branch_id'];
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$s_where[] = " service LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " payment_term LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getPaymentDetailByTypeSumup($search){
    	$db = $this->getAdapter();
    	 
    	$where = ' ' ;
    	 
    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$sql = 'SELECT * FROM v_test WHERE 1 ';
    	$order=" ORDER BY service_categoryid DESC ";
    	 
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$s_where[] = " service LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " payment_term LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	//echo $sql.$where.$order;
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
    	if($search['user']>0){
    		$where .= " and sp.user_id = ".$search['user'];
    	}
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
    
    function getAllSpecailDis($search = '',$type=null){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$from_date =(empty($search['start_date']))? '1': "d.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "d.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql = " SELECT d.*,
    	CASE    
				WHEN  d.duration_type = 1 THEN CONCAT(d.duration_type,' ".$tr->translate("MONTHLY")."') 
				WHEN  d.duration_type = 2 THEN CONCAT(d.duration_type,' ".$tr->translate("TERM")."')
				WHEN  d.duration_type = 3 THEN CONCAT(d.duration_type,' ".$tr->translate("SEMESTER")."')
				WHEN  d.duration_type = 4 THEN CONCAT(d.duration_type,' ".$tr->translate("YEAR")."')
				END AS duration_type,
    	(SELECT so.dis_name FROM rms_discount AS so WHERE so.disco_id = d.dis_type LIMIT 1) AS discount_type,
    	CASE    
				WHEN  d.status = 1 THEN '".$tr->translate("RELATIVE")."'
				WHEN  d.status = 2 THEN '".$tr->translate("FRIEND")."'
				WHEN  d.status = 3 THEN '".$tr->translate("BUSINESS_PARTNER")."'
				WHEN  d.status = 4 THEN '".$tr->translate("OTHER")."'
				END AS status,
    	(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name
    	FROM `rms_specail_discount` AS d WHERE 1 ";
    	$orderby = " ORDER BY d.dis_type ASC, d.id DESC ";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " d.request_name LIKE '%{$s_search}%'";
    		$s_where[] = " d.phone LIKE '%{$s_search}%'";
    		$s_where[] = " d.stu_name LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['dis_type'])){
    		$where.= " AND d.dis_type  = ".$db->quote($search['dis_type']);
    	}
    	if(!empty($search['status_type'])){
    		$where.= " AND d.status = ".$db->quote($search['status_type']);
    	}
    	return $db->fetchAll($sql.$where.$orderby);
    }
    
    public function getStudentPayment($search){
    	    	$db = $this->getAdapter();
    	    	$where=' ';
    	    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    		   	$sql="SELECT
    		   				sp.id,
    		   				sp.grand_total as total_payment,
    		   				sp.penalty,
    		   				sp.credit_memo,
    		   				sp.is_void
    		   			FROM
    		   				rms_student_payment as sp,
    						rms_student as s
    		   			WHERE
    		   				sp.student_id=s.stu_id AND is_void=0 AND sp.status=1 ";
    	    	$order=" ORDER BY id DESC";
    	 
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		
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
    	if($search['study_year']>0){
    		$where .= " and sp.academic_year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " and sp.degree = ".$search['degree'];
    	}
    	if($search['grade_all']>0){
    		$where .= " AND sp.grade = ".$search['grade_all'];
    	}
    	if($search['user']>0){
    		$where .= " and sp.user_id = ".$search['user'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getPaymentReciptDetail($id){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    	}
    	$sql=" SELECT 
			    	(SELECT `rms_student`.`stu_khname` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) AS kh_name,
			    	(SELECT `rms_student`.`stu_enname` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) AS en_name,
			    	(SELECT `rms_view`.$label FROM `rms_view` WHERE ((`rms_view`.`type` = 2) AND (`rms_view`.`key_code` =(SELECT `rms_student`.`sex` FROM `rms_student` WHERE (`rms_student`.`stu_id` = sp.`student_id`) LIMIT 1) )))  as sex,
			    	(SELECT $grade FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS service,
			    	(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS items_type,
			    	(SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS payment_term,
			    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			    	sp.receipt_number as receipt_number,
			    	sp.`grand_total` AS total_payment,
			    	sp.`paid_amount` as paid_amount,
			    	sp.`balance_due` as balance_due,
			    	sp.`amount_in_khmer` as amount_in_khmer,
			    	(SELECT CONCAT (`last_name`,' ', `first_name`) FROM `rms_users` WHERE `rms_users`.id = sp.user_id LIMIT 1) as user,
			    	spd.id,
			    	spd.payment_id,
			    	spd.is_onepayment,
			    	spd.subtotal,
			    	spd.paidamount,
			    	spd.fee,
			    	spd.qty,
			    	spd.extra_fee,
			    	spd.subtotal,
			    	spd.note,
			    	spd.start_date,
			    	spd.validate,
			    	(SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
			    	spd.discount_amount,
			    	spd.discount_percent,
			    	spd.service_type
    			FROM 
			    	rms_student_payment as sp,
			    	rms_student_paymentdetail AS spd ";
    	$sql.='WHERE sp.id=spd.payment_id 
    		AND spd.payment_id = '.$id;
		return $db->fetchAll($sql);    	
    }
    
	function submitPaidDate($data){
    	$db=$this->getAdapter();
		$this->_name='rms_student_payment';
		if(!empty($data['identity'])){
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$arr = array(
						'create_date'=>$data['create_date_'.$i]
						);
				$where=" id = ".$data['payment_id_'.$i];
				$this->update($arr, $where);
			}
		}
    }
   
    
    
    public function getAllStudentBepayService($search){
    	$db = $this->getAdapter();
    	$sql ='SELECT 
    				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
    				s.stu_id,
    				s.stu_enname,
    				s.stu_khname,
    				s.tel,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=s.sex LIMIT 1)AS sex,
			    	s.stu_code,
			    	s.is_subspend,
			    	s.create_date as date_start_study,
			    	
			    	sp.create_date,
			    	sp.receipt_number,
			    	sp.note,
			    	item.title as service_name,
			    	item.items_id,
			    	spd.payment_term,
			    	spd.start_date,
			    	spd.validate,
			    	spd.paidamount
			    	
    			FROM
    				rms_student AS s,
    				rms_itemsdetail as item,
    				rms_student_payment AS sp,
    				rms_student_paymentdetail AS spd
    				
    			WHERE
    				sp.student_id=s.stu_id
    				AND sp.id=spd.payment_id 
    				AND item.id = spd.itemdetail_id
    				and spd.is_start=1
    				and spd.is_onepayment = 0
			    	and s.status=1
			    	AND s.is_subspend=0
    		';
    	
    	$where = ' ';
    
    	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("s.branch_id");
    
    	//$order="  order by academic_year DESC,degree ASC,grade ASC,session ASC,stu_id DESC";
    	$order = " order by item.id ASC, item.items_id ,s.stu_enname ASC";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}if($search['degree']>0){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if($search['grade_all']>0){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND group_id='.$search['group'];
    	}
//     	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getStudentPaymentbyDegree($search,$order_no){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" SELECT
			    	spd.id,
			    	
			    	SUM(spd.fee*spd.qty) AS fee,
			    	SUM(spd.extra_fee) AS extra_fee,
			    	SUM(spd.paidamount) AS paidamount,
			    	
			    	spd.subtotal,
			    	spd.discount_percent,
			    	spd.fee,
			    	sp.penalty,
			    	sp.credit_memo,
			    	sp.create_date,
			    	sp.degree,
			    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=spd.itemdetail_id AND rms_items.type=1 LIMIT 1) AS service_name,
			    	(SELECT title FROM `rms_items` WHERE id=sp.degree LIMIT 1) AS degree_name
			    	
			    FROM
			    	rms_itemsdetail AS item,
				    rms_student_payment AS sp,
				    rms_student_paymentdetail AS spd,
				    rms_student AS s
			    WHERE
			    	item.id = spd.itemdetail_id
			    	AND s.stu_id = sp.student_id
			    	AND sp.id=spd.payment_id
    				AND is_void=0 ";
    	if(!empty($search['title'])){
    		$s_where = array();
//     		$s_search = addslashes(trim($search['title']));
//     		$s_where[] = " stu_code LIKE '%{$s_search}%'";
//     		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
//     		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
//     		$s_where[] = " p.title LIKE '%{$s_search}%'";
//     		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
//     		$where .=' AND ( '.implode(' OR ',$s_where).')';
        
    	}
    	if($search['branch_id']>0){
    		$where .= " and sp.branch_id = ".$search['branch_id'];
    	}
//     	if($search['payment_by']>0){
//     		$where .= " and spd.type = ".$search['payment_by'];
//     	}
//     	if(!empty($search['service'])){
//     		$where .= " AND spd.type!=1 AND spd.service_id = ".$search['service'];
//     	}
    	if($search['study_year']>0){
    		$where .= " and sp.year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " and sp.degree = ".$search['degree'];
    	}
//     	if($search['grade_all']>0){
//     		$where .= " AND spd.type=1 AND sp.grade = ".$search['grade_all'];
//     	}
    	$order=" GROUP BY sp.degree ASC 
    	ORDER BY sp.degree DESC,spd.itemdetail_id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getPaymentByDate($search){
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch = $_db->getAccessPermission();
    	
    	$sql="SELECT 
				  sp.id,
				  DATE_FORMAT(sp.create_date,'%Y-%m-%d') AS for_date,
				  SUM(CASE WHEN spd.type=1 THEN paidamount ELSE 0 END) AS fulltime_fee,
				  SUM(CASE WHEN spd.type=2 THEN paidamount ELSE 0 END) AS parttime_fee,
				  SUM(CASE WHEN spd.type=3 THEN paidamount ELSE 0 END) AS service_fee,
				  SUM(CASE WHEN spd.type=4 THEN paidamount ELSE 0 END) AS material_fee,
				  0 AS 'g_total_test_price',
				  0 AS 'total_test_price',
				  0 AS 'total_otherincome'
				FROM
				  rms_student_payment AS sp,
				  rms_student_paymentdetail AS spd 
				WHERE 
				  sp.id = spd.payment_id 
				  AND sp.is_void = 0 
				  $branch
    		";
    	
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	$group_by = " GROUP BY DATE_FORMAT(sp.create_date,'%Y-%m-%d') ";
    	$order=" order by sp.`create_date` ASC";
    	
    	 
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " AND sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['study_year']>0){
    		$where .= " AND sp.year = ".$search['study_year'];
    	}
    	if($search['degree']>0){
    		$where .= " AND sp.degree = ".$search['degree'];
    	}
    	if($search['grade_all']>0){
    		$where .= " AND sp.grade = ".$search['grade_all'];
    	}
    	if($search['session']>0){
    		$where .= " AND sp.session = ".$search['session'];
    	}
    	if($search['user']>0){
    		$where .= " AND sp.user_id = ".$search['user'];
    	}
    	
    	$row = $db->fetchAll($sql.$where.$group_by.$order);
    	$studentTestPayment = $this->getStudentTestPaymentDate($search);
    	if (!empty($studentTestPayment)){
    		$row = array_merge($row, $studentTestPayment);
    	}
    	$otherIncome = $this->getTotalOtherIncomeByDate($search);
    	if (!empty($otherIncome)){
    		$row = array_merge($row, $otherIncome);
    	}
//     	foreach ($row as $rs){
//     		echo $rs['for_date']." fulltime_fee: ".$rs['fulltime_fee']." parttime_fee: ".$rs['parttime_fee']." g_total_test_price: ".$rs['g_total_test_price']." g_total_test_price: ".$rs['total_otherincome']."<br />";
//     	}
//     	exit();
    	$payment = array();
    	$i=0;
    	foreach ($row as $key => $rs)
    	{ $i++;
    		$date = date_create($rs['for_date']);
    		$newIndex = date_format($date, "y").date_format($date, "m").date_format($date, "d").date_format($date, "H").date_format($date, "i").date_format($date, "s").time();
    		if (array_key_exists($newIndex,$payment)){
    			$sale_date = $rs['for_date'];
    			$seee = date("s",strtotime("$sale_date +$i second"));
    			$newIndex = date_format($date, "y").date_format($date, "m").date_format($date, "d").date_format($date, "H").date_format($date, "i").$seee.time();
    			$payment[$newIndex] = $rs;
    		}else{
    			$payment[$newIndex] = $rs;
    		}
    	}
//     	print_r($payment);exit();
    	krsort($payment);
    	return $payment;
    }
    function getStudentTestPaymentDate($search=null){
    	try{
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$branch_id = $_db->getAccessPermission('st.branch_id');
    		$db=$this->getAdapter();
    			
    		$from_date =(empty($search['start_date']))? '1': "st.paid_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "st.paid_date <= '".$search['end_date']." 23:59:59'";
    		
    		$sql="SELECT
    		st.id,
    		st.`paid_date` AS for_date,
    		0 AS 'fulltime_fee',
			0 AS 'parttime_fee',
			0 AS 'service_fee',
			0 AS 'material_fee',
    		SUM(st.total_price) AS g_total_test_price, 
			SUM(st.price) AS total_test_price,
			0 AS 'total_otherincome'
    		FROM
    		rms_student_test AS st
    		WHERE
    		total_price>0
    		AND status=1
    		$branch_id ";
    
    		$where = " AND ".$from_date." AND ".$to_date;
    		if($search['branch_id']>0){
    			$where .= " AND st.branch_id = ".$search['branch_id'];
    		}
    		if(!empty($search['title'])){
	    		$s_where=array();
	    		$s_search= addslashes(trim($search['title']));
    				$s_where[]= " st.receipt_no LIKE '%{$s_search}%'";
    				$s_where[]= " st.kh_name LIKE '%{$s_search}%'";
    				$s_where[]= " st.en_name LIKE '%{$s_search}%'";
    				$s_where[]= " st.serial LIKE '%{$s_search}%'";
    				$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['degree'])){
    			$where.= " AND st.degree = ".$search['degree'];
    		}
    		if(!empty($search['user'])){
    			$where.=" AND account_userid = ".$search['user'] ;
    		}
    		$group_by = " GROUP BY DATE_FORMAT(st.paid_date,'%Y-%m-%d') ";
    		$order=" order by st.paid_date ASC";
    		
    		return $db->fetchAll($sql.$where.$group_by.$order);
    					
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
    function getTotalOtherIncomeByDate($search){
    	try{
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$branch_id = $_db->getAccessPermission('st.branch_id');
    		$db=$this->getAdapter();
    		 
    		$from_date =(empty($search['start_date']))? '1': "ic.`date` >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "ic.`date` <= '".$search['end_date']." 23:59:59'";
    	
    		$sql="SELECT 
				ic.`id`,
				ic.`date` AS for_date,
				0 AS 'fulltime_fee',
				0 AS 'parttime_fee',
				0 AS 'service_fee',
				0 AS 'material_fee',
				0 AS 'g_total_test_price',
				0 AS 'total_test_price',
				SUM(ic.`total_amount`) AS total_otherincome
				FROM 
				`ln_income` AS ic
				WHERE 1
    		$branch_id ";
    	
    		$where = " AND ".$from_date." AND ".$to_date;
    		if($search['branch_id']>0){
    		$where .= " AND ic.branch_id = ".$search['branch_id'];
    		}
    		if(!empty($search['title'])){
    		$s_where=array();
    		$s_search= addslashes(trim($search['title']));
    		$s_where[]= " ic.title LIKE '%{$s_search}%'";
    		$s_where[]= " ic.invoice LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
//     		if(!empty($search['degree'])){
//     		$where.= " AND st.degree = ".$search['degree'];
//     		}
    		if(!empty($search['user'])){
    		$where.=" AND ic.user_id = ".$search['user'] ;
    		}
    		$group_by = " GROUP BY DATE_FORMAT(ic.`date`,'%Y-%m-%d') ";
    		$order=" order by ic.`date` ASC";
    		return $db->fetchAll($sql.$where.$group_by.$order);
    			
    	}catch(Exception $e){
    			echo $e->getMessage();
    	}
    }
    function getCustomerPaymentById($id){
    	try{
    		$db=$this->getAdapter();
    		$sql="select
			    		*,
			    		(select name_en from rms_view where type=2 and key_code=sex) as sex,
			    		(select name_en from rms_view where type=10 and key_code=is_void) as status,
			    		(select first_name from rms_users as u where u.id=void_by) as void_by,
			    		
			    		(select last_name from rms_users where id=c.user_id LIMIT 1) as last_name,
    					(select first_name from rms_users where id=c.user_id LIMIT 1) as user
    				from
    					rms_customer_payment c
    				where
    					id = $id
    				limit 1	
    			";
    		return $db->fetchRow($sql);
	    }catch(Exception $e){
	   		echo $e->getMessage();
	    }
    }
    function updateValidationbyreceipt($data){
             $this->_name="rms_student_paymentdetail";
             $ids = explode(',', $data['identity']);
             $disc = 0;
             $total = 0;
//              print_r($data);exit();
             foreach ($ids as $i){
		    	$_arr = array(
// 		    			'service_id'	=>$data['service_'.$i],
// 		    			'payment_term'	=>$data['term_'.$i],
// 		    			'fee'			=>$data['price_'.$i],
// 		    			'qty'			=>$data['qty_'.$i],
// 		    			'subtotal'		=>$data['subtotal_'.$i],
// 		    			'late_fee'		=>$data['late_fee_service_'.$i],
// 		    			'extra_fee'		=>$data['additional_fee_'.$i],
// 		    			'discount_percent'	=>$data['discount_'.$i],
// 		    			'discount_fix'	=>0,
// 		    			'paidamount'	=>$data['paidamount_'.$i],
// 		    			'balance'		=>0,
		    			'is_onepayment'=>$data['onepayment_'.$i],
		    			'start_date'	=>$data['start_date'.$i],
		    			'validate'		=>$data['end_date'.$i],
// 		    			'note'			=>$data['remark'.$i],
// 		    			'type'			=>$payfor_type,
// 		    			'is_parent'		=>$spd_id,
// 		    			'is_complete'   =>$complete,
// 		    			'comment'		=>$status,
		    	);
		    	
		    	$where="id = ".$data['id_'.$i];
		    	$this->update($_arr, $where);
             }
    }
    
    
    
}   