<?php

class Home_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	public function getAllStudent($search){
		$curr = new Application_Model_DbTable_DbGlobal();
		$lang= $curr->currentlang();
		$_db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
				$field = 'name_en';
				$colunmname='title_en';
				if ($lang==1){
					$field = 'name_kh';
					$colunmname='title';
				}
				$sql = "SELECT  s.stu_id,
				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,s.stu_khname,s.stu_enname,s.last_name,s.group_id,
				s.is_subspend,
				(SELECT name_en FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    			(SELECT name_en FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    			
				(SELECT $field from rms_view where type=5 and key_code=s.is_subspend LIMIT 1) as status_student,
				CONCAT(s.stu_khname,'-',s.stu_enname) AS name,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=2 AND key_code = s.sex LIMIT 1) AS sex,
				tel ,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
				(SELECT group_code FROM `rms_group` WHERE rms_group.id=s.group_id LIMIT 1) AS group_name,
				
			  (SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = s.degree AND i.type=1 LIMIT 1) AS degree,
			  (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = s.grade AND idd.items_type=1 LIMIT 1) AS grade,
			 
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(select room_name from rms_room where room_id=s.room LIMIT 1) as room,
				s.sex as sexcode,
				status,
				photo
				FROM rms_student AS s  WHERE  s.status = 1 AND s.customer_type = 1";
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
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
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
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
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
		if(!empty($search['grade_all'])){
			$where.=" AND s.grade=".$search['grade_all'];
		}
		if(!empty($search['session'])){
			$where.=" AND s.session=".$search['session'];
		}
		if(!empty($search['time'])){
			$where.=" AND sp.time=".$search['time'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
		return $_db->fetchAll($sql.$where.$orderby);
	}
	
	public function getStudentById($stu_id){
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
 		$sql = "SELECT s.*,
 				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_name,
 				(SELECT school_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_namekh,
 				(SELECT school_nameen FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_nameen,
				(SELECT photo FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS photo_branch,
				(SELECT br_address FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS br_address,
				(SELECT branch_tel FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_tel,
				(SELECT email FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS email_branch,
				(SELECT website FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS website,
				(SELECT name_kh from rms_view where type=5 and key_code=s.is_subspend LIMIT 1) as status_student,
			
 				(SELECT name_kh FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    			(SELECT name_kh FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    			
    			(SELECT name_kh FROM rms_view where type=21 and key_code=s.father_nation LIMIT 1) AS father_nation,
    			(SELECT name_kh FROM rms_view where type=21 and key_code=s.mother_nation LIMIT 1) AS mother_nation,
    			(SELECT name_kh FROM rms_view where type=21 and key_code=s.guardian_nation LIMIT 1) AS guardian_nation,
    			
 				(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
				(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
		    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
		    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
				(SELECT province_en_name FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name,
				
				(SELECT CONCAT(g.group_code,' ',
				(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation))  AS NAME 
				 FROM rms_group AS g WHERE g.id=s.group_id )  AS group_name,
				 
				 (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')')AS years FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year GROUP BY from_academic,to_academic,generation,TIME ) AS year_name,
				 
				(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = s.degree AND i.type=1 LIMIT 1) AS degree_name,
			    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = s.grade AND idd.items_type=1 LIMIT 1) AS grade_name,
			  
				 (SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1 ) AS room_name,
				  (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
				 (SELECT k.title FROM `rms_know_by` AS k WHERE k.id = s.know_by LIMIT 1) AS know_by,
				 (SELECT l.title FROM `rms_degree_language` AS l WHERE l.id = s.lang_level LIMIT 1) AS lang_level
				  
				FROM rms_student as s WHERE 1 AND s.stu_id=$stu_id";
		$where='';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
		return $db->fetchRow($sql.$where);
	}
	
	public function getStudentPaymentDetail($stu_id){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
	
		$currentLang = $_db->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
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
		p.title,
		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = p.items_id  LIMIT 1) AS category,
		(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS `user`,
		(SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
		(SELECT name_en FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status
		FROM
		rms_student_payment AS sp,
		rms_student_paymentdetail AS spd,
		rms_student AS s,
		rms_itemsdetail AS p
		WHERE
			s.stu_id = sp.student_id
			AND sp.id=spd.payment_id
			ANd p.id = spd.itemdetail_id
			AND p.items_type=1
			AND s.customer_type=1
			AND s.stu_id=$stu_id ORDER BY sp.id DESC ";
		return $db->fetchAll($sql);
	}
	 
	public function getStudentServiceUsing($stu_id,$search,$order_no){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
	
		$currentLang = $_db->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql=" SELECT
		spd.id,
		spd.fee,
		spd.qty,
		spd.subtotal,		
		spd.extra_fee,
		spd.discount_percent,	
		spd.paidamount,
		spd.note,
		spd.start_date,
		spd.validate,
		spd.is_start,
		sp.receipt_number,
		sp.create_date,
		sp.is_void,
		s.stu_code,
		s.stu_khname,
		s.stu_enname,
		p.title AS service_name,
 		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = p.items_id  LIMIT 1) AS category,		
		(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = sp.grade LIMIT 1) AS items_name,			  
		(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
		(SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
		(SELECT name_en FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status
		FROM
		rms_student_payment AS sp,
		rms_student_paymentdetail AS spd,
		rms_student AS s,
		rms_itemsdetail AS p
		WHERE
		s.stu_id = sp.student_id
		AND sp.id=spd.payment_id
		AND p.id = spd.itemdetail_id
		AND p.items_type=2
		AND spd.is_suspend=0 
		
		AND s.customer_type=1
		AND s.stu_id=$stu_id";
		return $db->fetchAll($sql);
	}
	 
	function getRescheduleByGroupId($id){
		$db=$this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql=" SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
    	
    	(SELECT CONCAT(rms_itemsdetail.$colunmname,' ',(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=rms_itemsdetail.items_id AND rms_items.type=1 LIMIT 1)) FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
				
    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times ,
    	gd.stu_id
    	FROM rms_group_reschedule AS gr,rms_group_detail_student AS gd
    	WHERE gr.group_id=gd.group_id
    	 AND   gd.stu_id=$id
    	    	 
    	GROUP BY gr.year_id,gr.group_id
    	ORDER BY gr.year_id,gr.group_id,times DESC";
		return $db->fetchAll($sql);
	}
	
	function getStudentDocumentById($id){
		$db=$this->getAdapter();
		$sql=" SELECT * from rms_student_document where stu_id = $id ";
		return $db->fetchAll($sql);
	}
	
	function getStudentMistake($stu_id){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql="SELECT
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
					(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					sdd.`stu_id`, st.`stu_code`, st.`stu_enname`, st.`stu_khname`, st.`sex`
				FROM
					`rms_group` AS g, 
					`rms_student` AS st,
					rms_student_attendence AS sd,
					`rms_student_attendence_detail` AS sdd
				WHERE
					(sd.type=2 OR sdd.`attendence_status` IN (4,5))
					AND sd.`id` = sdd.`attendence_id`
					AND sd.group_id = g.id 
					AND sd.status=1
					AND st.`stu_id` = sdd.`stu_id` 
					AND st.is_subspend = 0
					and sdd.stu_id = $stu_id
			";
		 
		$order =" GROUP BY g.id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade` DESC,g.group_code ASC ,g.id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getStatusMistakeByStudent($stu_id,$group){
		$db = $this->getAdapter();
		$sql="SELECT
					sd.`group_id`,
					sd.`type`,
					sdd.`attendence_status` as mistake_type,
					sdd.description,
					sd.`date_attendence` as mistake_date,
					sd.for_session
				FROM
					`rms_student_attendence` AS sd,
					`rms_student_attendence_detail` AS sdd
				WHERE
					(sd.type=2 OR sdd.`attendence_status` IN (4,5))
					AND sd.`id` = sdd.`attendence_id`
					AND sdd.`stu_id` = $stu_id
					AND sd.`group_id` = $group 
			";
		return $db->fetchAll($sql);
	}
	
	
	function getStudentAttendence($stu_id){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql="SELECT
					g.id AS group_id,
					g.`group_code`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
					(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					sdd.`stu_id`,
					st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`
				FROM
					`rms_group` AS g,
					`rms_student` AS st,
					rms_student_attendence AS sta,
					`rms_student_attendence_detail` AS sdd
				WHERE
					sta.type=1
					AND sta.`id` = sdd.`attendence_id`
					AND sta.type=1
					AND sta.group_id = g.id
					AND st.`stu_id` = sdd.`stu_id`
					AND sta.status=1
					AND g.is_pass!=1
					AND st.`stu_id` = $stu_id
			";
		$order =" GROUP BY sta.group_id,sdd.stu_id
		ORDER BY `g`.`degree`,`g`.`grade` DESC,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
		 
		return $db->fetchAll($sql.$order);
	}
	
	function getStatusAttendence($stu_id,$group){
		$db = $this->getAdapter();
		$sql="SELECT
					sat.`group_id`,
					satd.`attendence_status`,
					sat.`date_attendence`,
					satd.description
				FROM 
					`rms_student_attendence` AS sat,
					`rms_student_attendence_detail` AS satd
				WHERE 
					sat.`id`= satd.`attendence_id`
					AND sat.type=1
					AND satd.`stu_id`=$stu_id
					AND sat.`group_id`=$group
			";
		return $db->fetchAll($sql);
	}
	function getSumStatusAttendence($stu_id,$group){
		$db = $this->getAdapter();
		$sql="SELECT
		sat.`group_id`,
		satd.`attendence_status`,
		COUNT(satd.`attendence_status`) AS total,
		sat.`date_attendence`,
		satd.description
		FROM
		`rms_student_attendence` AS sat,
		`rms_student_attendence_detail` AS satd
		WHERE
		sat.`id`= satd.`attendence_id`
		AND sat.type=1
		AND satd.`stu_id`=$stu_id
		AND sat.`group_id`=$group GROUP BY satd.`attendence_status`";
		return $db->fetchAll($sql);
	}
	
	function addReadNews($id){
		try{
			$db = $this->getAdapter();
			$arr =array(
					'new_feed_id'=>$id,
					'cus_id'=>$this->getUserId(),
					'is_read'=>1,
					'is_click'=>1,
					'date'=>date("Y-m-d H:i:s"),
					);
			$this->_name="ln_news__read";
			$this->insert($arr);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getLastExamByStudent($stu_id){
		$db = $this->getAdapter();
		$sql="SELECT 
			s.*,sd.student_id FROM 
			`rms_score_detail` AS sd,
			`rms_score` AS s
			WHERE s.id = score_id
			AND sd.student_id = $stu_id
			GROUP BY s.id
			ORDER BY s.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
}

