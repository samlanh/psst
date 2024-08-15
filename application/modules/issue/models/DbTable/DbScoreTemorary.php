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
		$title = 'c.title_en';
		$label = 'name_en';
		$month = "month_en";
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
			$title = 'c.title';
			$label = 'name_kh';
			$month = "month_kh";
		}
		$branch = $dbp->getBranchDisplay();
		$sql = "
			SELECT 
				gt.id
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=gt.branchId LIMIT 1) As branchName
				,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gt.academicYear LIMIT 1) AS acadecmicYear
				,(SELECT group_code FROM rms_group WHERE id=gt.groupId limit 1 ) AS  groupCode
				,(SELECT $colunmname  FROM rms_subject WHERE id=gt.subjectId limit 1 ) AS  subjectName
				,$title
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =gt.examType LIMIT 1) as examType
				,gt.forSemester
				,CASE
					WHEN gt.examType = 2 THEN ''
					ELSE (SELECT $month FROM `rms_month` WHERE id=gt.forMonth  LIMIT 1) 
				END  as for_month
				,gt.createDate
				,(SELECT teacher_name_kh FROM rms_teacher WHERE gt.teacherId=rms_teacher.id LIMIT 1 ) AS taecherName
				,(SELECT COUNT(gd.id) FROM `rms_grading_detail_tmp` AS gd WHERE gd.gradingId = gt.id AND gd.totalGrading>0 LIMIT 1 )  AS studentAmount
		";
		//s.max_score,
		$sql .= $dbp->caseStatusShowImage("gt.status");
		$sql .= " FROM `rms_grading_tmp` AS gt
					INNER JOIN `rms_exametypeeng` AS c ON gt.criteriaId = c.id WHERE 1  ";

		$sql .= "  AND CASE WHEN (SELECT gd.isLock FROM `rms_grading` AS gd WHERE gd.status = 1 
					AND gd.groupId=gt.groupId 
					AND gd.settingEntryId = gt.settingEntryId  
					AND gd.subjectId = gt.subjectId  
					AND gd.teacherId = gt.teacherId 
					AND gd.examType = gt.examType LIMIT 1
					) IS NULL THEN c.criteriaType =1
					ELSE  c.criteriaType =2 
					END
					AND CASE WHEN c.criteriaType =2  THEN (SELECT gd.isLock FROM `rms_grading` AS gd WHERE gd.status = 1 
					AND gd.groupId=gt.groupId 
					AND gd.settingEntryId = gt.settingEntryId  
					AND gd.subjectId = gt.subjectId  
					AND gd.teacherId = gt.teacherId 
					AND gd.examType = gt.examType LIMIT 1
					)!=1
					ELSE '1'
					END ";

		$where = '';
		$from_date = (empty($search['start_date'])) ? '1' : " gt.createDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " gt.createDate <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
		}

		if (!empty($search['academic_year'])) {
			$where .= " AND gt.academicYear =" . $search['academic_year'];
		}

		if (!empty($search['group'])) {
			$where .= " AND `gt`.`groupId` =" . $search['group'];
		}

		if (!empty($search['teacher'])) {
			$where .= " AND gt.teacherId  =" . $search['teacher'];
		}
		if (!empty($search['subjectId'])) {
			$where .= " AND gt.subjectId   =" . $search['subjectId'];
		}
		if (!empty($search['criteriaId'])) {
			$where .= " AND gt.criteriaId =" . $search['criteriaId'];
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
		$where .= $dbp->getAccessPermission('gt.branchId');
		$order = " ORDER BY id DESC ";
		//  echo $sql . $where . $order; 
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
	function deleteTmpScore($id)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();

		try {
			$rs = $this->getScoreTmpById($id);

			$this->_name = "rms_grading_tmp";
			$where = " id = $id";
			$this->delete($where);

			$this->_name = 'rms_grading_detail_tmp';
			$this->delete("gradingId=" . $id);

			if ($rs['criteriaType'] == 2) {  // EXAM

				$this->_name = 'rms_grading';
				$this->delete("gradingTmpId=" . $id);

				$this->_name = 'rms_grading_detail';
				$this->delete("gradingTmpId=" . $id);

				$this->_name = 'rms_grading_total';
				$this->delete("gradingTmpId=" . $id);
			}

			$db->commit();
		} catch (exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	function getScoreTemporaryInfo($search = null)
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'subject_titleen';
		$title = 'c.title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
			$title = 'c.title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
		$sql = "
			SELECT 
				gt.*,
				(SELECT $branch FROM `rms_branch` WHERE br_id=gt.branchId LIMIT 1) As branchName,
				(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gt.academicYear LIMIT 1) AS acadecmicYear,
				(SELECT group_code FROM rms_group WHERE id=gt.groupId limit 1 ) AS  groupCode,
				(SELECT $colunmname  FROM rms_subject WHERE id=gt.subjectId limit 1 ) AS  subjectName,
				$title AS title,
				(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =gt.examType LIMIT 1) as examType,
				
				CASE
					WHEN gt.examType = 2 THEN ''
				ELSE (SELECT $month FROM `rms_month` WHERE id=gt.forMonth  LIMIT 1) 
				END as for_month,
				gt.createDate,
				(SELECT teacher_name_kh FROM rms_teacher WHERE gt.teacherId=rms_teacher.id LIMIT 1 ) AS taecherName,
				(SELECT COUNT(gd.id) FROM `rms_grading_detail_tmp` AS gd WHERE gd.gradingId = gt.id AND gd.totalGrading>0 LIMIT 1 )  AS studentAmount
		";
		$sql .= " FROM `rms_grading_tmp` AS gt
					INNER JOIN `rms_exametypeeng` AS c ON gt.criteriaId = c.id WHERE 1  ";

		$sql .= "  AND CASE WHEN (SELECT gd.isLock FROM `rms_grading` AS gd WHERE gd.status = 1 
					AND gd.groupId=gt.groupId 
					AND gd.settingEntryId = gt.settingEntryId  
					AND gd.subjectId = gt.subjectId  
					AND gd.teacherId = gt.teacherId 
					AND gd.examType = gt.examType LIMIT 1
					) IS NULL THEN c.criteriaType =1
					ELSE  c.criteriaType =2 
					END
					AND CASE WHEN c.criteriaType =2  THEN (SELECT gd.isLock FROM `rms_grading` AS gd WHERE gd.status = 1 
					AND gd.groupId=gt.groupId 
					AND gd.settingEntryId = gt.settingEntryId  
					AND gd.subjectId = gt.subjectId  
					AND gd.teacherId = gt.teacherId 
					AND gd.examType = gt.examType LIMIT 1
					)!=1
					ELSE '1'
					END ";

		$where = '';
		$where .= " AND gt.id =" . $search['gradingTmpId'];
		$order = " ORDER BY id DESC ";
		$order .= " LIMIT 1 ";
		return $db->fetchRow($sql . $where . $order);
	}
}
