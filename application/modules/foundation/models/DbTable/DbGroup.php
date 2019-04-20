<?php

class Foundation_Model_DbTable_DbGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function checkGroupExits($_data){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_group WHERE academic_year =".$_data['academic_year'];
    	$sql.=" AND group_code='".$_data['group_code']."'";
    	$sql.=" AND degree='".$_data['degree']."'";
    	$rs = $db->fetchOne($sql);
    	if(!empty($rs)){
    		return 1;
    	}
    	return null;
    }
	public function AddNewGroup($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$sql="SELECT id FROM rms_group WHERE branch_id =".$_data['branch_id'];
			$sql.=" AND group_code='".$_data['group_code']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr=array(
					'branch_id' 	=> $_data['branch_id'],
					'group_code' 	=> $_data['group_code'],
					'room_id' 		=> $_data['room'],
					'academic_year' => $_data['academic_year'],
					'semester' 		=> $_data['semester'],
					'session' 		=> $_data['session'],
					'time' 			=> $_data['time'],
					'degree' 		=> $_data['degree'],
					'grade' 		=> $_data['grade'],
					'calture' 		=> $_data['calture'],
// 					'start_date'	=> $_data['start_date'],
// 					'expired_date'	=> $_data['end_date'],
					'date' 			=> date("Y-m-d"),
					'status'   		=> 1,
					'teacher_id'   	=> $_data['teacher_id'],
					'teacher_assistance'=> $_data['teacher_ass'],
					'note'   		=> $_data['notes'],
					'user_id'	 	=> $this->getUserId(),
					'is_use' 		=> 0
			);
			$id = $this->insert($_arr);			
			$this->_name='rms_group_subject_detail';
			if(!empty($_data['identity1'])){
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i){
					$arr = array(
							'group_id'	=> $id,
							'subject_id'=> $_data['group_subject_study_'.$i],
							'amount_subject'=>$_data['amount_subject'.$i],
							'amount_subject_sem'=>$_data['amount_subject_semester'.$i],
							'teacher'   => $_data['teacher_'.$i],
							'note'   	=> $_data['group_note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId(),
					);
					$this->insert($arr);
				}
			}
			$db->commit();
			//return true;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function updateGroup($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=array(
					'branch_id' 	=> $_data['branch_id'],
					'group_code' 	=> $_data['group_code'],
					'room_id' 		=> $_data['room'],
					'academic_year' => $_data['academic_year'],
					'semester' 		=> $_data['semester'],
					'session' 		=> $_data['session'],
					'time' 			=> $_data['time'],
					'degree' 		=> $_data['degree'],
					'grade'		 	=> $_data['grade'],
					'calture' 		=> $_data['calture'],
// 					'start_date' 	=> $_data['start_date'],
// 					'expired_date'	=> $_data['end_date'],
					'date' 			=> date("Y-m-d"),
					'teacher_id'   	=> $_data['teacher_id'],
					'teacher_assistance'=> $_data['teacher_ass'],
					'status'   		=> $_data['status'],
					'note'   		=> $_data['notes'],
					'is_pass'   	=> $_data['is_pass'],
// 					'is_use'   		=> $_data['is_use'],
					'user_id'	  	=> $this->getUserId()
			);
			$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
			$this->_name='rms_group';
			$this->update($_arr, $where);
			
			$this->_name='rms_group_subject_detail';
			$where = 'group_id = '.$_data['id'];
			$this->delete($where);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'group_id'	=> $_data['id'],
							'subject_id'=> $_data['subject_study_'.$i],
							'amount_subject'=>$_data['amount_subject'.$i],
							'amount_subject_sem'=>$_data['amount_subject_semester'.$i],
							'teacher'   => $_data['teacher_'.$i],
							'note'   	=> $_data['note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId()
					);
					$this->insert($arr);
				}
			}
			
			return $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT g.* FROM rms_group as g WHERE g.id = ".$db->quote($id);
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('g.branch_id');
		$sql.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1)');
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
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
//   		$sql = ' SELECT * FROM `v_getallgroup` WHERE 1';
// 		$sql = ' SELECT group_code , CONCAT(from_academic,'-',to_academic) as year,semester,session,degree,grade,room_id,start_date,expired_date,note,status FROM `rms_group` WHERE 1';
		
		$sql = 'SELECT `g`.`id`,`g`.`group_code` AS `group_code`,academic_year as academic ,`g`.`semester` AS `semester`,
	(SELECT rms_items.'.$colunmname.' FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.'.$colunmname.' FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)AS grade,
		
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
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql = "SELECT `g`.`id`,
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
			`g`.`group_code` AS `group_code`,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS tuitionfee_id,		 
			 `g`.`semester` AS `semester`, 
			(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degree,
			(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade,
			(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			`g`.`note`,
			(select name_kh from rms_view where type=9 and key_code=is_pass) as group_status,
			g.status
			FROM `rms_group` AS `g`";
		
		$where =' WHERE 1 ';
		$from_date =(empty($search['start_date']))? '1': "g.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "g.date <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]="(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=' AND g.academic_year='.$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=' AND g.grade='.$search['grade'];
		}
		if(!empty($search['degree'])){
			$where.=' AND `g`.`degree`='.$search['degree'];
		}
		if(!empty($search['session'])){
			$where.=' AND g.session='.$search['session'];
		}
		if($search['status']>-1){
			$where.=' AND g.status='.$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND g.branch_id='.$search['branch_id'];
		}
		if(!empty($search['is_pass']) AND $search['is_pass']>-1){
			$where.=' AND g.is_pass='.$search['is_pass'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('g.branch_id');
		$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getAllYears(){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getAllYear();
	}
	
	
	public function getAllSubjectStudy($opt=null,$schoolOption=null){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rows = $_db->getAllSubjectStudy($schoolOption);
		array_unshift($rows,array('id' => -1,"name"=>$tr->translate("ADD_NEW_SUBJECT"),"shortcut"=>""));
		if($opt!=null){return $rows;}
		$options = '<option value="0">'.$tr->translate("CHOOSE_SUJECT").'</option>';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']."-".$value['shortcut'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	public function getAllTeacherOption($schoolOption=null,$branch_id=null){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$teacher = $this->getAllTeacher($schoolOption,$branch_id);
		array_unshift($teacher,array('id' => -1,"name"=>$tr->translate("ADD_NEW")));
		$teacher_options = '<option value="">'.$tr->translate("SELECT_TEACHER").'</option>';
		if(!empty($teacher))foreach($teacher as $value){
			$teacher_options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $teacher_options;
	}
	function getParentSubject(){
		$db = $this->getAdapter();
		$sql = "select id,subject_titlekh as name from rms_subject where is_parent =1 and status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllYear(){
		$db = $this->getAdapter();
		$sql = "select id,CONCAT(from_academic,'-',to_academic,'(',generation,')')as years from rms_tuitionfee ";
		return $db->fetchAll($sql);
	}
	public function getAllFecultyName(){
		//$db = $this->getAdapter();
		//$sql ="SELECT dept_id AS id, en_name AS NAME,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND en_name!='' ORDER BY en_name";
		//return $db->fetchAll($sql);
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllItems(1,null);
	}
	
	public function addNewRoom($_data){
		$this->_name='rms_room';
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'max_std'	  => $_data['max_std'],
				'floor'	  	  => $_data['floor'],
				'room_name'	  => $_data['room_name'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => $_data['status_room'],
				'user_id'	  => $this->getUserId(),
		);
		return  $this->insert($_arr);
	}
	
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
	
	public function addGradeAjax($_data){
		$this->_name='rms_major';
		try{
			$db = $this->getAdapter();
			$arr = array(
					'major_enname'  => $_data['major_enname'],
					'shortcut'	  => $_data['shortcut'],
					'dept_id'	  => $_data['degree_popup1'],
					'modify_date' => Zend_Date::now(),
					'is_active'	  => $_data['grade_status'],
					'user_id'	  => $this->getUserId()
			);
			return $this->insert($arr);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllTeacher($schoolOptin=null,$branch_id=null){
		$db = $this->getAdapter();
		$sql = " SELECT id,
				teacher_name_kh  as name 
			FROM rms_teacher 
			WHERE status=1 and staff_type=1 and teacher_name_kh!='' ";
		if (!empty($schoolOptin)){
			$sql.=" AND schoolOption =$schoolOptin";
		}
		if (!empty($branch_id)){
			$sql.=" AND branch_id =$branch_id";
		}
		return $db->fetchAll($sql);
	}
	function getTeacherByID($teacher_id){
		$db = $this->getAdapter();
		$sql ="SELECT * FROM rms_teacher AS g WHERE g.id=$teacher_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	
}

