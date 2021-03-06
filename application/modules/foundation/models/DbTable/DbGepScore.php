<?php

class Foundation_Model_DbTable_DbGepScore extends Zend_Db_Table_Abstract
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
			$_arr = array(
					'group_id'=>$_data['group'],
					'reportdate'=>$_data['reportdate'],
					'date_input'=>date("Y-m-d"),
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'type_score'=>$_data['score_type'],
					'title_score'=>$_data['score_title'],
			);
			$id=$this->insert($_arr);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$k=$k+1;
						foreach ($this->getSubjectByGroup($_data['group'],$_data['score_type']) as $index => $rs_parent){
							$parent_id = $rs_parent["subject_id"];
								if(!empty($this->getChildSubject($parent_id))){
									$count = count($this->getChildSubject($parent_id));
									$parent_score = 0;
									foreach ($this->getChildSubject($parent_id) as $key => $rs_subs){
										$sub_name = str_replace(' ','',$rs_subs["subject_titleen"]);
										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
										$subject_id = $rs_parent['subject_id'];
										$no = $key+1;
										$parent_score = $parent_score + $_data["$sub_name".$no];
										
									}
									
// 									if(!$_data["$sub_name".$i]==''){
									$arr=array(
											'score_id'=>$id,
											'group_id'=>$_data['group'],
											'student_id'=>$_data['stu_id_'.$k],
											'subject_id'=> $subject_id,
											'score'=> $parent_score,
											'status'=>1,
											'user_id'=>$this->getUserId(),
											'is_parent'=> $rs_parent["is_parent"]
									);
									$this->_name='rms_score_detail';
									$this->insert($arr);
// 									}
									foreach ($this->getChildSubject($parent_id) as $key2 => $rs_sub){  
										$no2= $key2+1;/////////if parent have subjects
										$subject_id = $rs_sub["id"];
										$sub_name = str_replace(' ','',$rs_sub["subject_titleen"]);
										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
										if(!$_data["$sub_name".$no2]==''){
										$arr=array(
												'score_id'=>$id,
												'group_id'=>$_data['group'],
												'student_id'=>$_data['stu_id_'.$k],
												'subject_id'=> $subject_id,
												'score'=> $_data["$sub_name".$no2],
												'status'=>1,
												'user_id'=>$this->getUserId(),
												'is_parent'=> $rs_sub["is_parent"]
										);
										$this->_name='rms_score_detail';
										$this->insert($arr);
									  }
									}
								}else{/////////if parent have not subjects
									$no3 = $index+1;
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
									if(!$_data["$sub_name".$no3]==''){
									$arr=array(
											'score_id'=>$id,
											'group_id'=>$_data['group'],
											'student_id'=>$_data['stu_id_'.$k],
											'subject_id'=> $subject_id,
											'score'=> $_data["$sub_name".$no3],
											'status'=>1,
											'user_id'=>$this->getUserId(),
											'is_parent'=> $rs_parent["is_parent"]
									);
									$this->_name='rms_score_detail';
									$this->insert($arr);
								  }
								}
						}
				}
			}
// 			exit();
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
		}
   }
   public function updateStudentScore($_data){
		//print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$_arr = array(
					'group_id'=>$_data['group'],
					'academic_id'=>$_data['year_study'],
// 					'degree'=>$_data['degree'],
// 					'grade'=>$_data['grade'],
// 					'session_id'=>$_data['session'],
						
					'reportdate'=>$_data['reportdate'],
					//'date_input'=>date("Y-m-d"),
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'type_score'=>$_data['score_type'],
					'title_score'=>$_data['score_title'],
			);
		$where="id=".$_data['score_id'];
		$db->getProfiler()->setEnabled(true);
		$this->update($_arr, $where);
		$id=$_data['score_id'];
		
		$this->_name='rms_score_detail';

		$this->delete("score_id=".$_data['score_id']);
		  
		if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$k=$k+1;
						foreach ($this->getSubjectByGroup($_data['group'],$_data['score_type']) as $index => $rs_parent){
							$parent_id = $rs_parent["subject_id"];
								if(!empty($this->getChildSubject($parent_id))){
									$count = count($this->getChildSubject($parent_id));
									$parent_score = 0;
									foreach ($this->getChildSubject($parent_id) as $key => $rs_subs){
										$sub_name = str_replace(' ','',$rs_subs["subject_titleen"]);
										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
										$subject_id = $rs_parent['subject_id'];
										$no = $key+1;
										$parent_score = $parent_score + $_data["$sub_name".$no];
										
									}
									
// 									if(!$_data["$sub_name".$i]==''){
									$arr=array(
											'score_id'=>$id,
											'group_id'=>$_data['group'],
											'student_id'=>$_data['stu_id_'.$k],
											'subject_id'=> $subject_id,
											'score'=> $parent_score,
											'status'=>1,
											'user_id'=>$this->getUserId(),
											'is_parent'=> $rs_parent["is_parent"]
									);
									$this->_name='rms_score_detail';
									$this->insert($arr);
// 									}
									foreach ($this->getChildSubject($parent_id) as $key2 => $rs_sub){  
										$no2= $key2+1;/////////if parent have subjects
										$subject_id = $rs_sub["id"];
										$sub_name = str_replace(' ','',$rs_sub["subject_titleen"]);
										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
										if(!$_data["$sub_name".$no2]==''){
										$arr=array(
												'score_id'=>$id,
												'group_id'=>$_data['group'],
												'student_id'=>$_data['stu_id_'.$k],
												'subject_id'=> $subject_id,
												'score'=> $_data["$sub_name".$no2],
												'status'=>1,
												'user_id'=>$this->getUserId(),
												'is_parent'=> $rs_sub["is_parent"]
										);
										$this->_name='rms_score_detail';
										$this->insert($arr);
									  }
									}
								}else{/////////if parent have not subjects
									$no3 = $index+1;
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
									if(!$_data["$sub_name".$no3]==''){
									$arr=array(
											'score_id'=>$id,
											'group_id'=>$_data['group'],
											'student_id'=>$_data['stu_id_'.$k],
											'subject_id'=> $subject_id,
											'score'=> $_data["$sub_name".$no3],
											'status'=>1,
											'user_id'=>$this->getUserId(),
											'is_parent'=> $rs_parent["is_parent"]
									);
									$this->_name='rms_score_detail';
									$this->insert($arr);
								  }
								}
						}
				}
			}
// 			exit();
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
	
	

	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS name FROM rms_tuitionfee WHERE `status`=1
		GROUP BY from_academic,to_academic,generation";
		$order=' ORDER BY id DESC';
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
		$sql="SELECT group_id AS id,(SELECT group_code FROM rms_group WHERE id=rms_score.group_id AND rms_group.degree NOT IN (1,2)) AS `name` 
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
		(SELECT CONCAT(s.stu_enname,' - ',s.stu_khname) FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name,
		(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex
		FROM `rms_group_detail_student` AS sgh
		WHERE sgh.`group_id`=".$group_id;
		$order=" ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) DESC";
		return $db->fetchAll($sql.$order);
	}
	function getSubjectByGroup($group_id,$score_type){
		$db=$this->getAdapter();
		$sql="SELECT *,
		(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
		(SELECT CONCAT(sj.subject_titleen,'-',sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name,
		(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
		(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen
		 FROM rms_group_subject_detail AS gsjd WHERE gsjd.group_id = ".$group_id;
		if($score_type==2){
			$sql.=" AND gsjd.subject_id!=9";
		}
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	function getChildSubject($subject_id){
		$db=$this->getAdapter();
		$sql="SELECT sj.`id`,CONCAT(sj.subject_titleen,'-',sj.subject_titlekh) AS sub_name,
		sj.`parent`,sj.`is_parent`,sj.`subject_titleen`
		 FROM `rms_subject` AS sj WHERE sj.`parent`=".$subject_id;
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
		WHERE sd.score_id =$score_id GROUP BY sd.`student_id`";
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
		sd.score ,
		sd.`is_parent`
		FROM
		rms_score_detail AS sd
		WHERE sd.score_id =$id ";
		return $db->fetchAll($sql);
	}
	function getScoreStudents($id){
		$db=$this->getAdapter();
		$sql="SELECT id,score_id,student_id,subject_id,score FROM rms_score_detail WHERE score_id=".$id;
		return $db->fetchAll($sql);
	}
	function getGroupStudent($id){
		$db=$this->getAdapter();
		$sql="SELECT id,academic_id,session_id,group_id,term_id,status,reportdate FROM rms_score WHERE id=$id LIMIT 1";
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
				  sd.score ,
  				  sd.`is_parent`
				FROM
				  rms_score_detail AS sd 
				WHERE sd.score_id = $score_id
				AND sd.`student_id`= $student_id
				AND (SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id=sd.`subject_id` LIMIT 1) =$parent_suj_id";
		return $db->fetchAll($sql);
	}
	
	function getAllgroupStudyNotPass($action=null){
		$db = $this->getAdapter();
		$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
		(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
		FROM `rms_group` AS `g` WHERE g.status =1 AND g.`degree` NOT IN(1,2)";
		$where ='';
		if (!empty($action)){
			$where = " AND (g.is_pass=0 || g.id = $action)";
		}else{
			$where = " AND g.is_pass=0 ";
		}
		return $db->fetchAll($sql.$where);
	}
}

