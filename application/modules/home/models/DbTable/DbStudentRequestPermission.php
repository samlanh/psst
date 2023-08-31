<?php class Home_Model_DbTable_DbStudentRequestPermission extends Zend_Db_Table_Abstract{

    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getAllStudentRequest($search = ''){
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="SELECT id,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = branchId LIMIT 1) AS branch_name,
		(SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = studentId LIMIT 1) AS studentCode,
		(SELECT CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s  WHERE s.stu_id = studentId LIMIT 1) AS StudentName,
		(SELECT g.group_code FROM `rms_group` AS g  WHERE g.id = groupId LIMIT 1) AS GroupName,
		amountDay, 
		CASE    
			WHEN  sessionType = 1 THEN '".$tr->translate("MORNING")."'
			WHEN  sessionType = 2 THEN '".$tr->translate("AFTERNOON")."'
			WHEN  sessionType = 3 THEN '".$tr->translate("FULL_DAY")."'
		END AS sessionType,
		phoneNumber, fromDate, toDate, reason, 
		CASE    
			WHEN  requestStatus = 0 THEN '".$tr->translate("PENDING")."'
			WHEN  requestStatus = 1 THEN '".$tr->translate("APPROVED")."'
			WHEN  requestStatus = 2 THEN '".$tr->translate("REJECTED")."'
		END AS requestStatus ";
		// $sql.=$dbp->caseStatusShowImage("requestStatus");
		$sql.="	FROM `rms_student_request_permission` 
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

	public function updatePermission($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr=array(
					'requestStatus'	  => $_data['request_status'],
			);
			$id = $_data['id'];
			$where="id=$id";
			$this->_name="rms_student_request_permission";
			$this->update($_arr, $where);

            // Add to Attendance 
            if($_data['request_status']==1){
				$branch = $_data['branchId'];
				$group = $_data['groupId'];
				$date = $_data['fromDate'];
			//	$for_semester = $_data['for_semester'];
				$session = $_data['sessionType'];
				$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group  and for_session = $session and date_attendence = '$date' and type=1 limit 1";
				$attendence_id = $_db->fetchOne($sql);

				if(empty($attendence_id )){
					$_arr = array(
						'branch_id'		=>$_data['branchId'],
						'group_id'		=>$_data['groupId'],
						'date_attendence'=>date("Y-m-d",strtotime($_data['fromDate'])),
						'date_create'	=>date("Y-m-d"),
						'modify_date'	=>date("Y-m-d"),
						'subject_id'	=> 0,
						'for_semester'	=> 0,
						'note'			=>$_data['reason'],
						'status'		=>1,
						'user_id'		=>$this->getUserId(),
						'for_session'	=>$_data['sessionType'],
						'type'			=>1, //for attendence
					);
					$this->_name ='rms_student_attendence';
					$attendence_id=$this->insert($_arr);
				}
					
				$arr = array(
					'attendence_id'		=>$attendence_id,
					'stu_id'			=>$_data['studentId'],
					'attendence_status'	=>3,
					'description'		=>$_data['reason'],
					'type'				=>2, //from one student 
				);
				$this->_name ='rms_student_attendence_detail';
				$this->insert($arr);
			}
			$_db->commit();
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			echo $e->getMessage();
			Application_Form_FrmMessage::message("Application Error!");
		}
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
			WHEN  sessionType = 1 THEN '".$tr->translate("MORNING")."'
			WHEN  sessionType = 2 THEN '".$tr->translate("AFTERNOON")."'
			WHEN  sessionType = 3 THEN '".$tr->translate("FULL_DAY")."'
		END AS SeesionLabel,
		CASE    
			WHEN  requestStatus = 0 THEN '".$tr->translate("PENDING")."'
			WHEN  requestStatus = 1 THEN '".$tr->translate("APPROVED")."'
			WHEN  requestStatus = 2 THEN '".$tr->translate("REJECTED")."'
		END AS StatusLbel
		FROM `rms_student_request_permission` WHERE id= ".$id;
	
		$row = $db->fetchRow($sql);
		return $row;
	}
    
}