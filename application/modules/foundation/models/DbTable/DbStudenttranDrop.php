<?php

class Foundation_Model_DbTable_DbStudenttranDrop extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_trandrop';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	public function getAllStudranDrop($search){
		$_db = $this->getAdapter();
		$sql = "SELECT  s.id,				
				s.stu_code,
				s.name_kh,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=2 AND key_code = s.sex LIMIT 1) AS sex,
				(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=s.degree LIMIT 1) AS degree,
				(SELECT CONCAT(`major_enname`) FROM `rms_major` WHERE `major_id`=s.grade LIMIT 1) AS grade,
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1) AS room,
				date_stop,
				reason,
				(SELECT first_name FROM `rms_users` WHERE id=S.user_id LIMIT 1) AS user_name,
				(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code = s.status LIMIT 1) AS STATUS
				FROM `rms_student_trandrop` AS s  ";
		$order_by=" order by id DESC";
		$where="";
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=s.degree LIMIT 1) AS degree LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['degree'])){
			$where.=" AND s.degree = ".$search['degree'];
		}
		if(!empty($search['room'])){
			$where.=" AND rms_student_trandrop.room =".$search['room'];
		}
		return $_db->fetchAll($sql.$order_by);
	}
	public function getStudentDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function addStudentDrop($_data){
		$_db= $this->getAdapter();
		//print_r($_data); exit();
		try{	
			$_arr= array(
					'stu_code'		=>$_data['student_id'],
					'name_kh'		=>$_data['name_kh'],
					'group'			=>$_data['group'],
					'sex'			=>$_data['sex'],
					'academic_year'	=>$_data['academic_year'],
					'calture'		=>$_data['calture'],
					'session'		=>$_data['session'],
					'degree'		=>$_data['degree'],
					'grade'			=>$_data['grade'],
					'room'			=>$_data['room'],
					'stu_stop'		=>$_data['stu_stop'],
					'reason'		=>$_data['reason'],
					'date_stop'		=>date('Y-m-d'),
					'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
					);
			$id = $this->insert($_arr);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	} 
	
}



