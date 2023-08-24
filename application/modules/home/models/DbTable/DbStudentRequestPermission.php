<?php class Home_Model_DbTable_DbStudentRequestPermission extends Zend_Db_Table_Abstract{

    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getAllStudentRequest($search = ''){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="SELECT id,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = branchId LIMIT 1) AS branch_name,
		(SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = studentId LIMIT 1) AS StudentName,
		(SELECT g.group_code FROM `rms_group` AS g  WHERE g.id = groupId LIMIT 1) AS GroupName,
		amountDay, 
		CASE    
			WHEN  sessionType = 1 THEN '".$tr->translate("FULL_DAY")."'
			WHEN  sessionType = 2 THEN '".$tr->translate("MORNING")."'
			WHEN  sessionType = 3 THEN '".$tr->translate("AFTERNOON")."'
		END AS sessionType,
		phoneNumber, fromDate, toDate, reason, 
		CASE    
			WHEN  requestStatus = 0 THEN '".$tr->translate("PENDING")."'
			WHEN  requestStatus = 2 THEN '".$tr->translate("APPOROVED")."'
			WHEN  requestStatus = 3 THEN '".$tr->translate("REJECTED")."'
		END AS requestStatus

		FROM `rms_student_request_permission` 
		 WHERE 1
    	";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " createDate >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " createDate <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where.= " AND  ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = studentId LIMIT 1),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g  WHERE g.id = groupId LIMIT 1),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(phoneNumber,' ','')  LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_search'])){
    		$where.= " AND branchId = ".$db->quote($search['branch_search']);
    	}
    	if(!empty($search['session_type'])){
    		$where.= " AND sessionType = ".$db->quote($search['session_type']);
    	}
    	if(!empty($search['request_status'])){
    		$where.= " AND requestStatus = ".$db->quote($search['request_status']);
    	}
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('branchId');
		$where.=" ORDER BY id DESC";
		$row = $db->fetchAll($sql.$where);
		return $row;
    }

	public function getRequestById($id){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT *,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = branchId LIMIT 1) AS branch_name,
		(SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = studentId LIMIT 1) AS StudentName,
		(SELECT g.group_code FROM `rms_group` AS g  WHERE g.id = groupId LIMIT 1) AS GroupName,
		amountDay, 
		CASE    
			WHEN  sessionType = 1 THEN '".$tr->translate("FULL_DAY")."'
			WHEN  sessionType = 2 THEN '".$tr->translate("MORNING")."'
			WHEN  sessionType = 3 THEN '".$tr->translate("AFTERNOON")."'
		END AS sessionType,
		CASE    
			WHEN  requestStatus = 0 THEN '".$tr->translate("PENDING")."'
			WHEN  requestStatus = 2 THEN '".$tr->translate("APPOROVED")."'
			WHEN  requestStatus = 3 THEN '".$tr->translate("REJECTED")."'
		END AS requestStatus
		FROM `rms_student_request_permission` WHERE id= ".$id;
	
		$row = $db->fetchRow($sql);
		return $row;
	}
    
}