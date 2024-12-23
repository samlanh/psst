<?php

class Foundation_Model_DbTable_DbStudentDrop extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_drop';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentID(){
		$db=new Application_Model_DbTable_DbGlobal();
		return $db->getAllStuCode();
	}
	
	
	public function getAllStudentNameEdit(){
		$_db = $this->getAdapter();
	
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission();
	
		$sql = "SELECT stu_id,CONCAT(stu_khname,'-',stu_enname) as name FROM `rms_student` where status = 1  $branch_id  ";
		$orderby = " ORDER BY stu_enname ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentDrop($search){//
		$_db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$titleCol = "title";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$titleCol = "titleKh";
		}
		$branch = $dbp->getBranchDisplay();
		$sql = "SELECT  
				s.id
				,(SELECT rms_branch.$branch FROM `rms_branch` WHERE rms_branch.br_id = s.branch_id LIMIT 1) AS branch_name	
				,(SELECT stu_code FROM `rms_student` WHERE `stu_id`=s.stu_id LIMIT 1) AS stu_id
				,(SELECT stu_khname FROM `rms_student` WHERE `stu_id`=s.stu_id LIMIT 1) AS student_kh
				,(SELECT CONCAT(last_name,' ',stu_enname) FROM `rms_student` WHERE `stu_id`=s.stu_id LIMIT 1) AS student_name
				,(SELECT $label FROM `rms_view` WHERE TYPE=2 AND key_code = s.gender LIMIT 1) AS sex
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.academic_year LIMIT 1) AS academic
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade
				,g.group_code AS group_name
				,(SELECT p.$titleCol FROM `rms_parttime_list` AS p WHERE p.id = s.session LIMIT 1) AS `session`
				,(SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1) AS room
				,(SELECT $label FROM `rms_view` WHERE TYPE=5 AND key_code = s.type LIMIT 1) AS type
				,date_stop
				,(SELECT $label FROM `rms_view` WHERE TYPE=43 AND key_code = s.type LIMIT 1) AS reasontype
				,reason
				,CASE WHEN s.isReturn = 1 
					THEN '".$tr->translate('returnStudy')."'
					ELSE ''
				END AS returnStudy
				,(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.=" FROM 
					`rms_student_drop` AS s 
					JOIN rms_group AS g ON g.id = s.group
				WHERE 1 ";
		$where = "";
		$from_date =(empty($search['start_date']))? '1': " s.date_stop >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.date_stop <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		$order_by=" order by id DESC";
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `stu_id`=s.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `stu_id`=s.stu_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND s.academic_year = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND s.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND s.grade=".$search['grade'];
		}
		if(!empty($search['partTimeList'])){
			$where.=" AND s.session=".$search['partTimeList'];
		}
		if($search['type']!=''){
			$where.=" AND s.type=".$search['type'];
		}
		
		$where.=$dbp->getAccessPermission('s.branch_id');
		$where.=$dbp->getDegreePermission('s.degree');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=$dbp->getDegreePermission('degree');
		return $db->fetchRow($sql);
	}
	public function addStudentDrop($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
			if (!empty($stuInfo)){
				$stu_id = $stuInfo['stu_id'];
				$_arr= array(
						'branch_id'	 => $_data['branch_id'],
						'study_id'	=>$_data['studentid'],
						'stu_id'	=>$stu_id,
						'gender'	 => $_data['gender'],
						'type'		 => $_data['type'],
						'date_stop'	 => $_data['datestop'],
						'reason'	 => $_data['reason'],
						'reasonId'	 => $_data['reasonId'],
						'group'		 => $_data['group'],
						'academic_year'	=> $_data['academic_year'],
						'calture'	 => $_data['calture'],
						'session'	 => $_data['session'],
						'degree'	 => $_data['degree'],
						'grade'		 => $_data['grade'],
						'room'		 => $_data['room'],
						'user_id'	 => $this->getUserId(),
						'create_date'=> date('Y-m-d H:i:s'),
						'modify_date'=> date('Y-m-d H:i:s'),
						'status' 	=>1
				);
				$id = $this->insert($_arr);
				
				$this->_name='rms_group_detail_student';
				
				$where = " gd_id=".$_data['studentid'];
				$ar=array(
					'stop_type'	=>	$_data['type'],
				);
				$this->update($ar, $where);
			}
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function updateStudentDrop($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{	
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
			if (!empty($stuInfo)){
				$stu_id = $stuInfo['stu_id'];
				$_arr=array(
						'branch_id'	=>$_data['branch_id'],
						'study_id'	=>$_data['studentid'],
						'stu_id'	=>$stu_id,
// 						'status'	=>$_data['status'],
						'date_stop'	=>$_data['datestop'],
						'reason'	=>$_data['reason'],
						'gender'	=>$_data['gender'],
						'group'		=>$_data['group'],
						'academic_year'	=>$_data['academic_year'],
						'calture'	=>$_data['calture'],
						'session'	=>$_data['session'],
						'reasonId'	 => $_data['reasonId'],
						'degree'	=>$_data['degree'],
						'grade'		=>$_data['grade'],
						'room'		=>$_data['room'],
						'user_id'	=>$this->getUserId(),
						'modify_date'=> date('Y-m-d H:i:s'),
				);
				$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
				$this->update($_arr, $where);
				
				$this->_name='rms_group_detail_student';
				$where = " gd_id=".$_data['studentid'];
				$ar=array(
						'stop_type'	=>	$_data['type'],
				);
				$this->update($ar, $where);
			}

			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 	* FROM rms_student AS st WHERE stu_id=$stu_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getAllDropType(){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getViewById(5);
	}	
}