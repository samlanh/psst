<?php

class Foundation_Model_DbTable_DbScoreexcel extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	public function addStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
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
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$k=0;
				if(!empty($ids))foreach ($ids as $i){
					$rssubject = $this->getSubjectByGroup($_data['group']);
					if(!empty($rssubject)){
						foreach ($rssubject as $index => $rs_parent){
							$subject_id = $rs_parent["subject_id"];
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'subject_id'=>$subject_id,
									'student_id'=>$_data['student_id'.$i],
									'score'=>$_data['score_'.$index.'_'.$i],
									'note'=>$_data['note_'.$i],
									'status'=>1,
									'user_id'=>$this->getUserId(),
									'is_parent'=> 1
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
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   public function updateStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
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
			$where="id=".$_data['score_id'];
			$this->update($_arr, $where);
		
		$id=$_data['score_id'];
		$this->_name='rms_score_detail';
		$this->delete("score_id=".$_data['score_id']);
		
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$rssubject = $this->getSubjectByGroup($_data['group']);
					if(!empty($rssubject)){
						foreach ($rssubject as $index => $rs_parent){
							$subject_id = $rs_parent["subject_id"];
							$arr=array(
									'score_id'=>$id,
									'group_id'=>$_data['group'],
									'subject_id'=>$subject_id,
									'student_id'=>$_data['student_id'.$i],
									'score'=>$_data['student_id'.$i].'subjectid_'.$subject_id,
									'note'=>$_data['note_'.$i],
									'status'=>1,
									'user_id'=>$this->getUserId(),
									'is_parent'=> 1
							);
							$this->_name='rms_score_detail';
							$this->insert($arr);
						}
					}
					
// 						$arr=array(
// 								'score_id'=>$id,
// 								'group_id'=>$_data['group'],
// 								'student_id'=>$_data['student_id'.$i],
// 								'subject_id'=> 1,
// 								'total_score'=>$_data['total_score'.$i],
// 								'score'=>$_data['average'.$i],
// 								'note'=>$_data['note_'.$i],
// 								'status'=>1,
// 								'user_id'=>$this->getUserId(),
// 								'is_parent'=> 1
// 						);
// 					$this->_name='rms_score_detail';
// 					$this->insert($arr);
				}
			}
		$db->commit();
	}catch (Exception $e){
		$db->rollBack();
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
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
// 	function getStudyYears(){
// 		$db=$this->getAdapter();
// 		$sql="SELECT id,CONCAT(from_academic,'-',to_academic) AS name FROM rms_group WHERE `status`=1";
// 		$order=" ORDER BY id DESC";
// 		return $db->fetchAll($sql.$order);
// 	}
// 	function getGroupAll(){
// 		$db=$this->getAdapter();
// 		$sql="SELECT id,group_code AS `name` FROM rms_group WHERE `status`=1";
// 		$order=" ORDER BY id DESC";
// 		return $db->fetchAll($sql.$order);
// 	}
// 	function getAllScore($search=null){
// 		$db=$this->getAdapter();
// 		$sql="SELECT s.id,s.title_score,
// 			(SELECT name_en FROM `rms_view` WHERE TYPE=14 AND key_code =s.exam_type LIMIT 1) as exam_type,
// 			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
// 			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_id,
// 			(SELECT en_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) AS degree,
// 			(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1)AS grade,
// 			(SELECT CONCAT(name_en ,'-',name_kh ) FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session`) AS session_id,
// 			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
// 			s.status
// 			FROM rms_score AS s,rms_group AS g WHERE s.group_id=g.id AND s.status=1";
// 		//before add more =>AND g.degree IN(1,2) 
// 		$where ='';
// 		$from_date =(empty($search['start_date']))? '1': " s.date_input >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': " s.date_input <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
		
// 		if(!empty($search['title'])){
// 			$s_where = array();
// 			$s_search = addslashes(trim($search['title']));
// 			$s_where[]=" s.title_score LIKE '%{$s_search}%'";
// 			$s_where[]=" s.note LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
// 		}
// 		if($search['degree']>0){
// 			$where.= " AND g.degree =".$search['degree'];
// 		}
// 		if(!empty($search['study_year'])){
// 			$where.=" AND g.academic_year =".$search['study_year'];
// 		}
// 		if(!empty($search['grade'])){
// 			$where.=" AND `g`.`grade` =".$search['grade'];
// 		}
// 		if(!empty($search['session'])){
// 			$where.=" AND `g`.`session` =".$search['session'];
// 		}
// 		if(!empty($search['room'])){
// 			$where.=" AND `g`.`room_id` =".$search['room'];
// 		}
// 		$order=" ORDER BY id DESC ";
// 		return $db->fetchAll($sql.$where.$order);
// 	}
}