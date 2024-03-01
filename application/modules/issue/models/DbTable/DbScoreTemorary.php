<?php

class Issue_Model_DbTable_DbScoreTemorary extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_score';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	

	function getAllScoreTemporary($search = null)
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'subject_titleen';
		$title = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
			$title = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
		$sql = "SELECT gt.id,
			(SELECT $branch FROM `rms_branch` WHERE br_id=gt.branchId LIMIT 1) As branchName,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gt.academicYear LIMIT 1) AS acadecmicYear,
			(SELECT group_code FROM rms_group WHERE id=gt.groupId limit 1 ) AS  groupCode,
			(SELECT $colunmname  FROM rms_subject WHERE id=gt.subjectId limit 1 ) AS  subjectName,
			(SELECT $title  FROM rms_exametypeeng WHERE id=gt.criteriaId limit 1 ) AS  criteriaName,
			(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =gt.examType LIMIT 1) as examType,
			gt.forSemester,
			CASE
				WHEN gt.examType = 2 THEN ''
			ELSE (SELECT $month FROM `rms_month` WHERE id=gt.forMonth  LIMIT 1) 
			END 
			as for_month,
			gt.dateInput,
			(SELECT teacher_name_kh FROM rms_teacher WHERE gt.teacherId=rms_teacher.id LIMIT 1 ) AS taecherName
		";
		//s.max_score,
		$sql .= $dbp->caseStatusShowImage("gt.status");
		$sql .= " FROM `rms_grading_tmp` AS gt  WHERE 1  "; //AND s.status=1

		$where = '';
		$from_date = (empty($search['start_date'])) ? '1' : " gt.dateInput >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " gt.dateInput <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
			// $s_where = array();
			// $s_search = addslashes(trim($search['adv_search']));
			// $s_where[] = " s.title_score LIKE '%{$s_search}%'";
			// $s_where[] = " s.note LIKE '%{$s_search}%'";
			// $where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
	
		if (!empty($search['academic_year'])) {
			$where .= " AND gt.academic_year =" . $search['academic_year'];
		}
	
		if (!empty($search['group'])) {
			$where .= " AND `gt`.`group_id` =" . $search['group'];
		}
		if (!empty($search['branch_id'])) {
			$where .= " AND `gt`.`branchId` =" . $search['branch_id'];
		}
		if ($search['for_month'] > 0) {
			$where .= " AND gt.forMonth =" . $search['for_month'];
		}
		if ($search['exam_type'] > 0) {
			$where .= " AND gt.examType =" . $search['exam_type'];
		}
		if ($search['for_semester'] > 0) {
			$where .= " AND gt.forSemester =" . $search['for_semester'];
		}
		$where .= $dbp->getAccessPermission('s.branchId');
		$order = " ORDER BY id DESC ";
		 echo $sql . $where . $order; 
		return $db->fetchAll($sql . $where . $order);
	}

	function getScoreById($score_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.*,
				(SELECT g.is_pass 
					FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) as is_pass 
			FROM rms_score AS s 
			WHERE s.id=$score_id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
}
