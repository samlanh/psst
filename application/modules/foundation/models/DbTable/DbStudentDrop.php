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
	
	function getAllgroupStudy(){
		$db = $this->getAdapter();
		$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
		FROM `rms_group` AS `g` WHERE g.status=1 and g.is_pass!=1";
	
		return $db->fetchAll($sql);
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
		$sql = "SELECT  s.id,				
				(SELECT `title` FROM `rms_items` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group LIMIT 1 ) AS group_name,
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1) AS room,
				date_stop,
				reason,
				(SELECT first_name FROM `rms_users` WHERE id=S.user_id LIMIT 1) AS user_name,
				(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code = s.status LIMIT 1) AS STATUS
				FROM `rms_student_drop` AS s  ";
		$order_by=" order by id DESC";
		$where = "";
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `title` FROM `rms_items` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND rms_student.academic_year = ".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND rms_student.grade=".$search['grade'];
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
		//$_db->beginTransaction();
		//print_r($_data); exit();
			try{	
				$_arr= array(
						'stu_id'	=>$_data['studentid'],
						'studentname'=>$_data['studentname'],
						'type'		=>$_data['type'],
						'status'	=>$_data['status'],
						'date_stop'	=>$_data['datestop'],
						'reason'	=>$_data['reason'],
						'create_date'=>date('Y-m-d'),
						'gender'	=>$_data['gender'],
						
						'group'		=>$_data['group'],
						'academic_year'	=>$_data['academic_year'],
						'calture'	=>$_data['calture'],
						'session'	=>$_data['session'],
						'degree'	=>$_data['degree'],
						'grade'		=>$_data['grade'],
						'room'		=>$_data['room'],
						'user_id'	=>$this->getUserId(),
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
					'date_stop'	=>$_data['datestop'],
					'reason'	=>$_data['reason'],
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
		$sql = "SELECT 	*,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=st.group_id LIMIT 1 ) AS group_name,
			(SELECT r.room_name FROM rms_room AS r WHERE r.room_id = st.room LIMIT 1 )AS room_id
			FROM rms_student AS st WHERE stu_id=$stu_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getAllDropType(){
		$db = $this->getAdapter();
		$sql = "SELECT key_code as id,name_kh as name from rms_view where type=5 and status=1";
		return $db->fetchAll($sql);
	}
	
	
	
	
	
	
	
}



