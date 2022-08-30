<?php

class Foundation_Model_DbTable_DbStudentReturn extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_drop_return';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentDropReturn($search){//
		$_db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		
		$sql = "SELECT  sdr.id,
				(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = sdr.branch_id LIMIT 1) AS branch_name,			
				(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS stu_code,
				(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS student_kh,
				(SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS student_name,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = sdr.academic_year LIMIT 1) AS academic,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degree AND type=1 LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ) AS group_name,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degree_id_return AND type=1 LIMIT 1) AS degreeReturn,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.grade_id_return AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeReturn,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group_id_return LIMIT 1 ) AS groupReturn,
				sdr.return_date,
				(SELECT first_name FROM `rms_users` WHERE id=sdr.user_id LIMIT 1) AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("sdr.status");
		$sql.=" FROM `rms_student_drop_return` AS sdr WHERE 1 ";
		$where = "";
		$from_date =(empty($search['start_date']))? '1': " sdr.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sdr.return_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		$order_by=" order by sdr.id DESC";
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE((SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','')LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group_id_return LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND sdr.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND sdr.academic_year = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND sdr.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND sdr.grade=".$search['grade'];
		}
		
		$where.=$dbp->getAccessPermission('sdr.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getStudentDropReturnById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop_return WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	function addStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
		
		
			$oldGroupId = $_data['groupId'];
			
			$stu_id = $_data['stu_id'];
			$newGroupId = $_data['group'];
			$newDegreeId = $_data['degree'];
			$newGradeId = $_data['grade'];
			
			$academic_year=0;
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($newGroupId);
			if(!empty($group_info)){
				$academic_year = $group_info['academic_year'];
			}
					
			$_arr= array(
						'branch_id'		=> $_data['branch_id'],
						'drop_id'		=>$_data['drop_id'],
						'stu_id'		=>$stu_id,
						'group'			=>$oldGroupId,
						'degree'		=>$_data['degreeId'],
						'grade'			=>$_data['gradeId'],
						
						
						'academic_year'	=> $academic_year,
						
						'degree_id_return'	=> $newDegreeId,
						'grade_id_return'	=> $newGradeId,
						'group_id_return'	=> $newGroupId,
						
						'return_date'		 => $_data['return_date'],
						'note'	 => $_data['note'],						
						
						'user_id'	 => $this->getUserId(),
						'create_date'=> date('Y-m-d H:i:s'),
						'modify_date'=> date('Y-m-d H:i:s'),
						'status' 	=>1
				);
				$this->_name="rms_student_drop_return";
				$idReturn = $this->insert($_arr);
				
				$_arrStuDropRecord=array(
						'isReturn'	=>1,
				);
				$whereStuDropRecord=$this->getAdapter()->quoteInto("id=?", $_data['drop_id']);
				$this->_name="rms_student_drop";
				$this->update($_arrStuDropRecord,$whereStuDropRecord);
				
				
				if($oldGroupId!=$newGroupId){
					
					$_arrOldGroupDetail = array(
							'is_current'		=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
					
					$_arrNewGroupDetail = array(
							'stu_id'			=>$stu_id,
							'status'			=>1,
							'group_id'			=>$newGroupId,
							'degree'			=>$newDegreeId,
							'grade'				=>$newGradeId,
							'academic_year'		=>$academic_year,
							'is_current'		=>1,
							'is_setgroup'		=>1,
							'is_maingrade'		=>1,
							'note'				=>"New Group Detail From Student Return",
							'date'				=>date("Y-m-d"),
							'create_date'		=>date("Y-m-d H:i:s"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$this->insert($_arrNewGroupDetail);
						
				}else{
					$_arrOldGroupDetail = array(
							'stop_type'		=>0,
							'note'			=>"Update StopType Group Detail From Student Return",
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}
		
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function updateStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
		
			$id=$_data['id'];
			$_arr= array(
					'return_date'		 => $_data['return_date'],
					'note'	 => $_data['note'],						
					'user_id'	 => $this->getUserId(),
					'modify_date'=> date('Y-m-d H:i:s'),
					'status' 	=>$_data['status']
			);
			$this->_name="rms_student_drop_return";
			$whereArr="id =".$id;
			$this->update($_arr,$whereArr);
			
			if($_data['status']==0){
				
				
				$record = $this->getStudentDropReturnById($id);;
				$dropId = $record['drop_id'];
				$_arrStuDropRecord=array(
						'isReturn'	=>0,
				);
				$whereStuDropRecord=$this->getAdapter()->quoteInto("id=?", $dropId);
				$this->_name="rms_student_drop";
				$this->update($_arrStuDropRecord,$whereStuDropRecord);
				
				$oldGroupId = $record['group'];
			
				$stu_id = $record['stu_id'];
				$newGroupId = $record['group_id_return'];
				
				
				$dbStuDrop= new Foundation_Model_DbTable_DbStudentDrop();
				$rowDrop = $dbStuDrop->getStudentDropById($dropId);
				$stopType = $rowDrop['type'];
				if($oldGroupId!=$newGroupId){
					
					$this->_name = 'rms_group_detail_student';
					$whereDeleteNewStudy="stu_id = ".$stu_id." AND is_current=1 ";
					if(!empty($newGroupId)){
						$whereDeleteNewStudy.=" AND group_id=$newGroupId ";
					}
					$this->delete($whereDeleteNewStudy);
					
					$_arrOldGroupDetail = array(
							'is_current'		=>1,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND is_current=0 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}else{
					$_arrOldGroupDetail = array(
							'stop_type'		=>$stopType,
							'note'			=>"Update StopType Group Detail From Student Return",
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}
				
			
			}
		
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllStudentDrop($_data){
		$db = $this->getAdapter();
		
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT 
			dr.id,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.isReturn=0
			AND dr.branch_id=$branch_id
		";
		if(!empty($_data['dropIdSelected'])){
			$sql.=" OR dr.id=".$_data['dropIdSelected'];
		}
		return $db->fetchAll($sql);
	}
	
	function getStudentDropInfo($_data){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		
		$drop_id = empty($_data['drop_id'])?0:$_data['drop_id'];
		$sql="
		SELECT 
			dr.*,
			(SELECT its.$colunmname FROM `rms_items` AS its WHERE its.id=dr.degree AND its.type=1 LIMIT 1) AS degreeTitle,
			(SELECT CONCAT(itsd.$colunmname) FROM `rms_itemsdetail` AS itsd WHERE itsd.id=dr.grade AND itsd.items_type=1 LIMIT 1) AS gradeTitle,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = dr.group LIMIT 1) AS groupCode,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = dr.academic_year LIMIT 1) AS academicYearTitle,
			(SELECT v.$label FROM `rms_view` AS v WHERE v.type=5 AND key_code = dr.type LIMIT 1) AS typeTitle,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.isReturn=0
			AND dr.id=$drop_id 
			LIMIT 1
		";
		return $db->fetchRow($sql);
	}
}