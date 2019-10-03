<?php

class Issue_Model_DbTable_DbScore extends Zend_Db_Table_Abstract
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
					'branch_id'=>$_data['branch_id'],
					'title_score'=>$_data['title'],
					'group_id'=>$_data['group'],
					'max_score'=>$_data['max_score'],
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
			$dbpush = new Application_Model_DbTable_DbGlobal();
			$rs_groupscore = $dbpush->getSumCutScorebyGroup($_data['group']);
			$total_cutscore = $rs_groupscore['score_short'];
// 			$dbpush->getTokenUser($_data['group'],null, 4);
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$total_score = 0;
				$rssubject = $_data['selector'];
				$subject_amt = 1 ;
				if(!empty($ids))foreach ($ids as $i){
					
					foreach ($rssubject as $subject){
						if($total_score>0 AND $old_studentid!=$_data['student_id'.$i]){
							$arr = array(
									'score_id'=>$id,
									'student_id'=>$old_studentid,
									'total_score'=>$total_score,
									'amount_subject'=>$subject_amt,
									'total_avg' =>number_format(($total_score)/$subject_amt,2)
							);
							$this->_name='rms_score_monthly';
							$this->insert($arr);
							$total_score = 0;
						}
						
						$old_studentid=$_data['student_id'.$i];
						$subject_amt = $_data['amount_subject'.$i];
						if($total_cutscore<=0){//=មិនកាត់ពិន្ទុតាមមុខវិជ្ជា
							$total_score = $total_score+$_data["score_".$i."_".$subject];
							$score_cut = 0;
						}else{//ពិន្ទុកាត់តាមមុខវិជ្ជា
							$rs_scorebygroup = $dbpush->getSumCutScorebyGroup($_data['group'],$subject);
							
							if(($_data["score_".$i."_".$subject]-$rs_scorebygroup['score_short'])<=0){
								$score = 0;
							}else{
								$score = $_data["score_".$i."_".$subject] - $rs_scorebygroup['score_short'];
							}
							$total_score = $total_score+$score;
							$score_cut = $rs_scorebygroup['score_short'];
						}
						
						$arr=array(
								'score_id'=>$id,
								'group_id'=>$_data['group'],
								'student_id'=>$_data['student_id'.$i],
								'amount_subject'=>$_data['amount_subject'.$i],
								'subject_id'=> $subject,
								'score'=> $_data["score_".$i."_".$subject],
								'score_cut'=> $score_cut,
								'status'=>1,
								'user_id'=>$this->getUserId(),
								'is_parent'=> 1
						);
						$this->_name='rms_score_detail';
						$this->insert($arr);
					}
				}
				
				if(!empty($ids)){
					if($total_score>0){
						$arr = array(
								'score_id'=>$id,
								'student_id'=>$old_studentid,
								'total_score'=>$total_score,
								'amount_subject'=>$subject_amt,
								'total_avg' =>number_format($total_score/$subject_amt,2)
						);
						$this->_name='rms_score_monthly';
						$this->insert($arr);
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   public function updateStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$_arr = array(
				'branch_id'=>$_data['branch_id'],
				'title_score'=>$_data['title'],
				'group_id'=>$_data['group'],
				'max_score'=>$_data['max_score'],
		        'exam_type'=>$_data['exam_type'],
				'date_input'=>date("Y-m-d"),
				'note'=>$_data['note'],
				'user_id'=>$this->getUserId(),
				'type_score'=>1, 
				'for_academic_year'=>$_data['year_study'],
				'for_semester'=>$_data['for_semester'],
				'for_month'=>$_data['for_month'],
			);
		$where="id=".$_data['score_id'];
		$this->update($_arr, $where);
		
		$id=$_data['score_id'];
		$this->_name='rms_score_detail';
		$this->delete("score_id=".$_data['score_id']);
		
		$this->_name='rms_score_monthly';
		$this->delete("score_id=".$_data['score_id']);
		$old_studentid = 0;
		
		$dbpush = new Application_Model_DbTable_DbGlobal();
		$rs_groupscore = $dbpush->getSumCutScorebyGroup($_data['group']);
		$total_cutscore = $rs_groupscore['score_short'];
		
		if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$total_score = 0;
				$rssubject = $_data['selector'];
				$subject_amt = 1 ;
				
				if(!empty($ids))foreach ($ids as $i){
					
					foreach ($rssubject as $subject){
						if($total_score>0 AND $old_studentid!=$_data['student_id'.$i]){
							$arr = array(
									'score_id'=>$id,
									'student_id'=>$old_studentid,
									'total_score'=>$total_score,
									'amount_subject'=>$subject_amt,
									'total_avg' =>number_format($total_score/$subject_amt,2)
							);
							$this->_name='rms_score_monthly';
							$this->insert($arr);
							$total_score = 0;
						}
							
						$old_studentid=$_data['student_id'.$i];
						$subject_amt = $_data['amount_subject'.$i];
						
						if($total_cutscore<=0){//=មិនកាត់ពិន្ទុតាមមុខវិជ្ជា
							$total_score = $total_score+$_data["score_".$i."_".$subject];
							$score_cut = 0;
						}else{//ពិន្ទុកាត់តាមមុខវិជ្ជា
							$rs_scorebygroup = $dbpush->getSumCutScorebyGroup($_data['group'],$subject);
								
							if(($_data["score_".$i."_".$subject]-$rs_scorebygroup['score_short'])<=0){
								$score = 0;
							}else{
								$score = $_data["score_".$i."_".$subject] - $rs_scorebygroup['score_short'];
							}
							$total_score = $total_score+$score;
							$score_cut = $rs_scorebygroup['score_short'];
						}
						
						$arr=array(
								'score_id'=>$id,
								'group_id'=>$_data['group'],
								'student_id'=>$_data['student_id'.$i],
								'amount_subject'=>$_data['amount_subject'.$i],
								'subject_id'=> $subject,
								'score'=> $_data["score_".$i."_".$subject],
								'score_cut'=> $score_cut,
								'status'=>1,
								'user_id'=>$this->getUserId(),
								'is_parent'=> 1
						);
						$this->_name='rms_score_detail';
						$this->insert($arr);
					}
				}
			}
			if(!empty($ids)){
				if($total_score>0){
					$arr = array(
						'score_id'=>$id,
						'student_id'=>$old_studentid,
						'total_score'=>$total_score,
						'amount_subject'=>$subject_amt,
						'total_avg' =>number_format($total_score/$subject_amt,2)
					);
					$this->_name='rms_score_monthly';
					$this->insert($arr);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
		$sql="SELECT s.id,
			(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) As branch_name,
			s.title_score,
			(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as exam_type,
			s.for_semester,
			CASE
				WHEN s.exam_type = 2 THEN ''
			ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
			END 
			as for_month,
			s.max_score,
			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_id,
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name` ";
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.=" FROM rms_score AS s,rms_group AS g WHERE s.group_id=g.id AND s.status=1  ";
		
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
// 		if(!empty($search['session'])){
// 			$where.=" AND `g`.`session` =".$search['session'];
// 		}
// 		if(!empty($search['room'])){
// 			$where.=" AND `g`.`room_id` =".$search['room'];
// 		}
		if(!empty($search['group'])){
			$where.=" AND `s`.`group_id` =".$search['group'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND `s`.`branch_id` =".$search['branch_id'];
		}
		if($search['for_month']>0){
			$where.=" AND s.for_month =".$search['for_month'];
		}
		if($search['exam_type']>0){
			$where.= " AND s.exam_type =".$search['exam_type'];
		}
		if($search['for_semester']>0){
			$where.= " AND s.for_semester =".$search['for_semester'];
		}
		$where.=$dbp->getAccessPermission('s.branch_id');
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getScoreById($score_id){
		$db=$this->getAdapter();
		$sql="SELECT s.*,(SELECT g.is_pass FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) as is_pass FROM rms_score AS s WHERE s.id=$score_id ";
// 		$sql.=" AND (SELECT g.is_pass FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1)=2"; // only group studying
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
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
					and sgh.stop_type=0
					and sgh.`group_id` = ".$group_id;
		$order=" ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC, (SELECT s.stu_enname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
		return $db->fetchAll($sql.$order);
	}
	function getSubjectByGroup($group_id,$teacher_id=null,$exam_type=1){
		$db=$this->getAdapter();
		
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbgb->currentlang();
			$colunmname='subject_titleen';
			if ($currentLang==1){
				$colunmname='subject_titlekh';
			}
			
		$sql="SELECT 
					gsjd.*,
					(SELECT max_score FROM `rms_dept_subject_detail` WHERE subject_id=gsjd.subject_id AND dept_id=g.degree LIMIT 1) AS max_subjectscore,
					(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
					(SELECT CONCAT(sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name,
					(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
					(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut,
					(gsjd.amount_subject) amtsubject_month,
					(gsjd.amount_subject_sem) amtsubject_semester,
					(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen,
					(SELECT sj.$colunmname FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name
					
				FROM 
			 		rms_group_subject_detail AS gsjd ,
			 		rms_group as g
				WHERE 
					g.id = gsjd.group_id
					and gsjd.group_id = ".$group_id;
		
		if($teacher_id!=null){
			$sql.=" AND gsjd.teacher = ".$teacher_id;
		}
		if($exam_type==1){//for month
			$sql.=" AND gsjd.amount_subject >0 ";
		}else{//for semester
			$sql.=" AND gsjd.amount_subject_sem >0 ";
		}
		$sql.=' ORDER BY gsjd.id ASC ';
		return $db->fetchAll($sql);
		
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
		  (SELECT (CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) FROM `rms_student` s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
		  (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
		  (SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
		  total_score,score,note,sd.amount_subject				
		FROM
	 	 rms_score_detail AS sd 
		WHERE sd.score_id =$score_id 
		GROUP BY sd.`student_id` order by 
		(SELECT s.`stu_code` FROM `rms_student`AS s 
		WHERE s.`stu_id`=sd.`student_id`) ASC ";
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
		$sql="SELECT id,score_id,student_id,subject_id,score 
		FROM rms_score_detail WHERE score_id=".$id;
		return $db->fetchAll($sql);
	}
	function getGroupStudent($id){
		$db=$this->getAdapter();
		$sql="SELECT id,group_id,status FROM rms_score WHERE id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getStudentScoreChildSubj($score_id,$student_id,$parent_suj_id){
		if($student_id==null){return false;}
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
				AND sd.`student_id`= $student_id ORDER BY sd.subject_id ASC ";
		return $db->fetchAll($sql);
	}
	

	function getAllMonth(){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$month = "month_kh";
		}else{ // English
			$month = "month_en";
		}
		$sql="select id , $month as month_kh from rms_month where status=1 ";
		return $db->fetchAll($sql);
	}	
	
	function checkSubjectScore($score_id,$subject){
		$db = $this->getAdapter();
		$sql=" SELECT
			sd.student_id,	
			sd.subject_id,
			(SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
			(SELECT `subject_titlekh` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titlekh,
			sd.score ,
			sd.`is_parent`
			FROM
			rms_score_detail AS sd
			WHERE sd.score_id =$score_id	
			AND sd.`subject_id` =$subject	
			GROUP BY sd.`subject_id` LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getStudentScoreBySubjectID($score_id,$student_id,$suj_id){
		if($student_id==null){
			return false;
		}
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
		AND sd.`subject_id` = $suj_id
		AND sd.`student_id`= $student_id ORDER BY sd.subject_id ASC LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	
}


