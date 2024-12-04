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
		$criterialList ='
			(SELECT
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
			FROM `v_criterial_setting` AS vs 
			WHERE vs.`score_setting_id` = g.`gradingId` 
			AND vs.forExamType = '.$search['exam_type'].' 
			AND vs.criteriaType > 0
			) AS criterialList
		';

		$jsonSubjectList ='
			(SELECT
				CONCAT(
					"[" ,
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"subject_id\":", `gs`.`subject_id`, ",",
							"\"subject_lang\":", `sj`.subject_lang, ",",
							"\"shortcut\":\"", `sj`.'.$subjectTitle.', "\",",
							"\"teacher\":\"", `t`.'.$teacherName.', "\"",
							"}"
						) 
						ORDER BY `sj`.`subject_lang`, sj.id ASC
					),
					"]"
				)
			FROM `rms_group_subject_detail` `gs`
				LEFT JOIN `rms_subject` `sj` ON `sj`.`id` = `gs`.`subject_id`
			    LEFT JOIN `rms_teacher` `t`	ON `t`.`id` = `gs`.`teacher`
				WHERE 
				`gs`.`group_id` = g.id  ';
			if($search['exam_type']==1){
				$jsonSubjectList .=' AND  `gs`.`amount_subject` > 0 ';
			}else{
				$jsonSubjectList .=' AND  `gs`.`amount_subject_sem` > 0 ';
			}
		 $jsonSubjectList .=' ) AS jsonSubjectList ';

		$sql = "SELECT `g`.`id`, `g`.`gradingId`,
			
			`g`.`group_code` AS `group_code`,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,	
			 `g`.`semester` AS `semester`, 
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			(select teacher_name_kh from rms_teacher where rms_teacher.id = g.teacher_id limit 1 ) as teaccher,
			 $criterialList ,
			 $jsonSubjectList, 
		      vgt.jsonScoreTmp,
			  vg.jsonScoreGrading
			 ";

		$sql .= $dbp->caseStatusShowImage("g.status");
		$sql .= " FROM `rms_group` AS `g` 
				LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = `g`.`degree`
				LEFT JOIN v_grading_tmp AS vgt ON vgt.groupId = g.id AND vgt.gradingSettingId = g.gradingId
				LEFT JOIN v_grading AS vg ON vg.groupId = g.id 
		";

		$where = ' WHERE 1 ';
		$from_date = (empty($search['start_date'])) ? '1' : "g.date >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "g.date <= '" . $search['end_date'] . " 23:59:59'";
		//$where.= " AND ".$from_date." AND ".$to_date;

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
			$where .= " AND vgt.examType =" . $search['exam_type'];
			$where .= " AND vg.examType =" . $search['exam_type'];
		}
		if ($search['for_month'] > 0) {
			$where .= " AND vgt.forMonth =" . $search['for_month'];
			$where .= " AND vg.forMonth =" . $search['for_month'];
		}
		if ($search['for_semester'] > 0) {
			$where .= " AND vgt.forSemester =" . $search['for_semester'];
			$where .= " AND vg.forSemester =" . $search['for_semester'];
		}

		$where .= $dbp->getAccessPermission('g.branch_id');
		$where .= $dbp->getSchoolOptionAccess('i.schoolOption');
		$order =  ' ORDER BY `g`.`degree`,g.grade ASC,  `g`.`id` DESC ';
		//echo $sql . $where . $order;
		return $db->fetchAll($sql . $where . $order);
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
