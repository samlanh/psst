<?php
class Foundation_Model_DbTable_DbChangeBranch extends Zend_Db_Table_Abstract
{
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}	
	function getAllBranch($br_id=null){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branch_id = $dbgb->getAccessPermission('br_id');
		$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch WHERE branch_nameen!='' AND  status=1  $branch_id ";
		if (!empty($br_id)){
			$sql.=" AND br_id != $br_id ";
		}
		return $db->fetchAll($sql);
	}
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,st.stu_code FROM `rms_student` as st,
			rms_group_detail_student as gds 
			where 
			gds.itemType=1 
			AND gds.is_pass=0 
			and gds.stu_id=st.stu_id 
			and is_setgroup=1 
			
			and st.status=1 group by gds.stu_id";
		return $_db->fetchAll($sql);		
	}
	
	
	
	public function getAllGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) ";

		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql = "SELECT 
		scg.id,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = scg.branch_id LIMIT 1) AS branch_name,
		(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` LIMIT 1) AS code,
		(SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` LIMIT 1) AS kh_name,
		(SELECT CONCAT(COALESCE(stu_enname,'-'),' ',COALESCE(last_name,'') ) FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` LIMIT 1) AS last_name,
		
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 AND `rms_view`.`key_code`=st.stu_id limit 1)AS sex,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = scg.to_branch LIMIT 1) AS to_branch_name,
		moving_date
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("scg.status");
		$sql.=" 
			FROM 
					`rms_student_change_branch` AS scg,
					rms_student AS st,
					rms_group_detail_student AS gds
			WHERE  
					gds.itemType=1 
					AND scg.stu_id=st.stu_id 
					AND gds.stu_id=st.stu_id 
					AND gds.stop_type=0
					AND gds.is_maingrade=1 ";
		
		$order_by=" ORDER BY scg.id DESC";
		$where=' ';
		$from_date =(empty($search['start_date']))? '1': "scg.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "scg.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND scg.branch_id=".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('scg.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentChangeBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_branch WHERE id =".$id." AND status = 1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	public function getDegreeAndGradeToGroup($group){
		$db = $this->getAdapter();
		$sql = "SELECT academic_year,degree,grade,session,room_id FROM rms_group WHERE id =".$group;
		return $db->fetchRow($sql);
	}
	
	
	function getStudentChangeGroup1ById($id){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getStudentGroupInfoById($id);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 	
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name,
		 st.`sex`,gds.`group_id` FROM 
		 `rms_student` AS st,
		 rms_group_detail_student AS gds 
		 WHERE 
		 gds.itemType=1 
		 AND gds.is_pass=0 and  st.stu_id=$stu_id AND st.stu_id=gds.stu_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function addStudentChangeBranch($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
			if (!empty($stuInfo)){
				$stu_id = $stuInfo['stu_id'];
				$_arr= array(
						'branch_id'		=>$_data['branch_id'],
						'study_id'		=>$_data['studentid'],
						'stu_id'		=>$stu_id,
						
						'to_branch'		=>$_data['to_branch'],
// 						'to_group'		=>$_data['to_group'],
// 						'from_group'	=>$_data['from_group'],
						'moving_date'	=>$_data['moving_date'],
						'reason'		=>$_data['reason'],
						'note'			=>$_data['note'],
						'user_id'		=>$this->getUserId(),
						'status'		=>1,
						'create_date' 	=> date("Y-m-d H:i:s"),
						'modify_date' 	=> date("Y-m-d H:i:s")
				);
				$this->_name='rms_student_change_branch';
				$id = $this->insert($_arr);
				
				$array = array(
						'branch_id'	 => $_data['branch_id'],
						'stu_id'	=>$stu_id,
				);
				$studyStudent = $dbstu->getAllStudyByStudent($array);
				if (!empty($studyStudent)) foreach ($studyStudent as $st){
					$this->_name='rms_group_detail_student';
					$arr= array(
							'is_pass'=>1,
							'stop_type'=>1,
							'note'=>'Student Change Branch'
					);
					$where = " gd_id=".$st['gd_id'];
					$this->update($arr, $where);
					
					$arr= array(
							'stu_id'		=>$stu_id,
							'old_group'		=>$st['group_id'],
							'academic_year'	=>$st['academic_year'],
							'degree'		=>$st['degree'],
							'grade'			=>$st['grade'],
							'is_maingrade'	=>$st['is_maingrade'],
							'status'	=>1,
							'is_current'	=>1,
							'is_newstudent'	=>1,
							'create_date' 	=> date("Y-m-d H:i:s"),
							'modify_date' 	=> date("Y-m-d H:i:s"),
							'user_id'		=>$this->getUserId(),
					);
					$this->insert($arr);
					
					$this->_name='rms_student';
					$array = array(
							'branch_id'		=>$_data['to_branch'],
					);
					$where = " stu_id=".$stu_id;
					$this->update($array, $where);
					
				}
			}
			return $_db->commit();
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	public function revertChangeBranch($id){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$row = $this->getStudentChangeBranchById($id);
			if (!empty($row)){
				
				$_arr= array(
					'user_id'	=> $this->getUserId(),
					'status'	=> 0,
					'modify_date'=> date("Y-m-d H:i:s")
				);
				$this->_name='rms_student_change_branch';
				$where = "id = ".$id;
				$id = $this->update($_arr, $where);
				
				$array = array(
						'branch_id'	 => $row['branch_id'],
						'stu_id'	=>$row['stu_id'],
				);
				$studyStudent = $dbstu->getAllStudyByStudent($array);//revert old
				if (!empty($studyStudent)) foreach ($studyStudent as $st){
					$this->_name='rms_group_detail_student';
					$arr= array(
							'is_pass'=> 0,
							'stop_type'=> 0,
							'note'=> 'Revert Student Change Branch'
					);
					$where = " gd_id=".$st['gd_id'];
					$this->update($arr, $where);
				}
				
				$this->_name='rms_group_detail_student';
				$where="stu_id=".$row['stu_id']." AND is_current =1";
				$where.=" AND CASE WHEN (SELECT g.branch_id FROM `rms_group` AS g WHERE g.id = group_id LIMIT 1) IS NULL THEN '0' 
				ELSE (SELECT g.branch_id FROM `rms_group` AS g WHERE g.id = group_id LIMIT 1) END <> ".$row['branch_id'];
				$this->delete($where);//must check student att,discipline,score...
				
				$this->_name='rms_student';
				$array = array(
					'branch_id'		=>$row['branch_id'],
				);
				$where = " stu_id=".$row['stu_id'];
				$this->update($array, $where);
			}
			return $_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
	
		}
	}
}

