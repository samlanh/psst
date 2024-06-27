<?php

class Application_Model_DbTable_DbReport extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_teacher';

	

	public static function getUserExternalId()
	{
		$zendRequest = new Zend_Controller_Request_Http();
		$userId = $zendRequest->getCookie(TEACHER_AUTH . 'userId');
		$userId = empty($userId) ? 0 : $userId;
		return $userId;
	}
	function getAttendanceDetailWithClassTeahchingSchedule($data) {
		$db = $this->getAdapter();
		$sql="
			SELECT 
				att.`id` 
				,att.`group_id` AS groupId
				,att.`date_attendence` AS dateAttendence
				,`schDetail`.`from_hour` AS fromHour
				,`schDetail`.`to_hour` AS toHour
				,`schDetail`.`day_id` AS dayId
				,`schDetail`.`subject_id` AS subjectId
				,g.`group_code`
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
				,(SELECT v.name_kh FROM rms_view AS v WHERE v.key_code=schDetail.day_id AND v.type=18 LIMIT 1)AS daysTitle
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
		";
		$sql.="
			FROM 
				`rms_student_attendence` AS att
				JOIN 
					(rms_group_reschedule AS schDetail 
					JOIN  rms_group_schedule AS sch ON sch.id =schDetail.main_schedule_id
					JOIN rms_group AS g ON g.id =sch.group_id AND g.is_use = 1 AND g.is_pass = 2  )  
				ON schDetail.`day_id` = (WEEKDAY(att.`date_attendence`)+1) AND g.id = att.`group_id`
		";
		$sql.=" WHERE 1 ";

		$groupId = empty($data["groupId"]) ? 0 : $data["groupId"];
		$sql.=" AND g.id = ".$groupId;
		
		if(!empty($data["attendanceId"])){
			$sql.=" AND  att.id = ".$data["attendanceId"];
		}
		if(!empty($data["subjectId"])){
			$sql.=" AND  `schDetail`.`subject_id` = ".$data["subjectId"];
		}
		if(!empty($data["fromHour"])){
			$sql.=" AND  `schDetail`.`from_hour` = ".$data["fromHour"];
		}
		if(!empty($data["toHour"])){
			$sql.=" AND  `schDetail`.`to_hour` = ".$data["toHour"];
		}
		$sql.=" AND schDetail.`techer_id` = ".$this->getUserExternalId();
		$sql.=" ORDER BY att.`date_attendence` ASC, schDetail.`day_id` ASC,schDetail.`from_hour` ASC ";
		return $db->fetchAll($sql);
	}

	function getCheckStudentAttendaceStatus($data){
		$db = $this->getAdapter();

		$groupId = empty($data["groupId"]) ? 0 : $data["groupId"];
		$attendanceId = empty($data["attendanceId"]) ? 0 : $data["attendanceId"];
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$fromHour = empty($data["fromHour"]) ? 0 : $data["fromHour"];
		$toHour = empty($data["toHour"]) ? 0 : $data["toHour"];
		$subjectId = empty($data["subjectId"]) ? 0 : $data["subjectId"];
		$sql= "
				SELECT 

					att.id
					,att.`date_attendence`
					,attd.`stu_id`
					,attd.`fromHour`
					,attd.`toHour`
					,attd.`subjectId`
					,attd.`byTeacherId`
					,attd.`attendence_status` AS attendenceStatus
					,CASE 
						WHEN attd.`attendence_status` = 2 THEN 'A' 
						WHEN attd.`attendence_status` = 3 THEN 'P' 
						WHEN attd.`attendence_status` = 4 THEN 'L'
						WHEN attd.`attendence_status` = 5 THEN 'EL'
						ELSE '&#10004;'
					END AS attendenceStatusTitle
					,attd.`description` AS reason


				FROM `rms_student_attendence` AS att
					JOIN `rms_student_attendence_detail` AS attd ON att.`id` = attd.`attendence_id` 
				WHERE  att.`id` = $attendanceId  
					AND att.`status` = 1
					AND att.`group_id` = $groupId 
					AND attd.`stu_id` = $studentId 
					AND attd.`fromHour` = $fromHour
					AND attd.`toHour` = $toHour
					AND attd.`subjectId`=$subjectId
		";
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getStudentListByTeachingSubject($data){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$studentName = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName = $studentName;
		$branchName = "branch_nameen";
		$subjectTitle = 'subject_titlekh';
		
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		if ($currentLang == 1) {
			//$studentName = 's.stu_khname';
			$branchName = 'branch_namekh';
			$subjectTitle = "subject_titleen";
			
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			
		}
		
		$sql=" 
			SELECT 
				g.`group_code` as groupCode
				,g.`id` as groupId
				,(SELECT b." . $branchName . " FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
				,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
				,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle	
			
			
				,schDetail.subject_id AS subjectId
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEn
				,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectShortcut
				,s.stu_code AS stuCode
				,s.stu_khname AS stuKhName
				,$studentName AS stuEnName
				,s.sex AS gender 
				,s.stu_id AS studentId
			";
		$sql.="
			FROM 
				(`rms_group_detail_student` AS sgh JOIN rms_student AS s ON   s.stu_id = sgh.stu_id )
				JOIN (rms_group_reschedule AS schDetail 
					JOIN  rms_group_schedule AS sch ON sch.id =schDetail.main_schedule_id
					JOIN rms_group AS g ON g.id =sch.group_id AND g.is_use = 1 AND g.is_pass = 2  
				 ) ON sgh.`group_id` = g.`id`
				
			WHERE 1
		";
		$sql.=" AND g.`id` = ".$data['groupId'];
		$sql.=" AND schDetail.`techer_id` = ".$this->getUserExternalId();
		$sql.=" ";
		$sql.=" GROUP BY schDetail.subject_id,sgh.stu_id ";
		$ordering=" ORDER BY schDetail.subject_id ASC ";
		
		$order = " ,s.stu_khname ASC ";
		if (!empty($data['sortStundent'])) {
			if ($data['sortStundent'] == 1) {
				$order = " ,s.stu_code ASC ";
			} else if ($data['sortStundent'] == 2) {
				$order = " ,s.stu_khname ASC ";
			} else if ($data['sortStundent'] == 3) {
				$order = " ," . $studentEnName . " ASC ";
			}
		}
		$ordering = $ordering.$order;
		return $db->fetchAll($sql.$ordering);
	}

	function getCountingAttendanceType($data){
		$db = $this->getAdapter();
		$sql = "
			SELECT
				sat.`group_id`,
				satd.`attendence_status`
				,sat.`date_attendence`
				,satd.description
				,satd.`stu_id`
				,COUNT(IF(satd.attendence_status = '2' , satd.attendence_status, NULL)) AS totalAbsent
				,COUNT(IF(satd.attendence_status = '3' , satd.attendence_status, NULL)) AS totalP
				,COUNT(IF(satd.attendence_status = '4' , satd.attendence_status, NULL)) AS totalLate
				,COUNT(IF(satd.attendence_status = '5' , satd.attendence_status, NULL)) AS totalEl
		";
		$sql.= "
				FROM
					`rms_student_attendence` AS sat,
					`rms_student_attendence_detail` AS satd
		";
		$sql.= "
			WHERE
				sat.`id`= satd.`attendence_id`
				AND sat.type=1 
		";

		$date = new DateTime($data['attendenceDate']);
		$attendenceDate =  $date->format("Y-m-d");
		$sql.= " AND sat.`group_id` = ".$data["groupId"];
		$sql.= " AND satd.`subjectId` = ".$data["subjectId"];
		$sql.= " AND satd.`stu_id` = ".$data["studentId"];
		$sql.= " AND DATE_FORMAT(sat.`date_attendence`, '%Y/%m') = DATE_FORMAT('$attendenceDate', '%Y/%m') ";
		$sql.= " GROUP BY satd.`stu_id` , DATE_FORMAT(sat.`date_attendence`, '%Y/%m')  ";
		return $db->fetchRow($sql);
	}
}
