<?php

class Foundation_Model_DbTable_DbRescheduleGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	public function addRescheduleGroup($_data){
    	$db= $this->getAdapter();
    	try{
    		if(!empty($_data['identity1'])){
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i){
					$arr = array(
							'branch_id'		=>$_data['branch_id'],
							'group_id'		=>$_data['group_code'],
							'year_id'		=>$_data['academic_year'],
							'day_id'		=>$_data['day_id_'.$i],
							'from_hour'		=>$_data['from_hour_'.$i],
							'to_hour'		=>$_data['to_hour_'.$i],
							'subject_id'	=>$_data['group_subject_study_'.$i],
							'techer_id'		=>$_data['teacher_'.$i],
							'note'			=>$_data['group_note_'.$i],
							'create_date'	=>date("Y-m-d H:i:s"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
						);
					$this->_name='rms_group_reschedule';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
	
	public function updateRescheduleGroup($_data,$id){
		
    	$db= $this->getAdapter();
    	try{
    		$this->_name="rms_group_reschedule";
    		$where = " group_id=$id ";
    		$this->delete($where);
    		
    		if(!empty($_data['identity1'])){
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i){
					$arr = array(
							'branch_id'		=>$_data['branch_id'],
							'group_id'		=>$_data['group_code'],
							'year_id'		=>$_data['academic_year'],
							'day_id'		=>$_data['day_id_'.$i],
							'from_hour'		=>$_data['from_hour_'.$i],
							'to_hour'		=>$_data['to_hour_'.$i],
							'subject_id'	=>$_data['group_subject_study_'.$i],
							'techer_id'		=>$_data['teacher_'.$i],
							'note'			=>$_data['group_note_'.$i],
							'create_date'	=>date("Y-m-d H:i:s"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
						);
					$this->_name='rms_group_reschedule';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
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
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
//   		$sql = ' SELECT * FROM `v_getallgroup` WHERE 1';
// 		$sql = ' SELECT group_code , CONCAT(from_academic,'-',to_academic) as year,semester,session,degree,grade,room_id,start_date,expired_date,note,status FROM `rms_group` WHERE 1';
		
		$sql = 'SELECT `g`.`id`,`g`.`group_code` AS `group_code`,academic_year as academic ,`g`.`semester` AS `semester`,
		
		(SELECT rms_items.'.$colunmname.' FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.'.$colunmname.' FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			
			
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
			$s_where[] = " (SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllRescheduleGroup($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT gr.group_id,
			(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
			(SELECT CONCAT(rms_tuitionfee.from_academic,'-',rms_tuitionfee.to_academic,'(',rms_tuitionfee.generation,')') 
       		FROM rms_tuitionfee WHERE rms_tuitionfee.status=1 AND rms_tuitionfee.is_finished=0 AND rms_tuitionfee.id=gr.year_id LIMIT 1) AS years,
       		(SELECT group_code FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AS group_code,
       		(SELECT name_en FROM rms_view WHERE rms_view.key_code=gr.day_id AND rms_view.type=18 LIMIT 1)AS days,
       		gr.from_hour,gr.to_hour,
       		(SELECT subject_titlekh FROM `rms_subject` WHERE is_parent=1 AND rms_subject.id = gr.subject_id AND subject_titlekh!='' LIMIT 1) AS subject_name,
       		(SELECT CONCAT(teacher_name_kh,'-',teacher_name_en) FROM rms_teacher WHERE rms_teacher.status=1 AND teacher_name_kh!='' LIMIT 1) AS teacher_name,
       		DATE_FORMAT(gr.create_date,'%d-%m-%Y'), (SELECT first_name FROM rms_users WHERE rms_users.id = gr.user_id) AS user
     		";
		$sql.=$dbp->caseStatusShowImage("gr.status");
		$sql.=" FROM rms_group_reschedule AS gr  WHERE gr.status=1 ";
		$where =' ';
		$order =  ' ORDER BY `gr`.`id` DESC ' ;
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=' AND gr.branch_id='.$search['branch_id'];
		}
		if(!empty($search['subject'])){
			$where.=' AND gr.subject_id='.$search['subject'];
		}
		if(!empty($search['day'])){
			$where.=' AND gr.day_id='.$search['day'];
		}
		if(!empty($search['study_year'])){
			$where.=' AND gr.year_id='.$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=' AND gr.group_id='.$search['group'];
		}
		$where.=$dbp->getAccessPermission('gr.branch_id');
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllGrade($grade_id){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
// 		$order=' ORDER BY id DESC';
// 		return $db->fetchAll($sql.$order);
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllGradeStudy(1);
	}
	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years FROM rms_tuitionfee WHERE `status`=1 and is_finished=0
		        GROUP BY from_academic,to_academic,generation";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	
	public function getAllSubjectStudy($opt=null){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rows = $_db->getAllSubjectStudy();
		array_unshift($rows,array('id' => -1,"name"=>"បន្ថែមមុខវិជ្ជាសិក្សា","shortcut"=>""));
		if($opt!=null){return $rows;}
		$options = '<option value="">ជ្រើសរើសរើសមុខវិជ្ជា</option>';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']."-".$value['shortcut'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	public function getAllTeacherOption(){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$teacher = $this->getAllTeacher();
		array_unshift($teacher,array('id' => -1,"name"=>"Add New"));
		$teacher_options = '<option value="">Select Teacher</option>';
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
// 		$db = $this->getAdapter();
// 		$sql ="SELECT dept_id AS id, en_name AS NAME,en_name,dept_id,shortcut FROM rms_dept WHERE is_active=1 AND en_name!='' ORDER BY en_name";
// 		return $db->fetchAll($sql);
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllItems(1,null);
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
	
	function getAllTeacher(){
		$db = $this->getAdapter();
		//$sql = "SELECT id,CONCAT(teacher_name_kh,'-',teacher_name_en) as name FROM rms_teacher WHERE status=1 and teacher_name_kh!='' ";
		$sql = "SELECT id,teacher_name_kh as name FROM rms_teacher WHERE status=1 and teacher_name_kh!='' ";
		return $db->fetchAll($sql);
	}
	
	
	
	public function addTeacherAjax($_data){
		$this->_name='rms_teacher';
		
		$_db = new Global_Model_DbTable_DbTeacher();
		$teacher_code = $_db->getTeacherCode();
		
		try{
			$db = $this->getAdapter();
			$arr = array(
					'teacher_code' => $teacher_code,
					'teacher_name_kh' => $_data['kh_name'],
					'teacher_name_en' => $_data['en_name'],
					'sex' => $_data['sex'],
					'dob' => $_data['dob'],
					'nationality'  => $_data['nationality'],
			        'tel'   => $_data['phone'],
					'address' => $_data['address'],
					'note' => $_data['note'],
		
					'branch_id' => 1,
			        'create_date' => Zend_Date::now(),
			        'user_id'	  => $this->getUserId(),
			);
			$id = $this->insert($arr);
			
			$teacher_option = $this->getAllTeacherOption();
			
			$array = array(
						'id'=>$id,
						'new_teacher_option'=>$teacher_option,
					);
			
			return $array;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getGroupName(){
		$db=$this->getAdapter();
		$sql=" SELECT id,group_code AS `name` FROM rms_group WHERE `status`=1";
		return $db->fetchAll($sql);
	}
	
	function getRescheduleById($id){
		$db=$this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT gr.*   FROM rms_group_reschedule AS gr  
   			  WHERE gr.group_id=$id";
		$sql.=$dbgb->getAccessPermission('gr.branch_id');
		return $db->fetchAll($sql);
	}
	
	function getSubjectByGroupId($id){
		
		$db=$this->getAdapter();
		$sql="SELECT sd.subject_id Ad id,
             (SELECT rms_subject.subject_titlekh  FROM `rms_subject` WHERE
   		      rms_subject.is_parent=1 AND rms_subject.status = 1 AND rms_subject.subject_titlekh!='' AND rms_subject.id=sd.subject_id LIMIT 1) AS `name`
   		      FROM rms_group_subject_detail AS sd
                WHERE  sd.subject_id>0 
                AND sd.group_id=10";
		//return $sql;
		return $db->fetchRow($sql);
	}
	
}

