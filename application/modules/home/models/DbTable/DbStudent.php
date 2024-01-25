<?php

class Home_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentFronDesk($search){
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
				$sql ="SELECT  s.stu_id,
							s.stu_code,s.stu_khname,s.stu_enname,s.last_name,
							CASE
								WHEN primary_phone = 1 THEN s.tel
								WHEN primary_phone = 2 THEN s.father_phone
								WHEN primary_phone = 3 THEN s.mother_phone
								ELSE s.guardian_tel
							END as tel,
							ds.stop_type AS is_subspend,
							s.sex as sexcode,
							s.status,
							photo,
							(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
							(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS group_name,
						    (SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
						    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
						    ds.group_id,
						    (SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year
						FROM rms_student AS s,
							rms_group_detail_student AS ds
						  WHERE  
						   ds.itemType=1 
						   AND ds.is_maingrade=1 
						   AND ds.is_current=1 
						   AND s.stu_id=ds.stu_id 
		                   AND s.status = 1 
						AND s.customer_type = 1 ";
			$orderby = " ORDER BY s.stu_khname ASC ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,stu_enname) LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','')  			LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_tel,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(home_num,' ','')  		LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(street_num,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(village_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(commune_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(district_name,' ','')  LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND ds.academic_year=".$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=" AND ds.group_id=".$search['group'];
		}
		if(!empty($search['degree'])){
			$where.=" AND ds.degree=".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.=" AND ds.grade=".$search['grade_all'];
		}
		if(!empty($search['session'])){
			$where.=" AND ds.session=".$search['session'];
		}
		if($search['study_status']>=0){
			$where.=' AND (SELECT rms_group.is_pass FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) ='.$search['study_status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('s.branch_id');
		return $_db->fetchAll($sql.$where.$orderby);
	}
	
	public function getStudentById($stu_id){
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		$vill = 'village_name';
		$comm = 'commune_name';
		$dist = 'district_name';
		$prov = 'province_en_name';
		$view = 'name_en';
		$occuTitle='occu_enname';
		if ($currentLang==1){
			$colunmname='title';
			$vill = 'village_namekh';
			$comm = 'commune_namekh';
			$dist = 'district_namekh';
			$prov = 'province_kh_name';
			$view = 'name_kh';
			$occuTitle = 'occu_name';
		}
		
 		$sql = "SELECT s.*,
 				 DATE_FORMAT(`s`.`dob`,'%d/%M/%Y') AS `dob`,
 				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_name,
 				(SELECT school_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_namekh,
 				(SELECT school_nameen FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_nameen,
				(SELECT photo FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS photo_branch,
				(SELECT br_address FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS br_address,
				(SELECT branch_tel FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_tel,
				(SELECT email FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS email_branch,
				(SELECT website FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS website,
				(SELECT $view from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
			
 				(SELECT $view FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    			(SELECT $view FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=s.father_nation LIMIT 1) AS father_nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=s.mother_nation LIMIT 1) AS mother_nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=s.guardian_nation LIMIT 1) AS guardian_nation,
    			
				(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
		    	(SELECT $comm FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
		    	(SELECT $dist FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
				(SELECT $prov FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name,
				ds.group_id,
				(SELECT g.group_code FROM rms_group AS g WHERE g.id=ds.group_id LIMIT 1) AS group_name,
				(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS year_name,
				(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 LIMIT 1) AS degree_name,
			    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 LIMIT 1) AS grade_name,
			  
				
				 (SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				 (SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				 (SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
				 (SELECT k.title FROM `rms_know_by` AS k WHERE k.id = s.know_by LIMIT 1) AS know_by,
				 (SELECT l.title FROM `rms_degree_language` AS l WHERE l.id = s.lang_level LIMIT 1) AS lang_level
				  
				FROM 
					rms_student as s,
					rms_group_detail_student AS ds
				WHERE 
					ds.itemType=1 
					AND s.stu_id = ds.stu_id 
					AND ds.is_maingrade=1 
					AND s.stu_id=$stu_id  ";
		$where='';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		$where.=" LIMIT 1";
		return $db->fetchRow($sql.$where);
	}
	public function getStudentByIdToken($stToken){//will combine with above
		$db = $this->getAdapter();
	
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		$vill = 'village_name';
		$comm = 'commune_name';
		$dist = 'district_name';
		$prov = 'province_en_name';
		$view = 'name_en';
		$occuTitle='occu_enname';
		if ($currentLang==1){
			$colunmname='title';
			$vill = 'village_namekh';
			$comm = 'commune_namekh';
			$dist = 'district_namekh';
			$prov = 'province_kh_name';
			$view = 'name_kh';
			$occuTitle = 'occu_name';
		}
	
		$sql = "SELECT s.*,
		DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`,
		(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_name,
		(SELECT school_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_namekh,
		(SELECT school_nameen FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_nameen,
		(SELECT photo FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS photo_branch,
		(SELECT br_address FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS br_address,
		(SELECT branch_tel FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_tel,
		(SELECT email FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS email_branch,
		(SELECT website FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS website,
		(SELECT $view from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
			
		(SELECT $view FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
		(SELECT $view FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
		(SELECT $view FROM rms_view where type=21 and key_code=s.father_nation LIMIT 1) AS father_nation,
		(SELECT $view FROM rms_view where type=21 and key_code=s.mother_nation LIMIT 1) AS mother_nation,
		(SELECT $view FROM rms_view where type=21 and key_code=s.guardian_nation LIMIT 1) AS guardian_nation,
		 
		(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
		(SELECT $comm FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
		(SELECT $dist FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
		(SELECT $prov FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name,
		ds.group_id,
		(SELECT g.group_code FROM rms_group AS g WHERE g.id=ds.group_id LIMIT 1) AS group_name,
		(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS year_name,
		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 LIMIT 1) AS degree_name,
		(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 LIMIT 1) AS grade_name,
			
	
		(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
		(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
		(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
		(SELECT k.title FROM `rms_know_by` AS k WHERE k.id = s.know_by LIMIT 1) AS know_by,
		(SELECT l.title FROM `rms_degree_language` AS l WHERE l.id = s.lang_level LIMIT 1) AS lang_level
	
		FROM
			rms_student as s,
			rms_group_detail_student AS ds
		WHERE
			ds.itemType=1
			AND s.stu_id = ds.stu_id
			AND ds.is_maingrade=1
			AND s.studentToken='".addslashes($stToken)."'";
		$where='';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		$where.=" LIMIT 1";
		return $db->fetchRow($sql.$where);
	}
	function getAllStudentStudyRecord($stu_id){
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		
		$label = "name_en";
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
			$label = "name_kh";
		}
		
 		$sql =" SELECT 
	 				ds.group_id,
					(SELECT g.group_code FROM rms_group AS g WHERE g.id=ds.group_id LIMIT 1) AS group_name,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year,
					(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 LIMIT 1) AS degree_name,
				    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 LIMIT 1) AS grade_name,
					(SELECT room_name FROM rms_room WHERE room_id=(SELECT room_id FROM `rms_group` WHERE rms_group.id=ds.group_id LIMIT 1) LIMIT 1 ) AS room_name,
					 (SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=(SELECT g.session FROM rms_group AS g WHERE g.id = ds.group_id LIMIT 1) LIMIT 1)AS session
				FROM 
					rms_group_detail_student AS ds
 			WHERE 
				ds.itemType=1 
				AND ds.stu_id = $stu_id AND ds.is_current=1 ";
		$where='';
		$dbp = new Application_Model_DbTable_DbGlobal();
		return $db->fetchAll($sql.$where);
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
					spd.payment_id, 
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
					sp.balance_due as balance,
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
					
					AND s.customer_type=1
					AND s.stu_id=$stu_id 
				ORDER BY 
					sp.id DESC ,
					p.items_id ASC
			";
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
					(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = spd.itemdetail_id LIMIT 1) AS items_name,			  
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
					AND s.stu_id=$stu_id
				group by spd.itemdetail_id
			";
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
		
		$sql=" SELECT 
					gr.id,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
					(select group_code from rms_group as g where g.id = gr.group_id limit 1) AS group_code,
					gr.branch_id,
					gr.year_id,
					gr.group_id,
					gr.day_id,
					gr.from_hour,
					gr.to_hour,
					gr.subject_id,
					gr.techer_id,
			    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
			    	(SELECT CONCAT(rms_itemsdetail.$colunmname,' ',(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=rms_itemsdetail.items_id AND rms_items.type=1 LIMIT 1)) FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
			    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times ,
			    	gd.stu_id
    			FROM 
    				rms_group_reschedule AS gr,
    				rms_group_detail_student AS gd
    			WHERE 
					gd.itemType=1 
    				AND gr.group_id=gd.group_id
    				and gd.is_pass = 0
    	 			AND gd.stu_id=$id
		    	GROUP BY 
		    		gr.year_id,
		    		gr.group_id
		    	ORDER BY 
					gr.year_id,
					gr.group_id,
					times DESC
			";
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
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					sdd.`stu_id`, st.`stu_code`, st.`stu_enname`, st.`stu_khname`, st.`sex`
				FROM
					rms_student_attendence AS sd 
					 JOIN `rms_student_attendence_detail` AS sdd ON sd.`id` = sdd.`attendence_id` 
						LEFT JOIN `rms_group` AS g ON sd.group_id = g.id 
						LEFT JOIN `rms_student` AS st ON st.`stu_id` = sdd.`stu_id`
						
				WHERE
					(sd.type=2 OR sdd.`attendence_status` IN (4,5))
					AND sd.`id` = sdd.`attendence_id`
					AND sd.group_id = g.id 
					AND sd.status=1
					AND st.`stu_id` = sdd.`stu_id` 
					and sdd.stu_id = $stu_id
			";
		 
		$order =" GROUP BY sd.group_id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade` DESC,g.group_code ASC ,g.id DESC";
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
		$sql.=" GROUP BY sd.id ORDER BY sd.`date_attendence` ASC,sdd.attendence_status DESC ";
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
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
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
					GROUP BY sat.`date_attendence` 
					ORDER BY satd.`attendence_status` DESC
			";
		return $db->fetchAll($sql);
	}
	function getSumStatusAttendence($stu_id,$group){
		$db = $this->getAdapter();
		
		$sql="SELECT
			sat.`group_id`,
			satd.`attendence_status`
			
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=1
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=1) AS totalComeSemester1
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=1
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=2) AS totalASemester1
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=1
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=3) AS totalPSemester1
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=1
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=4) AS totalLSemester1
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=1
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=5) AS totalELSemester1
				
		
			
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=2
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=1) AS totalComeSemester2
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=2
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=2) AS totalASemester2
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=2
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=3) AS totalPSemester2
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=2
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=4) AS totalLSemester2
			,(SELECT 
				COUNT(*) AS attendence_status
			FROM 
				`rms_student_attendence` AS sat2 
			WHERE					
				sat2.type=1
				AND sat2.for_semester=2
				AND sat2.`status`=1
				AND sat2.group_id =$group
				AND (SELECT satd2.attendence_status FROM `rms_student_attendence_detail` AS satd2 WHERE sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$stu_id ORDER BY satd2.attendence_status DESC LIMIT 1)=5) AS totalELSemester2
			
			
			,sat.`date_attendence`
			,satd.description
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE
			sat.`id`= satd.`attendence_id`
			AND sat.type=1
			AND satd.`stu_id`=$stu_id
			AND sat.`group_id`=$group GROUP BY satd.`stu_id` ";
			
		return $db->fetchRow($sql);
		
		/*
			COUNT(if(satd.attendence_status = '1' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalComeSemester1,
			COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalASemester1,
			COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalPSemester1,
			COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalLSemester1,
			COUNT(IF(satd.attendence_status = '5' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalELSemester1,
			
			COUNT(IF(satd.attendence_status = '1' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalComeSemester2,
			COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalASemester2,
			COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalPSemester2,
			COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalLSemester2,
			COUNT(IF(satd.attendence_status = '5' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalELSemester2,
		*/
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
	function getLastStudentEnvaluation($stu_id){
		$db = $this->getAdapter();
		$sql="SELECT e.* FROM `rms_student_evaluation` AS e
		WHERE e.student_id = $stu_id ORDER BY e.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getStudyHistoryByStudent($stu_id){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		if ($currentLang==1){// khmer
			$title='title';
			$view="name_kh";
			$branch="school_namekh";
			$student="stu_khname as name";
			$teacher="teacher_name_kh";
		}else{
			$title='title_en';
			$view="name_en";
			$branch="school_nameen";
			$student="CONCAT(last_name,'',stu_enname) as name";
			$teacher="teacher_name_en";
		}
		$sql="SELECT
					g.group_code,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_id,
					(SELECT rms_items.$title FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$title FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT $view FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					(select $teacher from rms_teacher as t where t.id = g.teacher_id) as teacher,
					(SELECT $view FROM `rms_view` WHERE TYPE=12 AND key_code = gds.is_pass LIMIT 1) as is_pass_label,
					gds.is_pass,
					gds.stop_type
				FROM
					rms_group_detail_student AS gds,
					rms_group AS g,
					rms_student as s
				WHERE 
					gds.itemType=1 
					AND gds.group_id = g.id
					AND gds.stu_id = s.stu_id
					and gds.stu_id = $stu_id
					and gds.status=1
				order by 
					gds.is_pass ASC,
					gds.create_date ASC
						
			";
		return $db->fetchAll($sql);
	}
	
	
	function getStudentAllTestInfo($stu_id){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
			$lang = $_db->currentlang();
			if($lang==1){// khmer 
				$label = "name_kh";
				$grade = "idd.title";
				$degree = "i.title";
			}else{ // English
				$label = "name_en";
				$grade = "idd.title_en";
				$degree = "i.title_en";
			}
			
			$db=$this->getAdapter();
			$testCondiction = TEST_CONDICTION;
			
			$sql=" SELECT st.*,
					(SELECT $label FROM rms_view WHERE TYPE=2 AND key_code=st.sex LIMIT 1) AS sex,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=str.academic_year LIMIT 1) AS academic,
					";
			
			if ($testCondiction==2){
				$sql.="(SELECT tm.note FROM `rms_test_term` AS tm WHERE tm.id=str.study_term) AS study_term,";
			}else{
				$sql.="(SELECT CONCAT(title,' ( ',DATE_FORMAT(start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(end_date, '%d/%m/%Y'),' )') FROM `rms_startdate_enddate` WHERE rms_startdate_enddate.id=str.study_term) AS study_term,";
			}
			$sql.="		
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.user_id LIMIT 1) AS user_id,
					(SELECT $label FROM rms_view WHERE TYPE=15 AND key_code = str.updated_result LIMIT 1) AS result_status,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by,
					str.create_date AS create_date_exam,
					str.result_date,
					str.test_date AS test_date_exam,
					str.updated_result AS updated_result_de,
					str.note AS note_result,
					str.is_registered
				FROM 
					`rms_student` AS st,
					`rms_student_test_result` AS str
				WHERE 
					st.is_studenttest = 1
					AND str.stu_test_id = st.stu_id
					AND status=1
					AND st.stu_khname!=''
					AND st.`stu_id` = $stu_id";
	
			$where = " ";
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where.=$dbp->getAccessPermission("st.branch_id");
			$sql.= $dbp->getSchoolOptionAccess('str.test_type');
			
			$order=" ORDER By str.updated_result DESC,str.degree_result ASC,str.grade_result ASC ";
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getCountAttendanceByStudent($_data){
		$db = $this->getAdapter();
		$attendenceStatus = empty($_data["attendenceStatus"]) ? 0 : $_data["attendenceStatus"];
		$studentId = empty($_data["studentId"]) ? 0 : $_data["studentId"];
		$groupId = empty($_data["groupId"]) ? 0 : $_data["groupId"];
		$forSemester = empty($_data["forSemester"]) ? 1 : $_data["forSemester"];
		$sql="
			SELECT 
				satd.attendence_id,
				satd.attendence_status
			FROM 
				`rms_student_attendence` AS sat ,
				`rms_student_attendence_detail` AS satd
					
			WHERE 
				sat.`id`= satd.`attendence_id`
				AND sat.status = 1
				AND satd.attendence_status = $attendenceStatus
				AND sat.for_semester = $forSemester
				AND satd.stu_id = $studentId
				AND sat.group_id = $groupId
				
			";
		$sql.="
				GROUP BY satd.attendence_id,satd.attendence_status
				ORDER BY satd.attendence_status DESC
		";
		$rs = $db->fetchAll($sql);
		$restult = "0";
		if(!empty($rs)){
			return $restult = "".COUNT($rs)."";
		}
		return $restult;
	}
}

