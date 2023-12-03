<?php
class Issuesetting_Model_DbTable_DbAllowTeacherScoreSetting extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_score_entry_setting';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllTeacherScoreSetting($search = '')
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = " SELECT  s.id,
		(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=s.branchId LIMIT 1) AS branchName,
		(SELECT group_code FROM `rms_group` WHERE id=s.group LIMIT 1) AS groupName,
		(SELECT teacher_name_kh FROM `rms_teacher` WHERE id=s.teacherId LIMIT 1) AS TeacherName,
		(SELECT GROUP_CONCAT(sub.subject_titlekh) FROM rms_subject AS sub WHERE FIND_IN_SET( sub.id , s.subjectId ) LIMIT 1) AS subjectTitle,
		s.endDate,
		(SELECT CONCAT(first_name) FROM rms_users WHERE id=userId )AS userName,
		s.createDate ";
		$sql .= $dbp->caseStatusShowImage("s.status");
		$sql .= " FROM `rms_allowed_teacher_score_setting` AS s WHERE 1  ";
		$orderby = "  ORDER BY s.id DESC";
		$where = ' ';
		$from_date = (empty($search['start_date'])) ? '1' : "s.createDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "s.createDate <= '" . $search['end_date'] . " 23:59:59'";
		$where .= " AND " . $from_date . " AND " . $to_date;
		if (!empty($search['advance_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " s.title LIKE '%{$s_search}%'";
			$s_where[] = " s.description LIKE '%{$s_search}%'";
			$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_search'])) {
			$where .= " AND s.branchId = " . $db->quote($search['branch_search']);
		}
		if ($search['status_search'] > -1) {
			$where .= " AND s.status = " . $db->quote($search['status_search']);
		}
		$where .= $dbp->getAccessPermission('s.branchId');
		return $db->fetchAll($sql . $where . $orderby);
	}
	public function addAllowTeacherScoreSetting($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {

			$subjectId = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($subjectId)){
	    			$subjectId = $rs;
	    		}else{ $subjectId = $subjectId.",".$rs;
	    		}
	    	}
			$_arr = array(
				'branchId'		 => $_data['branch_id'],
				'teacherId'		 => $_data['teacher_id'],
				'degree' 	 	 => $_data['degree'],
				'group'		 	 => $_data['group'],
				'academicYear' 	 => $_data['academic_year'],
				'subjectId'		 => $subjectId,
				'endDate'		 => $_data['end_date'],
				'createDate' 	 =>date("Y-m-d H:i:s"),
				'modifyDate' 	 =>date("Y-m-d H:i:s"),
				'userId'	 	 => $this->getUserId(),
				'status'	 	 => 1,
			);
			$this->_name = 'rms_allowed_teacher_score_setting';
			$this->insert($_arr);
			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	function getAllowTeacherScoreSettingById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.* FROM `rms_allowed_teacher_score_setting` AS s WHERE s.id=$id";

		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('s.branchId');
		return $db->fetchRow($sql);
	}

	public function editAllowTeacherScoreSetting($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$subjectId = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($subjectId)){
	    			$subjectId = $rs;
	    		}else{ $subjectId = $subjectId.",".$rs;
	    		}
	    	}
			$status = empty($_data['status']) ? 0 : 1;
			$_arr = array(
				'branchId'		 => $_data['branch_id'],
				'teacherId'		 => $_data['teacher_id'],
				'degree' 		 => $_data['degree'],
				'group'			 => $_data['group'],
				'academicYear' 	 => $_data['academic_year'],
				'subjectId'		 => $subjectId,
				'endDate'		 => $_data['end_date'],
				'createDate' 	 =>date("Y-m-d H:i:s"),
				'modifyDate' 	 =>date("Y-m-d H:i:s"),
				'userId'	 	 => $this->getUserId(),
				'status'	 	 => $status,
			);
			$this->_name = 'rms_allowed_teacher_score_setting';
			$where = ' id=' . $_data['id'];
			$this->update($_arr, $where);
			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}
