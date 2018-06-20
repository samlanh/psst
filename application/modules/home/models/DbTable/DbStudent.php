<?php

class Home_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	
	public function getAllStudent($search){
		$_db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
				$sql = "SELECT  s.stu_id,
				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,s.stu_khname,s.stu_enname,
				CONCAT(s.stu_khname,'-',s.stu_enname) AS name,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=2 AND key_code = s.sex LIMIT 1) AS sex,
				tel ,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
				(SELECT group_code FROM `rms_group` WHERE id=s.group_id) AS group_name,
				(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=s.degree LIMIT 1) AS degree,
				(SELECT CONCAT(`major_enname`) FROM `rms_major` WHERE `major_id`=s.grade LIMIT 1) AS grade,
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(select room_name from rms_room where room_id=s.room LIMIT 1) as room,
				s.sex as sexcode,
				status,
				photo
				FROM rms_student AS s  WHERE  s.is_subspend=0 AND s.status = 1 ";
// 				(SELECT name_kh FROM `rms_view` WHERE TYPE=1 AND key_code = status LIMIT 1) AS status,
		$orderby = " ORDER BY s.stu_enname,s.stu_khname ASC ";
		if(empty($search)){
			return $_db->fetchAll($sql.$orderby);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','')  			LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_tel,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(father_enname,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(mother_enname,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(guardian_enname,' ','')LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(remark,' ','')  		LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(home_num,' ','')  		LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(street_num,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(village_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(commune_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(district_name,' ','')  LIKE '%{$s_search}%'";
			
			//$s_where[]="(SELECT	rms_view.name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = s.session) LIKE '%{$s_search}%'";
			//$s_where[]="(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND s.academic_year=".$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=" AND s.group_id=".$search['group'];
		}
		if(!empty($search['degree'])){
			$where.=" AND s.degree=".$search['degree'];
		}
		if(!empty($search['grade_bac'])){
			$where.=" AND s.grade=".$search['grade_bac'];
		}
		if(!empty($search['session'])){
			$where.=" AND s.session=".$search['session'];
		}
		if(!empty($search['time'])){
			$where.=" AND sp.time=".$search['time'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
// 		echo $sql.$where.$orderby;exit();
		return $_db->fetchAll($sql.$where.$orderby);
	}
	
	public function getStudentById($stu_id){
		$db = $this->getAdapter();
// 		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;

 		$sql = "SELECT *,(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
				(SELECT province_en_name FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name,
				
				(SELECT CONCAT(g.group_code,' ',
				(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation))  AS NAME 
				 FROM rms_group AS g WHERE g.id=s.group_id )  AS group_name,
				 
				 (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')')AS years FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year GROUP BY from_academic,to_academic,generation,TIME ) AS year_name,
				 (SELECT en_name FROM rms_dept WHERE dept_id=s.degree LIMIT 1) AS degree_name,
				 (SELECT major_enname FROM rms_major WHERE major_id=s.grade LIMIT 1) AS grade_name,
				 (SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1 ) AS room_name,
				  (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job
				  
				FROM rms_student as s WHERE 1 AND s.stu_id=$stu_id";
		
// 		if(!empty($search['adv_search'])){
// 			$s_where = array();
// 			//$s_search = addslashes(trim($search['adv_search']));
// 			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
// 			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(stu_khname,' ','') 	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(stu_enname,' ','')    	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(tel,' ','')  		   	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(father_phone,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(mother_phone,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(guardian_tel,' ','')  	LIKE '%{$s_search}%'";
				
// 			$s_where[]=" REPLACE(father_enname,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(mother_enname,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(guardian_enname,' ','')LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(remark,' ','')  		LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(home_num,' ','')  		LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(street_num,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(village_name,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(commune_name,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(district_name,' ','')  LIKE '%{$s_search}%'";
				
// 			$s_where[]="(SELECT	rms_view.name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = s.session) LIKE '%{$s_search}%'";
// 			$s_where[]="(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
// 		}
		
// 		if(!empty($search['study_year'])){
// 			$where.=" AND s.academic_year=".$search['study_year'];
// 		}
// 		if(!empty($search['degree'])){
// 			$where.=" AND s.degree=".$search['degree'];
// 		}
// 		if(!empty($search['grade_bac'])){
// 			$where.=" AND s.grade=".$search['grade_bac'];
// 		}
// 		if(!empty($search['session'])){
// 			$where.=" AND s.session=".$search['session'];
// 		}
// 		if(!empty($search['time'])){
// 			$where.=" AND sp.time=".$search['time'];
// 		}
		$where='';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
		return $db->fetchRow($sql.$where);
	}
	
	public function getStudentPaymentDetail($stu_id){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
// 		$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
	
		$sql=" Select
		spd.id,
		spd.type,
		spd.fee,
		spd.qty,
		spd.subtotal,
		spd.late_fee,
		spd.extra_fee,
		spd.discount_percent,
		spd.discount_fix,
		spd.paidamount,
		spd.balance,
		spd.note,
		spd.start_date,
		spd.validate,
		spd.is_start,
		spd.is_parent ,
		spd.is_complete,
		sp.scholarship_percent,
		sp.scholarship_amount,
		sp.tuition_fee,
		sp.student_id,
		sp.receipt_number,
		sp.create_date,
		sp.is_void,
		s.stu_code,
		s.stu_khname,
		s.stu_enname,
		p.title AS service_name,
		(SELECT pg.name_kh FROM `rms_pro_category` AS pg WHERE pg.id = (SELECT pp.cat_id FROM `rms_product` AS pp WHERE pp.id = p.ser_cate_id LIMIT 1) LIMIT 1) AS product_category,
		(SELECT major_enname FROM `rms_major` WHERE major_id=sp.grade LIMIT 1) As major_name,
		(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
		(SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
		(select name_en from rms_view where type=10 and key_code=sp.is_void LIMIT 1) as void_status,
		(select title from rms_program_type where rms_program_type.id=p.ser_cate_id AND p.type=2 LIMIT 1) service_cate
		FROM
		rms_student_payment as sp,
		rms_student_paymentdetail as spd,
		rms_student as s,
		rms_program_name as p
		where
		s.stu_id = sp.student_id
		AND sp.id=spd.payment_id
		AND p.service_id=spd.service_id

		AND s.stu_id=$stu_id";
// 		if(!empty($search['adv_search'])){
// 			$s_where = array();
// 			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
// 			$s_where[] = " REPLACE(stu_code,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = " REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = " REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = " REPLACE(p.title,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = " REPLACE(receipt_number,' ','')LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
// 		}
		
// 		if($search['branch_id']>0){
// 			$where .= " and sp.branch_id = ".$search['branch_id'];
// 		}
// 		if($search['payment_by']>0){
// 			$where .= " and spd.type = ".$search['payment_by'];
// 		}
// 		if(!empty($search['service'])){
// 			$where .= " AND spd.type!=1 AND spd.service_id = ".$search['service'];
// 		}
// 		if($search['study_year']>0){
// 			$where .= " and sp.year = ".$search['study_year'];
// 		}
// 		if($search['degree']>0){
// 			$where .= " and sp.degree = ".$search['degree'];
// 		}
// 		if($search['grade_all']>0){
// 			$where .= " AND spd.type=1 AND sp.grade = ".$search['grade_all'];
// 		}
// 		if(!empty($search['session'])){
// 			$where.=" AND sp.session=".$search['session'];
// 		}
// 		if($search['user']>0){
// 			$where .= " and sp.user_id = ".$search['user'];
// 		}
// 		if($order_no==1){
// 			$order=" ORDER BY payment_id DESC, spd.type DESC";
// 		}elseif($order_no==2){//used order by student
// 			$order=" ORDER BY sp.student_id DESC ";
// 		}else{
// 			$order=" ORDER BY spd.type DESC, p.ser_cate_id DESC ";
// 		}
		//echo $sql.$where;
		return $db->fetchAll($sql);
	}
	 
	public function getStudentServiceUsing($stu_id,$search,$order_no){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
// 		$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date   = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
	
		$sql=" Select
		spd.id,
		spd.type,
		spd.fee,
		spd.qty,
		spd.subtotal,
		spd.late_fee,
		spd.extra_fee,
		spd.discount_percent,
		spd.discount_fix,
		spd.paidamount,
		spd.balance,
		spd.note,
		spd.start_date,
		spd.validate,
		spd.is_start,
		spd.is_parent ,
		spd.is_complete,
		sp.scholarship_percent,
		sp.scholarship_amount,
		sp.tuition_fee,
		sp.student_id,
		sp.receipt_number,
		sp.create_date,
		sp.is_void,
		s.stu_code,
		s.stu_khname,
		s.stu_enname,
		p.title AS service_name,
		(SELECT pg.name_kh FROM `rms_pro_category` AS pg WHERE pg.id = (SELECT pp.cat_id FROM `rms_product` AS pp WHERE pp.id = p.ser_cate_id LIMIT 1) LIMIT 1) AS product_category,
		(SELECT major_enname FROM `rms_major` WHERE major_id=sp.grade LIMIT 1) As major_name,
		(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
		(SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
		(select name_en from rms_view where type=10 and key_code=sp.is_void LIMIT 1) as void_status,
		(select title from rms_program_type where rms_program_type.id=p.ser_cate_id AND p.type=2 LIMIT 1) service_cate
		FROM
		rms_student_payment as sp,
		rms_student_paymentdetail as spd,
		rms_student as s,
		rms_program_name as p
		where
		s.stu_id = sp.student_id
		AND sp.id=spd.payment_id
		AND p.service_id=spd.service_id

		AND spd.is_suspend=0 
		AND spd.type=3
		
		AND s.stu_id=$stu_id";
// 		if(!empty($search['adv_search'])){
// 			$s_where = array();
// 			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
// 			//print_r($s_search);exit();
// 			$s_where[] = "REPLACE(stu_code,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(p.title,' ','')  		LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(receipt_number,' ','')LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
// 		}
	
// 		if($search['branch_id']>0){
// 			$where .= " and sp.branch_id = ".$search['branch_id'];
// 		}
// 		if($search['payment_by']>0){
// 			$where .= " and spd.type = ".$search['payment_by'];
// 		}
// 		if(!empty($search['service'])){
// 			$where .= " AND spd.type!=1 AND spd.service_id = ".$search['service'];
// 		}
// 		if($search['study_year']>0){
// 			$where .= " and sp.year = ".$search['study_year'];
// 		}
// 		if($search['degree']>0){
// 			$where .= " and sp.degree = ".$search['degree'];
// 		}
// 		if($search['grade_all']>0){
// 			$where .= " AND spd.type=1 AND sp.grade = ".$search['grade_all'];
// 		}
// 		if(!empty($search['session'])){
// 			$where.=" AND sp.session=".$search['session'];
// 		}
// 		if($search['user']>0){
// 			$where .= " and sp.user_id = ".$search['user'];
// 		}
// 		if($order_no==1){
// 			$order=" ORDER BY payment_id DESC, spd.type DESC";
// 		}elseif($order_no==2){//used order by student
// 			$order=" ORDER BY sp.student_id DESC ";
// 		}else{
// 			$order=" ORDER BY spd.type DESC, p.ser_cate_id DESC ";
// 		}
		//echo $sql.$where;
		return $db->fetchAll($sql);
	}
	 
	function getRescheduleByGroupId($id){
		$db=$this->getAdapter();
		$sql=" SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
    	(SELECT CONCAT(m.major_enname,' (',(SELECT d.en_name FROM rms_dept AS d WHERE m.dept_id=d.dept_id ),')')
    	FROM rms_major AS m WHERE 1 AND major_enname!='' AND m.major_id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1))AS grade_name,
    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times ,
    	gd.stu_id
    	FROM rms_group_reschedule AS gr,rms_group_detail_student AS gd
    	WHERE gr.group_id=gd.group_id
    	 AND   gd.stu_id=$id
    	    	 
    	GROUP BY gr.year_id,gr.group_id
    	ORDER BY gr.year_id,gr.group_id,times DESC";
		return $db->fetchAll($sql);
	}
	
}

