<?php

class Foundation_Model_DbTable_DbStudentDrop extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_drop';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission();
		
		$sql = "SELECT stu_id,stu_code FROM `rms_student` where status = 1 and is_subspend=0  $branch_id ";
		$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql.$orderby);		
	}
	
	function getAllStudentName(){
		$_db = $this->getAdapter();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission();
		
		$sql = "SELECT stu_id,CONCAT(stu_khname,'-',stu_enname) as name FROM `rms_student` where status = 1 and is_subspend=0  $branch_id ";
		$orderby = " ORDER BY stu_enname ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentIDEdit(){
		$_db = $this->getAdapter();
		
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission();
		
		$sql = "SELECT stu_id,stu_code FROM `rms_student` where status = 1  $branch_id  ";
		$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentNameEdit(){
		$_db = $this->getAdapter();
	
		$db=new Application_Model_DbTable_DbGlobal();
		$branch_id = $db->getAccessPermission();
	
		$sql = "SELECT stu_id,CONCAT(stu_khname,'-',stu_enname) as name FROM `rms_student` where status = 1  $branch_id  ";
		$orderby = " ORDER BY stu_enname ";
		return $_db->fetchAll($sql.$orderby);
	}
	
	public function getAllStudentDrop($search){
		$_db = $this->getAdapter();
		$sql = "SELECT id,(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1) AS code,
		(SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1) AS en_name,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1))AS sex,
		
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=academic_year) as academic_year,
		(SELECT CONCAT(`major_enname`) FROM `rms_major` WHERE `major_id`=grade ) AS grade,
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE `rms_view`.`type` = 4 AND `rms_view`.`key_code` = session ) AS session,
		
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`rms_student_drop`.`type` limit 1) as type,
		reason,date_stop from `rms_student_drop`,rms_student where rms_student.stu_id=rms_student_drop.stu_id and rms_student_drop.status=1 ";
		$order_by=" order by id DESC";
		$from_date =(empty($search['start_date']))? '1': " date_stop >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_stop <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id` limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`rms_student_drop`.`type` limit 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND rms_student.academic_year = ".$search['study_year'];
		}
		if(!empty($search['grade_bac'])){
			$where.=" AND rms_student.grade=".$search['grade_bac'];
		}
		if(!empty($search['session'])){
			$where.=" AND rms_student.session=".$search['session'];
		}
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function addStudentDrop($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
			try{	
				$_arr= array(
						'user_id'	=>$this->getUserId(),
						'stu_id'	=>$_data['studentid'],
						'type'		=>$_data['type'],
						'status'	=>$_data['status'],
						'date_stop'	=>$_data['datestop'],
						'reason'	=>$_data['reason'],
						'create_date'=>date('Y-m-d'),
						);
				$id = $this->insert($_arr);
				
				$this->_name='rms_student';
				$where=" stu_id=".$_data['studentid'];
				if($_data['status']==1){
					$arr=array(
						'is_subspend'	=>	$_data['type'],
					);
				}else{
					$arr=array(
						'is_subspend'	=>	0,
					);
				}
				$this->update($arr, $where);
				
				
				$this->_name='rms_student_payment';
				$where=" student_id=".$_data['studentid'];
				if($_data['status']==1){
					$arr=array(
							'is_suspend'	=>	$_data['type'],
					);
				}else{
					$arr=array(
							'is_suspend'	=>	0,
					);
				}
				$this->update($arr, $where);
				
				
				
				$this->_name='rms_group_detail_student';
				$where = " stu_id=".$_data['studentid']." and is_pass = 0";
				if($_data['status']==1){
					$ar=array(
							'type'	=>	2,
					);
				}else{
					$ar=array(
							'type'	=>	1,
					);
				}
				$this->update($ar, $where);
				
				$_db->commit();
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
	}
	public function updateStudentDrop($_data){
		$db= $this->getAdapter();
		$db->beginTransaction();
		try{	
			$_arr=array(
					'user_id'	=>$this->getUserId(),
					'stu_id'	=>$_data['studentid'],
					'type'		=>$_data['type'],
					'date_stop'		=>$_data['datestop'],
					'reason'	=>$_data['reason'],
					//'note'		=>$_data['note'],
					'status'	=>$_data['status'],
					);
			$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
			$this->update($_arr, $where);
			
			$this->_name='rms_student';
			
			$where=" stu_id=".$_data['studentid'];
			
			if($_data['status']==0){
				$arr=array(
						'is_subspend'	=>	0,
				);
			}else{
				$arr=array(
						'is_subspend'	=>	$_data['type'],
				);
			}
			$this->update($arr, $where);
			
			
			$this->_name='rms_student_payment';
			$where=" student_id=".$_data['studentid'];
			if($_data['status']==1){
				$arr=array(
						'is_suspend'	=>	$_data['type'],
				);
			}else{
				$arr=array(
						'is_suspend'	=>	0,
				);
			}
			$this->update($arr, $where);
			
			$this->_name='rms_group_detail_student';
			$where = " stu_id=".$_data['studentid']." and is_pass = 0";
			if($_data['status']==1){
				$ar=array(
						'type'	=>	2,
				);
			}else{
				$ar=array(
						'type'	=>	1,
				);
			}
			$this->update($ar, $where);

			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(st.stu_khname,' - ',st.stu_enname) as name,st.sex,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=st.academic_year) as academic_year, 
			(SELECT CONCAT(`major_enname`) FROM `rms_major` WHERE `major_id`=st.grade ) AS grade,
			(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE `rms_view`.`type` = 4 AND `rms_view`.`key_code` = st.session ) AS session
			FROM `rms_student` as st WHERE stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	
	
	
	
	
	
	
	
}



