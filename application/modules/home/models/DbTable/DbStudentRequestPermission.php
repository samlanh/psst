<?php class Home_Model_DbTable_DbStudentRequestPermission extends Zend_Db_Table_Abstract
{

	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllStudentRequest($search = '')
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branchLabel = $dbp->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				rp.id
				,(SELECT b.$branchLabel FROM `rms_branch` AS b  WHERE b.br_id = rp.branchId LIMIT 1) AS branch_name
				,(SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1) AS studentCode
				,(SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1) AS StudentName
				,g.group_code AS GroupName
				,rp.amountDay
			,CASE    
				WHEN  rp.sessionType = 1 THEN '" . $tr->translate("MORNING") . "'
				WHEN  rp.sessionType = 2 THEN '" . $tr->translate("AFTERNOON") . "'
				WHEN  rp.sessionType = 3 THEN '" . $tr->translate("FULL_DAY") . "'
			END AS sessionType
			,rp.phoneNumber
			,rp.fromDate
			,rp.toDate
			,rp.reason
			,CASE    
				WHEN  rp.requestStatus = 0 THEN '" . $tr->translate("PENDING") . "'
				WHEN  rp.requestStatus = 1 THEN '" . $tr->translate("APPROVED") . "'
				WHEN  rp.requestStatus = 2 THEN '" . $tr->translate("REJECTED") . "'
			END AS requestStatus 
		";
		// $sql .= $dbp->caseStatusShowImage("status");
		$sql .= " FROM 
					`rms_student_request_permission` AS rp
					JOIN rms_group AS g ON g.id = rp.groupId
					
		 WHERE 1
    	";
		$where = ' ';
		$from_date = (empty($search['start_date'])) ? '1' : " rp.createDate >= '" . date("Y-m-d", strtotime($search['start_date'])) . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " rp.createDate <= '" . date("Y-m-d", strtotime($search['end_date'])) . " 23:59:59'";
		$where .= " AND  " . $from_date . " AND " . $to_date;
		if (!empty($search['advance_search'])) {
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
			$s_where[] = " REPLACE((SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(g.group_code,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(phoneNumber,' ','')  LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_search'])) {
			$where .= " AND rp.branchId = " . $db->quote($search['branch_search']);
		}
		if (!empty($search['session_type'])) {
			$where .= " AND rp.sessionType = " . $db->quote($search['session_type']);
		}
		if (!empty($search['request_status'])) {
			$where .= " AND rp.requestStatus = " . $db->quote($search['request_status']);
		}

		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission('rp.branchId');
		$where .= $dbp->getDegreePermission('g.degree');
		$where .= " ORDER BY rp.id DESC";
		$row = $db->fetchAll($sql . $where);
		return $row;
	}

	public function updatePermission($_data)
	{
		$_arr = array(
			'requestStatus'	=> $_data['request_status'],
		);
		$id = $_data['id'];
		$where = "id =$id";
		$this->_name = "rms_student_request_permission";
		$this->update($_arr, $where);

		$this->_name = 'rms_student_attendence_detail';
		$whereDl = " studentRequestId = " . $id;
		$this->delete($whereDl);
		//Add to Attendance 
		if ($_data['request_status'] == 1) {
			$amount_day = $_data['amountDay'];
			$date = $_data['fromDate'];
			if (!empty($amount_day)) {
				for ($i = 0; $i < $amount_day; $i++) {
					$att_date = date('Y-m-d', strtotime($date . ' + ' . $i . ' days'));

					$sDate = new DateTime($att_date);
					$dayNum = $sDate->format('N');
					if ($dayNum != 7) {
						$arr = array(
							'studentRequestId' => $id,
							'attendence_id'	   => 0,
							'stu_id'		   => $_data['studentId'],
							'attendence_status' => 3,
							'description'	   => $_data['reason'],
							'type'			   => 2, //from one student 
							'branchId'		   => $_data['branchId'],
							'groupId'		   => $_data['groupId'],
							'forSemester'	   => 1,
							'forSession'	   => $_data['sessionType'],
							'attendanceDate'   => $att_date,
							'createDate'	   => date("Y-m-d"),
							'modifyDate'	   => date("Y-m-d"),
						);
						$this->_name = 'rms_student_attendence_detail';
						$this->insert($arr);
					}
				}
			}
		}
	}

	public function getRequestById($id)
	{
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
		SELECT 
			rp.*
			,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = rp.branchId LIMIT 1) AS branch_name
			,(SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1) AS StudentCode
			,(SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = rp.studentId LIMIT 1) AS StudentName
			,g.group_code AS GroupName
			,rp.amountDay
			,CASE    
				WHEN  rp.sessionType = 1 THEN '" . $tr->translate("MORNING") . "'
				WHEN  rp.sessionType = 2 THEN '" . $tr->translate("AFTERNOON") . "'
				WHEN  rp.sessionType = 3 THEN '" . $tr->translate("FULL_DAY") . "'
			END AS SeesionLabel
			,CASE    
				WHEN  rp.requestStatus = 0 THEN '" . $tr->translate("PENDING") . "'
				WHEN  rp.requestStatus = 1 THEN '" . $tr->translate("APPROVED") . "'
				WHEN  rp.requestStatus = 2 THEN '" . $tr->translate("REJECTED") . "'
			END AS StatusLbel
		FROM 
			`rms_student_request_permission` AS rp
			JOIN rms_group AS g ON g.id = rp.groupId
		WHERE rp.id= " . $id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('rp.branchId');
		$sql .= $dbp->getDegreePermission('g.degree');
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function getAttendacenDetailById($id)
	{
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "SELECT * FROM `rms_student_attendence_detail` WHERE studentRequestId= " . $id . " ORDER BY isCompleted DESC limit 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
}
