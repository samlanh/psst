<?php

class Foundation_Model_DbTable_DbStudenttranDrop extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_trandrop';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllStudranDrop($search){
		$_db = $this->getAdapter();
		$sql="SELECT  s.id,				
				s.stu_code,
				s.name_kh,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=2 AND key_code = s.sex LIMIT 1) AS sex,
				(SELECT `en_name` FROM `rms_dept` WHERE `dept_id`=s.degree LIMIT 1) AS degree,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=5 AND key_code = s.stu_stop LIMIT 1) AS stu_stop,
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(SELECT room_name FROM rms_room WHERE room_id=s.room LIMIT 1) AS room,
				date_stop,
				reason,
				(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
				(SELECT name_kh FROM `rms_view` WHERE TYPE=1 AND key_code = s.status LIMIT 1) AS status
				FROM `rms_student_trandrop` AS s 
			";
		$orderby=" ORDER BY ID DESC ";
		$where=' where 1 ';
		if (!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.name_kh LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']){
			$where.=" AND s.degree =".$search['degree'];
		}
		if($search['academic_year']){
			$where.=" AND s.academic_year =".$search['academic_year'];
		}
		if($search['session']){
			$where.=" AND s.session =".$search['session'];
		}
		if($search['status']>-1){
			$where.=" AND status =".$search['status'];
		}
		//echo $sql.$where; exit();
		return $_db->fetchAll($sql.$where);
	}
	public function getStudentDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function addStudentDrop($_data){
		$_db= $this->getAdapter();
		try{	
			$sql="SELECT id FROM rms_student_trandrop WHERE academic_year =".$_data['academic_year'];
			$sql.=" AND name_kh='".$_data['name_kh']."'";
			$sql.=" AND calture='".$_data['calture']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr= array(
					'stu_code'		=>$_data['student_id'],
					'name_kh'		=>$_data['name_kh'],
				//	'group'			=>$_data['group'],
					'sex'			=>$_data['sex'],
					'academic_year'	=>$_data['academic_year'],
					'calture'		=>$_data['calture'],
					'session'		=>$_data['session'],
					'degree'		=>$_data['degree'],
				//	'grade'			=>$_data['grade'],
					'room'			=>$_data['room'],
					'stu_stop'		=>$_data['stu_stop'],
					'reason'		=>$_data['reason'],
					'date_stop'		=>$_data['date_stop'],
					'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
					);
			$id = $this->insert($_arr);
			//$_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();exit();
		}
	} 
	
}



