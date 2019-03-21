<?php

class Issue_Model_DbTable_DbStudentEvaluation extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_evaluation';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	
	public function addStudentEvaluation($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'degree_id'		=>$_data['degree'],
			        'student_id'	=>$_data['student'],
					
					'for_type'		=>$_data['for_type'],
					'for_month'		=>$_data['for_month'],
					'for_semester'	=>$_data['for_semester'],
					
					'issue_date'	=>$_data['issue_date'],
					'return_date'	=>$_data['return_date'],
					'teacher_comment'=>$_data['teacher_comment'],
					'note'			=>$_data['note'],
					
					'create_date'	=>date("Y-m-d H:i:s"),
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'		=>$this->getUserId(),
					
			);
			$id=$this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$arr=array(
							'evaluation_id'	=>$id,
							'comment_id'	=>$_data['comment_id_'.$i],
							'rating_id'		=>$_data['rating_id_'.$i],
							'note'			=>$_data['remark'.$i],
					);
					$this->_name='rms_student_evaluation_detail';
					$this->insert($arr);
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
						$total_score = $total_score+$_data["score_".$i."_".$subject];
						
						$arr=array(
								'score_id'=>$id,
								'group_id'=>$_data['group'],
								'student_id'=>$_data['student_id'.$i],
								'amount_subject'=>$_data['amount_subject'.$i],
								'subject_id'=> $subject,
								'score'=> $_data["score_".$i."_".$subject],
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
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
	
	function getAllScore($search=null){
		$db=$this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql="SELECT s.id,
			(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) As branch_name,
			s.title_score,
			(SELECT name_kh FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as exam_type,
			s.for_semester,
			(SELECT month_kh FROM `rms_month` WHERE id=s.for_month  LIMIT 1) as for_month,
			s.max_score,
			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_id,
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			(SELECT CONCAT(name_en ,'-',name_kh ) FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			(SELECT name_en FROM rms_view WHERE type=1 AND key_code = s.status LIMIT 1) as status
				FROM rms_score AS s,rms_group AS g WHERE s.group_id=g.id AND s.status=1 ";
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
		if(!empty($search['group'])){
			$where.=" AND `s`.`group_id` =".$search['group'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('s.branch_id');
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getGroupName($academic,$session){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM  rms_group WHERE  `session`=$session AND academic_year=$academic  ";
		return $db->fetchAll($sql);
	}
	function getGroupSearch(){
		$db=$this->getAdapter();
		$sql="SELECT group_id AS id,(SELECT group_code FROM rms_group WHERE id=rms_score.group_id AND rms_group.degree IN (1,2)) AS `name` 
		               FROM  rms_score  WHERE  `status`=1 GROUP BY group_id";
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
	
	function getAllRating(){
		$db=$this->getAdapter();
		$sql="SELECT 
					id,
					rating
				FROM  
					rms_rating  
			";
		return $db->fetchAll($sql);
	}
	
	function getStudentByGroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT
					sgh.`stu_id` as id,
					CONCAT(s.stu_code,' - ',(CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END)) as name
				FROM 
					`rms_group_detail_student` AS sgh,
					rms_student as s
				WHERE 
					s.stu_id = sgh.stu_id
					and sgh.type = 1
					and sgh.is_pass = 0
					and sgh.group_id = $group_id
			";
		$order=" ORDER BY s.stu_code ASC ";
		return $db->fetchAll($sql.$order);
	}
	
	function getCommentByDegree($degree){
		$db=$this->getAdapter();
		$sql="SELECT
					dc.comment_id as id,
					c.comment
				FROM
					rms_degree_comment as dc,
					rms_comment as c
				WHERE
					dc.comment_id = c.id
					and dc.degree_id = $degree
			";
		return $db->fetchAll($sql);
	}
	
}


