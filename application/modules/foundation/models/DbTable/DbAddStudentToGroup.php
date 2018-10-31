<?php

class Foundation_Model_DbTable_DbAddStudentToGroup extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_detail_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	
	}
	
	function getAllYear(){
		$db = $this->getAdapter();
		$sql = "select id,CONCAT(from_academic,'-',to_academic,'(',generation,')')as years from rms_tuitionfee ";
		return $db->fetchAll($sql);
	}
	
	public function getRoom(){
		$db = $this->getAdapter();
		$sql = "SELECT room_id,room_name FROM rms_room WHERE is_active = 1";
		return $db->fetchAll($sql);
	}
	public function getGroup(){
		$db = $this->getAdapter();
		$sql="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
			 (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
			 FROM `rms_group` AS `g` where (g.is_pass=0 OR g.is_pass=2) and status=1 ORDER BY `g`.`id` DESC ";
		return $db->fetchAll($sql);
	}
	
	public function getGroupToEdit(){
		$db = $this->getAdapter();
		$sql="SELECT id,group_code as name FROM rms_group WHERE status = 1 ";
		return $db->fetchAll($sql);
	}
	
	public function getGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT id,status FROM rms_group WHERE id=".$id;
		return $db->fetchRow($sql);
	
	}
	public function getStudentGroup($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					`group_id`,
					stu_id,
					(SELECT `stu_code` FROM `rms_student`WHERE `stu_id`=`gds`.`stu_id`)AS code,
					(SELECT `stu_enname` FROM `rms_student`WHERE `stu_id`=`gds`.`stu_id`)AS en_name, 
					(SELECT `stu_khname` FROM `rms_student`WHERE `stu_id`=`gds`.`stu_id`)AS kh_name
				FROM 
					`rms_group_detail_student` as gds 
				WHERE 
					`status`=1 AND`group_id`=".$id;
			
		return $db->fetchAll($sql);
		
	}
	
	public function editStudentGroup($_data,$id){
		//print_r($_data);exit();
		$db = $this->getAdapter();
		$rr = $this->getStudentGroup($id);
		$this->_name='rms_student';
		if(!empty($rr)){
			foreach($rr as $row){
				$data=array(
						'is_setgroup' 	=> 0,
						'group_id' 		=> 0,
				);
				$where='stu_id = '.$row['stu_id'];
				$this->update($data, $where);
		    }
		}
		
		$where = " group_id = $id and status=1 and is_pass=0 ";
		$this->_name='rms_group_detail_student';
		$this->delete($where);
		
		
		if($_data['status'] != 0){
			if(!empty($_data['public-methods'])){
				
				$array_student = $_data['public-methods'];
				foreach ($array_student as $student){
					$arr = array(
							'user_id'=>$this->getUserId(),
							'group_id'=>$_data['group'],
							'stu_id'=>$student,
							'status'=>$_data['status'],
							'date'=>date('Y-m-d')
					);
					$this->_name='rms_group_detail_student';
					$this->insert($arr);
				
					$this->_name='rms_student';
					$data=array(
							'is_setgroup'	=> 1,
							'group_id'		=>$_data['group'],
							'academic_year'	=> $_data['academic_year_group'],
							'degree'		=> $_data['degree_group'],
							'grade'			=> $_data['grade_group'],
							'session'		=> $_data['session_group'],
							'room'			=> $_data['room_group'],
					);
					$where='stu_id = '.$student;
					$this->update($data, $where);
				}
			}
		}
	}
	
	
	public function addStudentGroup($_data){
		$db = $this->getAdapter();
		try{
		//print_r($_data);exit();		
			if(!empty($_data['public-methods'])){
				
				//print_r($_data['public-methods']);exit();
				
				$all_stu_id = $_data['public-methods'];
				foreach ($all_stu_id as $stu_id){
					$arr = array(
						'user_id'	=>$this->getUserId(),
						'group_id'	=>$_data['group'],
						'stu_id'	=>$stu_id,
						'status'	=>$_data['status'],
						'date'		=>date('Y-m-d')
					);
					$this->_name='rms_group_detail_student';
					$this->insert($arr);
					
					$this->_name='rms_student';
					$data=array(
						'is_setgroup'	=> 1,
						'academic_year'	=> $_data['academic_year_group'],
						'degree'		=> $_data['degree_group'],
						'grade'			=> $_data['grade_group'],
						'session'		=> $_data['session_group'],
						'room'			=> $_data['room_group'],
						'group_id'		=> $_data['group'],
					);
					$where='stu_id = '.$stu_id;
					$this->update($data, $where);
				}
				
				$this->_name = 'rms_group';
				$data_gro = array(
					'is_use'=> 1,
					'is_pass'=> 2,
				);
				$where = 'id = '.$_data['group'];
				$this->update($data_gro, $where);
			}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getGroupDetail($search){
		$db = $this->getAdapter();
		$sql = " SELECT
					`g`.`id`,
					(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
					`g`.`group_code` AS `group_code`,
					(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year LIMIT 1) as academic,
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) as degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
					(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
					(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					`g`.`start_date`,
					`g`.`expired_date`,
					`g`.`note`,
					(select name_en from rms_view where rms_view.type=9 and key_code=g.is_pass) as status,
					(SELECT COUNT(gds.`stu_id`) FROM `rms_group_detail_student` as gds WHERE gds.`group_id`=`g`.`id` GROUP BY gds.group_id LIMIT 1) AS Num_Student,
					(SELECT COUNT(gds.`stu_id`) FROM `rms_group_detail_student` as gds WHERE gds.is_pass=0 and gds.`group_id`=`g`.`id` and g.is_pass=1 GROUP BY gds.group_id LIMIT 1)AS remain_Student
				FROM 
					rms_group g 
				where 
					g.status=1 
					and g.is_pass != 1  ";
		
		$order = " ORDER BY `g`.`id` DESC " ;	
		
		$where=" ";
		
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]="(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
			$s_where[]="group_code LIKE '%{$s_search}%'";
			$s_where[]="(SELECT room_name FROM rms_room WHERE rms_room.room_id = g.room_id) LIKE '%{$s_search}%'";
			$s_where[]="(SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=g.degree) LIKE '%{$s_search}%'";
			$s_where[]="(SELECT major_enname FROM rms_major WHERE rms_major.major_id=g.grade) LIKE '%{$s_search}%'";
			$s_where[]="(SELECT	rms_view.name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = g.session) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['study_year'])){
			$where.=' AND g.academic_year='.$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=' AND g.id='.$search['group'];
		}
		if(!empty($search['grade_all'])){
			$where.=' AND g.grade='.$search['grade_all'];
		}
		if(!empty($search['session'])){
			$where.=' AND g.session='.$search['session'];
		}
		if(!empty($search['room'])){
			$where.=' AND g.room_id='.$search['room'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND g.branch_id='.$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('g.`branch_id`');
		
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getAllFecultyName(){
// 		$db = $this->getAdapter();
// 		$sql ="SELECT dept_id AS id, en_name AS NAME,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND en_name!=''  ORDER BY id DESC";
// 		return $db->fetchAll($sql);
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllItems(1,null);
	}
	
	function getSearchStudent($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				stu_id,
				stu_code,
				stu_enname,
				stu_khname,
				sex,
				degree,
				grade,
				academic_year ,
				(SELECT `title` FROM `rms_items` WHERE `id`=degree AND type=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=grade AND items_type=1 LIMIT 1) AS grade_title
			  from 
			  	rms_student 
		 	  WHERE 
				`status`=1 
				AND is_setgroup = 0 
				AND customer_type = 1 
				and is_subspend=0 ";
		if(!empty($search['academy'])){
			$sql.=" AND academic_year =".$search['academy'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND degree =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND grade =".$search['grade'];
		}
		if(!empty($search['session'])){
			$sql.=" AND session =".$search['session'];
		}
		if(!empty($search['branch_id'])){
			$sql.=" AND branch_id =".$search['branch_id'];
		}
		
		$sql.=" ORDER BY stu_enname ASC ";
		//echo $sql;
		
		return $db->fetchAll($sql);
	}
}

