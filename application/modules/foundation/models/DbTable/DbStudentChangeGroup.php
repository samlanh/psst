<?php
class Foundation_Model_DbTable_DbStudentChangeGroup extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}	
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,st.stu_code FROM `rms_student` as st,rms_group_detail_student as gds where gds.type=1 and gds.is_pass=0 and gds.stu_id=st.stu_id and is_setgroup=1 and st.is_subspend=0 and st.status=1 group by gds.stu_id";
		//$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql);		
	}
	
	public function getAllStudentName(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,		
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name
		FROM 
			`rms_student` as st,
			rms_group_detail_student as gds
		 WHERE gds.type=1 
		and gds.is_pass=0 
		and gds.stu_id=st.stu_id 
		and is_setgroup=1 
		and st.is_subspend=0 
		and st.status=1 
		group by gds.stu_id";
		//$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql);
	}
	
	public function getAllGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) ";
// 		$orderby = " ORDER BY stu_code ";
		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		$sql = "SELECT scg.id,(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS code,
		(SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS kh_name,
		(SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS en_name,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) limit 1)AS sex,
		
		(select group_code from rms_group where rms_group.id = scg.from_group limit 1)AS from_group,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=(select academic_year from rms_group where rms_group.id = scg.from_group limit 1)) AS from_academic,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(select grade from rms_group where rms_group.id = scg.from_group LIMIT 1)) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as from_grade,
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = (select session from rms_group where rms_group.id = scg.from_group limit 1))) LIMIT 1) AS `from_session`,
		
		group_code AS to_group,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_group.academic_year limit 1) AS to_academic,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=rms_group.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as to_grade,
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = rms_group.session )) LIMIT 1) AS `to_session`,
		
		moving_date,scg.note from `rms_student_change_group` as scg,rms_student as st,rms_group where scg.to_group=rms_group.id and scg.stu_id=st.stu_id and st.is_subspend=0 and scg.status=1";
		$order_by=" order by id DESC";
		$where=' ';
		
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			//$s_where[] = " en_name LIKE '%{$s_search}%'";
			$s_where[] = " (select group_code from rms_group where rms_group.id = scg.from_group) LIKE '%{$s_search}%'";
			$s_where[] = " (select group_code from rms_group where rms_group.id = scg.to_group) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND rms_group.academic_year like ".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND rms_group.grade=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND rms_group.session=".$search['session'];
		}
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getAllStudentChangeGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_group WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function getDegreeAndGradeToGroup($group){
		$db = $this->getAdapter();
		$sql = "SELECT academic_year,degree,grade,session,room_id FROM rms_group WHERE id =".$group;
		return $db->fetchRow($sql);
	}
	public function addStudentChangeGroup($_data){
			$_db= $this->getAdapter();
			$_db->beginTransaction();
			
			try{	
				$stu_id=$_data['studentid'];
				$_arr= array(
						'user_id'=>$this->getUserId(),
						'stu_id'=>$_data['studentid'],
						'from_group'=>$_data['from_group'],
						'to_group'=>$_data['to_group'],
						'moving_date'=>$_data['moving_date'],
						'note'=>$_data['note'],
						'status'=>$_data['status']
						);
				$this->_name='rms_student_change_group';
				$id = $this->insert($_arr);
				
				
				$this->_name='rms_group_detail_student';
				$arr= array(
						'group_id'=>$_data['to_group'],
						'old_group'	=>$_data['from_group'],
				);
				$where="stu_id=".$stu_id." and is_pass=0 and group_id=".$_data['from_group'];
				
				$this->update($arr, $where);
				
				
				$this->_name='rms_group';
				$arra = array(
						'is_pass'	=> 2,
						);
				$where = " id = ".$_data['to_group'];
				$this->update($arra, $where);
				
				
				$this->_name='rms_student';
				
				$test = $this->getDegreeAndGradeToGroup($_data['to_group']);
				
				if($test['degree']==1 || $test['degree']==2){
					$stu_type=1;    //  kid - 6
				}else if($test['degree']==3){
					$stu_type=2;    // 7-12
				}else{
					$stu_type=3;	// eng and other subject
				}
				$array = array(
							'academic_year'	=>$test['academic_year'],
							'degree'		=>$test['degree'],
							'grade'			=>$test['grade'],
							'session'		=>$test['session'],
							'room'			=>$test['room_id'],
							'stu_type'		=>$stu_type,
							'group_id'		=>$_data['to_group'],
						);
				$where = " stu_id=".$_data['studentid'];
				$this->update($array, $where);
				return $_db->commit();
			}catch(Exception $e){
				$_db->rollBack();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
	}
	public function updateStudentChangeGroup($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			if($_data['status']==1){
				$stu_id=$_data['studentid'];
				$_arr=array(
						'user_id'	=>$this->getUserId(),
						'from_group'=>$_data['from_group'],
						'to_group'	=>$_data['to_group'],
						'moving_date'=>$_data['moving_date'],
						'note'		=>$_data['note'],
						'status'	=>$_data['status'],
						);
				$where=" id = ".$_data['id'];
				$this->update($_arr, $where);
				
				
				$this->_name='rms_group_detail_student';
				$arr= array(
						'group_id'	=>$_data['to_group'],
						'old_group'	=>$_data['from_group'],
				);
				$where="stu_id=".$stu_id." and is_pass=0 and old_group = ".$_data['from_group'] ;
				$this->update($arr, $where);
				
				
				$this->_name='rms_student';
				$test = $this->getDegreeAndGradeToGroup($_data['to_group']);
				
				if($test['degree']==1 || $test['degree']==2){
					$stu_type=1;    //  kid - 6
				}else if($test['degree']==3){
					$stu_type=2;    // 7-12
				}else{
					$stu_type=3;	// eng and other subject
				}
				
				$array = array(
						'academic_year'	=>$test['academic_year'],
						'degree'		=>$test['degree'],
						'grade'			=>$test['grade'],
						'session'		=>$test['session'],
						'room'			=>$test['room_id'],
						'stu_type'		=>$stu_type,
						'group_id'			=>$_data['to_group'],
				);
				$where = " stu_id=".$_data['studentid'];
				$this->update($array, $where);
			}else{
				$_arr=array(
						'user_id'	=>$this->getUserId(),
						'status'	=>$_data['status'],
				);
				$where=" id = ".$_data['id'];
				$this->update($_arr, $where);
				
				
				// update back to old group
				$this->_name='rms_group_detail_student';
				$arr= array(
						'group_id'	=>$_data['from_group'],
						'old_group'	=>null,
				);
				$where="stu_id=".$_data['studentid']." and is_pass=0 and old_group = ".$_data['from_group'] ;
				$this->update($arr, $where);
				
				// update student info back to old_group
				$this->_name='rms_student';
				$test = $this->getDegreeAndGradeToGroup($_data['from_group']);
				
				if($test['degree']==1 || $test['degree']==2){
					$stu_type=1;    //  kid - 6
				}else if($test['degree']==3){
					$stu_type=2;    // 7-12
				}else{
					$stu_type=3;	// eng and other subject
				}
				
				$array = array(
						'academic_year'	=>$test['academic_year'],
						'degree'		=>$test['degree'],
						'grade'			=>$test['grade'],
						'session'		=>$test['session'],
						'room'			=>$test['room_id'],
						'stu_type'		=>$stu_type,
						'group_id'		=>$_data['from_group'],
				);
				$where = " stu_id=".$_data['studentid'];
				$this->update($array, $where);
			}
			return $_db->commit();
			
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();
		}
	}
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getStudentChangeGroup1ById($id){
		$db = $this->getAdapter();
		$sql = "SELECT start_date,expired_date,
					(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_group.academic_year )AS year,
					(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_group.degree AND rms_items.type=1 LIMIT 1) AS degree,
			                (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE `rms_group`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT name_en FROM `rms_view` WHERE `rms_view`.`type`=4 AND `rms_view`.`key_code`=`rms_group`.`session` LIMIT 1)AS SESSION,
					(SELECT room_name FROM rms_room WHERE room_id = rms_group.room_id LIMIT 1) AS room,
					academic_year AS academic_year_id,
					degree AS degree_id,
					grade AS grade_id,
					SESSION AS session_id,
					room_id
				FROM 
					`rms_group` WHERE id=$id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 	
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name,
		 st.`sex`,gds.`group_id` FROM `rms_student` AS st,rms_group_detail_student AS gds WHERE gds.is_pass=0 and  st.stu_id=$stu_id AND st.stu_id=gds.stu_id LIMIT 1";
// 		echo $sql;exit();
		return $db->fetchRow($sql);
	}
}

