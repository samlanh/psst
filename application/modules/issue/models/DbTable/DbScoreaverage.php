<?php

class Issue_Model_DbTable_DbScoreaverage extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
    public function getAllSubjectParent(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,subject_titleen FROM rms_subject WHERE is_parent=1";
    	return $db->fetchAll($sql);
    }
    
    public function getAllSubjectParentByID($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_subject WHERE id=".$id;
    	return $db->fetchRow($sql);
    }
    
	public function addStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($_data['group']);
			$year_study = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			
			$_arr = array(
					'title_score'=>$_data['title'],
					'group_id'=>$_data['group'],
			        'exam_type'=>$_data['exam_type'],
					'date_input'=>date("Y-m-d"),
					'note'=>$_data['note'],
					'user_id'=>$this->getUserId(),
					'type_score'=>1, // 1 => BacII score
					'for_academic_year'=>$year_study,
					'for_semester'=>$_data['for_semester'],
					'for_month'=>$_data['for_month'],
			);
			$id=$this->insert($_arr);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
						$arr=array(
							'score_id'=>$id,
							'group_id'=>$_data['group'],
							'student_id'=>$_data['student_id'.$i],
							'subject_id'=> 1,
							'total_score'=>$_data['total_score'.$i],
							'score'=>$_data['average'.$i],
							'note'=>$_data['note_'.$i],
							'status'=>1,
							'user_id'=>$this->getUserId(),
							'is_parent'=> 1
						);
					$this->_name='rms_score_detail';
					$this->insert($arr);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   public function updateStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($_data['group']);
			$year_study = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			
			//$_data['status']= empty($_data['status'])?1:$_data['status'];
				$_arr = array(
					'title_score'=>$_data['title'],
					'group_id'=>$_data['group'],
					'exam_type'=>$_data['exam_type'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'for_academic_year'=>$year_study,
					'for_semester'=>$_data['for_semester'],
					'for_month'=>$_data['for_month'],
					'status'=>$_data['status'],
				);
			$where="id=".$_data['score_id'];
			$this->update($_arr, $where);
		
		$id=$_data['score_id'];
		$this->_name='rms_score_detail';
		$this->delete("score_id=".$_data['score_id']);
		if(!empty($_data['identity'])){
			$ids = explode(',', $_data['identity']);
			if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'score_id'=>$id,
							'group_id'=>$_data['group'],
							'student_id'=>$_data['student_id'.$i],
							'subject_id'=> 1,
							'total_score'=>$_data['total_score'.$i],
							'score'=>$_data['average'.$i],
							'note'=>$_data['note_'.$i],
							'status'=>1,
							'user_id'=>$this->getUserId(),
							'is_parent'=> 1
					);
				$this->_name='rms_score_detail';
				$this->insert($arr);
			}
		}
		$db->commit();
		}catch (Exception $e){
			$db->rollBack();
		}
   }
	function getStudyYears(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(from_academic,'-',to_academic) AS name FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	function getGroupAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	
	
	function getScoreById($score_id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_score WHERE id=$score_id";
		return $db->fetchRow($sql);
	}
	
	function getGroupName($academic,$session){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM  rms_group WHERE  `session`=$session AND academic_year=$academic  ";
		return $db->fetchAll($sql);
	}
	function getParentNameByGroupId($group_id){
		$db=$this->getAdapter();
		$sql="SELECT subject_id AS id,(SELECT CONCAT(subject_titleen,' - ',subject_titlekh)
		        FROM rms_subject WHERE rms_subject.id= rms_group_subject_detail.subject_id) AS `name`
		        FROM rms_group_subject_detail WHERE group_id=$group_id";
		return $db->fetchAll($sql);
	}
	function getGroupSearch(){
		$db=$this->getAdapter();
		$sql="SELECT group_id AS id,(SELECT group_code FROM rms_group WHERE id=rms_score.group_id AND rms_group.degree IN (1,2)) AS `name` 
		               FROM  rms_score  WHERE  `status`=1 GROUP BY group_id";
		return $db->fetchAll($sql);
	}
	///get subject id all 
	function getSubjectId(){
		$db=$this->getAdapter();
		$sql="SELECT id,parent,CONCAT(subject_titleen,'-',subject_titlekh) AS sub_name FROM rms_subject  WHERE `status` =1";
		return $db->fetchAll($sql);
	}
	function countScore($id){
		$db = $this->getAdapter();
		$sql ="SELECT s.`score_id` FROM `rms_score_detail` AS s WHERE s.`score_id`=$id GROUP BY s.`student_id`";
		return $db->fetchAll($sql);
	}
	function studentScore($id){
		$db=$this->getAdapter();
		$sql="SELECT s.id,s.subject_titleen,s.is_parent
		FROM rms_subject AS s,rms_score_detail AS sd WHERE s.id=sd.subject_id AND sd.score_id=$id";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	
	
	
	function getStudent($year,$grade,$session){//not use
		$db=$this->getAdapter();
		$sql="SELECT stu_id,stu_code,CONCAT(stu_enname,' - ',stu_khname) AS stu_name,sex
	    	FROM rms_student AS s WHERE academic_year = $year and grade=$grade and session=$session";
		$order=" ORDER BY stu_code DESC";
		return $db->fetchAll($sql.$order);
	}
	function getStudentByGroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT
					sgh.`stu_id`,
					(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_code,
					(SELECT (CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END) FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name,
					(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex
				FROM 
					`rms_group_detail_student` AS sgh
				WHERE 
					sgh.type = 1
					and sgh.`group_id` = ".$group_id;
		$order=" ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) DESC";
		return $db->fetchAll($sql.$order);
	}
	function getSubjectByGroup($group_id,$teacher_id=null){
		$db=$this->getAdapter();
		$sql="SELECT *,
		(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
		(SELECT CONCAT(sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name,
		(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
		(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen
		 FROM rms_group_subject_detail AS gsjd WHERE gsjd.group_id = ".$group_id;
		if($teacher_id!=null){
			$sql.=" AND teacher = ".$teacher_id;
		}
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	function getChildSubject($subject_id){
		$db=$this->getAdapter();
		$sql="SELECT 
					sj.`id`,
					CONCAT(sj.subject_titlekh) AS sub_name,
					sj.`parent`,
					sj.`is_parent`,
					sj.`subject_titleen`
		 	  FROM 
					`rms_subject` AS sj 
			  WHERE 
					sj.`parent`=".$subject_id;
		
		return $db->fetchAll($sql);
	}
	
	function getStudentSccoreforEdit($score_id){
		$db = $this->getAdapter();
		$sql="SELECT 
		sd.student_id,
		(SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
		  (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
		  (SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex				
		FROM
	 	 rms_score_detail AS sd 
		WHERE sd.score_id =$score_id GROUP BY sd.`student_id` order by (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) DESC";
		return $db->fetchAll($sql);
	}
	function getSubjectById($id){
		$db = $this->getAdapter();
		$sql =" SELECT
		sd.student_id,
		(SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
		(SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
		(SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
		sd.subject_id,
		(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id=sd.`subject_id` LIMIT 1) AS parent,
		(SELECT CONCAT(`subject_titlekh`,'-',`subject_titleen`) FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_name,
		(SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
		(SELECT `subject_titlekh` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titlekh,
		sd.score ,
		sd.`is_parent`
		FROM
		rms_score_detail AS sd
		WHERE sd.score_id =$id ";
		return $db->fetchAll($sql);
	}
	function getScoreStudents($id){
		$db=$this->getAdapter();
		$sql="SELECT 
			id,
			score_id,
			student_id,
			subject_id,
			score 
		FROM 
		rms_score_detail 
			WHERE score_id=".$id;
		return $db->fetchAll($sql);
	}
	function getGroupStudent($id){
		$db=$this->getAdapter();
		$sql="SELECT id,group_id,status FROM rms_score WHERE id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getStudentScoreChildSubj($score_id,$student_id,$parent_suj_id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  sd.student_id,
				  (SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
				  (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
				  (SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
				  sd.subject_id,
				  (SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id=sd.`subject_id` LIMIT 1) AS parent,
				  (SELECT CONCAT(`subject_titlekh`,'-',`subject_titleen`) FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_name,
				  (SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
				  (SELECT `subject_titlekh` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titlekh,
				  sd.score ,
  				  sd.`is_parent`
				FROM
				  rms_score_detail AS sd 
				WHERE sd.score_id = $score_id
				AND sd.`student_id`= $student_id
				AND (SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id=sd.`subject_id` LIMIT 1) =$parent_suj_id";
		return $db->fetchAll($sql);
	}
	

	function getAllMonth(){
		$db = $this->getAdapter();
		$sql="select id , month_kh from rms_month where status=1 ";
		return $db->fetchAll($sql);
	}	
	
	
	
	
	
}








