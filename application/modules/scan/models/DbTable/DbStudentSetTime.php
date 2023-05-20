<?php

class Scan_Model_DbTable_DbStudentSetTime extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addSetLockTimeStudent($data){
		$db = $this->getAdapter();
		try{
	  		$ids = explode(',', $data['identity']);
			if(!empty($ids))foreach ($ids as $i){
				if(!empty($data['studentId'.$i])){
					$studentId = $data['studentId'.$i];
					$_arr = array(
						'lockFromTime'		=>str_replace('T', '', addslashes(trim($data['fromTime'.$i]))),
						'lockToTime'		=>str_replace('T', '', addslashes(trim($data['toTime'.$i]))),
						'dateUpdatedLock'	=>date("Y-m-d H:i:s"),
						'setLockBy'     	=>$this->getUserId()
					);
					$where=$this->getAdapter()->quoteInto("stu_id=?", $studentId);
					$this->update($_arr,$where);
				}
			}
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	function getAllSetLockTimeStudent($search){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
		
		$sql = "SELECT  s.stu_id,
				(SELECT $branch FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				s.stu_khname,
				CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
				(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
				
				(SELECT group_code FROM `rms_group` WHERE rms_group.id=(SELECT ds.group_id FROM rms_group_detail_student AS ds 
					WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 LIMIT 1) LIMIT 1) AS group_name,
				(SELECT first_name FROM rms_users WHERE s.setLockBy=rms_users.id LIMIT 1 ) AS userName,
			s.lockFromTime,
			s.lockToTime	
		";
						
		//$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_student` as s WHERE s.setLockBy>0 ";
			
		$where="";  
		$from_date =(empty($search['start_date']))? '1': "s.dateUpdatedLock >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.dateUpdatedLock <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		$order = ' ORDER BY s.stu_id DESC ';
		
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(last_name,stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(stu_enname,last_name),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(stu_enname,' ',last_name)  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,' ',stu_enname)  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(lockFromTime,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(lockToTime,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		
		return $db->fetchAll($sql.$where.$order);
	}	
}