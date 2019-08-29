<?php

class Issue_Model_DbTable_DbTeacherScore extends Zend_Db_Table_Abstract
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
    function existingReadyinputExam($group_id,$exam_type,$for_semster,$for_month){
    	$sql="SELECT id FROM `rms_score` WHERE group_id=$group_id AND exam_type=$exam_type AND for_semester=$for_semster AND for_month=$for_month";
		return $this->getAdapter()->fetchOne($sql);
    }
    
	public function addStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id = $this->existingReadyinputExam($_data['group'], $_data['exam_type'], $_data['for_semester'], $_data['for_month']);
			if(empty($id)){
				$_arr = array(
						'title_score'=>$_data['title'],
						'group_id'=>$_data['group'],
				        'exam_type'=>$_data['exam_type'],
						'date_input'=>date("Y-m-d"),
						'note'=>$_data['note'],
						'user_id'=>$this->getUserId(),
						'type_score'=>1, // 1 => BacII score
						'for_academic_year'=>$_data['year_study'],
						'for_semester'=>$_data['for_semester'],
						'for_month'=>$_data['for_month'],
				);
				$id=$this->insert($_arr);
			}
			
			$session_t=new Zend_Session_Namespace('authteacher');
			$teacher_id = $session_t->teacher_id;
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$k=$k+1;
						foreach ($this->getSubjectByGroup($_data['group'],$teacher_id) as $index => $rs_parent){
							$parent_id = $rs_parent["subject_id"];
							$getChildren= $this->getChildSubject($parent_id);
								if(!empty($getChildren)){
									$no = $index + 1;
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
									
										$arr=array(
												'score_id'=>$id,
												'group_id'=>$_data['group'],
												'student_id'=>$_data['stu_id_'.$k],
												'subject_id'=> $subject_id,
												'score'=> $_data["$sub_name".$no],
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
									    //}
									}
								}else{/////////if parent have not subjects
									$no3 = $index+1;
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
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
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
		}
   }
   public function updateStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$_arr = array(
					'title_score'=>$_data['title'],
					'group_id'=>$_data['group'],
					//'reportdate'=>$_data['reportdate'],
					//'date_input'=>date("Y-m-d"),
					'exam_type'=>$_data['exam_type'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'for_academic_year'=>$_data['year_study'],
					'for_semester'=>$_data['for_semester'],
					'for_month'=>$_data['for_month'],
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
						foreach ($this->getSubjectByGroup($_data['group']) as $index => $rs_parent){
							$parent_id = $rs_parent["subject_id"];
							$getChildren= $this->getChildSubject($parent_id);
								if(!empty($getChildren)){
									$no = $index + 1;
// 									$parent_score = 0;
// 									foreach ($this->getChildSubject($parent_id) as $key => $rs_subs){
// 										$sub_name = str_replace(' ','',$rs_subs["subject_titleen"]);
// 										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
// 										$subject_id = $rs_parent['subject_id'];
// 										$no = $key+1;
// 										$parent_score = $parent_score + $_data["$sub_name".$no];
										
// 									}
									
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
									
									
// 									if(!$_data["$sub_name".$i]==''){
									$arr=array(
											'score_id'=>$id,
											'group_id'=>$_data['group'],
											'student_id'=>$_data['stu_id_'.$k],
											'subject_id'=> $subject_id,
											'score'=> $_data["$sub_name".$no],
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
										//if(!$_data["$sub_name".$no2]==''){
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
									    //}
									}
								}else{/////////if parent have not subjects
									$no3 = $index+1;
									$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
									$sub_name = $_data['stu_id_'.$k].$sub_name;
									$subject_id = $rs_parent['subject_id'];
									//if($_data["$sub_name".$no3]==''){
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
								    //}
								}
						}
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
	
	function getAllScore($search=null){
		$db=$this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql="SELECT s.id,s.title_score,
			(SELECT name_en FROM `rms_view` WHERE TYPE=14 AND key_code =s.exam_type LIMIT 1) as exam_type,
			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_id,
			
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)AS grade,
			
		
			(SELECT CONCAT(name_en ,'-',name_kh ) FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session`) AS session_id,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			s.status
			FROM rms_score AS s,rms_group AS g WHERE s.group_id=g.id AND s.status=1";
		//before add more =>AND g.degree IN(1,2) 
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " s.date_input >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_input <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[]=" s.title_score LIKE '%{$s_search}%'";
			$s_where[]=" s.note LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']>0){
			$where.= " AND g.degree =".$search['degree'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND g.academic_year =".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND `g`.`grade` =".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND `g`.`session` =".$search['session'];
		}
		if(!empty($search['room'])){
			$where.=" AND `g`.`room_id` =".$search['room'];
		}
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getScoreById($score_id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_score WHERE id=$score_id";
		return $db->fetchRow($sql);
	}
	function getHomeWorkDetailScoreById($score_id){
		$db=$this->getAdapter();
		$sql="SELECT sd.id,s.id,sd.student_no,sd.student_id,sd.score_id,
              (SELECT CONCAT(stu_enname,'-',stu_khname)  FROM rms_student WHERE  stu_id=sd.student_id) AS student_name,
	           sd.sex,(SELECT CONCAT(major_enname,' - ',major_khname ) AS major_enname
	           FROM rms_major WHERE rms_major.major_id=sd.grade_id) AS grade,sd.grade_id,sd.score,sd.note
               FROM rms_score AS s,rms_score_detail AS sd WHERE s.id=sd.score_id AND sd.score_id=$score_id";
		return $db->fetchAll($sql);
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
	
	function getAllGrade($degree){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,CONCAT(major_enname) As name FROM rms_major WHERE is_active=1 and dept_id=".$degree;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
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
		$sql="SELECT id,score_id,student_id,subject_id,score FROM rms_score_detail WHERE score_id=".$id;
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
	
	/*  For Teacher Score */
	
	function getAllTeacherScore($search=null){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$session_t=new Zend_Session_Namespace('authteacher');
		$teacher_id = $session_t->teacher_id;
		$sql="SELECT s.id,s.title_score,
		(SELECT name_en FROM `rms_view` WHERE TYPE=14 AND key_code =s.exam_type LIMIT 1) as exam_type,
		(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_id,
		(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)AS grade,
			
			
		(SELECT CONCAT(name_en ,'-',name_kh ) FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session`) AS session_id,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name` ";
		
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.=" FROM rms_teacherscore AS s,rms_group AS g WHERE s.group_id=g.id AND s.status=1 ";
		
		if (!empty($teacher_id)){
			$sql.=" AND s.teacher_id=$teacher_id";
		}
		//before add more =>AND g.degree IN(1,2)
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " s.date_input >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_input <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[]=" s.title_score LIKE '%{$s_search}%'";
			$s_where[]=" s.note LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']>0){
			$where.= " AND g.degree =".$search['degree'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND g.academic_year =".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND `g`.`grade` =".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND `g`.`session` =".$search['session'];
		}
		if(!empty($search['room'])){
			$where.=" AND `g`.`room_id` =".$search['room'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND `g`.`branch_id` =".$search['branch_id'];
		}
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function existingReadyinputExamScore($group_id,$exam_type,$for_semster,$for_month,$teacher_id){
		$sql="SELECT id FROM `rms_teacherscore` WHERE group_id=$group_id AND exam_type=$exam_type AND for_semester=$for_semster AND for_month=$for_month AND teacher_id=$teacher_id";
		return $this->getAdapter()->fetchOne($sql);
	}
	public function addTeacherStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		//print_r($_data); exit();
		try{
			$session_t=new Zend_Session_Namespace('authteacher');
			$teacher_id = $session_t->teacher_id;
			$id = $this->existingReadyinputExamScore($_data['group'], $_data['exam_type'], $_data['for_semester'], $_data['for_month'],$teacher_id);
			if(empty($id)){
				$_arr = array(
						'title_score'=>$_data['title'],
						'group_id'=>$_data['group'],
						'exam_type'=>$_data['exam_type'],
						'date_input'=>date("Y-m-d"),
						'note'=>$_data['note'],
						//'user_id'=>$teacher_id,
						'type_score'=>1, // 1 => BacII score
						'for_academic_year'=>$_data['year_study'],
						'for_semester'=>$_data['for_semester'],
						'for_month'=>$_data['for_month'],
						'teacher_id'=>$teacher_id,
				);
				$this->_name='rms_teacherscore';
				$id=$this->insert($_arr);
			}else{
				return 1;
			}
				
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$k=$k+1;
					foreach ($this->getSubjectByGroup($_data['group'],$teacher_id) as $index => $rs_parent){
						$parent_id = $rs_parent["subject_id"];
						$getChildren= $this->getChildSubject($parent_id);
						if(!empty($getChildren)){
							$no = $index + 1;
							$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
							$sub_name = $_data['stu_id_'.$k].$sub_name;
							$subject_id = $rs_parent['subject_id'];
								
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'student_id'=>$_data['stu_id_'.$k],
									'subject_id'=> $subject_id,
									'score'=> $_data["$sub_name".$no],
									'status'=>1,
									//'user_id'=>$this->getUserId(),
									'is_parent'=> $rs_parent["is_parent"],
									'behavior_comment'=> $_data['behavior'.$sub_name.$no],
									'homework_comment'=> $_data['homework'.$sub_name.$no],
									'class_participate_comment'=> $_data['classprticipate'.$sub_name.$no],
									'comment'=> $_data['comment'.$sub_name.$no],
							);
							$this->_name='rms_teacherscore_detail';
							$this->insert($arr);
							// 									}
							foreach ($this->getChildSubject($parent_id) as $key2 => $rs_sub){
								$no2= $key2+1;/////////if parent have subjects
								$subject_id = $rs_sub["id"];
								$sub_name = str_replace(' ','',$rs_sub["subject_titleen"]);
								$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
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
								$this->_name='rms_teacherscore_detail';
								$this->insert($arr);
								//}
							}
						}else{/////////if parent have not subjects
							$no3 = $index+1;
							$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
							$sub_name = $_data['stu_id_'.$k].$sub_name;
							$subject_id = $rs_parent['subject_id'];
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'student_id'=>$_data['stu_id_'.$k],
									'subject_id'=> $subject_id,
									'score'=> $_data["$sub_name".$no3],
									'status'=>1,
									'user_id'=>$this->getUserId(),
									'is_parent'=> $rs_parent["is_parent"],
									'behavior_comment'=> $_data['behavior'.$sub_name.$no3],
									'homework_comment'=> $_data['homework'.$sub_name.$no3],
									'class_participate_comment'=> $_data['classprticipate'.$sub_name.$no3],
									'comment'=> $_data['comment'.$sub_name.$no3],
							);
							$this->_name='rms_teacherscore_detail';
							$this->insert($arr);
						}
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function getStudentSccoreforEditTeacherScore($score_id){
		$db = $this->getAdapter();
		$sql="SELECT
		sd.student_id,
		(SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
		(SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
		(SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex
		FROM
		rms_teacherscore_detail AS sd
		WHERE sd.score_id =$score_id GROUP BY sd.`student_id` order by (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) DESC";
		return $db->fetchAll($sql);
	}
	function getScoreTeacherById($score_id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_teacherscore WHERE id=$score_id";
		return $db->fetchRow($sql);
	}
	function getScoreStudentsTeacherscore($id){
		$db=$this->getAdapter();
		$sql="SELECT id,score_id,student_id,subject_id,score FROM rms_teacherscore_detail WHERE score_id=".$id;
		return $db->fetchAll($sql);
	}
	
	function getSubjectByIdTeacherScore($id){
		$db = $this->getAdapter();
		$sql =" SELECT
		sd.*,
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
		rms_teacherscore_detail AS sd
		WHERE sd.score_id =$id ";
		return $db->fetchAll($sql);
	}
	
	function getGroupStudentTeacherScore($id){
		$db=$this->getAdapter();
		$sql="SELECT id,group_id,status FROM rms_teacherscore WHERE id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	public function updateTeacherStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_t=new Zend_Session_Namespace('authteacher');
			$teacher_id = $session_t->teacher_id;
			
			$_arr = array(
					'title_score'=>$_data['title'],
					'group_id'=>$_data['group'],
					//'reportdate'=>$_data['reportdate'],
					//'date_input'=>date("Y-m-d"),
					'exam_type'=>$_data['exam_type'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'user_id'=>$this->getUserId(),
					'for_academic_year'=>$_data['year_study'],
					'for_semester'=>$_data['for_semester'],
					'for_month'=>$_data['for_month'],
					'teacher_id'=>$teacher_id,
			);
			$this->_name='rms_teacherscore';
			$where="id=".$_data['score_id'];
			$db->getProfiler()->setEnabled(true);
			$this->update($_arr, $where);
	
			
			
			$id=$_data['score_id'];
			$this->_name='rms_teacherscore_detail';
			$this->delete("score_id=".$_data['score_id']);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$k=$k+1;
					foreach ($this->getSubjectByGroup($_data['group'],$teacher_id) as $index => $rs_parent){
						$parent_id = $rs_parent["subject_id"];
						$getChildren= $this->getChildSubject($parent_id);
						if(!empty($getChildren)){
							$no = $index + 1;
							// 									$parent_score = 0;
							// 									foreach ($this->getChildSubject($parent_id) as $key => $rs_subs){
							// 										$sub_name = str_replace(' ','',$rs_subs["subject_titleen"]);
							// 										$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
							// 										$subject_id = $rs_parent['subject_id'];
							// 										$no = $key+1;
							// 										$parent_score = $parent_score + $_data["$sub_name".$no];
	
							// 									}
								
							$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
							$sub_name = $_data['stu_id_'.$k].$sub_name;
							$subject_id = $rs_parent['subject_id'];
								
								
							// 									if(!$_data["$sub_name".$i]==''){
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'student_id'=>$_data['stu_id_'.$k],
									'subject_id'=> $subject_id,
									'score'=> $_data["$sub_name".$no],
									'status'=>1,
									'user_id'=>$this->getUserId(),
									'is_parent'=> $rs_parent["is_parent"],
									'behavior_comment'=> $_data['behavior'.$sub_name.$no],
									'homework_comment'=> $_data['homework'.$sub_name.$no],
									'class_participate_comment'=> $_data['classprticipate'.$sub_name.$no],
									'comment'=> $_data['comment'.$sub_name.$no],
							);
							$this->_name='rms_teacherscore_detail';
							$this->insert($arr);
							// 									}
							foreach ($this->getChildSubject($parent_id) as $key2 => $rs_sub){
								$no2= $key2+1;/////////if parent have subjects
								$subject_id = $rs_sub["id"];
								$sub_name = str_replace(' ','',$rs_sub["subject_titleen"]);
								$sub_name = "child".$_data['stu_id_'.$k].$sub_name;
								//if(!$_data["$sub_name".$no2]==''){
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
								$this->_name='rms_teacherscore_detail';
								$this->insert($arr);
								//}
							}
						}else{/////////if parent have not subjects
							$no3 = $index+1;
							$sub_name = str_replace(' ','',$rs_parent["subject_titleen"]);
							$sub_name = $_data['stu_id_'.$k].$sub_name;
							$subject_id = $rs_parent['subject_id'];
							//if($_data["$sub_name".$no3]==''){
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'student_id'=>$_data['stu_id_'.$k],
									'subject_id'=> $subject_id,
									'score'=> $_data["$sub_name".$no3],
									'status'=>1,
									'user_id'=>$this->getUserId(),
									'is_parent'=> $rs_parent["is_parent"],
									'behavior_comment'=> $_data['behavior'.$sub_name.$no3],
									'homework_comment'=> $_data['homework'.$sub_name.$no3],
									'class_participate_comment'=> $_data['classprticipate'.$sub_name.$no3],
									'comment'=> $_data['comment'.$sub_name.$no3],
							);
							$this->_name='rms_teacherscore_detail';
							$this->insert($arr);
							//}
						}
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}








