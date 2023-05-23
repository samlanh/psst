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
		$sql = "select id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')')as years from rms_tuitionfee ";
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
			 (SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
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
					sgd.itemType=1 
					AND `status`=1 AND`group_id`=".$id;
			
		return $db->fetchAll($sql);
		
	}
	
	public function getGroupDetail($search){
		$db = $this->getAdapter();
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$branch = "branch_namekh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}else{ // English
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		//(select CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year LIMIT 1) as academic,
		$sql = " SELECT
					`g`.`id`,
					(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
					`g`.`group_code` AS `group_code`,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) as academic,
					(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) as degree,
					(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
					(SELECT	`rms_view`.$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
					(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					`g`.`note`,
					(select $label from rms_view where rms_view.type=9 and key_code=g.is_pass) as status,
					(SELECT COUNT(gds.`stu_id`) FROM `rms_group_detail_student` as gds WHERE gds.itemType=1 AND gds.`group_id`=`g`.`id` GROUP BY gds.group_id LIMIT 1) AS Num_Student,
					(SELECT COUNT(gds.`stu_id`) FROM `rms_group_detail_student` as gds WHERE gds.itemType=1 AND gds.is_pass=0 and gds.`group_id`=`g`.`id` and g.is_pass=1 GROUP BY gds.group_id LIMIT 1)AS remain_Student
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
			$s_where[]="(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
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
		if(!empty($search['grade'])){
			$where.=' AND g.grade='.$search['grade'];
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
	
	public function editStudentGroup($_data,$id){
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
				$dbGroup = new Foundation_Model_DbTable_DbGroup();
				$group_info = $dbGroup->getGroupById($_data['group']);
				$all_stu_id = $_data['public-methods'];
				
				foreach ($all_stu_id as $stu_id){
					
					$_arrcheck = array(
						'gd_id'	=>$stu_id,
						'degree'=>$group_info['degree'],
						'grade'	=>$group_info['grade'],
					);
					$checkRecord = $this->checkStudentGroupDetail($_arrcheck);
					if (!empty($checkRecord)){
						$arr_up = array(
								'user_id'	=>$this->getUserId(),
								'group_id'	=>$_data['group'],
								'stu_id'	=>$checkRecord['stu_id'],
								'status'	=>1,
								'degree'	=>$group_info['degree'],
								'grade'		=>$group_info['grade'],
								'session'	=>$group_info['session'],
								'is_current'=>1,
								'is_setgroup'=>1,
								'is_maingrade'	=>1,
								'date'		=>date('Y-m-d'),
								'modify_date'=>date("Y-m-d H:i:s"),
						);
						
						$where=  "stu_id = ".$checkRecord['stu_id']." AND gd_id=".$checkRecord['gd_id'];
						$this->_name='rms_group_detail_student';
						$this->update($arr_up, $where);
					}
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
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
// 	public function addStudentGroup($_data){
// 		$db = $this->getAdapter();
// 		try{
// 			if(!empty($_data['public-methods'])){
				
// 				$all_stu_id = $_data['public-methods'];
// 				foreach ($all_stu_id as $stu_id){
// 					$arr = array(
// 						'user_id'	=>$this->getUserId(),
// 						'group_id'	=>$_data['group'],
// 						'stu_id'	=>$stu_id,
// 						'status'	=>1,
// 						'date'		=>date('Y-m-d')
// 					);
// 					$this->_name='rms_group_detail_student';
// 					$this->insert($arr);
					
// 					$this->_name='rms_student';
// 					$data=array(
// 						'is_setgroup'	=> 1,
// 						'academic_year'	=> $_data['academic_year_group'],
// 						'degree'		=> $_data['degree_group'],
// 						'grade'			=> $_data['grade_group'],
// 						'session'		=> $_data['session_group'],
// 						'room'			=> $_data['room_group'],
// 						'group_id'		=> $_data['group'],
// 					);
// 					$where='stu_id = '.$stu_id;
// 					$this->update($data, $where);
// 				}
				
// 				$this->_name = 'rms_group';
// 				$data_gro = array(
// 					'is_use'=> 1,//ប្រើប្រាស់
// 					'is_pass'=> 2,//កំពុងសិក្សា
// 				);
// 				$where = 'id = '.$_data['group'];
// 				$this->update($data_gro, $where);
// 			}
// 		}catch(Exception $e){
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
// 	}

	
	public function getAllFecultyName(){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllItems(1,null);
	}
	function getSearchStudent($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				sd.gd_id,
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				s.sex,
				sd.degree,
				sd.grade,
				sd.feeId AS fee_id,
				sd.academic_year,
				(SELECT `title` FROM `rms_items` WHERE `id`=sd.degree AND TYPE=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=sd.grade AND items_type=1 LIMIT 1) AS grade_title
			  FROM 
			  	rms_student AS s,
			  	`rms_group_detail_student` AS sd 
		 	  WHERE 
				sd.itemType=1 
				AND s.stu_id = sd.stu_id
				AND s.`status`=1 
				AND s.customer_type = 1 
				AND sd.stop_type=0
				AND sd.is_setgroup = 0 ";
		if(!empty($search['academic_year'])){
			$sql.=" AND (SELECT tf.academic_year FROM `rms_tuitionfee` AS tf WHERE tf.id =(SELECT sf.fee_id FROM `rms_student_fee_history` AS sf WHERE sf.student_id = s.stu_id AND sf.is_current = 1 ORDER BY sf.id DESC LIMIT 1) LIMIT 1) =".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND sd.degree =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND sd.grade =".$search['grade'];
		}
		if(!empty($search['session'])){
			$sql.=" AND sd.session =".$search['session'];
		}
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
		$sql.=" GROUP BY s.stu_id,sd.degree,sd.grade";
		$sql.=" ORDER BY s.stu_id DESC ";
		return $db->fetchAll($sql);
	}
	function checkStudentGroupDetail($_data){
		$db=$this->getAdapter();
		$sql=" SELECT
				sd.gd_id,
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				s.sex,
				sd.degree,
				sd.grade,
				sd.academic_year,
				sd.is_setgroup
			  FROM 
			  	rms_student AS s,
			  	`rms_group_detail_student` AS sd 
		 	  WHERE 
				sd.itemType=1 
				AND s.stu_id = sd.stu_id
				AND s.`status`=1 
				AND s.customer_type = 1 
				AND sd.stop_type=0
				AND sd.is_setgroup=0
			 ";
		if(!empty($_data['gd_id'])){
			$sql.=" AND sd.gd_id =".$_data['gd_id'];
		}
		if(!empty($_data['degree'])){
			$sql.=" AND sd.degree =".$_data['degree'];
		}
		if(!empty($_data['grade'])){
			$sql.=" AND sd.grade =".$_data['grade'];
		}
		$sql.=" GROUP BY s.stu_id,sd.degree,sd.grade ";
		$sql.=" ORDER BY s.stu_id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
}

