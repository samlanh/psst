<?php

class Foundation_Model_DbTable_DbGroupStudy extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
	public function AddNewGroup($_data){
		$db = $this->getAdapter();
		//$db->beginTransaction();
		try{
			$_arr=array(
					'group_code' 	=> $_data['group_code'],
					'academic_year' => $_data['academic_year'],
					'room_id' 		=> $_data['room'],
					'semester' 		=> $_data['semester'],
					'session' 		=> $_data['session'],
					'degree' 		=> $_data['degree'],
					'time' 			=> $_data['time'],
					'grade' 		=> $_data['grade'],
					'amount_month' 	=> $_data['amountmonth'],
					'start_date' 	=> $_data['start_date'],
					'expired_date'	=>$_data['end_date'],
					'date' 			=> date("Y-m-d"),
					'status'   		=> $_data['status'],
					'note'   		=> $_data['note'],
					'user_id'	  	=> $this->getUserId(),
					'is_use' 		=> 0
			);
			$id = $this->insert($_arr);
// 			return $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	public function updateGroup($_data){
		$db = $this->getAdapter();
		try{
			$_arr=array(
					'group_code' 	=> $_data['group_code'],
					'room_id' 		=> $_data['room'],
					'academic_year' => $_data['academic_year'],
					'semester' 		=> $_data['semester'],
					'session' 		=> $_data['session'],
					'degree' 		=> $_data['degree'],
					'time' 			=> $_data['time'],
					'grade' 		=> $_data['grade'],
					'amount_month' 	=> $_data['amountmonth'],
					'start_date' 	=> $_data['start_date'],
					'expired_date'	=>$_data['end_date'],
					'status'   		=> $_data['status'],
					'note'   		=> $_data['note'],
					'user_id'	  	=> $this->getUserId()
			);
			$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
			$this->update($_arr, $where);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	public function getGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	public function getGroupSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group_subject_detail WHERE group_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
	
	
	public function getallSubjectTeacherById($teacher_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_teacher_subject` WHERE teacher_id= ".$db->quote($teacher_id);
		return $db->fetchAll($sql);;
	}
	public function updateTeacher($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		$_arr=array(
					'teacher_code' => $_data['code'],
					'teacher_name_en' => $_data['en_name'],
					'teacher_name_kh' => $_data['kh_name'],
					'sex' => $_data['sex'],
					'phone' => $_data['phone'],
					'dob' => $_data['dob'],
					'pob' => $_data['pob'],
					'address' => $_data['address'],
					'email' => $_data['email'],
					'degree' => $_data['degree'],
					//'photo' => $_data['kh_subject'],
					'note'=>$_data['note'],
					'date' => Zend_Date::now(),
					'status'   => $_data['status'],
					'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
		$this->update($_arr, $where);
		
		$this->_name='rms_teacher_subject';
		$ids = explode(',', $_data['record_row']);
		foreach ($ids as $i){
			$arr = array(
					'subject_id'=>$_data['subject_id'.$i],
					'teacher_id'=>$_data["id"],
					'status'   => $_data['status'.$i],
					'date' => Zend_Date::now(),
					'user_id'	  => $this->getUserId()
		
			);
			if(!empty($_data['subexist_id'.$i])){
				$where=$this->getAdapter()->quoteInto("id=?", $_data['subexist_id'.$i]);
				$this->update($arr, $where);
			}else{
				$this->insert($arr);
			}
		}
		return $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	
	function getAllGroup($search){
		$db = $this->getAdapter();
//   		$sql = ' SELECT * FROM `v_getallgroup` WHERE 1';
// 		$sql = ' SELECT group_code , CONCAT(from_academic,'-',to_academic) as year,semester,session,degree,grade,room_id,start_date,expired_date,note,status FROM `rms_group` WHERE 1';
		
		$sql = 'SELECT `g`.`id`,`g`.`group_code` AS `group_code`,academic_year as academic ,`g`.`semester` AS `semester`,
		(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`)) AS degree,
		(SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`))AS grade,
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
		`g`.`start_date`,`g`.`expired_date`,`g`.`note`
		FROM `rms_group` `g`
		';	
		
// 		(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1)
// 				AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`
		
		$where =' WHERE 1 ';
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllGroups($search){
		$db = $this->getAdapter();
		$sql = "SELECT `g`.`id`,`g`.`group_code` AS `group_code`,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS tuitionfee_id,
		 `g`.`semester` AS `semester`,
		(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`)) AS degree,
		(SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`))AS grade,
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
		`g`.`start_date`,`g`.`expired_date`,`g`.`note`
		FROM `rms_group` as `g`";
		$where =' WHERE 1 and degree IN (2,3,4) ';
// 		$from_date =(empty($search['start_date']))? '1': "g.start_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': "g.start_date <= '".$search['end_date']." 23:59:59'";
// 		$where.= " AND ".$from_date." AND ".$to_date;
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]="(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT major_khname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`)) LIKE '%{$s_search}%'";
			$s_where[]="(SELECT kh_name FROM rms_dept WHERE rms_dept.dept_id=g.degree) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=' AND g.academic_year='.$search['study_year'];
		}
		if(!empty($search['grade_bac'])){
			$where.=' AND g.grade='.$search['grade_bac'];
		}
		if(!empty($search['session'])){
			$where.=' AND g.session='.$search['session'];
		}
		
		//print_r($sql.$where);
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years FROM rms_tuitionfee WHERE `status`=1
		        GROUP BY from_academic,to_academic,generation";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	
	public function getAllSubjectStudy(){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rows = $_db->getAllSubjectStudy();
		array_unshift($rows,array('id' => '',"name"=>""));
		$options = '';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	
	function getAllYear(){
		$db = $this->getAdapter();
		$sql = "select id,CONCAT(from_academic,'-',to_academic,'(',generation,')')as years from rms_tuitionfee ";
		return $db->fetchAll($sql);
	}
	
	public function getAllFecultyName(){
		$db = $this->getAdapter();
		$sql ="SELECT dept_id AS id, en_name AS NAME,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND en_name!='' and dept_id IN(2,3,4) ORDER BY dept_id DESC";
		return $db->fetchAll($sql);
	}
	
	public function addNewRoom($_data){
		$this->_name='rms_room';
		$_arr=array(
				'room_name'	  => $_data['room_name'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => $_data['status_room'],
				'user_id'	  => $this->getUserId(),
		);
		return  $this->insert($_arr);
	}
	
}

