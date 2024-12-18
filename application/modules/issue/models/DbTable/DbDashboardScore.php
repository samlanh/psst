<?php

class Issue_Model_DbTable_DbDashboardScore extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_group';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllGroups($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$criterialTitle="criterialTitleEn";
		$subjectTitle="subject_titleen";
		$teacherName="teacher_name_en";

		if ($currentLang == 1) {
		
			$criterialTitle="criterialTitle";
			$subjectTitle="subject_titlekh";
			$teacherName="teacher_name_kh";
		}

		// Concat Criteria

		$criterialList ='
			(
				CONCAT(
					"[" ,
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"criteriaId\":", vs.criteriaId, ",",
							"\"criteriaType\":", vs.criteriaType, ",",
							"\"criterialTitle\":\"", vs.'.$criterialTitle.', "\"",
							"}"
						) 
						ORDER BY vs.criteriaType,vs.criteriaId ASC
					),
					"]"
				)
			) AS criterialList
		';

		// Subquery group concat subject AND  scoretmp

		$scoreSubjectTmpList ='
		(SELECT
			CONCAT(
				"[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"subjectId\":", COALESCE(gs.`subject_id`, 0), ",",
							"\"teacherId\":", COALESCE(gs.`teacher`, 0), ",",
							"\"gradingId\":", COALESCE(vgs.`gradingId`, 0), ",",
							"\"subject_lang\":", COALESCE(sj.`subject_lang`, 0), ",",
							"\"shortcut\":\"", COALESCE(sj.'.$subjectTitle.', ""), "\",",
							"\"teacher\":\"", COALESCE(t.'.$teacherName.', ""), "\",",
							"\"dateInput\":\"", COALESCE(vgs.`dateInput`, ""), "\",",
							"\"jsonScoreTmp\":", COALESCE(vgs.`jsonScoreTmp`, "[]"),
							"}"
						)
						ORDER BY sj.`subject_lang`, sj.`id` ASC
					),
				"]"
			) AS json_result
			FROM `rms_group_subject_detail` `gs`
			    LEFT JOIN  `v_grading_tmp_by_subject` AS vgs  ON
				 ( vgs.`subjectId` = gs.`subject_id` AND vgs.`groupId` = gs.`group_id` ';

				 //  Search Grading Score 

				    if ($search['exam_type'] > 0) {
						$scoreSubjectTmpList .='  AND vgs.examType = '.$search['exam_type'];
					}
					if ($search['for_month'] > 0) {
						$scoreSubjectTmpList .='  AND vgs.forMonth = '.$search['for_month'];
					}
					if ($search['for_semester'] > 0) {
						$scoreSubjectTmpList .='  AND vgs.forSemester = '.$search['for_semester'];
					}
				 
				$scoreSubjectTmpList .=' )
				LEFT JOIN `rms_subject` `sj` ON `sj`.`id` = `gs`.`subject_id`
			    LEFT JOIN `rms_teacher` `t`	ON `t`.`id` = `gs`.`teacher`
				WHERE gs.`group_id` = g.`id`
			';
			if ($search['exam_type'] > 0) {
				if($search['exam_type']==1){
					$scoreSubjectTmpList .=' AND  `gs`.`amount_subject` > 0 ';
				}else{
					$scoreSubjectTmpList .=' AND  `gs`.`amount_subject_sem` > 0 ';
				}
			}
			if (!empty($search['teacher'])) {
				$scoreSubjectTmpList .= ' AND `gs`.`teacher` =' . $search['teacher'];
			}
			if (!empty($search['subjectId'])) {
				$scoreSubjectTmpList .= ' AND `gs`.`subject_id` =' . $search['subjectId'];
			}
			if (!empty($search['issueScoreStatus'])) {
				if($search['issueScoreStatus']==1){
					$scoreSubjectTmpList .= " AND vgs.gradingId > 0 "; // ប្រឡងរួច
				}elseif($search['issueScoreStatus']==2){
					$scoreSubjectTmpList .= " AND ( vgs.gradingId = 0 OR vgs.jsonScoreTmp IS NULL ) "; // មិនទាន់ប្រឡង
				}
				// elseif($search['issueScoreStatus']==3){
				// 	$scoreSubjectTmpList .= " AND vgs.jsonScoreTmp IS NULL "; // មិនទាន់បញ្ចូលរបបពិន្ទុ
				// }
			}
		$scoreSubjectTmpList .=' ) ';

		/// Group Query

		$sql = "SELECT `g`.`id`, `g`.`gradingId`,
			
			`g`.`group_code` AS `group_code`,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,	
			 `g`.`semester` AS `semester`, 
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			(select $teacherName from rms_teacher where rms_teacher.id = g.teacher_id limit 1 ) as teaccher,
			 $criterialList ,
			 $scoreSubjectTmpList  AS scoreSubjectTmpList,
			 COALESCE(s.id , 0) AS scoreId,
    		 COALESCE(sts.id, 0) AS assementId
			 ";

		$sql .= $dbp->caseStatusShowImage("g.status");
		$sql .= " FROM `rms_group` AS `g` 
		 		LEFT JOIN  `v_criterial_setting` AS vs ON (  vs.`score_setting_id` = g.`gradingId`  AND vs.criteriaType > 0)
				LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = `g`.`degree`
				LEFT JOIN `rms_score` AS s ON 
				( 
					s.status = 1 AND s.group_id = g.id ";
					if ($search['exam_type'] > 0) {
						$sql .='  AND  s.exam_type = '.$search['exam_type'];
					}
					if ($search['for_month'] > 0) {
						$sql .='  AND s.for_month = '.$search['for_month'];
					}
					if ($search['for_semester'] > 0) {
						$sql .='  AND s.for_semester = '.$search['for_semester'];
					}
					
		$sql .= " )
				LEFT JOIN `rms_studentassessment` AS sts ON (sts.scoreId = s.id AND sts.groupId = g.id )
		";
		$where = ' WHERE 1 ';

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[] = "(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND `g`.`degree`=' . $search['degree'];
		}
		if ($search['exam_type'] > 0) {
			$where .= ' AND vs.forExamType=' . $search['exam_type'];
		}
		if (!empty($search['scoreCombineStatus'])) {
			if ($search['scoreCombineStatus']==1 ) {
				$where .='  AND  s.id IS NOT NULL ';
			}elseif($search['scoreCombineStatus']==2){
				$where .='  AND  s.id IS NULL ';
			}
		}
		if (!empty($search['evaluationStatus'])) {
			if ($search['evaluationStatus']==1 ) {
				$where .='  AND sts.id IS NOT NULL ';
			}elseif($search['evaluationStatus']==2){
				$where .='  AND sts.id IS NULL ';
			}
		}
		$where .= ' AND ('.$scoreSubjectTmpList.') IS NOT NULL  '; // hide group that value null

		$where .= $dbp->getAccessPermission('g.branch_id');
		$where .= $dbp->getSchoolOptionAccess('i.schoolOption');
		$groupBy="  GROUP BY g.id  ";
		$order =  ' ORDER BY `g`.`degree`,g.grade,`g`.`group_code` ASC ';
		//echo $sql . $where .$groupBy. $order; exit();
		return $db->fetchAll($sql . $where .$groupBy. $order);

	}

	function getScoreTmpById($score_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.*,
		(SELECT c.criteriaType FROM  `rms_exametypeeng` AS c WHERE c.id = s.`criteriaId` LIMIT 1 ) AS criteriaType
		FROM rms_grading_tmp AS s WHERE s.id =$score_id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branchId');
		return $db->fetchRow($sql);
	}
	function deleteTmpScore($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();

		try {
			
			$id= $_data['id'];

			$rs = $this->getScoreTmpById($id);
			if(empty($rs)){
				return 2;//not permission
			}
			$this->_name = "rms_grading_tmp";
			$where = " id = $id";
			$this->delete($where);

			$this->_name = 'rms_grading_detail_tmp';
			$this->delete("gradingId=" . $id);
			if(!empty($rs)){
				if ($rs['criteriaType'] == 2) {  // EXAM

					$this->_name = 'rms_grading';
					$this->delete("gradingTmpId=" . $id);
	
					$this->_name = 'rms_grading_detail';
					$this->delete("gradingTmpId=" . $id);
	
					$this->_name = 'rms_grading_total';
					$this->delete("gradingTmpId=" . $id);
				}

			}
			$db->commit();
			return 1;
		} catch (exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}
