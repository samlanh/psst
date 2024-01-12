<?php

class Allreport_Model_DbTable_DbScoreTranscript extends Zend_Db_Table_Abstract
{

	function getTranscriptExam($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);


		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreSubjectInfo =  $this->getSubjectScoreTranscript($resultScoreArr);

		$scoreResultList = array();
		if (!empty($scoreSubjectInfo)) {
			foreach ($scoreSubjectInfo as $key => $result) {

				$scoreResultList[$key] = array(
					'subject_id' => $result['subject_id'],
					'gradingTotalId' => $result['gradingTotalId'],
					'totalAverage' => $result['totalAverage'],
					'rankingSubject' => $result['rankingSubject'],

					'score_cut' => $result['score_cut'],
					'sub_name' => $result['sub_name'],
					'subjectLang' => $result['subjectLang'],
					'sub_name_en' => $result['sub_name_en'],
					'maxScore'	=> $result['maxScore'],
					'multiSubject' => $result['multiSubject'],
					'amount_subject' => $result['amount_subject'],
					'gradingTotalId' => $result['gradingTotalId'],
					'innerSubject' => 0
				);

				$arrSub = array(
					'gradingId' => $result['gradingTotalId'],
					'subjectId' => $result['subject_id'],
					'studentId' => $studentId,
				);
				$resultSubScore = $this->getSubScoreList($arrSub);
				if (!empty($resultSubScore)) {
					$scoreResultList[$key]['innerSubject'] = count($resultSubScore);
					$scoreResultList[$key]['innerSubjectList'] = $resultSubScore;
				}
			}
		}
		$arreValue = array(
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getTranscriptSemesterExam($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);


		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreSemesterInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreSubjectInfo =  $this->getSubjectScoreTranscript($resultScoreArr);

		$scoreResultList = array();
		if (!empty($scoreSubjectInfo)) {
			foreach ($scoreSubjectInfo as $key => $result) {

				$scoreResultList[$key] = array(
					'subject_id' => $result['subject_id'],
					'gradingTotalId' => $result['gradingTotalId'],
					'totalAverage' => $result['totalAverage'],
					'rankingSubject' => $result['rankingSubject'],

					'score_cut' => $result['score_cut'],
					'sub_name' => $result['sub_name'],
					'subjectLang' => $result['subjectLang'],
					'sub_name_en' => $result['sub_name_en'],
					'maxScore'	=> $result['maxScore'],
					'multiSubject' => $result['multiSubject'],
					'amount_subject' => $result['amount_subject'],
					'gradingTotalId' => $result['gradingTotalId'],
					'innerSubject' => 0
				);

				$arrSub = array(
					'gradingId' => $result['gradingTotalId'],
					'subjectId' => $result['subject_id'],
					'studentId' => $studentId,
				);
				$resultSubScore = $this->getSubScoreList($arrSub);
				if (!empty($resultSubScore)) {
					$scoreResultList[$key]['innerSubject'] = count($resultSubScore);
					$scoreResultList[$key]['innerSubjectList'] = $resultSubScore;
				}
			}
		}
		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getSubScoreList($data)
	{
		$sql = "SELECT 
					totalGrading,
					subCriterialTitleKh,
					subCriterialTitleEng 
				FROM `rms_grading_detail` gd
				WHERE 
					gd.subCriterialTitleEng IS NOT NULL ";
		if (!empty($data['gradingId'])) {
			$sql .= " AND gd.gradingId=" . $data['gradingId'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND gd.studentId=" . $data['studentId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND gd.subjectId=" . $data['subjectId'];
		}
		return $this->getAdapter()->fetchAll($sql);
	}
	function getSubjectScoreTranscript($data)
	{ //transcript and score detail
		$db = $this->getAdapter();
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE 
						s.id=sd.subject_id LIMIT 1) ";

		$strCollect = 'amount_subject';
		$strMaxScore = 'max_score';
		if ($data['examType'] == 2) { //semester
			$strCollect = 'amount_subject_sem';
			$strMaxScore = 'semester_max_score';
		}

		$strSubjecMaxScore = " (SELECT $strMaxScore FROM `rms_group_subject_detail` WHERE
		group_id=sd.group_id AND
		subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";

		$strMultiSubject = " (SELECT $strCollect FROM `rms_group_subject_detail` WHERE
		group_id=sd.group_id AND subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";
		//need to check this score is monthly or semester?

		$subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$sql = "SELECT
					$strSubjectLange AS subjectLang,
					$strSubjecMaxScore AS maxScore,
					$strMultiSubject AS multiSubject,
					sd.`subject_id`,
					sd.gradingTotalId,
					sd.`score` AS totalAverage,
					
					FIND_IN_SET(sd.`score`,
						(SELECT GROUP_CONCAT(insd.score ORDER BY insd.score DESC)
						FROM 
							rms_score_detail AS insd 
						 WHERE
							insd.`score_id`=$scoreId
						 	AND sd.`subject_id`=insd.subject_id
						ORDER BY insd.`score` DESC )) AS rankingSubject,	
					sd.score_cut,
					(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name,
					(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name_en,
					sd.amount_subject
				FROM  `rms_score_detail` AS sd 
					LEFT JOIN rms_group AS g ON g.id=sd.group_id 
					LEFT JOIN rms_grade_subject_detail AS gsj ON sd.subject_id=gsj.subject_id AND g.`grade`=gsj.`grade_id`
				WHERE 1 ";
		if (!empty($scoreId)) {
			$sql .= " AND sd.`score_id`=" . $scoreId;
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND sd.`student_id`=" . $data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.`subject_id`=" . $subjectId;
		}
		if (!empty($data['groupbySubjectId'])) { //for get all subject in result detail
			$sql .= " GROUP BY subject_id ";
		}
		$sql .= " ORDER  BY $strSubjectLange  ASC, gsj.subject_order  ASC ";
		return $db->fetchAll($sql);
	}

	function getScoreInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_avg FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(total_avg ORDER BY total_avg DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	
				 
				 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInKhmer,
					
					
					
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInEnglish,
				 
				
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInChinese,
				 
				 
				 (SELECT sm.total_avg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalAvg,
				 		
				  (SELECT sm.total_score 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalScoreAvg,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}
		return $db->fetchRow($sql);
	}

	function getScoreSemesterInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.semesterTotalAverage as semesterTotalAverage,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_avg FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(total_avg ORDER BY total_avg DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	

				 FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm WHERE 
				 sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
						WHERE
							s.`id`=dd.`score_id`
						)
				) AS rankOverall,	
				 
				 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInKhmer,
					
					
					
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInEnglish,
				 
				
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInChinese,
				 
				 
				 (SELECT sm.total_avg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalAvg,
				 		
				  (SELECT sm.total_score 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalScoreAvg,

				 (SELECT sm.totalMaxScore 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalMaxScore,


				 (SELECT sm.strMonthlySemesterLangAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS strMonthlySemesterLangAvg,

				 (SELECT sm.monthlySemesterAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

				(SELECT sm.overallAssessmentSemester 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}

		return $db->fetchRow($sql);
	}

	function getStudentProfile($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
			stu_id AS student_id,
			stu_code,
			stu_khname,
			last_name,
			stu_enname,
			sex,
			DATE_FORMAT(dob,'%d-%m-%Y') As dob
		 FROM rms_student WHERE 1 ";
		if (!empty($data['studentId'])) {
			$sql .= " AND stu_id=" . $data['studentId'];
		}
		return $db->fetchRow($sql);
	}
	function getStudentAssessmentEvaluation($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
				(SELECT `comment` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) commentLabel,
				(SELECT r.rating FROM `rms_rating` r WHERE r.id=ratingId LIMIT 1) ratingLabel,
				(SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) AS commentTypeId,
				(SELECT CONCAT(v.name_kh,' ',v.name_en) FROM `rms_view` AS v WHERE key_code=(SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) AND v.type=36) AS commentType,
				(SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code=(SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) AND v.type=36) AS commentTypeEng,
				(SELECT v.name_kh FROM `rms_view` AS v WHERE v.key_code=(SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) AND v.type=36) AS commentTypeKh,
				smd.teacherComment
			FROM `rms_studentassessment` AS sm,
				`rms_studentassessment_detail` AS smd
				WHERE smd.assessmentId=sm.id ";
		if (!empty($data['studentId'])) {
			$sql .= " AND smd.studentId=" . $data['studentId'];
		}
		if (!empty($data['scoreId'])) {
			$sql .= " AND sm.scoreId=" . $data['scoreId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND sm.groupId=" . $data['groupId'];
		}
		if (!empty($data['forType'])) {
			$sql .= " AND sm.forType=" . $data['forType'];
		}
		if (!empty($data['forSemester'])) {
			$sql .= " AND sm.forSemester=" . $data['forSemester'];
		}
		if (($data['forType']) == 1) {

			$sql .= " AND sm.forType=" . $data['forType'];

			if ($data['forType'] == 1 and !empty($data['forMonth'])) {
				$sql .= " AND sm.forMonth=" . $data['forMonth'];
			}
		}

		$sql .= " ORDER BY (SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) ASC,smd.id ASC ";
		return $db->fetchAll($sql);
	}
	function countAttendenceTranscript($data = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";

		// 			if(!empty($for_semester)){
		// 				$sql.= " AND sat.for_semester=".$for_semester;
		// 			}
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
		}

		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		$sql .= " GROUP BY sat.date_attendence";
		//echo $sql; exit();
		return $db->fetchAll($sql);
	}
	function countDisplineTranscript($data = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=2 ";

		// 			if(!empty($for_semester)){
		// 				$sql.= " AND sat.for_semester=".$for_semester;
		// 			}
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
		}

		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		$sql .= " LIMIT 1";
		return $db->fetchOne($sql);
	}
}
