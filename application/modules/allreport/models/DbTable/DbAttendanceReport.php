<?php
class Allreport_Model_DbTable_DbAttendanceReport extends Zend_Db_Table_Abstract
{
	public  function getCountingAllClass($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(g.id)
			FROM 
				rms_group AS g 
			WHERE 1
				AND g.is_use = 1
				AND g.status = 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		return $_db->fetchOne($sql);
	}
	
	public  function getCountingAllClassHasIssuedAtt($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
			  COUNT(DISTINCT(att.group_id)) AS groupId
			FROM  rms_student_attendence AS att
				JOIN rms_group AS g  ON g.id = att.group_id
			WHERE 1
		";
		$sql.='';
		if(!empty($search["currentDate"])){
			$date = new DateTime($search['currentDate']);
			$currentDate =  $date->format("Y-m-d");
			$sql.=" AND att.date_attendence = DATE_FORMAT('$currentDate', '%Y/%m/%d') ";
		}
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		return $_db->fetchOne($sql);
	}
	
	public  function getCountingAttedanceSummary($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(IF(v.`maxAttendenceStatus` = 2,v.maxAttendenceStatus,NULL)) AS totalAbsent
				,COUNT(IF(v.`maxAttendenceStatus` = 3,v.maxAttendenceStatus,NULL)) AS totalPermission
				,COUNT(IF(v.`maxAttendenceStatus` = 4,v.maxAttendenceStatus,NULL)) AS totalLate
				,COUNT(IF(v.`maxAttendenceStatus` = 5,v.maxAttendenceStatus,NULL)) AS totalEarlyLate
			FROM  `v_studentattendancestatusperdate` AS v
			WHERE 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND v.branchId = '.$search["branch_id"];
		}
		if(!empty($search["currentDate"])){
			$date = new DateTime($search['currentDate']);
			$currentDate =  $date->format("Y-m-d");
			$sql.=" AND v.`dateAttendence` = DATE_FORMAT('$currentDate', '%Y/%m/%d') ";
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND v.academicYear = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND v.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND v.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('v.branchId');
		$sql .= $dbGb->getDegreePermission('v.degree');
		$sql .= $dbGb->getSchoolOptionAccess('v.schoolOption');
		return $_db->fetchRow($sql);
	}
	
	public  function getCountingAllStudentByDegree($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT
				COUNT(DISTINCT(gd.`stu_id`)) AS totalStudent
				,g.`group_code` AS groupCode
				,it.`title` 
				,it.`shortcut`
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 2 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalAbsent
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 3 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalPermission
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 4 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalLate
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 5 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalEarlyLate
				
			FROM 
				(rms_group AS g JOIN `rms_group_detail_student` AS gd ON g.id = gd.`group_id` AND gd.`itemType` = 1 )
				LEFT JOIN `rms_items` AS it ON it.`id` = gd.`degree` AND it.`type` = 1
			WHERE 1
				AND g.is_use = 1
				AND g.status = 1
				AND it.`status` = 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$sql.=" GROUP BY g.id ";
		$sql.=" ORDER BY g.`school_option` ASC,it.`ordering` ASC,it.`id` ASC ";
		return $_db->fetchAll($sql);
	}
	
	
	public  function getStudentAbsenteeReport($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT
				att.id AS attendanceId
				,g.`group_code` AS groupCode
				,att.`date_attendence` AS attendanceDate
				,attd.`stu_id` AS studentId 
				,attd.`fromHour`
				,attd.`toHour`
				,attd.`subjectId`
				,(SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branchNameKh
				,(SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branchNameEng
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectTitleEn
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectShortcut
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =attd.fromHour LIMIT 1) AS fromHourTitle
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =attd.toHour LIMIT 1) AS toHourTitle
				
				,s.`stu_code` AS studentCode
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
				,s.`stu_khname` AS stuNameKh
				,s.`sex`
				,(SELECT v.`name_kh` FROM `rms_view` AS v WHERE v.type=2 AND v.key_code = s.sex LIMIT 1) AS genderTitle
				
				,attd.`byTeacherId`
				,attd.`attendence_status` AS attendenceStatus
				,attd.`description` AS reason
				,(SELECT t.`teacher_name_en` FROM `rms_teacher` AS t WHERE t.id = attd.`byTeacherId` LIMIT 1) AS teacherNameEn
				,(SELECT t.`teacher_name_kh` FROM `rms_teacher` AS t WHERE t.id = attd.`byTeacherId` LIMIT 1) AS teacherNameKh
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectShortcut
				,attd.`createDate`
				,attd.`modifyDate`
				,CASE 
						WHEN attd.`attendence_status` = 2 THEN '".$tr->translate("ABSENT")."' 
						WHEN attd.`attendence_status` = 3 THEN '".$tr->translate("PERMISSION")."' 
						WHEN attd.`attendence_status` = 4 THEN '".$tr->translate("LATE")."'
						WHEN attd.`attendence_status` = 5 THEN '".$tr->translate("EARLY_LEAVE")."'
						ELSE '&#10004;'
					END AS attendenceStatusTitle
				,(SELECT CONCAT(u.first_name) FROM rms_users AS u WHERE u.id = att.`user_id`) AS byUserName
				
			FROM 
				(`rms_student_attendence` AS att JOIN `rms_student_attendence_detail` AS attd ON att.id = attd.`attendence_id`)
				JOIN `rms_student` AS s ON s.`stu_id` = attd.`stu_id` 
				LEFT JOIN rms_group AS g ON g.id = att.`group_id` 
			WHERE 1
				AND att.`status`=1
				AND att.type=1 
		";
		$sql.='';
		$from_date =(empty($search['start_date']))? '1': " att.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " att.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " REPLACE(s.`stu_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`last_name,' ','')` LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_enname`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(g.`group_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(attd.`description`,' ','') LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$sql.=" ORDER BY att.`date_attendence` DESC,att.`id` DESC,attd.`id` DESC ";
		return $_db->fetchAll($sql);
	}
	
	
	
	//function for Old Report Action
	function getStudentAttendence($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
    	}
		
    	$sql=' SELECT 
				g.id AS group_id,
				g.`group_code`,
				g.`branch_id`,
				(SELECT CONCAT(ac.fromYear,"-",ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, `g`.`semester` AS `semester`,
				(SELECT`rms_view`.name_kh FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
				gsd.`stu_id`, 
				st.`stu_code`,
				st.`stu_enname`,
				st.`stu_khname`,
				st.`last_name`,
				st.`sex` ,
				(SELECT 
				CONCAT("[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"dateAttendance\":\"",
							v_att.dateAttendance,
							"\""
							",",
							"\"attendanceStatus\":",
							v_att.attendanceStatus,
							"}"
						)
					),
				"]"
				) FROM `v_daily_stu_attendance` AS v_att 
					WHERE v_att.`groupId`= g.`id`
						AND v_att.type=1 
						AND v_att.`studentId`=gsd.`stu_id` 
					)  AS attendanceStatusList
				
				FROM `rms_student` AS st 
					INNER JOIN `rms_group_detail_student` AS gsd 
					ON (st.`stu_id` = gsd.`stu_id` AND gsd.itemType=1 )
					LEFT JOIN `rms_group` AS g ON (g.`id` = gsd.`group_id` AND g.is_pass!=1) 
					INNER JOIN rms_student_attendence AS sta ON (sta.group_id = g.id) 
				WHERE 
					sta.type=1
					AND sta.status=1 
					AND gsd.status=1
					AND gsd.stop_type=0
				 	AND st.customer_type=1';
		
    	$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`g`.`branch_id`');
    	
    	$order =" GROUP BY sta.group_id,gsd.stu_id 
    		ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	
	function getStudentMistake($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) AS grade,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, 
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					 sdd.`stu_id`, st.`stu_code`, 
					 st.`stu_enname`,
					 st.last_name,
					 st.`stu_khname`,
					 st.`sex` 
				FROM 
					 rms_student_attendence AS sd 
					 JOIN `rms_student_attendence_detail` AS sdd ON sd.`id` = sdd.`attendence_id` 
						LEFT JOIN `rms_group` AS g ON sd.group_id = g.id 
						LEFT JOIN `rms_student` AS st ON st.`stu_id` = sdd.`stu_id`
				WHERE 
					 (sd.type=2 OR sdd.`attendence_status` IN (4,5)) 
					 AND sd.group_id = g.id 
					 AND sd.status=1 
    		";
    	
    	$from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
    	$order =" GROUP BY g.id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC ";
		
    	return $db->fetchAll($sql.$where.$order);
    }
	
}