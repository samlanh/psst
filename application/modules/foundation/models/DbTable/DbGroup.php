<?php

class Foundation_Model_DbTable_DbGroup extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function checkGroupExits($_data){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_group WHERE branch_id =".$_data['branch_id']." AND academic_year =".$_data['academic_year'];
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
			$dbg = new Application_Model_DbTable_DbGlobal();
			$schoolOption = $dbg->getSchoolOptionbyDegree($_data['degree']);
			
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
					'school_option' => $schoolOption,
					'date' 			=> date("Y-m-d"),
					'status'   		=> 1,
					'teacher_id'   	=> $_data['teacher_id'],
					'teacher_assistance'=> $_data['teacher_ass'],
					'note'   		=> $_data['notes'],
					'user_id'	 	=> $this->getUserId(),
					'total_score' 		=> $_data['total_max_score'],
					'amount_subject' 	=> $_data['divide_subject'],
					'max_average' 		=> $_data['max_average'],

					'semesterTotalScore' 	=> $_data['semesterTotalScore'],
					'semesterTotalSubject' 	=> $_data['semesterTotalSubject'],
					'semesterTotalAverage' 	=> $_data['semesterTotalAverage'],

					'is_use' 		=> 0,
					'gradingId' 		=> $_data['gradingId'],
			);
			if (EDUCATION_LEVEL==1){
				$_arr['calture'] = $_data['calture'];
			}
			
			$id = $this->insert($_arr);			
			$this->_name='rms_group_subject_detail';
			if(!empty($_data['identity1'])){
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i){
					$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();
					
					$arr = array(
							'group_id'		=> $id,
							'subject_id'	=> $_data['group_subject_study_'.$i],
							'max_score'		=> $_data['max_score'.$i],
							'score_short'	=> $_data['scoreshort_'.$i],
							'amount_subject'=> $_data['amount_subject'.$i],
							'semester_max_score' => $_data['semester_max_score'.$i],
							'amount_subject_sem'=> $_data['amount_subject_semester'.$i],
							'teacher'   	=> $_data['teacher_'.$i],
							'note'   		=> $_data['group_note_'.$i],
							'date' 			=> date("Y-m-d"),
							'user_id'		=> $this->getUserId(),
					);
					$this->insert($arr);

				}
			}
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	public function updateGroup($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbg = new Application_Model_DbTable_DbGlobal();
			$schoolOption = $dbg->getSchoolOptionbyDegree($_data['degree']);
			
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
					'school_option' => $schoolOption,
// 					'start_date' 	=> $_data['start_date'],
// 					'expired_date'	=> $_data['end_date'],
					'date' 			=> date("Y-m-d"),
					'teacher_id'   	=> $_data['teacher_id'],
					'teacher_assistance'=> $_data['teacher_ass'],
					'status'   		=> $_data['status'],
					'note'   		=> $_data['notes'],
					'is_pass'   	=> $_data['is_pass'],
					'total_score' 		=> $_data['total_max_score'],
					'amount_subject' 	=> $_data['divide_subject'],
					'max_average' 		=> $_data['max_average'],

					'semesterTotalScore' 	=> $_data['semesterTotalScore'],
					'semesterTotalSubject' 	=> $_data['semesterTotalSubject'],
					'semesterTotalAverage' 	=> $_data['semesterTotalAverage'],
					
					'user_id'	  	=> $this->getUserId(),
					'gradingId' 		=> $_data['gradingId'],
			);
			
			if (EDUCATION_LEVEL==1){
				$_arr['calture'] = $_data['calture'];
			}
			$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
			$this->_name='rms_group';
			$this->update($_arr, $where);
			
			$this->_name='rms_group_subject_detail';
			$where = 'group_id = '.$_data['id'];
			$this->delete($where);
			$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();
			if(!empty($_data['identity1'])){
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i){
				

					$arr = array(
							'group_id'		=> $_data['id'],
							'subject_id'	=> $_data['group_subject_study_'.$i],
							'max_score'		=> $_data['max_score'.$i],
							'score_short'	=> $_data['scoreshort_'.$i],
							'amount_subject'=> $_data['amount_subject'.$i],
							'semester_max_score' => $_data['semester_max_score'.$i],
							'amount_subject_sem'=> $_data['amount_subject_semester'.$i],
							'teacher'   	=> $_data['teacher_'.$i],
							'note'   		=> $_data['group_note_'.$i],
							'date' 			=> date("Y-m-d"),
							'user_id'		=> $this->getUserId()
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
		$sql.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
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
	function getAllGroups($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
		
		$sql = "SELECT `g`.`id`,
			(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
			`g`.`group_code` AS `group_code`,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS tuitionfee_id,	
			 `g`.`semester` AS `semester`, 
			(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degree,
			(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade,
			(SELECT`rms_view`.$label FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			(select teacher_name_kh from rms_teacher where rms_teacher.id = g.teacher_id limit 1 ) as teaccher,
			time,
			`g`.`note`,
			(select $label from rms_view where type=9 and key_code=is_pass) as group_status ";
		
		$sql.=$dbp->caseStatusShowImage("g.status");
		$sql.=" FROM `rms_group` AS `g` ";
		
		$where =' WHERE 1 ';
		$from_date =(empty($search['start_date']))? '1': "g.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "g.date <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]="(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) LIKE '%{$s_search}%'";
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
		if(!empty($search['academic_year'])){
			$where.=' AND g.academic_year='.$search['academic_year'];
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
		if(!empty($search['status']) AND $search['status']>-1 ){
			$where.=' AND g.status='.$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND g.branch_id='.$search['branch_id'];
		}
		if(!empty($search['teacher'])){
			$where.=' AND g.teacher_id='.$search['teacher'];
		}
		if(!empty($search['is_pass']) AND $search['is_pass']>-1){
			$where.=' AND g.is_pass='.$search['is_pass'];
		}
		$where.=$dbp->getAccessPermission('g.branch_id');
		$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		return $db->fetchAll($sql.$where.$order);
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
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	public function getAllTeacherOption($schoolOption=null,$branch_id=null){
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$teacher = $this->getAllTeacher($schoolOption,$branch_id);
		array_unshift($teacher,array('id' => -1,"name"=>$tr->translate("ADD_NEW")));
		$teacher_options = '<option value="0">'.$tr->translate("PLEASE_SELECT").'</option>';
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
		$sql = "select id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')')as years from rms_tuitionfee ";
		return $db->fetchAll($sql);
	}
	public function getAllFecultyName(){
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllItems(1,null);
	}
	
	
	
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
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