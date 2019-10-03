<?php

class Foundation_Model_DbTable_DbAddStudentToGroup extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_detail_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
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
			if(!empty($_data['public-methods'])){
				
				$all_stu_id = $_data['public-methods'];
				foreach ($all_stu_id as $stu_id){
					$arr = array(
						'user_id'	=>$this->getUserId(),
						'group_id'	=>$_data['group'],
						'stu_id'	=>$stu_id,
						'status'	=>1,
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
					'is_use'=> 1,//ប្រើប្រាស់
					'is_pass'=> 2,//កំពុងសិក្សា
				);
				$where = 'id = '.$_data['group'];
				$this->update($data_gro, $where);
			}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	
	public function getAllFecultyName(){
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
				last_name,
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
		return $db->fetchAll($sql);
	}
}

