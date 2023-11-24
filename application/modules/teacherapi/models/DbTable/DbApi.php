<?php

class Teacherapi_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{
	function getTeacherLogin($_data){
		$db = $this->getAdapter();
		$_data['userName']=trim($_data['userName']);
		$_data['password']=trim($_data['password']);
		try{
			$sql =" SELECT
				t.*
			FROM
				rms_teacher AS t
			WHERE t.status = 1 AND t.staff_type =1 ";
			$sql.= " AND ".$db->quoteInto('t.user_name=?', $_data['userName']);
			$sql.= " AND ".$db->quoteInto('t.password=?', md5($_data['password']));
			$row = $db->fetchRow($sql);
			
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function getTeacherInformation($_data){
		$_db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$colunmname='title_en';
			$lbView="name_en";
			$branch = "branch_nameen";
			$schoolName = "school_nameen";
	
			$province = "province_en_name";
			$commune = "commune_name";
			$district = "district_name";
			$vill = 'village_name';
			$occuTitle='occu_enname';
			$teacherName='teacher_name_en';
			if ($currentLang==1){
				$colunmname='title';
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
		   
				$province = "province_kh_name";
				$commune = "commune_namekh";
				$district = "district_namekh";
				$vill = 'village_namekh';
				$occuTitle = 'occu_name';
				$teacherName='teacher_name_kh';
			}
			$sql ="SELECT
						t.*
						,t.id AS teacherId
						,t.teacher_code AS teacherCode
						,t.$teacherName AS teacherName
						
						,t.branch_id AS branchId
						,(SELECT $lbView from rms_view where type=2 and key_code=t.sex LIMIT 1) as genderTitle
						,(SELECT $lbView FROM rms_view where type=21 and key_code=t.nationality LIMIT 1) AS nationality
						 
						,(SELECT $province FROM rms_province AS p WHERE p.province_id=t.province_id LIMIT 1) AS provinceTitle
						,(SELECT $district FROM ln_district AS p WHERE p.dis_id=t.district_name LIMIT 1) AS districtTitle
						,(SELECT $commune FROM ln_commune AS p WHERE p.com_id=t.commune_name LIMIT 1) AS communeTitle
						,(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = t.village_name LIMIT 1) AS village_name
						
			FROM
				rms_teacher AS t 
			WHERE
				t.staff_type=1 
				AND t.id=$userId ";
			$sql.=" LIMIT 1";
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
    
    function generateToken($row){
    	$db = $this->getAdapter();
    	try{
    		$this->_name = "mobile_mobile_token";
    		
    		$token = $row['mobileToken'];
    		$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' AND stu_id=0 LIMIT 1";
    		$rsid = $db->fetchOne($sql);
    		if(!empty($rsid)){
    			$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
    					'device_model' 	=> "",
    			);
    			$where ='id= '.$rsid;
    			$this->update($_arr, $where);
    		}else{
				
	    		$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$row['id']." AND token='".$token."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
					$_arr =array(
	    				'stu_id' 	=> $row['id'],
	    				'token' 	=> $token,
	    				'device_type' => $row['deviceType'],
						'tokenType' => $row['tokenType'],
	    				'device_model' => "",
	    			);
					
					$tokenType = $row['tokenType'];
					$currentUserCheck = 0;
					if($row['currentUserId']>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$row['currentUserId']." AND token='".$token."' AND tokenType='".$tokenType."' LIMIT 1";
						$currentUserCheck = $db->fetchOne($sql);
					}
					if($currentUserCheck >0){
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentUserCheck ";
						$this->update($_arr,$where);
					}else{
						$_arr['date'] = date("Y-m-d H:i:s");
						$this->_name = "mobile_mobile_token";
						$this->insert($_arr);
					}
					
	    		}
    		}
    		
    		return $token;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return null;
    	}
    }
	
	
	function getTeachingSchedule($_data){
		$_db = $this->getAdapter();
		try{
			
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$forDay = empty($_data['forDay'])?0:$_data['forDay'];
			$day = 0;
			
			$date = new DateTime();
			$dayofweek = $date->format('w');
			$currentDay=$dayofweek;
			$nextDay=$dayofweek+1;
			if($dayofweek==0){
				$currentDay=7;
			}
			if($forDay==1){ // today
				$day = $currentDay;
			}else if($forDay==2){ // tomorrow
				$day = $nextDay;
			}
			
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branchName = "branch_nameen";
			$subjectTitle = "subject_titleen";
			if ($currentLang==1){
				$subjectTitle = "subject_titlekh";
				$branchName = "branch_namekh";
				$label = "name_kh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
			}
			$sql ="
			SELECT 
				(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
				,(SELECT sj.".$subjectTitle." FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
				,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
				,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
				,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS daysKh
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
				
				,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
				,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
				
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,`g`.`group_code` AS groupCode
				,`g`.`degree` AS degree_id
				,`g`.`grade` AS gradeId
				,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
				,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
				,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle	
				,schDetail.*
			FROM 
				rms_group_reschedule AS schDetail
				,rms_group_schedule AS sch
				,rms_group AS g
			WHERE 
				sch.id =schDetail.main_schedule_id
				AND g.id =sch.group_id
				AND g.is_use =1
				AND g.is_pass =2 
			";
			$sql.=" AND schDetail.techer_id=".$userId;
			if(!empty($day)){
				$sql.=" AND schDetail.day_id=".$day;
			}
			$sql.=" ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
	
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function getIssueScoreListByClass($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$colunmname='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$month = "month_en";
			$subjectTitle='subject_titleen';
			if ($currentLang==1){
				$colunmname='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$month = "month_kh";
				$subjectTitle='subject_titlekh';
			}
			
			$sql="
				SELECT 
					grd.*
					,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
					,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
					,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
					,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
					,CASE
						WHEN grd.examType = 2 THEN grd.forSemester
						ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
					END AS forMonthTitle
					,g.group_code AS  groupCode
					,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
					,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
					,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
					,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
					,(SELECT sEtStt.title FROM rms_score_entry_setting AS sEtStt WHERE sEtStt.id = grd.settingEntryId LIMIT 1) titleScoreFor
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.group_id =grd.groupId  ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.stu_id=gds.stu_id AND gds.group_id =grd.groupId  ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.stu_id=gds.stu_id AND gds.group_id =grd.groupId  ) AS femaleStudent
					
					,(SELECT COUNT( IF( grdT.totalAverage >= (grdT.maxScore/2), grdT.id, NULL))  FROM `rms_grading_total` AS grdT WHERE grdT.gradingId = grd.id AND grdT.maxScore >0 LIMIT 1 ) AS aboveAverage
					,(SELECT COUNT( IF( grdT.totalAverage >= (grdT.maxScore/2) AND s.sex = '1', grdT.id, NULL))  FROM `rms_grading_total` AS grdT,rms_student AS s WHERE s.stu_id=grdT.studentId AND grdT.gradingId = grd.id AND grdT.maxScore >0 LIMIT 1 ) AS aboveAverageMale
					,(SELECT COUNT( IF( grdT.totalAverage < (grdT.maxScore/2), grdT.id, NULL))  FROM `rms_grading_total` AS grdT WHERE grdT.gradingId = grd.id AND grdT.maxScore >0 LIMIT 1 ) AS belowAverage
					,(SELECT COUNT( IF( grdT.totalAverage < (grdT.maxScore/2) AND s.sex = '1', grdT.id, NULL))  FROM `rms_grading_total` AS grdT,rms_student AS s WHERE s.stu_id=grdT.studentId AND grdT.gradingId = grd.id AND grdT.maxScore >0 LIMIT 1 ) AS belowAverageMale
			";
			
			$sql.=" FROM rms_grading AS grd,
						rms_group AS g 
				WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
			$sql.=' AND grd.teacherId='.$userId;
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year =".$_data['academicYear'];
			}
			if(!empty($_data['degree'])){
				$sql.=" AND g.degree =".$_data['degree'];
			}
			if(!empty($_data['groupId'])){
				$sql.=" AND g.id =".$_data['groupId'];
			}
			$sql.=" ORDER BY grd.id DESC ";
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	
	
	function getSubjectOfClassAvailableForIssue($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$forSettingType = empty($_data['forSettingType'])?0:$_data['forSettingType'];
			
			$colunmName='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$subjectTitle='subject_titleen';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$subjectTitle='subject_titlekh';
			}
			
			$sql="
				SELECT 
					sett.id AS settingEntryId
					,sett.branchId AS branchId
					,sett.title AS settingTitle
					,sett.fromDate AS startDateCriteria
					,sett.endDate AS endDateCriteria
					,sett.examFromDate AS startDateExam
					,sett.examEndDate AS endDateExam
					,sett.examType AS examType
					,sett.forMonth AS forMonth
					,sett.forSemester AS forSemester
					
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.gradingId AS gradingSettingId
					,g.academic_year AS academicYearId
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,(SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1) AS `roomName`
					
					,subj.$subjectTitle AS subjectTitle
					,subj.subject_titleen AS subjectTitleEng
					,subj.subject_titlekh AS subjectTitleKh
					,subj.shortcut AS subjectShortCut
					,subj.subject_lang AS subjectLang
					,gsjb.subject_id AS subjectId
					,COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) AS totalStudent
					,COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) AS maleStudent
					,COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) AS femaleStudent
					,CASE 
						WHEN tmp.criteriaId IS NOT NULL
						THEN '1'
						ELSE '0'
					END AS isIssuedMonthly
					,COALESCE(tmp.id,'0') AS gradingTmpId
					,(SELECT gr.isLock FROM rms_grading AS gr WHERE gr.gradingTmpId = COALESCE(tmp.id,'0') LIMIT 1 ) isLockGrading
					
					
			";
			
			$sql.=" FROM 
						`rms_group_subject_detail` AS gsjb
						JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1 
						JOIN `rms_score_entry_setting` AS sett ON FIND_IN_SET(g.degree,sett.degreeId) AND sett.status = 1
						LEFT JOIN `rms_subject` AS subj ON subj.id = gsjb.subject_id 
						LEFT JOIN (rms_student AS s JOIN `rms_group_detail_student` AS gds ON  gds.stu_id = s.stu_id AND s.status = 1 AND s.customer_type=1 AND gds.stop_type = 0) ON  gds.group_id = g.id
						LEFT JOIN (`rms_grading_tmp` AS tmp JOIN `rms_exametypeeng` AS cri ON cri.id = tmp.criteriaId AND cri.criteriaType=2 ) ON tmp.settingEntryId = sett.id AND tmp.groupId = gsjb.group_id AND tmp.subjectId = gsjb.subject_id
				";
			$sql.=' WHERE gsjb.teacher='.$userId;
			
			$date = new DateTime();
			$currentDate = $date->format("Y-m-d");
			$sql.= " AND sett.fromDate <= '".$currentDate."' AND sett.examEndDate >='".$currentDate."' ";
			//if($forSettingType==1){
			//	$sql.= " AND sett.examFromDate <= '".$currentDate."' AND sett.examEndDate >='".$currentDate."' ";
			//}else{
			//	$sql.= " AND sett.fromDate <= '".$currentDate."' AND sett.endDate >='".$currentDate."' ";
			//}
			$sql.=" GROUP BY 
						sett.id,
						gsjb.group_id,
						gsjb.subject_id
				";
			$sql.=' ORDER BY `g`.`group_code` ASC  ';
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getSubjectCriteriaOfClassByTeacher($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$colunmName='title_en';
			$label = 'name_en';
			$subjectTitle='subject_titleen';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
				$subjectTitle='subject_titlekh';
			}
			
			$sql="
				SELECT 
					sett.id AS settingEntryId
					,sett.branchId AS branchId
					,sett.title AS settingTitle
					,gsjb.group_id AS groupId
					,gsjb.subject_id as subjectIdValue
					,gsjb.max_score as maxScoreSubject
					,sttD.criteriaId
					,sttD.timeInput
					,sttD.pecentage_score AS percentageScore
					,sttD.subCriterialTitleKh AS subCriteriaTitleKh
					,sttD.subCriterialTitleEng AS subCriteriaTitleEng
					,sttD.subjectId AS subjectIdInCriteriaSetting
					
					,(SELECT crit.$colunmName FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitle
					,(SELECT crit.title FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleKh
					,(SELECT crit.title_en FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleEng
					,(SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaType
					
					,g.group_code AS groupCode
					,subj.$subjectTitle AS subjectTitle
					,subj.subject_titleen AS subjectTitleEng
					,subj.subject_titlekh AS subjectTitleKh
					,subj.shortcut AS subjectShortCut
					,subj.subject_lang AS subjectLang
					,(SELECT COUNT(tmp.id) FROM `rms_grading_tmp` AS tmp WHERE  tmp.settingEntryId = sett.id AND  tmp.groupId = g.id AND tmp.subjectId = gsjb.subject_id AND tmp.criteriaId = sttD.criteriaId LIMIT 1 ) AS totalInputted
			";
			
			$sql.=" FROM 
						`rms_group_subject_detail` AS gsjb
						JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1 
						JOIN `rms_score_entry_setting` AS sett ON FIND_IN_SET(g.degree,sett.degreeId) AND sett.status = 1
						LEFT JOIN `rms_subject` AS subj ON subj.id = gsjb.subject_id 
						LEFT JOIN ( `rms_scoreengsettingdetail` AS sttD JOIN `rms_scoreengsetting` AS stt ON stt.id = sttD.score_setting_id ) ON stt.degreeId = g.degree AND stt.id = g.gradingId 
							AND (sttD.subjectId =gsjb.subject_id OR sttD.subjectId=0)
				";
			$sql.=' WHERE gsjb.teacher='.$userId;
			$sql.=' AND g.is_pass !=3 '; //មិនស្មើរថ្នាក់ដែលរៀនចប់
			
			$date = new DateTime();
			$currentDate = $date->format("Y-m-d");
			$sql.= " AND sett.fromDate <= '".$currentDate."' AND sett.examEndDate >='".$currentDate."' ";
			$sql.=" GROUP BY 
						sett.id,
						gsjb.group_id,
						gsjb.subject_id,
						sttD.id,
						sttD.criteriaId 
				";
			$sql.=' ORDER BY g.group_code ASC  
							,gsjb.subject_id ASC
							,(SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) ASC
							,sttD.criteriaId ASC
							,sttD.subjectId DESC
				';
			$row = $_db->fetchAll($sql);
			if(!empty($row)){ 
				$newArray = array();
				$criteriaId = "";
				foreach($row as $criteria){
					if($criteriaId != $criteria["criteriaId"]){
						array_push($newArray, $criteria);
					}
					$criteriaId = $criteria["criteriaId"];
				}
				$row = $newArray;
			}
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	
	function getStudentList($_data){
		
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$groupId = empty($_data['groupId'])?0:$_data['groupId'];
			if($currentLang==1){// khmer
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
			$sql="SELECT 
					s.stu_id AS studentId
					,s.stu_code AS stuCode
					,s.stu_khname AS stuNameInKhmer
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
					,(SELECT v.$label FROM rms_view AS v WHERE v.type=2 and v.key_code=s.sex LIMIT 1 ) as genderTitle
					,(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name
					,gds.academic_year
					,gds.session
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academicYearTitle
					,(SELECT $label FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=gds.session LIMIT 1) AS `sessionName`
					,gds.grade
					,gds.degree
					,(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $degree FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degreeTitle
				
			";
			$sqlColScoreValue=",'0' AS scoreValue
								,'0' AS tmpDetailId 
							";
			$sqlTmpJoin="";
			if(!empty($_data['gradingTmpId'])){
				$gradingTmpId = $_data['gradingTmpId'];
				$sqlColScoreValue = ",tmpD.totalGrading AS scoreValue
									,tmpD.id AS tmpDetailId
								";
				$sqlTmpJoin=" LEFT JOIN `rms_grading_detail_tmp` AS tmpD ON tmpD.studentId = s.stu_id AND tmpD.`gradingId` = $gradingTmpId ";
			}
			$sql.=$sqlColScoreValue;
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds
					JOIN rms_student AS s  ON  gds.stu_id = s.stu_id 
			";
			$sql.=$sqlTmpJoin;
			
			
			$sql.="WHERE 
					gds.itemType=1 
					AND s.status = 1 
					AND s.customer_type=1
					AND gds.stop_type = 0 ";
			
			$where=' ';
			$where.=' AND gds.group_id='.$groupId;
			$where.=' GROUP By gds.stu_id ';
			
			$order_by = " ORDER BY s.stu_id ASC ";
			$row =  $_db->fetchAll($sql.$where.$order_by);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
    }
	
	
	
	function submitCriteriaScore($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$scoreSettingEntryPost 	 = empty($_data['scoreSettingEntry'])?null:$_data['scoreSettingEntry'];
			
			$scoreInfo 	 = Zend_Json::decode($scoreSettingEntryPost);
			
			$listFromPost 	 = empty($_data['studentScoreListSubmit'])?null:$_data['studentScoreListSubmit'];
			$studentScoreList 	 = Zend_Json::decode($listFromPost);
			
    		$arr = array(
    				'branchId'			=>$scoreInfo['branchId'],
    				'settingEntryId'	=>$scoreInfo['settingEntryId'],
    				'gradingSettingId'	=>$scoreInfo['gradingSettingId'],
    				'academicYear'		=>$scoreInfo['academicYearId'],
    				'groupId'			=>$scoreInfo['groupId'],
    				'examType'			=>$scoreInfo['examType'],
    				'forSemester'		=>$scoreInfo['forSemester'],
    				'forMonth'			=>$scoreInfo['forSemester'],
    				'subjectId'			=>$_data['subjectId'],
    				'criteriaId'		=>$_data['criteriaId'],
    				'inputOption'		=>2,
										
    				'status'			=>1,
    				'dateInput'			=>date("Y-m-d H:i:s"),
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'teacherId'			=>$_data['userId'],
    		);
    		$this->_name='rms_grading_tmp';
    		$gradinTmpId = $this->insert($arr);
    		
			if(!empty($studentScoreList)) foreach($studentScoreList AS $row){
				
				$arrDetail = array(
						'gradingId'			=>$gradinTmpId,
						'studentId'			=>$row['studentId'],
						'totalGrading'		=>$row['scoreValue'],
							
				);
				$this->_name='rms_grading_detail_tmp';	
				$this->insert($arrDetail);
				
			}
			
			
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	function submitEditCriteriaScore($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			if(!empty($_data['gradingTmpId'])){
				$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
				$gradinTmpId	 = empty($_data['gradingTmpId'])?0:$_data['gradingTmpId'];
				$scoreSettingEntryPost 	 = empty($_data['scoreSettingEntry'])?null:$_data['scoreSettingEntry'];
				
				$scoreInfo 	 = Zend_Json::decode($scoreSettingEntryPost);
				
				$listFromPost 	 = empty($_data['studentScoreListSubmit'])?null:$_data['studentScoreListSubmit'];
				$studentScoreList 	 = Zend_Json::decode($listFromPost);
				
				$arr = array(
						'examType'			=>$scoreInfo['examType'],
						'forMonth'			=>$scoreInfo['forMonth'],
						'forSemester'		=>$scoreInfo['forSemester'],
						'modifyDate'		=>date("Y-m-d H:i:s"),
						'teacherId'			=>$_data['userId'],
					);
				$this->_name='rms_grading_tmp';
				$where=" id = ".$gradinTmpId;
				$this->update($arr, $where);
				
				if(!empty($studentScoreList)) foreach($studentScoreList AS $row){
					
					if(!empty($row['tmpDetailId'])){
						$arrDetail = array(
							'studentId'			=>$row['studentId'],
							'totalGrading'		=>$row['scoreValue'],
						);
						$this->_name='rms_grading_detail_tmp';	
						$where=" id = ".$row['tmpDetailId']." AND gradingId = ".$gradinTmpId;
						$this->update($arrDetail, $where);
					}else{
						$arrDetail = array(
							'gradingId'			=>$gradinTmpId,
							'studentId'			=>$row['studentId'],
							'totalGrading'		=>$row['scoreValue'],
						);
						$this->_name='rms_grading_detail_tmp';	
						$this->insert($arrDetail);
					}
					
				}
			
			}
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	function getCriteriaScoreList($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$_data['groupId'] = empty($_data['groupId'])?0:$_data['groupId'];
			$_data['settingEntryId'] = empty($_data['settingEntryId'])?0:$_data['settingEntryId'];
			$_data['subjectId'] = empty($_data['subjectId'])?0:$_data['subjectId'];
			$_data['criteriaId'] = empty($_data['criteriaId'])?0:$_data['criteriaId'];
			
			$colunmName='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$subjectTitle='subject_titleen';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$subjectTitle='subject_titlekh';
			}
			
			$sql="
				SELECT 
				
					gTmp.*
					,sett.title AS settingTitle
					,sett.fromDate AS startDateCriteria
					,sett.endDate AS endDateCriteria
					,sett.examFromDate AS startDateExam
					,sett.examEndDate AS endDateExam
					
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.gradingId AS gradingSettingId
					,g.academic_year AS academicYearId
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,(SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1) AS `roomName`
					
					,subj.$subjectTitle AS subjectTitle
					,subj.subject_titleen AS subjectTitleEng
					,subj.subject_titlekh AS subjectTitleKh
					,subj.shortcut AS subjectShortCut
					,subj.subject_lang AS subjectLang
					
					,COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) AS totalStudent
					,COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) AS maleStudent
					,COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) AS femaleStudent
					
					,(SELECT COUNT(IF(tmpD.totalGrading > 0  , s.sex, NULL))  FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.gradingId = gTmp.id LIMIT 1) AS issuedScore
					,(SELECT COUNT(IF(tmpD.totalGrading <= 0  , s.sex, NULL))  FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.gradingId = gTmp.id LIMIT 1) AS noScore					
					
			";
			
			$sql.=" FROM `rms_grading_tmp` AS gTmp
						JOIN `rms_group` AS g ON g.`id` = gTmp.`groupId`
						LEFT JOIN rms_score_entry_setting AS sett ON sett.`id` = gTmp.`settingEntryId`
						LEFT JOIN `rms_subject` AS subj ON subj.id = gTmp.subjectId 
						LEFT JOIN (rms_student AS s JOIN `rms_group_detail_student` AS gds ON  gds.stu_id = s.stu_id AND s.status = 1 AND s.customer_type=1 AND gds.stop_type = 0) ON  gds.group_id = g.id
				";
			$sql.=' WHERE gTmp.teacherId='.$userId;
			if(!empty($_data['settingEntryId'])){
				$sql.=' AND gTmp.settingEntryId='.$_data['settingEntryId'];
			}
			if(!empty($_data['groupId'])){
				$sql.=' AND gTmp.groupId='.$_data['groupId'];
			}
			if(!empty($_data['subjectId'])){
				$sql.=' AND gTmp.subjectId='.$_data['subjectId'];
			}
			if(!empty($_data['criteriaId'])){
				$sql.=' AND gTmp.criteriaId='.$_data['criteriaId'];
			}
			$sql.=" GROUP BY gTmp.id
				";
			$sql.=' ORDER BY gTmp.id DESC  ';
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getPolicyInfoForIssueMonthlyScore($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$groupId = empty($_data['groupId'])?0:$_data['groupId'];
			if($currentLang==1){// khmer
				$label = "name_kh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
			}else{ // English
				$label = "name_en";
				$grade = "rms_itemsdetail.title_en";
				$degree = "rms_items.title_en";
			}
			$sql="SELECT 
					s.stu_id AS studentId
					,s.branch_id AS branch_id
					,s.stu_code AS stuCode
					,s.stu_khname AS stuNameInKhmer
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
					,(SELECT v.$label FROM rms_view AS v WHERE v.type=2 and v.key_code=s.sex LIMIT 1 ) as genderTitle
					,gds.academic_year
					,gds.session
					,gds.grade
					,gds.degree
					
					,COALESCE(g.`gradingId`,'0') AS gradingId
					
					,'0' AS totalAbsent
					,'0' AS totalPermission
					,'0' AS totalLate
					,'0' AS totalEalyLeave
					,'0' AS totalAttendanceScore
					
				
			";
			$sqlColScoreValue="
					,'' AS subCriteriaTitleKh
					,'' AS subCriteriaTitleEng
					,'' AS subCriteriaScore
					,'0' AS criteriaScoreAvg
					,'0' AS grandTotalAverageScore
				";
			$sqlTmpJoin="";
			if(!empty($_data['gradingTmpId'])){
				$gradingTmpId = $_data['gradingTmpId'];
				$sqlColScoreValue = "
						,(SELECT tmpD.subCriterialTitleKh FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId LIMIT 1) AS subCriteriaTitleKh
						,(SELECT tmpD.subCriterialTitleKh FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId LIMIT 1) AS subCriteriaTitleEng
						,(SELECT GROUP_CONCAT(tmpD.totalGrading) FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId ) AS subCriteriaScore
						,(SELECT  IF(SUM(tmpD.totalGrading) >0, FORMAT((COALESCE(SUM(tmpD.totalGrading),'0') / COUNT(tmpD.id) ),2) ,'0') FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId) AS criteriaScoreAvg
						,(SELECT grdT.totalAverage FROM `rms_grading_total` AS grdT WHERE grdT.studentId = s.stu_id AND grdT.`gradingTmpId` =$gradingTmpId LIMIT 1) AS grandTotalAverageScore
					";
			}
			$sql.=$sqlColScoreValue;
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds
					JOIN rms_student AS s  ON  gds.stu_id = s.stu_id 
					LEFT JOIN `rms_group` AS g  ON g.id = gds.group_id
			";
			$sql.=$sqlTmpJoin;
			
			
			$sql.="WHERE 
					gds.itemType=1 
					AND s.status = 1 
					AND s.customer_type=1
					AND gds.stop_type = 0 ";
			
			$where=' ';
			$where.=' AND gds.group_id='.$groupId;
			$where.=' GROUP By gds.stu_id ';
			
			$order_by = " ORDER BY s.stu_id ASC ";
			$row =  $_db->fetchAll($sql.$where.$order_by);
			
			
			if(!empty($_data['forIssueMonthly'])){
				
				$settingEntryId = empty($_data['settingEntryId'])?0:$_data['settingEntryId'];
				$subjectId = empty($_data['subjectId'])?0:$_data['subjectId'];
				$arrAttTypeCount =array(
					"settingEntryId" => $settingEntryId,
					"groupId" => $groupId,
					"subjectId" => $subjectId,
				);
				if(!empty($row)){
					$gradingScoreSettingId = empty($row[0]["gradingId"]) ? 0 : $row[0]["gradingId"];
					$attScoreSetting = $this->getScoreAttendanceSetting($gradingScoreSettingId);
					foreach($row as $index => $student){
						$arrAttTypeCount["studentId"] = $student["studentId"];
						$arrAttTypeCount["attendenceStatus"] = 2;
						$totalAbsent = $this->getCountAttendanceByStudent($arrAttTypeCount);
						
						$arrAttTypeCount["attendenceStatus"] = 3;
						$totalPermission = $this->getCountAttendanceByStudent($arrAttTypeCount);
						
						$arrAttTypeCount["attendenceStatus"] = 4;
						$totalLate = $this->getCountAttendanceByStudent($arrAttTypeCount);
						
						$arrAttTypeCount["attendenceStatus"] = 5;
						$totalEalyLeave = $this->getCountAttendanceByStudent($arrAttTypeCount);
						
						$row[$index]["totalAbsent"] = $totalAbsent;
						$row[$index]["totalPermission"] = $totalPermission;
						$row[$index]["totalLate"] = $totalLate;
						$row[$index]["totalEalyLeave"] = $totalEalyLeave;
						
						$student["totalAbsent"] 	= $totalAbsent;
						$student["totalPermission"] = $totalPermission;
						$student["totalLate"] 		= $totalLate;
						$student["totalEalyLeave"] 	= $totalEalyLeave;
						
						$row[$index]["totalAttendanceScore"] = $this->calculatingScoreAttendance($attScoreSetting,$student);
					}
				}
			}
			
			$criteriaInfoList = $this->getCriteriaScoreOfStudent($_data);
			$criteriaInfoList = empty($criteriaInfoList) ? null : $criteriaInfoList;
			$arrayResult = array(
				'studentList' 		 =>$row,
				"criteriaInfoList"   =>$criteriaInfoList
			);
			$result = array(
					'status' =>true,
					'value' =>$arrayResult,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getCriteriaScoreOfStudent($_data){
		$db = $this->getAdapter();
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$groupId = empty($_data['groupId'])?0:$_data['groupId'];
		$subjectId = empty($_data['subjectId'])?0:$_data['subjectId'];
		$settingEntryId = empty($_data['settingEntryId'])?0:$_data['settingEntryId'];
		$colunmName='title_en';
		if ($currentLang==1){
			$colunmName='title';
		}
		$sql="
			SELECT 
				sttD.criteriaId
				,sttD.pecentage_score AS percentageScore
				,crit.$colunmName AS criteriaTitle
				,crit.title AS criteriaTitleKh
				,crit.title_en AS criteriaTitleEng
				,crit.criteriaType AS criteriaType
				,gds.stu_id AS studentId
				,COALESCE(SUM(tmpD.totalGrading),'0') AS totalCriteriaScore
				,COUNT(tmpD.id) AS inputTime
				, FORMAT(IF(COUNT(tmpD.id)>0 AND COALESCE(SUM(tmpD.totalGrading),'0') >0, (SUM(tmpD.totalGrading) / COUNT(tmpD.id)),0),2) AS averageScore
				
				,COALESCE(g.`gradingId`,'0') AS gradingId
				,g.group_code AS groupCode
				,gsjb.subject_id AS subjectId
				,subj.shortcut AS subjectShortCut
				,subj.subject_lang AS subjectLang
		";
		$sql.="FROM 
				(`rms_scoreengsettingdetail` AS sttD JOIN `rms_scoreengsetting` AS stt ON stt.id = sttD.score_setting_id )
				JOIN `rms_group` AS g ON g.`gradingId` = stt.`id`
				LEFT JOIN `rms_exametypeeng` AS crit ON crit.id = sttD.criteriaId 
				LEFT JOIN `rms_group_subject_detail` AS gsjb ON g.id = gsjb.group_id 
				LEFT JOIN `rms_subject` AS subj ON subj.id = gsjb.subject_id 
				LEFT JOIN `rms_group_detail_student` AS gds ON  gds.group_id = g.id AND gds.stop_type = 0
				LEFT JOIN (`rms_grading_detail_tmp` AS tmpD JOIN `rms_grading_tmp` AS tmp ON tmp.id =tmpD.gradingId) ON tmp.settingEntryId =".$settingEntryId." AND tmp.criteriaId = sttD.criteriaId AND g.id = tmp.groupId AND gds.stu_id = tmpD.studentId
			";
		$sql.=" WHERE 1 ";
		$sql.="
				AND g.is_use=1 
				AND g.id = ".$groupId." 
				AND gsjb.subject_id = ".$subjectId." 
				AND gsjb.teacher=".$userId." 
				AND crit.criteriaType !=2
			";
		$sql.="
			GROUP BY
				g.id,
				gsjb.subject_id,
				sttD.criteriaId,
				gds.stu_id
		";
		$sql.=" ORDER BY 
				`g`.`group_code` ASC
				,gds.stu_id ASC
				,crit.criteriaType ASC 
				,sttD.criteriaId ASC
				";
		return $db->fetchAll($sql);
	}
	
	function calculatingScoreAttendance($arrAttSetting,$rowStudentInfo){
		$totalDeduct = 0;
		$scoreAttendance=0;
		$maxAttendance=0;
		if(!empty($arrAttSetting))foreach($arrAttSetting as $key => $rs){
			
			if($rs['attendanceType']==2 AND $rowStudentInfo["totalAbsent"] >0){
				$totalDeduct =$totalDeduct+($rowStudentInfo['totalAbsent']*$rs['scoreDeduct']);
			}
			elseif($rs['attendanceType']==3 AND $rowStudentInfo["totalPermission"] >0){
				$totalDeduct = $totalDeduct+($rowStudentInfo['totalPermission']*$rs['scoreDeduct']);
			}
			elseif($rs['attendanceType']==4 AND $rowStudentInfo["totalLate"] >0){
				$totalDeduct = $totalDeduct+($rowStudentInfo['totalLate']*$rs['scoreDeduct']);
			}
			elseif($rs['attendanceType']==5 AND $rowStudentInfo["totalEalyLeave"] >0){
				$totalDeduct = $totalDeduct+($rowStudentInfo['totalEalyLeave']*$rs['scoreDeduct']);
			}
			if($key==0){$maxAttendance = $rs["attendanceScore"];}
		}
		$scoreAttendance = $maxAttendance - $totalDeduct;
		if($scoreAttendance<0){
			$scoreAttendance = 0;
		}
		return $scoreAttendance;
	}
	function getScoreAttendanceSetting($gradingScoreSettingId){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				attS.`score` AS attendanceScore
				,attSD.attendanceType AS attendanceType
				,attSD.scoreDeduct AS scoreDeduct
			FROM `rms_scoreengsetting` AS sSet
				JOIN (`rms_attendance_score_setting` AS attS JOIN `rms_attendance_score_setting_detail` AS attSD  ON attSD.settingId = attS.id) 
				ON attS.`id` = sSet.`settingScoreAttId`
			WHERE sSet.id = $gradingScoreSettingId
		";
		return $db->fetchAll($sql);
	}

	function getCountAttendanceByStudent($_data){
		$db = $this->getAdapter();
		$settingEntryId = empty($_data["settingEntryId"]) ? "0" : $_data["settingEntryId"];
		$sqlGrad="
			SELECT entSett.*  FROM rms_score_entry_setting AS entSett WHERE  entSett.id = $settingEntryId LIMIT 1
		";
		$entrySetting = $db->fetchRow($sqlGrad);
		
		$restult = "0";
		if(!empty($entrySetting)){
			$studentId = empty($_data["studentId"]) ? "0" : $_data["studentId"];
			$groupId = empty($_data["groupId"]) ? "0" : $_data["groupId"];
			$attendenceStatus = empty($_data["attendenceStatus"]) ? "0" : $_data["attendenceStatus"];
			$subjectId = empty($_data["subjectId"]) ? "0" : $_data["subjectId"];
			$sql="
				SELECT 
					satd.attendence_id,
					satd.attendence_status
				FROM 
					`rms_student_attendence` AS sat ,
					`rms_student_attendence_detail` AS satd
						
				WHERE satd.attendence_status = $attendenceStatus
					AND sat.`id`= satd.`attendence_id`
					AND satd.stu_id = $studentId
					AND sat.group_id = $groupId
					AND satd.subjectId = $subjectId
					
				";
			$sql.=" AND sat.`date_attendence` >='".$entrySetting["fromDate"]."' AND sat.`date_attendence` <= '".$entrySetting["endDate"]."' ";
			$sql.="
					GROUP BY satd.attendence_id,satd.attendence_status
					ORDER BY satd.attendence_status DESC
			";
			$rs = $db->fetchAll($sql);
			if(!empty($rs)){
				return $restult = "".COUNT($rs)."";
			}
		}
		return $restult;
	}
	
	function getGradingByTmpGradingId($gradingTmpId){
		$db = $this->getAdapter();
		$sql = "
			SELECT gr.* FROM rms_grading AS gr WHERE gr.gradingTmpId = $gradingTmpId LIMIT 1
		";
		return $db->fetchRow($sql);
	}
	function submitMonthlyScore($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$scoreSettingEntryPost 	 = empty($_data['scoreSettingEntry'])?null:$_data['scoreSettingEntry'];
			$scoreInfo 	 = Zend_Json::decode($scoreSettingEntryPost);
			
			$criteriaInfoPost 	 = empty($_data['criteriaInfo'])?null:$_data['criteriaInfo'];
			$criteriaInfo 	 = Zend_Json::decode($criteriaInfoPost);
			
			$listFromPost 	 = empty($_data['studentScoreListSubmit'])?null:$_data['studentScoreListSubmit'];
			$studentScoreList 	 = Zend_Json::decode($listFromPost);
			
			$criteriaListOfStudentPost 	 = empty($_data['criteriaListOfStudent'])?null:$_data['criteriaListOfStudent'];
			$criteriaListOfStudentList 	 = Zend_Json::decode($criteriaListOfStudentPost);
			$criteriaId = $_data['criteriaId'];
			$subjectId = $_data['subjectId'];
    		$arr = array(
    				'branchId'			=>$scoreInfo['branchId'],
    				'settingEntryId'	=>$scoreInfo['settingEntryId'],
    				'gradingSettingId'	=>$scoreInfo['gradingSettingId'],
    				'academicYear'		=>$scoreInfo['academicYearId'],
    				'groupId'			=>$scoreInfo['groupId'],
    				'examType'			=>$scoreInfo['examType'],
    				'forSemester'		=>$scoreInfo['forSemester'],
    				'forMonth'			=>$scoreInfo['forMonth'],
    				'subjectId'			=>$subjectId,
    				'criteriaId'		=>$criteriaId,
    				'inputOption'		=>2,
										
    				'status'			=>1,
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'teacherId'			=>$_data['userId'],
    		);
			$this->_name='rms_grading_tmp';
			if(!empty($_data['gradingTmpId'])){
				$gradingTmpId = empty($_data['gradingTmpId']) ? 0 : $_data['gradingTmpId'];
				$where=" id = ".$gradingTmpId;
				$this->update($arr, $where);
			}else{
				$arr['dateInput'] = date("Y-m-d H:i:s");
				$arr['createDate'] = date("Y-m-d H:i:s");
				$gradingTmpId = $this->insert($arr);
			}
			
			$arrGrading = array(
					'branchId'			=>$scoreInfo['branchId'],
					'gradingTmpId'		=>$gradingTmpId,
					'settingEntryId'	=>$scoreInfo['settingEntryId'],
					'gradingSettingId'	=>$scoreInfo['gradingSettingId'],
					'groupId'			=>$scoreInfo['groupId'],
			        'examType'			=>$scoreInfo['examType'],
					
					'forMonth'			=>$scoreInfo['forMonth'],
					'forSemester'		=>$scoreInfo['forSemester'],
					'academicYear'		=>$scoreInfo['academicYearId'],
					
					'subjectId'			=>$subjectId,
					'inputOption'		=>2, 
					'status'			=>1,
					
					'teacherId'			=>$_data['userId'],
					'modifyDate'		=>date("Y-m-d H:i:s"),
			);
			$this->_name='rms_grading';
			if(!empty($_data['gradingTmpId'])){
				$gradingRs = $this->getGradingByTmpGradingId($gradingTmpId);
				$idGrading = empty($gradingRs['id']) ? 0 : $gradingRs['id'];
				if(!empty($gradingRs)){
					$whereGrading=" id = ".$idGrading;
					$this->update($arrGrading, $whereGrading);
					
					$where='gradingId='.$gradingTmpId;
					$this->_name='rms_grading_detail_tmp';
					$this->delete($where);
					
					$where='gradingTmpId='.$gradingTmpId;
					$this->_name='rms_grading_detail';
					$this->delete($where);
					
					$this->_name='rms_grading_total';
					$this->delete($where);
				}
			}else{
				$arrGrading['dateInput'] = date("Y-m-d H:i:s");
				$arrGrading['createDate'] = date("Y-m-d H:i:s");
				$idGrading = $this->insert($arrGrading);
			}
			if(!empty($gradingTmpId) && !empty($idGrading) ){
				$subCriteriaTitle = empty($criteriaInfo['subCriteriaTitleKh']) ? "" : $criteriaInfo['subCriteriaTitleKh'];
				$subjectIdInCriteriaSetting = empty($criteriaInfo['subjectIdInCriteriaSetting']) ? "0" : $criteriaInfo['subjectIdInCriteriaSetting'];
				
				$subCriteriaTitleList = explode(',', $criteriaInfo['subCriteriaTitleKh']);
				$subCriteriaAmount = count($subCriteriaTitleList);
				
				$maxScoreSubject = empty($criteriaInfo['maxScoreSubject']) ? "" : $criteriaInfo['maxScoreSubject'];
				$pecentageScore = empty($criteriaInfo['pecentageScore']) ? "" : $criteriaInfo['pecentageScore'];
				
				if(!empty($studentScoreList)) foreach($studentScoreList AS $row){
					if($subCriteriaTitle!="" && $subjectIdInCriteriaSetting !="0"){
						$subCriteriaScoreExp = explode(',', $row['subCriteriaScore']);
						$subCriteriaTitleEngExp = explode(',', $row['subCriteriaTitleEng']);
						foreach ($subCriteriaTitleList as $key => $i){
							$arrDetail = array(
									'gradingId'			    =>$gradingTmpId,
									'studentId'			    =>$row['studentId'],
									'subCriterialTitleKh'	=>$i,
									'subCriterialTitleEng'	=>$subCriteriaTitleEngExp[$key],
									'totalGrading'			=>$subCriteriaScoreExp[$key],
										
							);
							$this->_name='rms_grading_detail_tmp';	
							$this->insert($arrDetail);
							
							$arrGradingDetail=array(
								'gradingId'				=> $idGrading,
								'gradingTmpId'			=> $gradingTmpId,
								'studentId'				=> $row['studentId'],
								'subjectId'				=> $subjectId,
								'criteriaId'			=> $criteriaId,
								'totalGrading'			=> $subCriteriaScoreExp[$key],
								'criteriaAmount'		=> $subCriteriaAmount,
								'percentage'			=> $pecentageScore,
								'subCriterialTitleKh'	=> $i,
								'subCriterialTitleEng'	=> $subCriteriaTitleEngExp[$key],
								);
							
							$this->_name='rms_grading_detail';
							$this->insert($arrGradingDetail);
						}
					}else{
						$subCriteriaScore = empty($row['subCriteriaScore']) ? "0" : $row['subCriteriaScore'];
						$arrDetail = array(
							'gradingId'			=>$gradingTmpId,
							'studentId'			=>$row['studentId'],
							'totalGrading'		=>$subCriteriaScore,
								
						);
						$this->_name='rms_grading_detail_tmp';	
						$this->insert($arrDetail);
						
						$arrGradingDetail=array(
								'gradingId'				=> $idGrading,
								'gradingTmpId'			=> $gradingTmpId,
								'studentId'				=> $row['studentId'],
								'subjectId'				=> $subjectId,
								'criteriaId'			=> $criteriaId,
								'totalGrading'			=> $subCriteriaScore,
								'criteriaAmount'		=> $subCriteriaAmount,
								'percentage'			=> $pecentageScore,
								);
							
						$this->_name='rms_grading_detail';
						$this->insert($arrGradingDetail);
					}
					
					$arr=array(
						'gradingId'			=> $idGrading,
						'gradingTmpId'		=> $gradingTmpId,
						'studentId'			=> $row['studentId'],
						'subjectId'			=> $subjectId,
						'totalAverage'		=> $row['grandTotalAverageScore'],
						'maxScore'			=> $maxScoreSubject,
					);
					$this->_name='rms_grading_total';
					$this->insert($arr);
					
				}
				
				if(!empty($criteriaListOfStudentList)) {
					foreach($criteriaListOfStudentList as $criteriaStudent){
						$tmpRecord = $this->getStudentCriteriaTmpRecord($criteriaStudent);
						if(COUNT($tmpRecord)>1){
							foreach($tmpRecord as $indexKey => $tmpInfo){
								$arrGradingDetail=array(
									'gradingId'				=> $idGrading,
									'gradingTmpId'			=> $gradingTmpId,
									'studentId'				=> $criteriaStudent['studentId'],
									'subjectId'				=> $subjectId,
									'criteriaId'			=> $criteriaStudent["criteriaId"],
									'totalGrading'			=> $tmpInfo["totalGrading"],
									'criteriaAmount'		=> ($indexKey+1),
									'percentage'			=> $criteriaStudent["percentageScore"],
									);
								$this->_name='rms_grading_detail';
								$this->insert($arrGradingDetail);
							}
						}else{
							$arrGradingDetail=array(
								'gradingId'				=> $idGrading,
								'gradingTmpId'			=> $gradingTmpId,
								'studentId'				=> $criteriaStudent['studentId'],
								'subjectId'				=> $subjectId,
								'criteriaId'			=> $criteriaStudent["criteriaId"],
								'totalGrading'			=> $criteriaStudent["averageScore"],
								'criteriaAmount'		=> 0,
								'percentage'			=> $criteriaStudent["percentageScore"],
								);
							$this->_name='rms_grading_detail';
							$this->insert($arrGradingDetail);
						}
						
					}
				}
			}
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	function getStudentCriteriaTmpRecord($_data){
		$db = $this->getAdapter();
		$_data["criteriaId"] = empty($_data["criteriaId"]) ? 0 : $_data["criteriaId"];
		$_data["studentId"] = empty($_data["studentId"]) ? 0 : $_data["studentId"];
		$sql="
			SELECT 
				tmp.id AS gradingTmpId
				,tmp.criteriaId
				,tmp.`groupId`
				,tmpD.`studentId`
				,tmpD.`totalGrading`
				,tmpD.`subCriterialTitleEng`
				,tmpD.`subCriterialTitleKh`
			FROM 
				`rms_grading_tmp` AS tmp,
				`rms_grading_detail_tmp` AS tmpD
			WHERE 
				tmp.id = tmpD.`gradingId`
				AND tmp.`criteriaId` = ".$_data["criteriaId"]."
				AND tmpD.`studentId` = ".$_data["studentId"]."
			ORDER BY tmp.id ASC
		";
		return $db->fetchAll($sql);
	}
	
	function getGroupMonthlyScoreDetail($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$groupId = empty($_data['groupId'])?0:$_data['groupId'];
			$gradingId = empty($_data['gradingId'])?0:$_data['gradingId'];
			if($currentLang==1){// khmer
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
			$sql="SELECT 
					gds.`stu_id` AS studentId
					,s.`stu_code` AS stuCode
					,s.stu_khname AS stuNameInKhmer
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
					,(SELECT v.$label FROM rms_view AS v WHERE v.type=2 and v.key_code=s.sex LIMIT 1 ) as genderTitle
					,(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name
					,gds.academic_year
					,gds.session
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academicYearTitle
					,(SELECT $label FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=gds.session LIMIT 1) AS `sessionName`
					,gds.grade
					,gds.degree
					,(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $degree FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degreeTitle
					,grdT.`totalAverage`
					,grdT.`maxScore`
					,FIND_IN_SET( 
						grdT.totalAverage, 
							(    
								SELECT 
									GROUP_CONCAT( dd.totalAverage ORDER BY dd.totalAverage DESC ) 
								FROM rms_grading_total AS dd 
								WHERE  dd.`gradingId`= grdT.`gradingId`
							)
						) AS ranking
				
			";
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds 
					JOIN `rms_student` AS s ON s.`stu_id` = gds.`stu_id`
					LEFT JOIN `rms_grading_total` AS grdT ON grdT.`studentId` = gds.`stu_id`
			";
			
			
			$sql.="WHERE 
					1
				";
			
			$where='';
			$where.=' AND gds.`group_id`='.$groupId;
			$where.=' AND grdT.`gradingId`='.$gradingId;
			$where.=' GROUP By gds.stu_id ';
			
			$order_by = " ORDER BY grdT.`totalAverage` DESC,s.`stu_code` ASC ";
			$row =  $_db->fetchAll($sql.$where.$order_by);
			
			$criteriaInfoList = $this->groupGradingDetail($_data);
			$arrayResult = array(
				'studentList' 		 =>$row,
				"criteriaInfoList"   =>$criteriaInfoList
			);
			
			$result = array(
					'status' =>true,
					'value' =>$arrayResult,
			);
			return $result;
			
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function groupGradingDetail($_data){
		$db = $this->getAdapter();
		$groupId = empty($_data['groupId'])?0:$_data['groupId'];
		$gradingId = empty($_data['gradingId'])?0:$_data['gradingId'];
		$sql="
			SELECT 
				grdd.`gradingId`
				,gds.`stu_id` AS studentId
				,grdd.`criteriaId`
				,cri.`title`
				,grdd.`totalGrading`
				,grdd.`percentage`
				,grdd.`subCriterialTitleEng`
				,grdd.`subCriterialTitleKh`
			FROM 
				`rms_group_detail_student` AS gds 
				LEFT JOIN `rms_grading_detail` AS grdd ON grdd.`studentId` = gds.`stu_id`
				LEFT JOIN `rms_exametypeeng` AS cri ON cri.id = grdd.`criteriaId`
			WHERE 
				gds.`group_id` = $groupId 
				AND grdd.`gradingId` = $gradingId
			ORDER BY 
				grdd.`studentId` ASC
				,cri.`criteriaType` ASC
				,cri.`id` ASC
		";
		return $db->fetchAll($sql);
	}
}