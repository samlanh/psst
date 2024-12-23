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
					
    				fam.fatherName AS father_enname,
    				fam.motherName AS mother_enname,
    				
					fam.fatherPhone AS father_phone,
    				fam.motherPhone AS mother_phone,
    				fam.guardianPhone AS guardian_tel,
					
    				(SELECT $label from rms_view where type=2 and key_code=s.sex LIMIT 1) as sex,
    				(SELECT $label from rms_view where type=40 and key_code=s.studentType LIMIT 1) as studentType,
    				sp.data_from,
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
					sp.academic_year AS feeId,
    				COALESCE(sp.degree,0) AS degreeId,
    			   (SELECT $label FROM rms_view where rms_view.type = 8 and key_code=sp.payment_method LIMIT 1) AS payment_method,
    			   (SELECT bank_name from `rms_bank` where id=sp.bank_id LIMIT 1) AS bankName,
    			   sp.number,
				    (SELECT CONCAT_WS('-',ac.fromYear,ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = t.academic_year LIMIT 1) as academic_year,
				    (SELECT title_kh FROM `rms_studytype` AS ac WHERE ac.id = t.term_study LIMIT 1) AS term_study,
					(SELECT payment_term from `rms_student_paymentdetail` as spd WHERE sp.id = spd.payment_id AND spd.service_type=1 and payment_term between 2 and 4 limit 1) AS payment_term,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT $degree FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1)AS degree,
					(SELECT $label from rms_view where rms_view.type = 4 AND key_code=sp.session LIMIT 1) as session,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT gds.grade FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND gds.stu_id = s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle,
					(SELECT p.`title`  FROM `rms_parttime_list` AS p WHERE p.`id` = (SELECT gds.`session` FROM `rms_group_detail_student` as gds WHERE  gds.stu_id = s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 LIMIT 1 ) LIMIT 1) as parttimeTitle,
					(SELECT group_code FROM `rms_group` WHERE rms_group.id=(SELECT ds.group_id FROM rms_group_detail_student AS ds 
					WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.grade=sp.grade AND ds.degree=sp.degree ORDER BY ds.gd_id DESC LIMIT 1) LIMIT 1) AS group_name
					
					,COALESCE((SELECT spd.`academicFeeTermId` FROM `rms_student_paymentdetail` AS spd WHERE spd.`payment_id` = sp.`id` AND spd.`service_type` = 1 LIMIT 1 ),0) AS academicFeeTermId
					,(SELECT spd.`start_date` FROM `rms_student_paymentdetail` AS spd WHERE spd.`payment_id` = sp.`id` AND spd.`service_type` = 1 LIMIT 1 ) AS startDatePmt
    			FROM
    				rms_student_payment as sp 
					JOIN rms_student as s ON sp.student_id=s.stu_id 
					LEFT JOIN rms_tuitionfee t
					ON (t.id=sp.academic_year)
    				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					
    			WHERE 
    				1
    				AND sp.id=$id  ";
    	//$sql.=$_db->getAccessPermission('sp.branch_id');
    	$sql.=" LIMIT 1 ";
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
    function getDiscountSetting($search = '',$type=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		$colunmname = 'name_en';
		$strDegree = 'shortcut';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";
		$sqlPeriod = "(SELECT $colunmname FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT ds.id, 
					(SELECT $branch FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountCode,
					discountTitle,
					$sqlDiscountFor AS discountForText,
					(SELECT $colunmname FROM `rms_view` WHERE TYPE=38 AND key_code=ds.discountForType LIMIT 1) AS discountForOption,
					(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,degree)) as degreeList,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id LIMIT 1 ) AS StuAmount,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=1  LIMIT 1 ) StuAmountUsed,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=0  LIMIT 1 ) AmountStopUsed,		
					CASE 
						WHEN  ds.discountPeriod=2 THEN CONCAT(COALESCE(DATE_FORMAT(ds.startDate,'%d/%m/%Y'),''),'-',COALESCE(DATE_FORMAT(ds.endDate,'%d/%m/%Y'),''))
						ELSE $sqlPeriod
					END AS discountPeriod,
					(SELECT first_name FROM rms_users WHERE id=ds.userId LIMIT 1 ) AS user_name,
					ds.createDate";

		$sql .= $dbp->caseStatusShowImage("ds.status");
		$sql .= " FROM rms_dis_setting AS ds ";

		$order =" ORDER BY id DESC ";
		$where =" WHERE 1";
	

		if (!empty($search['title'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " discountTitle LIKE '%{$s_search}%'";
			$s_where[] = " discountValue LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (($search['academic_year'])>0) {
			$where .= ' AND ds.academicYear=' . $search['academic_year'];
		}
		
		if (!empty($search['branch_id'])) {
			$where .= ' AND ds.branchId=' . $search['branch_id'];
		}
		if (!empty($search['studentId'])) {
			$where .= ' AND ds.studentId=' . $search['studentId'];
		}
		if (!empty($search['discountId'])) {
			$where .= ' AND ds.discountId=' . $search['discountId'];
		}
		if (!empty($search['discountFor'])) {
			$where .= ' AND ds.discountFor=' . $search['discountFor'];
		}
		if (!empty($search['discountPeriod'])) {
			$where .= ' AND ds.discountPeriod=' . $search['discountPeriod'];
		}
		if ($search['status_search'] > -1) {
			$where .= ' AND status=' . $search['status_search'];
		}
		$where .= $dbp->getAccessPermission('ds.branchId');
		return $db->fetchAll($sql . $where . $order);
		
    	
    
     if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " (SELECT dis_name AS name FROM `rms_discount` WHERE disco_id=discountType LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " discountValue LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
     }
     if(!empty($search['branch_id'])){
    		$where.=' AND g.branch_id='.$search['branch_id'];
     }
     if(!empty($search['studentId'])){
    		$where.=' AND g.studentId='.$search['studentId'];
     }
     if(!empty($search['discountId'])){
     	$where.=' AND g.discountType='.$search['discountId'];
     }
   	 if($search['status_search']>-1){
    		$where.=' AND status='.$search['status_search'];
     }
    	$where.=$dbp->getAccessPermission('g.branch_id');
    	return $db->fetchAll($sql.$where.$order);
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
//     	if($search['user']>0){
//     		$where .= " and sp.user_id = ".$search['user'];
//     	}
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
			    	(SELECT $grade FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS service,
			    	(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS items_type,
			    	spd.payment_term as paymentTermId,
			    	(SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS payment_term,
			    	spd.is_onepayment,
			    	spd.subtotal,
			    	spd.paidamount,
			    	spd.fee,
			    	spd.qty,
			    	spd.extra_fee,
			    	spd.totalpayment,
			    	spd.note,
			    	DATE_FORMAT(spd.start_date,'%d-%m-%Y') start_date,
			    	DATE_FORMAT(spd.validate,'%d-%m-%Y') validate,
			    	(SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
			    	spd.discount_amount,
			    	spd.discount_percent,
			    	spd.service_type
					,spd.academicFeeTermId 
					,(SELECT sed.title FROM rms_startdate_enddate AS sed WHERE sed.id =spd.academicFeeTermId  LIMIT 1 ) AS academicFeeTermTitle
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
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
	   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    }
    }
    function updateValidationbyreceipt($data){
             $this->_name="rms_student_paymentdetail";
             $ids = explode(',', $data['identity']);
             $disc = 0;
             $total = 0;
             foreach ($ids as $i){
		    	$_arr = array(
		    			'is_onepayment'=>$data['onepayment_'.$i],
		    			'start_date'	=>$data['start_date'.$i],
		    			'validate'		=>$data['end_date'.$i],
		    	);
		    	
		    	$where="id = ".$data['id_'.$i];
		    	$this->update($_arr, $where);
             }
    }
	
	
	function getPaymentHistory($stuId){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('sp.branch_id');
			
			$lang = $_db->currentlang();
			if($lang==1){// khmer
				$label = "name_kh";
			}else{ // English
				$label = "name_en";
			}
	
			$db=$this->getAdapter();
			$sql=" SELECT
			            sp.id,
						sp.branch_id,
						sp.receipt_number,
						s.stu_code,
						s.stu_khname,
						s.stu_enname,
						s.last_name,
						(SELECT title FROM `rms_items` WHERE rms_items.id=sp.degree LIMIT 1 ) AS degree,
						(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS grade,
						(SELECT name_en FROM rms_view WHERE rms_view.type = 4 AND key_code=sp.session LIMIT 1) AS session,
						sp.create_date,
						sp.is_void,
						(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user_id,
						(SELECT name_en FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS void_status,
						(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = sp.payment_method LIMIT 1) AS paymentMethod,
						sp.grand_total AS total_payment,
						sp.credit_memo,
						sp.grand_total,
						sp.penalty,
						sp.paid_amount,
						sp.balance_due,
						sp.note,
						sp.is_closed,
						sp.payment_method,
						(SELECT first_name FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by
				  FROM
						rms_student AS s,
						rms_student_payment AS sp
				  WHERE 
						s.stu_id = sp.student_id  
						$branch_id  ";
	
			$where = " AND s.stu_id = $stuId ";
			$order=" ORDER By sp.id DESC ";
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    
    public function getDiscountsetById($id)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";
		$sqlPeriod = "(SELECT $colunmname FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT  ds.id, 
					(SELECT $branch FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountCode,
					discountTitle,
					(CASE 
						WHEN ds.discountFor=2 THEN $sqlDiscountFor
						WHEN ds.discountFor=1 THEN $strStudent
					END) AS discountForText,
					(SELECT $colunmname FROM `rms_view` WHERE TYPE=38 AND key_code=ds.discountFor LIMIT 1) AS discountForOption,
					(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,degree)) as degreeList,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id LIMIT 1 ) AS StuAmount,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=1  LIMIT 1 ) AS  StuAmountUsed,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=0  LIMIT 1 ) AS AmountStopUsed,		
					CONCAT(COALESCE($sqlPeriod),'',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),' ',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) AS discountPeriod, 
					(SELECT first_name FROM rms_users WHERE id=ds.userId LIMIT 1 ) AS user_name,
					ds.createDate
		 FROM rms_dis_setting AS ds WHERE id=" . $db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branch_id');
		$sql .= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	}
	function getStudentDiscountById($id,$search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		
		$grade = 'title_en';
		$degree = 'title_en';
		if ($currentLang == 1) {
			$grade = 'title';
			$degree = 'title';
		}
		$sql = "
			SELECT 
			    s.stu_id,
				s.stu_code AS stu_code
			
				,s.stu_khname AS stu_name
				,CONCAT(s.last_name,' ' ,s.stu_enname) AS stu_name_en
				,s.sex AS sex
				,tel
				,(SELECT $degree FROM `rms_items` WHERE rms_items.id=ds.degreeId LIMIT 1 ) AS degree
				,(SELECT $grade FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=ds.grade LIMIT 1) AS grade
				,COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.academicYearEnroll LIMIT 1),'') AS startYear
				,COALESCE((SELECT shortcut FROM `rms_view` WHERE key_code= s.studentType AND TYPE=40  LIMIT 1),'') AS studentType 
				,ds.isCurrent ";

		$sql .=" FROM  `rms_discount_student` AS ds 
					INNER JOIN rms_student AS s ON ds.studentId = s.stu_id
				";
		$sql.=" WHERE  ds.discountGroupId = $id  ";

		if(!empty($search['academicYearEnroll'])){
			$sql .= " AND s.academicYearEnroll = ".$search['academicYearEnroll'];
		}
		if(!empty($search['studentType'])){
			$sql .= " AND s.studentType = ".$search['studentType'];
		}
		if(!empty($search['degree'])){
			$sql .= " AND ds.degreeId = ".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql .= " AND ds.grade = ".$search['grade'];
		}
		if(!empty($search['discount_status'])){
			$sql .= " AND ds.isCurrent = ".$search['discount_status'];
		}
		
		$sql.=" ORDER By ds.degreeId,ds.grade ASC , ds.isCurrent DESC";
		return $db->fetchAll($sql);
	}

	public function getFeeById($id)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$field = 'name_en';
    	$str = 'title_eng';
    	if ($currentLang==1){
    		$field = 'name_kh';
    		$str = 'title_kh';
    	}
    	$sql = "SELECT 
					t.id
					,(SELECT b.$branch from rms_branch AS b WHERE b.br_id =t.branch_id LIMIT 1) AS branch
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM rms_academicyear AS ac WHERE ac.id=t.academic_year LIMIT 1) AS academic
					,(SELECT $str FROM rms_studytype WHERE rms_studytype.id =t.term_study  LIMIT 1) AS study_type
					,CASE is_multi_study
						WHEN 1 THEN '".$tr->translate("MULTY_PROGRAM")."'
						WHEN 0 THEN '".$tr->translate("ONE_PROGRAM_ONLY")."'
					END is_multistudy
					,t.generation
					,(SELECT sch.title FROM `rms_schooloption` AS sch WHERE sch.id=t.school_option LIMIT 1) AS school_option
					,t.create_date
					,(SELECT v.$field FROM rms_view AS v WHERE v.type=12 and v.key_code=t.is_finished LIMIT 1) AS is_finished
			";
    	
    	$sql.=$dbp->caseStatusShowImage("t.status");
    	$sql.=" FROM `rms_tuitionfee` AS t
    		WHERE t.type=1 AND t.id	= ". $db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('t.branch_id');
		$sql .= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	}
	function getStudentFeeDetailById($id,$search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		
		$grade = 'title_en';
		$degree = 'title_en';
		if ($currentLang == 1) {
			$grade = 'title';
			$degree = 'title';
		}
		$sql = "SELECT 
					s.stu_id,
					s.stu_code AS stu_code

					,s.stu_khname AS stu_name
					,CONCAT(s.last_name,' ' ,s.stu_enname) AS stu_name_en
					,s.sex AS sex
					,tel
					,COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.academicYearEnroll LIMIT 1),'') AS startYear
					,COALESCE((SELECT shortcut FROM `rms_view` WHERE key_code= s.studentType AND TYPE=40  LIMIT 1),'') AS studentType 
					,COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1),'') AS academic_year
					,(SELECT $degree FROM `rms_items` WHERE rms_items.id=ds.degree LIMIT 1 ) AS degree
					,(SELECT $grade  FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=ds.grade LIMIT 1) AS grade
					,(SELECT name_kh FROM rms_view WHERE TYPE=5 AND key_code=ds.stop_type LIMIT 1) AS status_student
					,ds.stop_type
					,ds.is_pass ";

		$sql .=" FROM  `rms_group_detail_student` AS ds 
					INNER JOIN rms_student AS s ON ds.stu_id = s.stu_id
				";
		$sql.=" WHERE ds.itemType=1 AND ds.is_current=1  AND ds.feeId  = $id  ";

		if(!empty($search['academicYearEnroll'])){
			$sql .= " AND s.academicYearEnroll = ".$search['academicYearEnroll'];
		}
		if(!empty($search['studentType'])){
			$sql .= " AND s.studentType = ".$search['studentType'];
		}
		if(!empty($search['degree'])){
			$sql .= " AND ds.degree = ".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql .= " AND ds.grade = ".$search['grade'];
		}
		if(!empty($search['student_status'])){
			if($search['student_status'] == 1){
				$sql .= " AND ds.stop_type = 0 ";
			}elseif($search['student_status'] == 2){
				$sql .= " AND ds.stop_type > 0 ";
			}
		}
		
		$sql.=" ORDER By ds.degree,ds.grade ASC ";
		return $db->fetchAll($sql);
	}
    
}   