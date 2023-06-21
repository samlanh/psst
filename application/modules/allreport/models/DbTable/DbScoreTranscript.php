<?php

class Allreport_Model_DbTable_DbScoreTranscript extends Zend_Db_Table_Abstract{
    
// 	function getTranscriptExamasdfasdf($data){//not use
// 		$db = $this->getAdapter();
// 		$sql="
// 		SELECT
// 		s.`id`,
// 		(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
// 		(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_monthen,
// 		s.exam_type,
// 		s.for_month AS for_month_id,
// 		s.for_semester,
// 		s.reportdate,
// 		s.title_score,
// 		s.max_score,
// 		sd.`student_id`,
// 		sm.total_score,
// 		sm.total_avg,
// 		FIND_IN_SET( total_avg,
// 		(
// 		SELECT GROUP_CONCAT( total_avg ORDER BY total_avg DESC )
// 		FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
// 		ss.`id`=dd.`score_id`
// 		AND ss.group_id= s.`group_id`
// 		AND ss.id=s.`id`
// 		)
// 		) AS rank,
// 		(SELECT count(ss.`id`) FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
// 		ss.`id`=dd.`score_id`
// 		AND ss.group_id= s.`group_id`
// 		AND ss.id=s.`id` LIMIT 1) as amountStudent,
// 		vst.*,
// 		(SELECT rms_group.group_code FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) AS group_code,
// 		gds.group_id AS group_id,
// 		(SELECT t.`teacher_name_kh` FROM `rms_teacher` t WHERE t.id =(SELECT rms_group.teacher_id FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) LIMIT 1) AS teacher,
// 		(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gds.academic_year LIMIT 1) as academic_year,
// 		(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
// 		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
// 		gds.degree AS degree_id,
// 		gds.academic_year AS for_academic_year,
// 		(SELECT br.school_namekh FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_namekh,
// 		(SELECT br.school_nameen FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_nameen,
// 		(SELECT br.photo FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS photo_branch
// 		FROM
// 		`rms_score` AS s,
// 		`rms_score_detail` AS sd,
// 		`rms_score_monthly` AS sm,
// 		rms_student AS vst,
// 		rms_group_detail_student AS gds
// 		WHERE
// 		gds.itemType=1
// 		AND s.`id`=sd.`score_id`
// 		AND vst.stu_id = sm.`student_id`
// 		AND vst.stu_id = sd.`student_id`
// 		AND vst.stu_id = gds.`stu_id`
// 		AND s.group_id = gds.`group_id`
// 		AND s.`id`=sm.`score_id`
// 		AND s.status = 1
			
// 		";
// 		if (!empty($data['scoreId'])){
// 			$sql.=" AND vst.`stu_id`=".$data['scoreId'];
// 		}
// 		if (!empty($data['studentId'])){
// 			$sql.=" AND gds.`group_id`=".$data['studentId'];
// 		}
// 		if (!empty($data['stu_id'])){
// 			$sql.=" AND vst.`stu_id`=".$data['stu_id'];
// 		}
// 		$sql.=" ORDER BY s.id DESC LIMIT 1";
// 		$data = array(
// 				'studentId'=>$data['studentId']
// 		);
// 		$studentInfo = $this->getStudentProfile($data);
	
		
// 		return $db->fetchRow($sql);
// 	}
	
	function getTranscriptExam($data){
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
				'studentId'=>$studentId
				);
		$studentInfo =  $this->getStudentProfile($arrStudent);
		
		$resultArray= array(
				'scoreId'=>$scoreId,
				'studentId'=>$studentId
		);
		$scoreInfo =  $this->getScoreInformation($resultArray);
		
		$resultScoreArr = array(
				'scoreId'=>$scoreId,
				'studentId'=>$studentId
				);
		$scoreSubjectInfo =  $this->getSubjectScoreTranscript($resultScoreArr);
		
		$scoreResultList = array();
		if(!empty($scoreSubjectInfo)){
			foreach ($scoreSubjectInfo as $key=> $result){
				
				$scoreResultList[$key] = array(
						'subject_id'=>$result['subject_id'],
						'gradingTotalId'=>$result['gradingTotalId'],
						'totalAverage'=>$result['totalAverage'],
						'score_cut'=>$result['score_cut'],
						'sub_name'=>$result['sub_name'],
						'subjectLang'=>$result['subjectLang'],
						'sub_name_en'=>$result['sub_name_en'],
						'maxScore'	=>$result['maxScore'],
						'amount_subject'=>$result['amount_subject'],
						'gradingTotalId'=>$result['gradingTotalId'],
						'innerSubject'=>0
					);
				
				$arrSub= array(
					'gradingId'=>$result['gradingTotalId'],
					'subjectId'=>$result['subject_id'],
					'studentId'=>$studentId,
					);
				$resultSubScore = $this->getSubScoreList($arrSub);
				if(!empty($resultSubScore)){
					$scoreResultList[$key]['innerSubject']=count($resultSubScore);
					$scoreResultList[$key]['innerSubjectList']=$resultSubScore;
				}
			}
		}
		$arreValue= array(
				'studentId'=>$studentId,
				'groupId'=>$scoreInfo['group_id'],
				'forType'=>$scoreInfo['exam_type'],
				'forSemester'=>$scoreInfo['for_semester'],
				'forMonth'=>$scoreInfo['for_month'],
				);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);
		
		$result= array(
				'studentInfo'=>$studentInfo,
				'scoreInfo'=>$scoreInfo,
				'scoreSubjectInfo'=>$scoreResultList,
				'EvalueationList'=>$resultEvalueAtion,
				);
		return $result;
	}
	function getSubScoreList($data){
		$sql="SELECT 
					totalGrading,
					subCriterialTitleKh,
					subCriterialTitleEng 
				FROM `rms_grading_detail` gd
				WHERE 
					gd.subCriterialTitleEng IS NOT NULL ";
		if(!empty($data['gradingId'])){
			$sql.=" AND gd.gradingId=".$data['gradingId'];
		}
		if(!empty($data['studentId'])){
			$sql.=" AND gd.studentId=".$data['studentId'];
		}
		if(!empty($data['subjectId'])){
			$sql.=" AND gd.subjectId=".$data['subjectId'];
		}
		return $this->getAdapter()->fetchAll($sql);
	}
	function getSubjectScoreTranscript($data){
		$db = $this->getAdapter();
		$strSubjectLange = " (SELECT subject_lang FROM `rms_group_subject_detail` WHERE 
						group_id=sd.group_id AND 
							subject_id=sd.subject_id LIMIT 1) ";
		
		$strSubjecMaxScore = " (SELECT max_score FROM `rms_group_subject_detail` WHERE
		group_id=sd.group_id AND
		subject_id=sd.subject_id LIMIT 1) ";
		
		
		$sql="SELECT
					$strSubjectLange AS subjectLang,
					$strSubjecMaxScore AS maxScore,
					sd.`subject_id`,
					sd.gradingTotalId,
					sd.`score` AS totalAverage,
					sd.score_cut,
					(SELECT CONCAT(sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name,
					(SELECT CONCAT(sj.subject_titleen) FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name_en,
					sd.amount_subject
				FROM  `rms_score_detail` AS sd
					WHERE 1 ";
		if(!empty($data['scoreId'])){
			$sql.=" AND sd.`score_id`=".$data['scoreId'];
		}
		if(!empty($data['studentId'])){
			$sql.=" AND sd.`student_id`=".$data['studentId'];
		}
		if(!empty($data['subjectId'])){
			$sql.=" AND sd.`subject_id`=".$data['subjectId'];
		}
		$sql.=" ORDER  BY $strSubjectLange ASC ";
		return $db->fetchAll($sql);
	}
	function getScoreInformation($data){
		$db = $this->getAdapter();
		$sql="SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					s.id as scoreId,
					s.group_id,
					s.title_score,
					s.exam_type,
					s.for_month,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_avg FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=".$data['studentId']." LIMIT 1),
					(SELECT GROUP_CONCAT(total_avg ORDER BY total_avg DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	
				 
				 (SELECT sm.total_avg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=".$data['studentId']." LIMIT 1) AS totalAvg,
				 		
				  (SELECT sm.total_score 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=".$data['studentId']." LIMIT 1) AS totalScoreAvg,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
			if(!empty($data['scoreId'])){
				$sql.=" AND s.id=".$data['scoreId'];
			}
		return $db->fetchRow($sql);
	}
	
	function getStudentProfile($data){
		$db = $this->getAdapter();
		$sql="SELECT 
			stu_id AS student_id,
			stu_code,
			stu_khname,
			last_name,stu_enname,
			sex FROM rms_student WHERE 1 ";
		if(!empty($data['studentId'])){
			$sql.=" AND stu_id=".$data['studentId'];
		}
		return $db->fetchRow($sql);
	}
	function getStudentAssessmentEvaluation($data){
		$db = $this->getAdapter();
		$sql="SELECT 
				(SELECT `comment` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) commentLabel,
				(SELECT r.rating FROM `rms_rating` r WHERE r.id=ratingId LIMIT 1)  ratingLabel,
				smd.teacherComment
					FROM `rms_studentassessment` AS sm,
					`rms_studentassessment_detail` AS smd
				WHERE smd.assessmentId=sm.id ";
		if (!empty($data['studentId'])){
			$sql.=" AND smd.studentId=".$data['studentId'];
		}
		if (!empty($data['groupId'])){
			$sql.=" AND sm.groupId=".$data['groupId'];
		}
		if (!empty($data['forType'])){
			$sql.=" AND sm.forType=".$data['forType'];
		}
		if (!empty($data['forSemester'])){
			$sql.=" AND sm.forSemester=".$data['forSemester'];
		}
		if (($data['forType'])==1){
				
			$sql.=" AND sm.forType=".$data['forType'];
				
			if ($data['forType']==1 AND !empty($data['forMonth'])){
				$sql.=" AND sm.forMonth=".$data['forMonth'];
			}
		}
		
		$sql.=" ORDER BY smd.id ASC ";
		return $db->fetchAll($sql);
	}
	function countAttendenceTranscript($data=null){
		$db = $this->getAdapter();
		$sql="SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";
			
// 			if(!empty($for_semester)){
// 				$sql.= " AND sat.for_semester=".$for_semester;
// 			}
			if(!empty($data['groupId'])){
				$sql.=" AND sat.group_id=".$data['groupId'];
			}
			if(!empty($data['semesterId'])){
				$sql.=" AND sat.for_semester=".$data['semesterId'];
			}
			if(!empty($data['studentId'])){
				$sql.=" AND satd.stu_id=".$data['studentId'];
			}
			if(!empty($data['forMonth'])){
				$sql.=" AND EXTRACT(MONTH FROM sat.date_attendence)=".$data['forMonth'];
			}
			
			if(!empty($data['attStatus'])){
				$sql.=" AND satd.attendence_status=".$data['attStatus'];
			}
		$sql.=" LIMIT 1";
		return $db->fetchOne($sql);
	}
	 
}