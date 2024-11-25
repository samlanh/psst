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
						,t.teacher_name_kh AS teacherNameKh
						,t.teacher_name_en AS teacherNameEng
						
						,t.branch_id AS branchId
						,(SELECT $lbView from rms_view where type=2 and key_code=t.sex LIMIT 1) as genderTitle
						,(SELECT $lbView FROM rms_view where type=21 and key_code=t.nationality LIMIT 1) AS nationality
						 
						,(SELECT $province FROM rms_province AS p WHERE p.province_id=t.province_id LIMIT 1) AS provinceTitle
						,(SELECT $district FROM ln_district AS p WHERE p.dis_id=t.district_name LIMIT 1) AS districtTitle
						,(SELECT $commune FROM ln_commune AS p WHERE p.com_id=t.commune_name LIMIT 1) AS communeTitle
						,(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = t.village_name LIMIT 1) AS villageTitle
						,CASE 
							WHEN g.id IS NOT NULL THEN '1'
							ELSE '0'
						END AS isMainTeacher
						
			FROM
				rms_teacher AS t 
				LEFT JOIN `rms_group` AS g ON g.`teacher_id` = t.`id` AND g.status =1 AND g.is_pass =2 AND g.`is_use` =1
			WHERE
				t.staff_type=1 
				AND t.id=$userId 
			";//g.is_pass =2  active group
			$sql.=" GROUP BY t.id ";
			$sql.=" LIMIT 1";
			$row = $_db->fetchAll($sql);
			$row = empty($row) ? array() : $row;
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
			
			$token = $row['mobileToken'];
    		$this->_name = "mobile_mobile_token";
			if(!empty($row['mobileToken'])){
				$token = $row['mobileToken'];
				$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' LIMIT 1";
				$rsid = $db->fetchOne($sql);
				if(!empty($rsid)){
					$row['id'] = empty($row['id']) ? 0 : $row['id'];
					$row['deviceType'] = empty($row['deviceType']) ? 0 : $row['deviceType'];
					$row['tokenType'] = empty($row['tokenType']) ? 0 : $row['tokenType'];
					$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
					);
					//$where ='id= '.$rsid;
					$where ="token= '".$token."'";
					$this->update($_arr, $where);
				}else{
					$row['id'] = empty($row['id']) ? 0 : $row['id'];
					$row['deviceType'] = empty($row['deviceType']) ? 0 : $row['deviceType'];
					$row['tokenType'] = empty($row['tokenType']) ? 0 : $row['tokenType'];
					$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
					);
					
					$_arr['date'] = date("Y-m-d H:i:s");
					$this->_name = "mobile_mobile_token";
					$this->insert($_arr);
				}
			}
			
			/*
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
    		}*/
    		
    		return $token;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return null;
    	}
    }
	
	function checkChangePassword($_data){
		$db = $this->getAdapter();
		try{
	
			$sql ="
			SELECT
				t.id AS id
				,t.teacher_code AS teacherCode
				,t.teacher_name_kh AS teaccherNameKh
				,t.teacher_name_en AS teaccherNameEng
				,t.photo
			FROM
				rms_teacher AS t
			WHERE t.status = 1 ";
			$sql.= " AND ".$db->quoteInto('t.id=?', $_data['userId']);
			$sql.= " AND ".$db->quoteInto('t.password=?', md5($_data['oldPassword']));
			$row = $db->fetchRow($sql);
			if (empty($row)){
				return false;
			}
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function changePassword($_data){
		$db = $this->getAdapter();
		try{
			$_arr=array(
					'password'	  	=> md5($_data['newPassword']),
			);
			$this->_name = "rms_teacher";
			$where = $this->getAdapter()->quoteInto("id=?",$_data['userId']);
			$this->update($_arr, $where);
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
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
			$timeTitle = "title_en";
			if ($currentLang==1){
				$timeTitle = "title";
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
				,(SELECT sj.subject_lang FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectLang
				,(SELECT CASE 
							WHEN sj.subject_lang =1 THEN sj.subject_titlekh
							ELSE sj.subject_titleen
						END FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
				,(SELECT v.".$label." FROM rms_view AS v WHERE v.key_code=schDetail.day_id AND v.type=18 LIMIT 1)AS daysTitle
				,(SELECT t.$timeTitle FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
				,(SELECT t.$timeTitle FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
				
				,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
				,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
				
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,`g`.`group_code` AS groupCode
				,`g`.`degree` AS degree_id
				,`g`.`grade` AS gradeId
				,COALESCE((SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1),'N/A') AS `roomName`
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
			
			$annual='Annual';
			$colunmname='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$month = "month_en";
			$subjectTitle='subject_titleen';
			if ($currentLang==1){
				$annual='ប្រចាំឆ្នាំ';
				$colunmname='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$month = "month_kh";
				$subjectTitle='subject_titlekh';
			}
			
			$sql="
				SELECT 
					grd.*
					,CONCAT(grd.academicYear,COALESCE(grd.examType,0),COALESCE(grd.forSemester,0),COALESCE(grd.forMonth,0)) AS rowMonthlyScore
					,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
					,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
					,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
					,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
					,CASE
						WHEN grd.examType = 2 THEN grd.forSemester
						WHEN grd.examType = 3 THEN '".$annual."'
						ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
					END AS forMonthTitle
					,g.group_code AS  groupCode
					,(SELECT
						CASE
							WHEN sj.`subject_lang` = 2 THEN sj.`subject_titleen`
							ELSE sj.subject_titlekh
						END  FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1
					) AS subjectTitle
					,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
					,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
					,COALESCE((SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1),'N/A') AS roomName
					,(SELECT sEtStt.title FROM rms_score_entry_setting AS sEtStt WHERE sEtStt.id = grd.settingEntryId LIMIT 1) titleScoreFor
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.group_id =grd.groupId LIMIT 1 ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.stu_id=gds.stu_id AND gds.group_id =grd.groupId LIMIT 1 ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.stu_id=gds.stu_id AND gds.group_id =grd.groupId LIMIT 1 ) AS femaleStudent
					
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
			if(!empty($_data['subjectId'])){
				$sql.=" AND grd.subjectId =".$_data['subjectId'];
			}
			$sql.=" ORDER BY grd.id DESC ";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
				
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
					,CASE 
							WHEN aTs.endDate IS NOT NULL
							THEN aTs.endDate
							ELSE sett.examEndDate
						END AS endDateExam
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
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
					
					
					,(SELECT subj.$subjectTitle FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitle
					,(SELECT subj.subject_titleen FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitleEng
					,(SELECT subj.subject_titlekh FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitleKh
					,(SELECT subj.shortcut FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS shortcut
					,(SELECT subj.subject_lang FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectLang
					
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
					,if(COALESCE(tmp.id,'0')>0, (SELECT gr.isLock FROM rms_grading AS gr WHERE gr.gradingTmpId = COALESCE(tmp.id,'0') LIMIT 1 ),0) AS isLockGrading
					
					,COALESCE((SELECT vSt.`keyValue` FROM `rms_setting` AS vSt WHERE vSt.`keyName`='criteriaLimitation' LIMIT 1),0) AS criteriaLimitSetting
			";
			
			$sql.=" FROM 
						`rms_group_subject_detail` AS gsjb
						JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1 AND g.is_pass=2
						JOIN `rms_score_entry_setting` AS sett ON FIND_IN_SET(g.degree,sett.degreeId) AND sett.status = 1 AND sett.academicYear = g.academic_year
							LEFT JOIN (rms_student AS s JOIN `rms_group_detail_student` AS gds ON  gds.stu_id = s.stu_id AND s.status = 1 AND s.customer_type=1 AND gds.stop_type = 0) ON  gds.group_id = g.id
							LEFT JOIN (`rms_grading_tmp` AS tmp JOIN `rms_exametypeeng` AS cri ON cri.id = tmp.criteriaId AND cri.criteriaType=2 ) ON tmp.settingEntryId = sett.id AND tmp.groupId = gsjb.group_id AND tmp.subjectId = gsjb.subject_id
							LEFT JOIN `rms_allowed_teacher_score_setting` AS aTs 
								ON aTs.teacherId = gsjb.teacher AND g.id = aTs.group AND FIND_IN_SET(gsjb.subject_id,(aTs.subjectId))
									AND aTs.endDate > sett.examEndDate
				";
			$sql.=' WHERE gsjb.teacher='.$userId;
			$sql.=' AND g.`gradingId` !=0 ';
			
			$date = new DateTime();
			$currentDate = $date->format("Y-m-d");
			$sql.= " AND sett.fromDate <= '".$currentDate."' 
					 AND CASE 
							WHEN aTs.endDate IS NOT NULL
							THEN aTs.endDate
							ELSE sett.examEndDate
						END >='".$currentDate."' 
					";
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
			$sql.="
				ORDER BY 
					sett.id DESC
					,CASE 
						WHEN tmp.criteriaId IS NOT NULL
						THEN '1'
						ELSE '0'
					END ASC
					,`g`.`group_code` ASC
					, gsjb.subject_id ASC
			";
			$row = $_db->fetchAll($sql);
			$row = empty($row) ? array() : $row;
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
					,CASE WHEN sett.examType = 2
								THEN gsjb.semester_max_score
							ELSE gsjb.max_score
					END AS maxScoreSubject
					,sttD.criteriaId
					,sttD.timeInput
					,sttD.pecentage_score AS percentageScore
					,COALESCE(sttD.subCriterialTitleKh,'') AS subCriteriaTitleKh
					,COALESCE(sttD.subCriterialTitleEng,'') AS subCriteriaTitleEng
					,sttD.subjectId AS subjectIdInCriteriaSetting
					
					,(SELECT crit.$colunmName FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitle
					,(SELECT crit.title FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleKh
					,(SELECT crit.title_en FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleEng
					,(SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaType
					
					,g.group_code AS groupCode
					
					,(SELECT subj.$subjectTitle FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitle
					,(SELECT subj.subject_titleen FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitleEng
					,(SELECT subj.subject_titlekh FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitleKh
					,(SELECT subj.shortcut FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS shortcut
					,(SELECT subj.subject_lang FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectLang
					
					,(SELECT COUNT(tmp.id) FROM `rms_grading_tmp` AS tmp WHERE  tmp.settingEntryId = sett.id AND  tmp.groupId = g.id AND tmp.subjectId = gsjb.subject_id AND tmp.criteriaId = sttD.criteriaId LIMIT 1 ) AS totalInputted
			";
			
			$sql.=" FROM 
						`rms_group_subject_detail` AS gsjb
						JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1 
						JOIN `rms_score_entry_setting` AS sett ON FIND_IN_SET(g.degree,sett.degreeId) AND sett.status = 1
						
						LEFT JOIN ( `rms_scoreengsettingdetail` AS sttD JOIN `rms_scoreengsetting` AS stt ON stt.id = sttD.score_setting_id ) ON stt.degreeId = g.degree AND stt.id = g.gradingId AND sttD.forExamType=sett.`examType` 
							AND (sttD.subjectId =gsjb.subject_id OR sttD.subjectId=0) 
						LEFT JOIN `rms_allowed_teacher_score_setting` AS aTs ON aTs.teacherId = gsjb.teacher AND g.id = aTs.group AND FIND_IN_SET(gsjb.subject_id,(aTs.subjectId)) AND aTs.endDate > sett.examEndDate
				";
			$sql.=' WHERE gsjb.teacher='.$userId;
			$sql.=' AND g.`gradingId` !=0 AND g.is_pass !=3 '; //មិនស្មើរថ្នាក់ដែលរៀនចប់
			$sql.=' AND CASE WHEN sett.examType =2 THEN gsjb.amount_subject_sem 
						ELSE gsjb.amount_subject 
						END  > 0 
				'; 
			
			$sql.="
				AND (SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) 
					> CASE 
						WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = g.`gradingId` AND sttDi.subjectId =  gsjb.subject_id ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
						ELSE '0'
					END 
				AND sttD.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = g.`gradingId` AND sttDi.subjectId =  gsjb.subject_id ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
					END 
			";
			
			$date = new DateTime();
			$currentDate = $date->format("Y-m-d");
			$sql.= " AND sett.fromDate <= '".$currentDate."' 
					 AND CASE 
							WHEN aTs.endDate IS NOT NULL
							THEN aTs.endDate
							ELSE sett.examEndDate
						END >='".$currentDate."'  
					";
			$sql.=" GROUP BY 
						sett.id,
						gsjb.group_id,
						gsjb.subject_id,
						sttD.id,
						sttD.criteriaId 
				";
			$sql.=' ORDER BY 
							sett.id DESC,
							 g.group_code ASC  
							,gsjb.subject_id ASC
							,(SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) ASC
							,sttD.criteriaId ASC
							,sttD.subjectId DESC
				';
			$row = $_db->fetchAll($sql);
			
			if(!empty($row)){ 
				$newArray = array();
				$criteriaId = "";
				$groupId = "";
				$subjectId = "";
				foreach($row as $criteria){
					if( ($criteriaId != $criteria["criteriaId"]) OR ($groupId != $criteria["groupId"]) ){
						array_push($newArray, $criteria);
					}else if($groupId == $criteria["groupId"]){
						if(($subjectId != $criteria["subjectIdValue"]) AND ($criteriaId == $criteria["criteriaId"])){
							array_push($newArray, $criteria);
						}
					}
					$criteriaId = $criteria["criteriaId"];
					$groupId = $criteria["groupId"];
					$subjectId = $criteria["subjectIdValue"];
				}
				$row = $newArray;
			}
			$row = empty($row) ? array() : $row;
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
				$grade = "title";
				$degree = "title";
			}else{ // English
				$label = "name_en";
				$branch = "branch_nameen";
				$grade = "title_en";
				$degree = "title_en";
			}
			$sql="SELECT 
					s.stu_id AS id
					,s.stu_id AS studentId
					,COALESCE(s.stu_code,'') AS stuCode
					,COALESCE(s.stu_khname,'') AS stuNameInKhmer
					,CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS `name`
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
					,(SELECT v.$label FROM rms_view AS v WHERE v.type=2 and v.key_code=s.sex LIMIT 1 ) as genderTitle
					,(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name
					
					,g.`academic_year` AS academic_year
					,g.`degree` AS degree
					,g.`grade` AS grade
					,g.`session` AS `session`
					
					
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academicYearTitle
					,(SELECT v.$label FROM rms_view AS v WHERE v.type=4 AND v.key_code = g.`session` LIMIT 1) AS `sessionName`
					,(SELECT gr.$grade FROM rms_itemsdetail AS gr WHERE gr.id=g.grade AND gr.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT dg.$degree FROM rms_items AS dg WHERE dg.id=g.degree AND dg.type=1 LIMIT 1) AS degreeTitle
					
				
			";
			$sqlColScoreValue=",'0' AS scoreValue
								,'0' AS tmpDetailId 
							";
			if(!empty($_data['gradingTmpId'])){
				$gradingTmpId = $_data['gradingTmpId'];
				$sqlColScoreValue = "
							,(SELECT COALESCE(tmpD.totalGrading,'0') FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` = $gradingTmpId LIMIT 1 ) AS scoreValue
							,(SELECT COALESCE(tmpD.id,'0') FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` = $gradingTmpId LIMIT 1 ) AS tmpDetailId
					";
			}else if(!empty($_data['forEvaluationInfo'])){
				$scoreId = empty($_data['scoreId']) ? "0" : $_data['scoreId'];
				$sqlColScoreValue="
					,FIND_IN_SET( 
						COALESCE((SELECT ms.total_avg FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0'), 
						(    
							SELECT 
								GROUP_CONCAT( dd.total_avg ORDER BY dd.total_avg DESC ) 
							FROM rms_score_monthly AS dd 
							WHERE  dd.`score_id`= $scoreId
						)
					) AS ranking
					,COALESCE((SELECT ms.total_avg FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0') AS totalAverage
					,COALESCE((SELECT ms.total_score FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0') AS totalScore
					,IF( COALESCE((SELECT ms.total_score FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0') >0 AND COALESCE((SELECT ms.totalMaxScore FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0') >0, 
							(SELECT sd.metion_grade AS mention
							FROM `rms_metionscore_setting_detail` AS sd,
								`rms_metionscore_setting` AS s
							WHERE s.id = sd.metion_score_id
								AND s.academic_year=g.`academic_year`
								AND degree = g.`degree`
								AND FORMAT((
								COALESCE((SELECT ms.total_score FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0')
								/(COALESCE((SELECT ms.totalMaxScore FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0')/100)),2) >=sd.max_score
								ORDER BY sd.max_score DESC
								LIMIT 1)
								,NULL 
						) AS mentionGrade
				";
				
				if($_data['forEvaluationInfo']=="1"){//Evaluation
					$stringWhere = " AND ass.scoreId = $scoreId ";
					$stringWherePrevious = " AND ass.scoreId < $scoreId ";
					if(!empty($_data['assessmentId'])){
						$stringWhere = " AND ass.id = ".$_data['assessmentId'];
					}
					$sqlColScoreValue.="
						,COALESCE((SELECT assD.teacherComment FROM `rms_studentassessment` AS ass,`rms_studentassessment_detail` AS assD WHERE ass.id = assD.assessmentId $stringWherePrevious AND gds.stu_id=assD.studentId AND ass.groupId = gds.group_id ORDER BY assD.teacherComment DESC LIMIT 1),'') AS previousTeacherComment
						,COALESCE((SELECT assD.teacherComment FROM `rms_studentassessment` AS ass,`rms_studentassessment_detail` AS assD WHERE ass.id = assD.assessmentId $stringWhere AND gds.stu_id=assD.studentId AND ass.groupId = gds.group_id ORDER BY assD.teacherComment DESC LIMIT 1),'') AS teacherComment
						,COALESCE((SELECT assD.commentId FROM `rms_studentassessment` AS ass,`rms_studentassessment_detail` AS assD WHERE ass.id = assD.assessmentId $stringWhere AND gds.stu_id=assD.studentId AND ass.groupId = gds.group_id LIMIT 1),'0') AS isEvaluated
					";
				}
			}
			$sql.=$sqlColScoreValue;
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds 
					JOIN `rms_group` AS g ON g.`id` = gds.group_id
					LEFT JOIN rms_student AS s  ON  gds.stu_id = s.stu_id 
			";
			
			
			$sql.="WHERE 
					gds.itemType=1 
					AND s.status = 1 
					AND s.customer_type=1
					AND gds.stop_type = 0 ";
			
			$where=' ';
			$where.=' AND gds.group_id='.$groupId;
			$where.=' GROUP By gds.stu_id ';
			
			$order_by = " ORDER BY  ";
			if(!empty($_data['forEvaluationInfo'])){
				$scoreId = empty($_data['scoreId']) ? "0" : $_data['scoreId'];
				$order_by.="
					COALESCE((SELECT ms.total_avg FROM `rms_score_monthly` AS ms WHERE ms.score_id = $scoreId AND ms.student_id = s.stu_id LIMIT 1),'0') DESC,
				";
			}
			
			$order_by.=" s.stu_id ASC ";
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
	
	
	function checkTimeSecondBeforeSubmit($scoreInfo){
		$db = $this->getAdapter();
		$settingEntryId = empty($scoreInfo['settingEntryId']) ? 0 : $scoreInfo['settingEntryId'];
		$academicYearId = empty($scoreInfo['academicYearId']) ? 0 : $scoreInfo['academicYearId'];
		$groupId		= empty($scoreInfo['groupId']) ? 0 : $scoreInfo['groupId'];
		$examType 		= empty($scoreInfo['examType']) ? 0 : $scoreInfo['examType'];
		$forSemester 	= empty($scoreInfo['forSemester']) ? 0 : $scoreInfo['forSemester'];
		$forMonth 		= empty($scoreInfo['forMonth']) ? 0 : $scoreInfo['forMonth'];
		$subjectId 		= empty($scoreInfo['subjectId']) ? 0 : $scoreInfo['subjectId'];
		$criteriaId 	= empty($scoreInfo['criteriaId']) ? 0 : $scoreInfo['criteriaId'];
		$teacherId 		= empty($scoreInfo['userId']) ? 0 : $scoreInfo['userId'];
		$sql ="
			SELECT 
					gtmp.`createDate`
			FROM 
				`rms_grading_tmp` AS gtmp
			WHERE 1 
					AND gtmp.`groupId` =$groupId
					AND gtmp.`settingEntryId`=$settingEntryId
					AND gtmp.`examType` =$examType
					AND gtmp.`forMonth`=$forMonth
					AND gtmp.`criteriaId` =$criteriaId
					AND gtmp.`subjectId`=$subjectId 
					AND gtmp.`teacherId`=$teacherId 
					ORDER BY gtmp.`id` DESC
				LIMIT 1 ";
		$row = $db->fetchRow($sql);
		
		if(!empty($row)){
			$secondLimit="10";
			$createDate = $row["createDate"];
			$newCreateDate = new DateTime($createDate);
			$newCreateDate->add(new DateInterval('PT'.$secondLimit.'S')); 
			$createDateNew = $newCreateDate->format('Y-m-d H:i:s');
			
			$todayDate = new DateTime();
			$timeToday = $todayDate->format('Y-m-d H:i:s');
			
			$timeToday = date_create($timeToday);
			$timeCreateDateNew = date_create($createDateNew);
			
			if($timeToday > $timeCreateDateNew){
				return false;
			}
			return true;
		}
		return false;
	}
	
	function submitCriteriaScore($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$scoreSettingEntryPost 	 = empty($_data['scoreSettingEntry'])?null:$_data['scoreSettingEntry'];
			
			$scoreInfo = Zend_Json::decode($scoreSettingEntryPost);
			$scoreInfo["subjectId"] =$_data['subjectId'];
			$scoreInfo["criteriaId"] =$_data['criteriaId'];
			$scoreInfo["userId"] = $_data['userId'];
			
			
			$listFromPost 	 = empty($_data['studentScoreListSubmit'])?null:$_data['studentScoreListSubmit'];
			$studentScoreList 	 = Zend_Json::decode($listFromPost);
			
			$checkingSubmitCriterial = $this->checkTimeSecondBeforeSubmit($scoreInfo);
			if(empty($checkingSubmitCriterial)){
				$arr = array(
    				'branchId'			=>$scoreInfo['branchId'],
    				'settingEntryId'	=>$scoreInfo['settingEntryId'],
    				'gradingSettingId'	=>$scoreInfo['gradingSettingId'],
    				'academicYear'		=>$scoreInfo['academicYearId'],
    				'groupId'			=>$scoreInfo['groupId'],
    				'examType'			=>$scoreInfo['examType'],
    				'forSemester'		=>$scoreInfo['forSemester'],
    				'forMonth'			=>$scoreInfo['forMonth'],
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
			}
			
    		
			
			
			$db->commit();
			$result = array(
					'status' =>true,
					'value' =>$gradinTmpId,
			);
			return $result;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
    		return $result;
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
					,(SELECT sett.title 	FROM rms_score_entry_setting AS sett WHERE sett.`id` = gTmp.`settingEntryId` LIMIT 1) AS settingTitle
					,(SELECT sett.fromDate 	FROM rms_score_entry_setting AS sett WHERE sett.`id` = gTmp.`settingEntryId` LIMIT 1) AS startDateCriteria
					,(SELECT sett.endDate 	FROM rms_score_entry_setting AS sett WHERE sett.`id` = gTmp.`settingEntryId` LIMIT 1) AS endDateCriteria
					,(SELECT sett.examFromDate 	FROM rms_score_entry_setting AS sett WHERE sett.`id` = gTmp.`settingEntryId` LIMIT 1) AS startDateExam
					,(SELECT sett.examEndDate 	FROM rms_score_entry_setting AS sett WHERE sett.`id` = gTmp.`settingEntryId` LIMIT 1) AS endDateExam
					
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.gradingId AS gradingSettingId
					,g.academic_year AS academicYearId
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
					
					
					,(SELECT subj.$subjectTitle 	FROM `rms_subject` AS subj 	WHERE subj.id = gTmp.subjectId   LIMIT 1) AS subjectTitle
					,(SELECT subj.subject_titleen 	FROM `rms_subject` AS subj	WHERE subj.id = gTmp.subjectId   LIMIT 1) AS subjectTitleEng
					,(SELECT subj.subject_titlekh 	FROM `rms_subject` AS subj	WHERE subj.id = gTmp.subjectId   LIMIT 1) AS subjectTitleKh
					,(SELECT subj.shortcut 			FROM `rms_subject` AS subj 	WHERE subj.id = gTmp.subjectId   LIMIT 1) AS subjectShortCut
					,(SELECT subj.subject_lang 		FROM `rms_subject` AS subj 	WHERE subj.id = gTmp.subjectId   LIMIT 1) AS subjectLang
					
					,COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) AS totalStudent
					,COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) AS maleStudent
					,COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) AS femaleStudent
					
					,(SELECT COUNT(IF(tmpD.totalGrading > 0  , s.sex, NULL))  FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.gradingId = gTmp.id LIMIT 1) AS issuedScore
					,(SELECT COUNT(IF(tmpD.totalGrading <= 0  , s.sex, NULL))  FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.gradingId = gTmp.id LIMIT 1) AS noScore					
					
			";
			
			$sql.=" FROM `rms_grading_tmp` AS gTmp
						JOIN `rms_group` AS g ON g.`id` = gTmp.`groupId`
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
					
					,(SELECT COALESCE(g.`gradingId`,'0') FROM `rms_group` AS g  WHERE g.id = gds.group_id LIMIT 1) AS gradingId
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
						,COALESCE((SELECT GROUP_CONCAT(tmpD.subCriterialTitleKh) FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId LIMIT 1),'') AS subCriteriaTitleKh
						,COALESCE((SELECT GROUP_CONCAT(tmpD.subCriterialTitleEng) FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId LIMIT 1),'') AS subCriteriaTitleEng
						,(SELECT GROUP_CONCAT(tmpD.totalGrading) FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId ) AS subCriteriaScore
						,(SELECT  IF(SUM(tmpD.totalGrading) >0, FORMAT((COALESCE(SUM(tmpD.totalGrading),'0') / COUNT(tmpD.id) ),2) ,'0') FROM `rms_grading_detail_tmp` AS tmpD WHERE tmpD.studentId = s.stu_id AND tmpD.`gradingId` =$gradingTmpId) AS criteriaScoreAvg
						,(SELECT COALESCE(grdT.totalAverage,'0') FROM `rms_grading_total` AS grdT WHERE grdT.studentId = s.stu_id AND grdT.`gradingTmpId` =$gradingTmpId LIMIT 1) AS grandTotalAverageScore
					";
			}
			$sql.=$sqlColScoreValue;
			if(!empty($_data['forIssueMonthly'])){
				$subjectId = empty($_data['subjectId'])?0:$_data['subjectId'];
				$sql.=",COALESCE((SELECT subd.`max_score` FROM `rms_group_subject_detail` AS subd WHERE gds.group_id = subd.`group_id` AND subd.`subject_id`= $subjectId LIMIT 1 ),0) AS maxSubjectScore";
				$sql.=",COALESCE((SELECT subd.`semester_max_score` FROM `rms_group_subject_detail` AS subd WHERE gds.group_id = subd.`group_id` AND subd.`subject_id`= $subjectId LIMIT 1 ),0) AS maxSubjectScoreSemester";
			}
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds JOIN rms_student AS s ON gds.stu_id = s.stu_id 
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
			$criteriaInfoList = empty($criteriaInfoList) ? array() : $criteriaInfoList; 
			
			$_data['detailCriteriaStudent'] = 1;
			$detailCriteriaStudent = $this->getCriteriaScoreOfStudent($_data);
			$detailCriteriaStudent = empty($detailCriteriaStudent) ? array() : $detailCriteriaStudent;
			$arrayResult = array(
				'studentList' 		 =>$row,
				"criteriaInfoList"   =>$criteriaInfoList,
				"detailCriteriaStudent"   =>$detailCriteriaStudent
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
		
		$sqlGrad="
			SELECT entSett.*  FROM rms_score_entry_setting AS entSett WHERE  entSett.id = $settingEntryId LIMIT 1
		";
		$entrySetting = $db->fetchRow($sqlGrad);
		$examType = empty($entrySetting["examType"]) ? 0 : $entrySetting["examType"];
		
		$colunmName='title_en';
		if ($currentLang==1){
			$colunmName='title';
		}
		$sql="
			SELECT 
				sttD.criteriaId
				,sttD.pecentage_score AS percentageScore
				
				,(SELECT crit.$colunmName 	FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitle
				,(SELECT crit.title 		FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleKh
				,(SELECT crit.title_en 		FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaTitleEng
				,(SELECT crit.criteriaType 	FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) AS criteriaType
				
				,gds.stu_id AS studentId
				
				,COALESCE(g.`gradingId`,'0') AS gradingId
				,g.group_code AS groupCode
				,tmp.subjectId AS subjectId
				,tmp.settingEntryId
				,(SELECT subj.subject_titleen 	FROM `rms_subject` AS subj	WHERE subj.id = tmp.subjectId  LIMIT 1) AS subjectTitleEng
				,(SELECT subj.subject_titlekh 	FROM `rms_subject` AS subj	WHERE subj.id = tmp.subjectId  LIMIT 1) AS subjectTitleKh
				,(SELECT subj.shortcut 			FROM `rms_subject` AS subj 	WHERE subj.id = tmp.subjectId  LIMIT 1) AS subjectShortCut
				,(SELECT subj.subject_lang 		FROM `rms_subject` AS subj 	WHERE subj.id = tmp.subjectId  LIMIT 1) AS subjectLang
				
					
				,tmp.createDate AS createDate
				,tmp.createDate AS dateInput
		";
		$sqlCol = "
				
				,COALESCE(SUM(IF(sttD.`criteriaId`  = tmp.`criteriaId`, tmpD.totalGrading, 0)),'0') AS totalCriteriaScore
				,COALESCE((SELECT COUNT(tmp1.id) FROM `rms_grading_tmp` AS tmp1 WHERE tmp.subjectId = tmp1.subjectId AND tmp1.settingEntryId =".$settingEntryId." AND tmp1.criteriaId = sttD.criteriaId AND g.id = tmp1.groupId LIMIT 1 ),'0') AS inputTime
	
				,FORMAT(
					IF(
					COALESCE((SELECT COUNT(tmp1.id) FROM `rms_grading_tmp` AS tmp1 WHERE tmp.subjectId = tmp1.subjectId AND tmp1.settingEntryId =".$settingEntryId." AND tmp1.criteriaId = sttD.criteriaId AND g.id = tmp1.groupId LIMIT 1 ),'0')>0 AND COALESCE(SUM(tmpD.totalGrading),'0') >0, 
					(SUM(IF(sttD.`criteriaId`  = tmp.`criteriaId`, tmpD.totalGrading, 0)) / COALESCE((SELECT COUNT(tmp1.id) FROM `rms_grading_tmp` AS tmp1 WHERE tmp.subjectId = tmp1.subjectId AND tmp1.settingEntryId =".$settingEntryId." AND tmp1.criteriaId = sttD.criteriaId AND g.id = tmp1.groupId LIMIT 1 ),'0')),
					0)
					,2) AS averageScore
		";
		$sqlGrouby="
			GROUP BY
				g.id,
				tmp.subjectId,
				sttD.criteriaId,
				gds.stu_id
		";
		$sqlWhereC = "";
		if(!empty($_data["detailCriteriaStudent"])){
			$sqlCol = "
				,tmpD.totalGrading
			";
			$sqlWhereC = " AND tmp.`criteriaId` = sttD.`criteriaId` ";
			$sqlGrouby = "
				GROUP BY
					g.id,
					tmp.subjectId,
					tmp.id,
					gds.stu_id
			";
		}
		$sql.=$sqlCol;
		$sql.="
		FROM 
		
			`rms_scoreengsettingdetail` AS sttD
			JOIN `rms_group` AS g ON g.`gradingId` = sttD.`score_setting_id`
				LEFT JOIN `rms_group_detail_student` AS gds ON  gds.group_id = g.id AND gds.stop_type = 0
				LEFT JOIN (`rms_grading_detail_tmp` AS tmpD JOIN `rms_grading_tmp` AS tmp ON tmp.id =tmpD.gradingId )  ON  tmp.settingEntryId =".$settingEntryId." AND gds.stu_id = tmpD.studentId AND g.id = tmp.groupId
			
			";
		
		
		
		$sql.=" WHERE 1 AND sttD.forExamType=$examType ";
		$sql.=$sqlWhereC;
		$sql.="
				AND (SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) 
					>= CASE 
						WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = g.`gradingId` AND sttDi.subjectId =  ".$subjectId."  ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
						ELSE '0'
					END 
				AND sttD.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = g.`gradingId` AND sttDi.subjectId =  ".$subjectId."  ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
					END 
			";
		$sql.="
				
				AND g.is_use=1 
				AND g.id = ".$groupId." 
				AND tmp.subjectId = ".$subjectId."  
				AND tmp.teacherId=".$userId." 
				AND (SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) !=2
			";
		$sql.=$sqlGrouby;
		$sql.=" ORDER BY 
					`g`.`group_code` ASC
					,gds.stu_id ASC
					,(SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = sttD.criteriaId LIMIT 1) ASC 
					,sttD.criteriaId ASC
					,tmpD.id ASC
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
			//if($key==0){$maxAttendance = $rs["attendanceScore"];}
			if($key==0){$maxAttendance = empty($rowStudentInfo["maxSubjectScore"]) ? 0 : $rowStudentInfo["maxSubjectScore"];}
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
			
			$scoreInfo["criteriaId"] = $criteriaId;
			$scoreInfo["subjectId"] = $subjectId;
			$scoreInfo["userId"] = $_data['userId'];
			$checkingSubmitCriterial = $this->checkTimeSecondBeforeSubmit($scoreInfo);
			
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
				$gradingTmpId = 0;
				if(empty($checkingSubmitCriterial)){
					$arr['dateInput'] = date("Y-m-d H:i:s");
					$arr['createDate'] = date("Y-m-d H:i:s");
					$gradingTmpId = $this->insert($arr);
				}
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
				$idGrading = 0;
				if(empty($checkingSubmitCriterial)){
					$arrGrading['dateInput'] = date("Y-m-d H:i:s");
					$arrGrading['createDate'] = date("Y-m-d H:i:s");
					$idGrading = $this->insert($arrGrading);
				}
			}
			if(!empty($gradingTmpId) && !empty($idGrading) ){
				$subCriteriaTitle = empty($criteriaInfo['subCriteriaTitleKh']) ? "" : $criteriaInfo['subCriteriaTitleKh'];
				$subjectIdInCriteriaSetting = empty($criteriaInfo['subjectIdInCriteriaSetting']) ? "0" : $criteriaInfo['subjectIdInCriteriaSetting'];
				
				$subCriteriaTitleList = explode(',', $criteriaInfo['subCriteriaTitleKh']);
				$subCriteriaAmount = count($subCriteriaTitleList);
				
				$maxScoreSubject = empty($criteriaInfo['maxScoreSubject']) ? "" : $criteriaInfo['maxScoreSubject'];
				$percentageScore = empty($criteriaInfo['percentageScore']) ? "" : $criteriaInfo['percentageScore'];
				
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
								'percentage'			=> $percentageScore,
								'subCriterialTitleKh'	=> $i,
								'subCriterialTitleEng'	=> $subCriteriaTitleEngExp[$key],
								'dateInput'		=>date("Y-m-d H:i:s"),
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
								'percentage'			=> $percentageScore,
								'dateInput'		=>date("Y-m-d H:i:s"),
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
									'dateInput'				=> $tmpInfo["createDate"],
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
							if(!empty($criteriaStudent["createDate"])){
								$arrGradingDetail['dateInput'] = $criteriaStudent["createDate"];
							}
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
				,tmp.createDate AS createDate
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
				AND tmp.`settingEntryId` = ".$_data["settingEntryId"]."
				AND tmp.`subjectId` = ".$_data["subjectId"]."
				AND tmpD.`studentId` = ".$_data["studentId"]."
			ORDER BY tmp.id ASC
		";
		return $db->fetchAll($sql);
	}
	
	function getGradingScoreInfomation($_data){
		$db = $this->getAdapter();
		$gradingScoreId = empty($_data['gradingId'])?0:$_data['gradingId'];
		$sql="
			SELECT 
				grS.* 
				,g.`gradingId` AS gradingSettingId

			FROM 
				`rms_grading` AS grS
				LEFT JOIN  `rms_group` AS g ON g.id = grS.`groupId`
			WHERE grs.id =$gradingScoreId
		";
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
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
					,IF(COALESCE(grdT.`totalAverage`,'0') >0 AND COALESCE(grdT.`maxScore`,'0') >0, 
							(SELECT sd.metion_grade AS mention
							FROM `rms_metionscore_setting_detail` AS sd,
								`rms_metionscore_setting` AS s
							WHERE s.id = sd.metion_score_id
								AND s.academic_year=g.`academic_year`
								AND degree = g.`degree`
								AND FORMAT((COALESCE(grdT.`totalAverage`,'0')/(COALESCE(grdT.`maxScore`,'0')/100)),2) >=sd.max_score
								ORDER BY sd.max_score DESC
								LIMIT 1)
								,NULL 
					) AS mentionGrade
				
			";
			$sql.="
				FROM 
					`rms_group_detail_student` AS gds 
					JOIN `rms_student` AS s ON s.`stu_id` = gds.`stu_id`
					JOIN rms_group AS g ON g.`id` = gds.`group_id`
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
			
			$scoreGrading = $this->getGradingScoreInfomation($_data);
			$_data['subjectId'] = empty($scoreGrading["subjectId"]) ? 0 : $scoreGrading["subjectId"];
			$_data['gradingSettingId'] = empty($scoreGrading["gradingSettingId"]) ? 0 : $scoreGrading["gradingSettingId"];
			
			$gradingSystem = $this->getGradingSystemInfo($_data);
			$criteriaInfoList = $this->groupGradingDetail($_data);
			$_data['summaryCriteriaStudent'] = 1;
			$criteriaSummaryList = $this->groupGradingDetail($_data);
			$arrayResult = array(
				'studentList' 		 	=>$row,
				"criteriaInfoList"   	=>$criteriaInfoList,
				"criteriaSummaryList"   =>$criteriaSummaryList,
				"gradingSystem"   		=>$gradingSystem,
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
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$colunmName='title_en';
		if ($currentLang==1){
			$colunmName='title';
		}
		$sql="
			SELECT 
				grdd.`gradingId`
				,grdd.`dateInput`
				,gds.`stu_id` AS studentId
				,grdd.`criteriaId`
				
				,(SELECT cri.$colunmName	FROM `rms_exametypeeng` AS cri WHERE cri.id = grdd.`criteriaId` LIMIT 1) AS criteriaTitle
				,(SELECT cri.title 			FROM `rms_exametypeeng` AS cri WHERE cri.id = grdd.`criteriaId` LIMIT 1) AS criteriaTitleKh
				,(SELECT cri.title_en 		FROM `rms_exametypeeng` AS cri WHERE cri.id = grdd.`criteriaId` LIMIT 1) AS criteriaTitleEng
				,(SELECT cri.criteriaType 	FROM `rms_exametypeeng` AS cri WHERE cri.id = grdd.`criteriaId` LIMIT 1) AS criteriaType
				
				,grdd.`totalGrading`
				,grdd.`percentage`
				,grdd.`subCriterialTitleEng`
				,grdd.`subCriterialTitleKh`
			
		";
		$sqlCol="";
		$sqlGroupBy="";
		if(!empty($_data['summaryCriteriaStudent'])){
			$sqlCol="
				,SUM(grdd.`totalGrading`) AS totalGradingScore
				,COUNT(grdd.`criteriaId`) AS timesInputCriteria
				,FORMAT(IF(COUNT(grdd.`criteriaId`)>0 AND COALESCE(SUM(grdd.`totalGrading`),'0') >0, (SUM(grdd.totalGrading) / COUNT(grdd.`criteriaId`)),0),2) AS averageScore
			";
			$sqlGroupBy="
				GROUP BY grdd.`criteriaId`,
						grdd.`studentId`
			";
		}
		$sql.= $sqlCol;
		$sql.="
			FROM 
				`rms_group_detail_student` AS gds 
				LEFT JOIN `rms_grading_detail` AS grdd ON grdd.`studentId` = gds.`stu_id`
			WHERE 
				gds.`group_id` = $groupId 
				AND grdd.`gradingId` = $gradingId
		";
		$sql.= $sqlGroupBy;
		$sql.="
			ORDER BY 
				grdd.`studentId` ASC
				,(SELECT cri.criteriaType 	FROM `rms_exametypeeng` AS cri WHERE cri.id = grdd.`criteriaId` LIMIT 1) ASC
				,grdd.`criteriaId` ASC
				,grdd.`id` ASC
		";
		return $db->fetchAll($sql);
	}
	
	function getGradingSystemInfo($_data){
		$db = $this->getAdapter();
		$gradingSettingId = empty($_data['gradingSettingId'])?0:$_data['gradingSettingId'];
		$subjectId = empty($_data['subjectId'])?0:$_data['subjectId'];
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$colunmName='title_en';
		if ($currentLang==1){
			$colunmName='title';
		}
		$sql="
			SELECT 
				gSet.id AS gradingSettingId
				,cri.`title` AS criteriaTitleKh
				,cri.`title_en` AS criteriaTitleEng
				,gSetD.`pecentage_score` AS percentageScore
				,gSetD.`subCriterialTitleEng` AS subCriteriaTitleEng
				,gSetD.`subCriterialTitleKh` AS subCriteriaTitleKh
		";
		$sql.="
				FROM `rms_scoreengsetting` AS gSet
					JOIN `rms_scoreengsettingdetail`  AS gSetD ON gSet.`id` = gSetD.`score_setting_id`
					LEFT JOIN `rms_exametypeeng` AS cri ON cri.id = gSetD.`criteriaId`
			";
		$sql.="
			WHERE gSet.`id` = gSetD.`score_setting_id`
				AND gSet.id = $gradingSettingId
				
			";
			
		$sql.="
				AND cri.`criteriaType`
					>= CASE 
						WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = gSet.id AND sttDi.subjectId =  ".$subjectId."  ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
						ELSE '0'
					END 
				AND gSetD.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id = gSet.id AND sttDi.subjectId =  ".$subjectId."  ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
					END 
			";
		$sql.="
			GROUP BY gSetD.`criteriaId` 
			ORDER BY 
				cri.`criteriaType` ASC
				,cri.id ASC
		";
		return $db->fetchAll($sql);
	}
	
	public function getViewListByType($search){
		$db=$this->getAdapter();
		
		$currentLang = empty($search['currentLang']) ? 1:$search['currentLang'];
		$viewType = empty($search['viewType']) ? 0:$search['viewType'];
		
		$label = "name_en";
		if($currentLang==1){// khmer
			$label = "name_kh";
		}
		$sql="
			SELECT
				v.key_code as id,
				v.$label as name
			FROM
				rms_view as v
			WHERE
				v.status = 1
				AND v.type = $viewType
				
			";
		if($viewType==9){
			$sql.=" AND v.key_code NOT IN (0,5,6) ";
		}
		$sql.=" ORDER BY v.key_code ASC ";
		return $db->fetchAll($sql);
	}
	public function getFormOptionSelect($_data){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$getControlType = empty($_data['getControlType'])?"status":$_data['getControlType'];
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['branchId'] = empty($_data['branchId'])?0:$_data['branchId'];
			$row=array();
			if($getControlType=="status"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "ប្រើប្រាស់" : "Active"),
					array("id"=>2,"name"=>$currentLang==1 ? "មិនប្រើប្រាស់" : "Deactive"),
				);
			}else if($getControlType=="academicYear"){
				$row = $this->getAllAcadmicYearByTeacher($_data);
			}else if($getControlType=="teachingGroup"){
				$row = $this->getAllTeachingGroupByTeacher($_data);
			}else if($getControlType=="teachingSubject"){
				$row = $this->getAllTeachingSubjectByTeacher($_data);
			}else if($getControlType=="teachingDegree"){
				$row = $this->getAllTeachingDegreeByTeacher($_data);
			}else if($getControlType=="ratingOption"){
				$row = $this->getRatingOption($_data);
			}else if($getControlType=="groupStatus"){
				$_data["viewType"] = 9;
				$row = $this->getViewListByType($_data);
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
	function getRatingOption($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$sql="
			SELECT 
				rt.id,
				rt.rating AS name
			FROM 
				rms_rating AS rt
		";
		$sql.=" WHERE rt.`status` = 1 ";
		$sql.=" 
		ORDER BY rt.`id` ASC
		";
		return $db->fetchAll($sql);
	}
	function getAllAcadmicYearByTeacher($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$sql="
			SELECT 
				ac.id
				,CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) AS `name`
			FROM 
				rms_group AS g
				JOIN `rms_group_subject_detail` AS gSubj ON g.`id` = gSubj.`group_id`
				LEFT JOIN `rms_academicyear` AS ac ON ac.id = g.`academic_year`
		";
		$sql.=" WHERE gSubj.`teacher` = $userId ";
		$sql.=" 
		GROUP BY ac.id
		ORDER BY 
			ac.`fromYear` DESC
		";
		return $db->fetchAll($sql);
	}
	function getAllTeachingGroupByTeacher($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$_data['academicYear'] = empty($_data['academicYear'])?0:$_data['academicYear'];
		$sql="
			SELECT 
				g.`id`
				,CONCAT(COALESCE(g.`group_code`,''),' ',COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) AS `name`
			FROM 
				rms_group AS g
				JOIN `rms_group_subject_detail` AS gSubj ON g.`id` = gSubj.`group_id`
				LEFT JOIN `rms_academicyear` AS ac ON ac.id = g.`academic_year`
		";
		$sql.=" WHERE gSubj.`teacher` = $userId ";
		if(!empty($_data['academicYear'])){
			$sql.=" AND g.academic_year = ".$_data['academicYear'];	
		}
		$sql.=" GROUP BY g.`id` ";
		$sql.=" ORDER BY 
			g.`degree` ASC,
			g.grade ASC,
			g.id ASC 
		";
		return $db->fetchAll($sql);
	}
	
	function getAllTeachingDegreeByTeacher($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$sql="
			SELECT 
				g.`degree` AS id
				,(SELECT de.title FROM `rms_items` AS de WHERE de.type=1 AND de.id = g.`degree` LIMIT 1 ) AS `name`
			FROM 
				rms_group AS g
				JOIN `rms_group_subject_detail` AS gSubj ON g.`id` = gSubj.`group_id`
		";
		$sql.=" WHERE gSubj.`teacher` = $userId ";
		$sql.=" GROUP BY g.`degree` ";
		$sql.=" ORDER BY g.`degree` ASC ";
		return $db->fetchAll($sql);
	}
	
	function getAllTeachingSubjectByTeacher($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$sql="
			SELECT 
				sub.`id`
				,CASE
					WHEN sub.`subject_lang` = 2 THEN sub.`subject_titleen`
					ELSE sub.subject_titlekh
				END AS `name`
			FROM 
				`rms_group_subject_detail` AS gSubj 
				 JOIN `rms_subject` AS sub ON sub.id = gSubj.`subject_id`
		";
		$sql.=" WHERE gSubj.`teacher` = $userId ";
		$sql.=" GROUP BY sub.`id`
				ORDER BY 
				CASE
					WHEN sub.`subject_lang` = 2 THEN sub.`subject_titleen`
					ELSE sub.subject_titlekh
				END ASC
		";
		return $db->fetchAll($sql);
	}
	
	function getCountingClassByTeacher($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$sql="
				SELECT
					g.id
					,COUNT(DISTINCT gsjb.`group_id`) AS totalClass
					,COUNT(DISTINCT IF(g.is_pass NOT IN (0,2),gsjb.`group_id`, NULL)) AS competedClass
					,COUNT(DISTINCT IF(g.is_pass=2,gsjb.`group_id`, NULL)) AS teachingClass
				FROM `rms_group_subject_detail` AS gsjb,
					`rms_group` AS g 
				WHERE 
					g.id = gsjb.group_id 
					AND g.is_use=1 
					AND g.`status` = 1
					AND gsjb.`teacher` = $userId
			";
			$sql.=" GROUP BY gsjb.`teacher` ";
			$sql.=" LIMIT 1";
			$row = $_db->fetchAll($sql);
			$row = empty($row) ? array() : $row;
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
	function getTeachingClassList($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$label = "name_en";
			$branch = "branch_nameen";
			if($currentLang==1){// khmer
				$label = "name_kh";
				$branch = "branch_namekh";
			}
			$sql="
				SELECT
					g.id AS groupId
					,(SELECT br.$branch FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchName
					,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
					,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
					,(SELECT b.school_nameen FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
					,g.`group_code` AS groupCode
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.`academic_year` LIMIT 1) AS academicYear
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)),'N/A') AS `roomName`
					,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS mainTeacherNameKh
					,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS mainTeacherNameEng
					,CASE 
						WHEN g.teacher_id = gsjb.`teacher` THEN '1'
						ELSE '0'
					END AS isMainTeacher
					,g.is_pass AS groupProcess
					,(SELECT $label FROM rms_view AS v WHERE v.type=9 AND v.key_code=g.is_pass LIMIT 1) as groupProcessTitle
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.itemType=1 AND gds.group_id =g.id AND gds.`is_maingrade` = 1 LIMIT 1  ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE gds.itemType=1 AND s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id AND gds.`is_maingrade` = 1 LIMIT 1 ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE gds.itemType=1 AND s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id  AND gds.`is_maingrade` = 1 LIMIT 1 ) AS femaleStudent
					
				FROM `rms_group_subject_detail` AS gsjb
						JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1 AND g.`status` = 1
				WHERE gsjb.`teacher` = $userId
			";
			
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year =".$_data['academicYear'];
			}
			if(!empty($_data['degree'])){
				$sql.=" AND g.degree =".$_data['degree'];
			}
			if(!empty($_data['groupProcess'])){
				$sql.=" AND g.is_pass =".$_data['groupProcess'];
			}
			$sql.=" GROUP BY g.id ";
			$sql.=" ORDER BY g.`academic_year` DESC ,g.degree ASC,g.grade ASC,g.group_code ASC ";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
			
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
	
	function getScoreResultOfClass($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$format = 'Y-m-d';
			$date = new DateTime();
			$todayDate = $date->format($format);
			
			$date->modify('+7 day');
			$nextDate = $date->format($format);
			
			$annual='Annual';
			$colunmName='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$month = "month_en";
			$scoreTitle = "title_score_en";
			if ($currentLang==1){
				$annual='ប្រចាំឆ្នាំ';
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$month = "month_kh";
				$scoreTitle = "title_score";
			}
			$sql="
				SELECT 
					sc.`id` AS scoreId
					,sc.`branch_id` AS branchId
					,sc.$scoreTitle AS scoreTitle
					,sc.`exam_type` AS examType
					,sc.`for_month` AS forMonth
					,sc.`for_semester` AS forSemester
					,sc.`date_input` AS entryDate
					,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=19 AND v.key_code =sc.`exam_type` LIMIT 1) as forTypeTitle
					,CASE
						WHEN sc.exam_type = 2 THEN sc.for_semester
						WHEN sc.exam_type = 3 THEN '".$annual."'
						ELSE (SELECT $month FROM `rms_month` WHERE id=sc.for_month  LIMIT 1) 
					END AS forMonthTitle
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.degree AS degreeId
					,g.academic_year AS academicYearId
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
					
					
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.group_id =g.id AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1  ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1 ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id  AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1 ) AS femaleStudent
					,'' AS groupProcessTitle
					,(SELECT COUNT( IF( grdT.total_score >= (grdT.totalMaxScore/2), grdT.id, NULL))  FROM `rms_score_monthly` AS grdT WHERE grdT.score_id = sc.`id` AND grdT.totalMaxScore >0 LIMIT 1 ) AS aboveAverage
					,(SELECT COUNT( IF( grdT.total_score < (grdT.totalMaxScore/2), grdT.id, NULL))  FROM `rms_score_monthly` AS grdT WHERE grdT.score_id = sc.`id` AND grdT.totalMaxScore >0 LIMIT 1 ) AS belowAverage
					
			";
			
			$sql.=" FROM 
						`rms_score` AS sc,
						rms_group AS g
				";
			$sql.=' WHERE 
						sc.`group_id` = g.`id`
						AND g.`teacher_id` ='.$userId;
			$sql.=' 
					AND g.`is_use` = 1 
					AND g.`status` =1
					AND sc.`status` =1
				';
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year =".$_data['academicYear'];
			}
			if(!empty($_data['degree'])){
				$sql.=" AND g.degree =".$_data['degree'];
			}
			if(!empty($_data['groupProcess'])){
				$sql.=" AND g.is_pass =".$_data['groupProcess'];
			}
			$sql.="
				ORDER BY sc.id DESC, g.id ASC
			";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
			$row = $_db->fetchAll($sql);
			
			$row = empty($row) ? array() : $row;
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
	
	
	function getClassAvailableForEvaluation($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$format = 'Y-m-d';
			$date = new DateTime();
			$todayDate = $date->format($format);
			
			$date->modify('+7 day');
			$nextDate = $date->format($format);
			
			$colunmName='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$month = "month_en";
			$scoreTitle = "title_score_en";
			$annual='Annual';
			if ($currentLang==1){
				$annual='ប្រចាំឆ្នាំ';
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$month = "month_kh";
				$scoreTitle = "title_score";
			}
			$sql="
				SELECT 
					sc.`id` AS scoreId
					,sc.`branch_id` AS branchId
					,sc.$scoreTitle AS scoreTitle
					,sc.`exam_type` AS examType
					,sc.`for_month` AS forMonth
					,sc.`for_semester` AS forSemester
					,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=19 AND v.key_code =sc.`exam_type` LIMIT 1) as forTypeTitle
					,CASE
						WHEN sc.exam_type = 2 THEN sc.for_semester
						WHEN sc.exam_type = 3 THEN '".$annual."'
						ELSE (SELECT $month FROM `rms_month` WHERE id=sc.for_month  LIMIT 1) 
					END AS forMonthTitle
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.degree AS degreeId
					,g.academic_year AS academicYearId
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
					
					
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.group_id =g.id AND gds.`itemType` = 1 LIMIT 1  ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id AND gds.`itemType` = 1 LIMIT 1 ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id  AND gds.`itemType` = 1 LIMIT 1 ) AS femaleStudent
					,CASE
						WHEN COALESCE((SELECT ass.issueDate FROM `rms_studentassessment` AS ass WHERE ass.scoreId = sc.`id`  LIMIT 1),NULL) IS NOT NULL
							THEN (SELECT ass.issueDate FROM `rms_studentassessment` AS ass WHERE ass.scoreId = sc.`id`  LIMIT 1)
						ELSE '".$todayDate."' 
					END AS evaluationIssueDate
					,CASE
						WHEN COALESCE((SELECT ass.returnDate FROM `rms_studentassessment` AS ass WHERE ass.scoreId = sc.`id`  LIMIT 1),NULL) IS NOT NULL
							THEN (SELECT ass.returnDate FROM `rms_studentassessment` AS ass WHERE ass.scoreId = sc.`id`  LIMIT 1)
						ELSE '".$nextDate."' 
					END AS evaluationReturnDate
					,(SELECT COUNT(DISTINCT(assD.studentId)) FROM `rms_studentassessment` AS ass,`rms_studentassessment_detail` AS assd WHERE ass.id = assD.assessmentId AND ass.scoreId = sc.`id`  LIMIT 1) AS totalStudentEvaluated
					,COALESCE((SELECT ass.isLock FROM rms_studentassessment AS ass WHERE ass.scoreId = sc.id LIMIT 1),'0') AS isLock
			";
			
			$sql.=" FROM 
						`rms_score` AS sc,
						rms_group AS g
				";
			$sql.=' WHERE 
						sc.`group_id` = g.`id`
						AND g.`teacher_id` ='.$userId;
			$sql.=' 
					AND g.`is_use` = 1 
				
					AND g.`status` =1
					AND sc.`status` =1
				';
				//AND g.`is_pass` = 2
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year =".$_data['academicYear'];
			}
			if(!empty($_data['degree'])){
				$sql.=" AND g.degree =".$_data['degree'];
			}
			if(!empty($_data['groupProcess'])){
				$sql.=" AND g.is_pass =".$_data['groupProcess'];
			}
			$sql.="
				ORDER BY sc.id DESC, g.id ASC
			";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
			
			$row = $_db->fetchAll($sql);
			
			$row = empty($row) ? array() : $row;
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
	function getEvaluationCommentByDegree($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$degreeId = empty($_data['degreeId'])?0:$_data['degreeId'];
			$groupId = empty($_data['groupId'])?0:$_data['groupId'];
			$studentId = empty($_data['studentId'])?0:$_data['studentId'];
			$scoreId = empty($_data['scoreId'])?0:$_data['scoreId'];
			
			$label = 'name_en';
			if ($currentLang==1){
				$label = 'name_kh';
			}
			$sql="
				SELECT 
					dc.comment_id AS commentId
					,c.comment AS `commentTitle`
					,c.commentType
					
					,(SELECT v.$label FROM `rms_view` AS v WHERE v.key_code=c.commentType AND v.type=36 LIMIT 1) AS commentTypeTitle
					,(SELECT v.name_kh FROM `rms_view` AS v WHERE v.key_code=c.commentType AND v.type=36 LIMIT 1) AS commentTypeTitleKh
					,(SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code=c.commentType AND v.type=36 LIMIT 1) AS commentTypeTitleEn
					
					,COALESCE((SELECT assD.ratingId FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.scoreId < $scoreId AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND assD.commentId = c.id ORDER BY assD.assessmentId DESC  LIMIT 1 ),'0') AS previousRatingValue
					,COALESCE((SELECT rt.rating FROM rms_rating AS rt WHERE rt.id = COALESCE((SELECT assD.ratingId FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.scoreId < $scoreId AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND assD.commentId = c.id ORDER BY assD.assessmentId DESC  LIMIT 1 ),'0') LIMIT 1 ),'') AS previousRatingTitle
					,COALESCE((SELECT assD.teacherComment FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.scoreId < $scoreId AND ass.status=1 AND ass.groupId=$groupId AND assD.studentId=$studentId ORDER BY assD.teacherComment DESC,assD.assessmentId DESC  LIMIT 1 ),'') AS previousTeacherComment
					
					,COALESCE((SELECT assD.ratingId FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND ass.scoreId=$scoreId AND assD.commentId = c.id ORDER BY assD.assessmentId DESC  LIMIT 1 ),'2') AS ratingValue
					,(SELECT rt.rating FROM rms_rating AS rt WHERE rt.id = COALESCE((SELECT assD.ratingId FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.scoreId =$scoreId AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND assD.commentId = c.id ORDER BY assD.assessmentId DESC  LIMIT 1 ),'0') LIMIT 1 ) AS ratingTitle
					,(SELECT assD.teacherComment FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND ass.scoreId=$scoreId ORDER BY assD.teacherComment DESC,assD.assessmentId DESC  LIMIT 1 ) AS teacherComment
					,COALESCE((SELECT GROUP_CONCAT(assD.id) FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND ass.scoreId=$scoreId ORDER BY assD.assessmentId DESC  LIMIT 1 ),'') AS detailIdList
					,COALESCE((SELECT assD.id FROM `rms_studentassessment_detail` AS assD,`rms_studentassessment` AS ass WHERE assD.assessmentId=ass.id AND ass.status=1 AND ass.groupId=$groupId AND  assD.studentId=$studentId AND ass.scoreId=$scoreId AND assD.commentId = c.id ORDER BY assD.assessmentId DESC  LIMIT 1 ),'') AS detailId
			";
			
			$sql.=" FROM 
						rms_degree_comment AS dc,
						rms_comment AS c
				";
			$sql.=' WHERE 
						dc.comment_id = c.id
						AND dc.degree_id = '.$degreeId;
			$sql.=' ORDER BY commentType ASC, c.id ASC ';
			
			
			$row = $_db->fetchAll($sql);
			$row = empty($row) ? array() : $row;
			
			$ratingOption = $this->getRatingOption($_data);
			$arrayResult = array(
				'commentList' 	=>$row,
				"ratingOption"  =>$ratingOption,
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
	
	function checkingGroupAssessmentByScore($_data){
		$dbS = $this->getAdapter();
		$userId = empty($_data['userId'])?0:$_data['userId'];
		$scoreId = empty($_data['scoreId'])?0:$_data['scoreId'];
		$sql="
			SELECT 
				ass.*
			FROM `rms_studentassessment` AS ass 
			WHERE ass.`scoreId` = ".$scoreId;
		$sql.=" LIMIT 1 ";
		return $dbS->fetchRow($sql);
	}
	function submitEvaluationRatingStudent($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			$format = 'Y-m-d';
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$_data['studentId']	= empty($_data['studentId'])?0:$_data['studentId'];
			
			$commentListPost 	 = empty($_data['commentList'])?null:$_data['commentList'];
			$commentList 	 = Zend_Json::decode($commentListPost);
			
			$groupEvaluationInfoPost 	 = empty($_data['groupEvaluationInfo'])?null:$_data['groupEvaluationInfo'];
			$groupInfo 	 = Zend_Json::decode($groupEvaluationInfoPost);
			$_data['scoreId']	= empty($groupInfo['scoreId'])?0:$groupInfo['scoreId'];	
			$checkingAss = $this->checkingGroupAssessmentByScore($_data);
			$assessmentId = empty($checkingAss["id"]) ? "0" : $checkingAss["id"];
			
			
			$dateString = $_data['evaluationIssueDate'];
			$date = new DateTime($dateString);
			$evaluationIssueDate = $date->format($format);
			
			$toDateString = $_data['evaluationReturnDate'];
			$toDate = new DateTime($toDateString);
			$evaluationReturnDate = $toDate->format($format);
			
			if(empty($checkingAss)){
				$arr = array(
    				'branchId'			=>$groupInfo['branchId'],
    				'groupId'			=>$groupInfo['groupId'],
    				'degreeId'			=>$groupInfo['degreeId'],
    				'forType'			=>$groupInfo['examType'],
    				'forMonth'			=>$groupInfo['forMonth'],
    				'forSemester'		=>$groupInfo['forSemester'],
    				'issueDate'			=>$evaluationIssueDate,
    				'returnDate'		=>$evaluationReturnDate,
    				'scoreId'			=>$groupInfo['scoreId'],
					
    				'status'			=>1,
    				'inputOption'		=>2,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'teacherId'			=>$_data['userId'],
				);
				$this->_name='rms_studentassessment';
				$assessmentId = $this->insert($arr);
			}else{
				$arr = array(
					'issueDate'			=>$evaluationIssueDate,
    				'returnDate'		=>$evaluationReturnDate,
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'teacherId'			=>$_data['userId'],
				);
				$this->_name='rms_studentassessment';
				$where=" id = ".$assessmentId;
				$this->update($arr, $where);
				
				if(!empty($commentList)){
					$detailIdList = empty($commentList[0]["detailIdList"]) ? "" : $commentList[0]["detailIdList"];
					
					$this->_name="rms_studentassessment_detail";
					$whereDetail="assessmentId = ".$assessmentId." AND studentId=".$_data['studentId'];
					if (!empty($detailIdList)){
						$whereDetail.=" AND id NOT IN ($detailIdList) ";
					}
					$this->delete($whereDetail);
				}
				
			}
			if(!empty($commentList)){
				foreach($commentList as $key => $comment){
					$teacherComment=  ($key>0) ? null : $_data['teacherComment'];
					if(!empty($comment['detailId'])){
						$arrDetail = array(
							'assessmentId'		=>$assessmentId,
							'studentId'			=>$_data['studentId'],
							'commentId'			=>$comment['commentId'],
							'ratingId'			=>$comment['ratingValue'],
							'teacherComment'	=>$teacherComment,
							
						);
						
						$this->_name='rms_studentassessment_detail';
						$whereDetail = " id =".$comment['detailId'];
						$this->update($arrDetail, $whereDetail);
					}else{
						$arrDetail = array(
							'assessmentId'		=>$assessmentId,
							'studentId'			=>$_data['studentId'],
							'commentId'			=>$comment['commentId'],
							'ratingId'			=>$comment['ratingValue'],
							'teacherComment'	=>$teacherComment,
						);
						$this->_name='rms_studentassessment_detail';
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
	
	
	function getAssessmentListOfClass($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			
			$annual='Annual';
			$colunmName='title_en';
			$label = 'name_en';
			$branch = "branch_nameen";
			$month = "month_en";
			$scoreTitle = "title_score_en";
			if ($currentLang==1){
				$annual='ប្រចាំឆ្នាំ';
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				$month = "month_kh";
				$scoreTitle = "title_score";
			}
			$sql="
				SELECT 
					ass.`id` AS assessmentId
					,ass.scoreId AS scoreId
					,ass.`branchId` AS branchId
					,sc.$scoreTitle AS scoreTitle
					,sc.`exam_type` AS examType
					,sc.`for_month` AS forMonth
					,sc.`for_semester` AS forSemester
					,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=19 AND v.key_code =sc.`exam_type` LIMIT 1) as forTypeTitle
					,CASE
						WHEN sc.exam_type = 2 THEN sc.for_semester
						WHEN sc.exam_type = 3 THEN '".$annual."'
						ELSE (SELECT $month FROM `rms_month` WHERE id=sc.for_month  LIMIT 1) 
					END AS forMonthTitle
					,g.id AS groupId
					,g.group_code AS groupCode
					,g.degree AS degreeId
					,g.academic_year AS academicYearId
					,g.is_pass AS groupProcess
					,(SELECT $label FROM rms_view AS v WHERE v.type=9 AND v.key_code=g.is_pass LIMIT 1) as groupProcessTitle
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
					,(SELECT id.$colunmName FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
					,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS `r` WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
					
					
					,(SELECT COUNT(IF(gds.stu_id !=0 AND gds.stop_type = '0', gds.stu_id, NULL)) FROM `rms_group_detail_student` AS gds WHERE gds.group_id =g.id AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1  ) AS totalStudent
					,(SELECT COUNT(IF(s.sex = '1' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1 ) AS maleStudent
					,(SELECT COUNT(IF(s.sex = '2' AND gds.stop_type = '0'  , s.sex, NULL)) FROM `rms_group_detail_student` AS gds,rms_student AS s WHERE s.status=1 AND s.stu_id=gds.stu_id AND gds.group_id =g.id  AND gds.`is_current` = 1 AND gds.`is_maingrade` = 1 LIMIT 1 ) AS femaleStudent
					,ass.issueDate AS evaluationIssueDate
					,ass.returnDate AS evaluationReturnDate
					,(SELECT COUNT(DISTINCT(assD.studentId)) FROM `rms_studentassessment_detail` AS assd WHERE ass.id = assD.assessmentId AND ass.scoreId = sc.`id`  LIMIT 1) AS totalStudentEvaluated
					,ass.isLock AS isLock
			";
			
			$sql.=" FROM 
						`rms_studentassessment` AS ass JOIN `rms_group` AS g ON g.id  = ass.`groupId` 
						LEFT JOIN `rms_score` AS sc ON sc.id = ass.`scoreId`
				";
			$sql.=' WHERE  g.`teacher_id` ='.$userId;
			$sql.=' 
					AND g.`is_use` = 1 
					AND g.`status` =1
					AND ass.`status` =1
				';
				//AND g.`is_pass` = 2
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year =".$_data['academicYear'];
			}
			if(!empty($_data['degree'])){
				$sql.=" AND g.degree =".$_data['degree'];
			}
			if(!empty($_data['groupProcess'])){
				$sql.=" AND g.is_pass =".$_data['groupProcess'];
			}
			$sql.="
				ORDER BY g.academic_year DESC,ass.id DESC, g.id ASC
			";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
			$row = $_db->fetchAll($sql);
			
			$row = empty($row) ? array() : $row;
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
	
	
	function getVideoTeacherTutorial($_data){
		$_db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$colunmName='title_en';
			$label = 'name_en';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
			}
			$sql="
				SELECT
		    	act.`id`
				,act.video_link AS videoYoutubeId
				,act.ordering AS ordering
		    	,(SELECT ad.title FROM `mobile_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS title
		    	,(SELECT ad.description FROM `mobile_video_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS description
		    	,(SELECT GROUP_CONCAT( COALESCE(i.shortcut,i.$colunmName) ) FROM `rms_items` AS i WHERE i.type=1 AND FIND_IN_SET(i.id,act.`degreeList`) LIMIT 1) AS degreeListTitle
				,act.`publish_date` AS publishDate
		    	,(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS userName
			";
			
			$sql.=" FROM `mobile_video` AS act 
				";
			$sql.=' WHERE act.status = 1 AND act.typeOfVideo = 2 ';
			$sql.='';
			if(!empty($_data['degree'])){
				$sql.=" AND FIND_IN_SET(".$_data['degree'].",act.`degreeList`) ";
			}
			$sql.="
				ORDER BY act.ordering ASC
			";
			
			if(!empty($_data['LimitStart'])){
				$sql.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$sql.=" LIMIT ".$_data['limitRecord'];
			}
			$row = $_db->fetchAll($sql);
			
			$row = empty($row) ? array() : $row;
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
}