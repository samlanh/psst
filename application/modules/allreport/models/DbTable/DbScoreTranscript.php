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

	function getTranscriptAnnaulExam($data)
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
		$scoreInfo =  $this->getScoreAnnaulInformation($resultArray);

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

	function getAcademicTranscript($data)
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
		$scoreInfo =  $this->getScoreAcsdemicInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreSubjectInfo =  $this->getSubjectScoreAcademicTranscript($resultScoreArr);

		$arrSemester1 = array(
			'groupId' => $scoreInfo['group_id'],
			'studentId' => $studentId,
			'examType' => 2,
			'semester' => 1,
		);
		$scoreSubjectInfoSemester1 =  $this->getSubjectScoreForSemester($arrSemester1);

		$arrSemester2 = array(
			'groupId' => $scoreInfo['group_id'],
			'studentId' => $studentId,
			'examType' => 2,
			'semester' => 2,
		);
		$scoreSubjectInfoSemester2 =  $this->getSubjectScoreForSemester($arrSemester2);

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
					'innerSubject' => 0,

					'totalAvgSemester1' => $scoreSubjectInfoSemester1[$key]['totalAverage'],
					'totalAvgSemester2' => $scoreSubjectInfoSemester2[$key]['totalAverage'],

					// 'totalAvgSemester1' => $result['totalAvgSemester1'],
					// 'totalAvgSemester2' => $result['totalAvgSemester2'],
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
		/*
		if (!empty($data['gradingId'])) {
			$sql .= " AND gd.gradingId=" . $data['gradingId'];
		}
		*/
		$data['gradingId'] = empty($data['gradingId']) ? 0 : $data['gradingId'];
		$sql .= " AND gd.gradingId=" . $data['gradingId'];
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
		if ($data['examType'] == 2 OR $data['examType'] == 3) { //semester
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
	function getSubjectScoreAcademicTranscript($data)
	{ //transcript and score detail
		$db = $this->getAdapter();
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE 
						s.id=sd.subject_id LIMIT 1) ";

		$strCollect = 'amount_subject_sem';
		$strMaxScore = 'semester_max_score';

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
					(SELECT sdt.score 
						FROM rms_score_detail AS sdt INNER JOIN rms_score AS d 
						ON d.`id` = sdt.score_id
						WHERE d.exam_type=2 
						AND  d.`for_semester`=1
						AND sdt.student_id= ".$data['studentId']."
						AND sdt.subject_id= sd.subject_id
						AND d.group_id = sdt.`group_id`   LIMIT 1
					) AS totalAvgSemester1,
					(SELECT sdt.score 
						FROM rms_score_detail AS sdt INNER JOIN rms_score AS d 
						ON d.`id` = sdt.score_id
						WHERE d.exam_type=2 
						AND  d.`for_semester`=2 
						AND sdt.student_id= ".$data['studentId']."
						AND sdt.subject_id= sd.subject_id
						AND d.group_id = sdt.`group_id`   LIMIT 1
					) AS totalAvgSemester2,
					
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
	function getSubjectScoreForSemester($data)
	{ //transcript and score detail
		$db = $this->getAdapter();
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE 
		s.id=sd.subject_id LIMIT 1) ";

		$strCollect = 'amount_subject_sem';
		$strMaxScore = 'semester_max_score';

		$strSubjecMaxScore = " (SELECT $strMaxScore FROM `rms_group_subject_detail` WHERE
		group_id=sd.group_id AND
		subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";

		$strMultiSubject = " (SELECT $strCollect FROM `rms_group_subject_detail` WHERE
		group_id=sd.group_id AND subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";

		$subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];
	
		$sql = "SELECT
					sd.`subject_id`,
					sd.`score` AS totalAverage
				FROM rms_score_detail AS sd
				 INNER JOIN rms_score AS s ON s.`id` = sd.score_id
				 LEFT JOIN rms_group AS g ON g.id=sd.group_id 
				 LEFT JOIN rms_grade_subject_detail AS gsj ON sd.subject_id=gsj.subject_id AND g.`grade`=gsj.`grade_id`
				WHERE 1";

		if (!empty($data['studentId'])) {
			$sql .= " AND sd.`student_id`=" . $data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.`subject_id`=" . $subjectId;
		}
		if (!empty($data['examType'])) {
			$sql .= " AND s.`exam_type`=" . $data['examType'];
		}
		if (!empty($data['semester'])) {
			$sql .= " AND s.`for_semester`=" . $data['semester'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND s.`group_id`=" . $data['groupId'];
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
					g.max_average AS max_average,
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

	function getScoreAnnaulInformation($data)
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
					s.note as promoteResult,
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

				FIND_IN_SET( 
					COALESCE((SELECT ms.OveralAvgKh FROM rms_score_monthly AS ms WHERE ms.score_id = s.`id` AND ms.student_id = " . $data['studentId'] . " LIMIT 1),'0'), 
					(    
					  SELECT 
						GROUP_CONCAT( dd.OveralAvgKh ORDER BY dd.OveralAvgKh DESC ) 
					  FROM rms_score_monthly AS dd 
					  WHERE  dd.score_id= s.`id`
					)
				  ) AS rankOverallKh,

			   FIND_IN_SET((SELECT sm.OveralAvgEng FROM rms_score_monthly sm WHERE 
			   sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				  (SELECT GROUP_CONCAT(OveralAvgEng ORDER BY OveralAvgEng DESC)
					  FROM 
						  rms_score_monthly AS dd 
					  WHERE
						  s.`id`=dd.`score_id`
					  )
			  ) AS rankOverallEng,	

			  FIND_IN_SET((SELECT sm.OveralAvgCh FROM rms_score_monthly sm WHERE 
			  sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				 (SELECT GROUP_CONCAT(OveralAvgCh ORDER BY OveralAvgCh DESC)
					 FROM 
						 rms_score_monthly AS dd 
					 WHERE
						 s.`id`=dd.`score_id`
					 )
			 ) AS rankOverallCh,	
				 
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


				 (SELECT sm.totalKhAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
				(SELECT sm.totalEnAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
				(SELECT sm.totalChAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,
				(SELECT sm.OveralAvgKh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
				(SELECT sm.OveralAvgEng 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
				(SELECT sm.OveralAvgCh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,		

				 (SELECT sm.monthlySemesterAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

				(SELECT sm.overallAssessmentSemester 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent,

				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS overalSemester1,
				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS overalSemester2,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS totalScoreSemester1,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS totalScoreSemester2,

				FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm INNER JOIN rms_score AS d ON d.`id` = sm.score_id WHERE 
				d.exam_type=2 AND d.`for_semester`=1 AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
							 INNER JOIN  rms_score AS d ON d.`id` = dd.score_id
						WHERE
						d.exam_type=2  AND d.`for_semester`=1 AND d.group_id=s.group_id
						)
				) AS rankSemester1,

				FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm INNER JOIN rms_score AS d ON d.`id` = sm.score_id WHERE 
				d.exam_type=2 AND d.`for_semester`=2 AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
							 INNER JOIN  rms_score AS d ON d.`id` = dd.score_id
						WHERE
						d.exam_type=2  AND d.`for_semester`=2 AND d.group_id=s.group_id
						)
				) AS rankSemester2

			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}

		return $db->fetchRow($sql);
	}

	function getScoreAcsdemicInformation($data)
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
					s.note as promoteResult,
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

				FIND_IN_SET( 
					COALESCE((SELECT ms.OveralAvgKh FROM rms_score_monthly AS ms WHERE ms.score_id = s.`id` AND ms.student_id = " . $data['studentId'] . " LIMIT 1),'0'), 
					(    
					  SELECT 
						GROUP_CONCAT( dd.OveralAvgKh ORDER BY dd.OveralAvgKh DESC ) 
					  FROM rms_score_monthly AS dd 
					  WHERE  dd.score_id= s.`id`
					)
				  ) AS rankOverallKh,

			   FIND_IN_SET((SELECT sm.OveralAvgEng FROM rms_score_monthly sm WHERE 
			   sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				  (SELECT GROUP_CONCAT(OveralAvgEng ORDER BY OveralAvgEng DESC)
					  FROM 
						  rms_score_monthly AS dd 
					  WHERE
						  s.`id`=dd.`score_id`
					  )
			  ) AS rankOverallEng,	

			  FIND_IN_SET((SELECT sm.OveralAvgCh FROM rms_score_monthly sm WHERE 
			  sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				 (SELECT GROUP_CONCAT(OveralAvgCh ORDER BY OveralAvgCh DESC)
					 FROM 
						 rms_score_monthly AS dd 
					 WHERE
						 s.`id`=dd.`score_id`
					 )
			 ) AS rankOverallCh,	
				 
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


				 (SELECT sm.totalKhAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
				(SELECT sm.totalEnAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
				(SELECT sm.totalChAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,
				(SELECT sm.OveralAvgKh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
				(SELECT sm.OveralAvgEng 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
				(SELECT sm.OveralAvgCh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,		

				 (SELECT sm.monthlySemesterAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

				(SELECT sm.overallAssessmentSemester 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent,

				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS overalSemester1,
				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS overalSemester2,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS totalScoreSemester1,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS totalScoreSemester2,

				FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm INNER JOIN rms_score AS d ON d.`id` = sm.score_id WHERE 
				d.exam_type=2 AND d.`for_semester`=1 AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
							 INNER JOIN  rms_score AS d ON d.`id` = dd.score_id
						WHERE
						d.exam_type=2  AND d.`for_semester`=1 AND d.group_id=s.group_id
						)
				) AS rankSemester1,

				FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm INNER JOIN rms_score AS d ON d.`id` = sm.score_id WHERE 
				d.exam_type=2 AND d.`for_semester`=2 AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
							 INNER JOIN  rms_score AS d ON d.`id` = dd.score_id
						WHERE
						d.exam_type=2  AND d.`for_semester`=2 AND d.group_id=s.group_id
						)
				) AS rankSemester2

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

				FIND_IN_SET( 
					COALESCE((SELECT ms.OveralAvgKh FROM rms_score_monthly AS ms WHERE ms.score_id = s.`id` AND ms.student_id = " . $data['studentId'] . " LIMIT 1),'0'), 
					(    
					  SELECT 
						GROUP_CONCAT( dd.OveralAvgKh ORDER BY dd.OveralAvgKh DESC ) 
					  FROM rms_score_monthly AS dd 
					  WHERE  dd.score_id= s.`id`
					)
				  ) AS rankOverallKh,

			   FIND_IN_SET((SELECT sm.OveralAvgEng FROM rms_score_monthly sm WHERE 
			   sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				  (SELECT GROUP_CONCAT(OveralAvgEng ORDER BY OveralAvgEng DESC)
					  FROM 
						  rms_score_monthly AS dd 
					  WHERE
						  s.`id`=dd.`score_id`
					  )
			  ) AS rankOverallEng,	

			  FIND_IN_SET((SELECT sm.OveralAvgCh FROM rms_score_monthly sm WHERE 
			  sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				 (SELECT GROUP_CONCAT(OveralAvgCh ORDER BY OveralAvgCh DESC)
					 FROM 
						 rms_score_monthly AS dd 
					 WHERE
						 s.`id`=dd.`score_id`
					 )
			 ) AS rankOverallCh,	
				 
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


				 (SELECT sm.totalKhAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
				(SELECT sm.totalEnAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
				(SELECT sm.totalChAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,
				(SELECT sm.OveralAvgKh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
				(SELECT sm.OveralAvgEng 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
				(SELECT sm.OveralAvgCh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,		

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
	
	function getScoreEntrySettingInfo($data){ // for get validation date of monthly result for counting attendence and discipline In Monthly period
		$db = $this->getAdapter();
		$forSemester = empty($data["semesterId"]) ? "0" : $data["semesterId"];
		$forMonth = empty($data["forMonth"]) ? "0" : $data["forMonth"];
		$degree = empty($data["degree"]) ? "0" : $data["degree"];
		$examType = !empty($data['examType'])?$data['examType']:1;
		$sqlGrad="
			SELECT 
				stt.*  
			FROM rms_score_entry_setting AS stt 
			WHERE  stt.`status` =1 
				AND stt.`forSemester` = ".$forSemester."
				AND stt.`forMonth` = ".$forMonth." 
				AND stt.`examType` = ".$examType."
				AND FIND_IN_SET('".$degree."',stt.degreeId) 
			ORDER BY stt.id DESC 
			LIMIT 1
		";
		 return $db->fetchRow($sqlGrad);
	}
	function countAttendenceTranscript($data = null)
	{
		
		$db = $this->getAdapter();
		
		$examType = !empty($data['examType'])?$data['examType']:1;
		$entrySetting = array();
		if($examType==1){
			$entrySetting = $this->getScoreEntrySettingInfo($data);
		}
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";

		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		if(!empty($entrySetting)){
			$sql.=" AND sat.`date_attendence` >='".$entrySetting["fromDate"]."' AND sat.`date_attendence` <= '".$entrySetting["endDate"]."' ";
		}else{
			if($examType==1){
				if (!empty($data['forMonth'])) {
					$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
				}
			}
		}
		
		$sql .= " GROUP BY sat.date_attendence ORDER BY satd.attendence_status DESC";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function countAnnaulAttendence($data = null)
	{
		$db = $this->getAdapter();
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";

		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
	
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		$sql .= " GROUP BY sat.date_attendence ORDER BY satd.attendence_status DESC";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function countDisplineTranscript($data = null)
	{
		$db = $this->getAdapter();
		$examType = !empty($data['examType'])?$data['examType']:1;
		$entrySetting = array();
		if($examType==1){
			$entrySetting = $this->getScoreEntrySettingInfo($data);
		}
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=2 ";

	
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
	
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}

		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		if(!empty($entrySetting)){
			$sql.=" AND sat.`date_attendence` >='".$entrySetting["fromDate"]."' AND sat.`date_attendence` <= '".$entrySetting["endDate"]."' ";
		}else{
			if($examType==1){
				if (!empty($data['forMonth'])) {
					$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
				}
			}
		}
		$sql .= " LIMIT 1";
		
		return $db->fetchOne($sql);
	}
	function countAnnaulDispline($data = null)
	{
		$db = $this->getAdapter();
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=2 ";
	
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
	
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
	
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		$sql .= " LIMIT 1";
		
		return $db->fetchOne($sql);
	}
}
