<?php

class Issue_Model_DbTable_DbAchievement extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_achievement';
    public function getUserId(){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	return $dbp->getUserId();
    }
    function getAllAchievement($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = "
			SELECT 
				ac.id
				,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = ac.branchId LIMIT 1) AS branch_name
				,CONCAT( g.group_code,' ',(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1)) AS `group_code`
				,CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentName
				,(SELECT first_name FROM rms_users WHERE rms_users.id = ac.userId LIMIT 1) AS userName
				,ac.createDate
    	";
    	$sql.=$dbp->caseStatusShowImage("ac.status");
    	$sql.=" FROM 
					`rms_student_achievement` AS ac
					JOIN `rms_student` AS s ON s.stu_id = ac.studentId
					LEFT JOIN `rms_group` AS g ON g.id = ac.groupId
				WHERE 1 
		";
    	$where ='';
		$from_date =(empty($search['start_date']))? '1': "ac.createDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "ac.createDate <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
    	if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " g.group_code LIKE '%{$s_search}%'";
	    	$s_where[] = " ac.achievementType LIKE '%{$s_search}%'";
	    	$s_where[] = " ac.title LIKE '%{$s_search}%'";
	    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
	    if(!empty($search['branch_id'])){
	    	$where.=' AND ac.branchId='.$search['branch_id'];
	    }
	    if(!empty($search['group'])){
	    	$where.=' AND ac.branchId='.$search['group'];
	    }
	    if($search['status']>-1){
	    	$where.=' AND ac.status='.$search['status'];
	    }
	    $where.= $dbp->getAccessPermission('ac.branchId');
		$where.= $dbp->getDegreePermission('g.degree');
	    $order =  ' ORDER BY ac.`id` DESC ' ;
	    return $db->fetchAll($sql.$where.$order);
    }
	
	public function addStudentAchievement($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr= array(
					'branchId'			=>$_data['branch_id'],
					'achievementType'	=>$_data['achievementType'],
					'groupId'			=>$_data['groupId'],
					'studentId'			=>$_data['studentId'],
					'note'				=>$_data['note'],
					
					'title'				=>$_data['title'],
					'description'		=>$_data['description'],
					
					'createDate'		=>date("Y-m-d H:i:s"),
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'status'		=>1,
					'userId'		=>$this->getUserId(),
					);
			$this->_name='rms_student_achievement';
			$id = $this->insert($_arr);
			
			$_db->commit();
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	public function updateIssueLetterpraise($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$status = empty($_data['status'])?0:1;
			$_arr= array(
					'branchId'			=>$_data['branch_id'],
					'achievementType'	=>$_data['achievementType'],
					'groupId'			=>$_data['groupId'],
					'studentId'			=>$_data['studentId'],
					'note'				=>$_data['note'],
					'title'				=>$_data['title'],
					'description'		=>$_data['description'],
					
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'status'			=>$status,
					'userId'			=>$this->getUserId(),
			);
			$this->_name='rms_student_achievement';
			$id = $_data['id'];
			$where=" id =".$id;
			$this->update($_arr, $where);
			
			$_db->commit();
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	function getStudentAchievementById($id){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				c.* 
			FROM `rms_student_achievement` AS c 
				LEFT JOIN `rms_group` AS g ON g.id = c.groupId
			WHERE c.id = $id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('c.branchId');
		$sql.=$dbp->getDegreePermission('g.degree');
		$sql." LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getAllAchievementType(){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				DISTINCT(ac.achievementType) AS id,
				ac.achievementType AS `name`
			FROM `rms_student_achievement` AS ac 
			WHERE 1";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('ac.branchId');
		$sql." ORDER BY ac.achievementType ASC";
		
		return $db->fetchAll($sql);
	}
	
}