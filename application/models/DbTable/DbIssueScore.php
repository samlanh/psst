<?php

class Application_Model_DbTable_DbIssueScore extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_score';
    	
	public static function getUserExternalId(){
		$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
		$userId = $sessionUserExternal->userId;
		return $userId;
	}
	function getAllScoreByUser($search=null){
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
		$sql="SELECT 
				s.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) As branchName
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as examTypeTitle
				,CASE
					WHEN s.exam_type = 2 THEN s.for_semester
				ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_score AS s,
					rms_group AS g 
			WHERE s.group_id=g.id  AND s.score_option=2 ";
		
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " s.date_input >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_input <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$where.=' AND s.teacherId='.$this->getUserExternalId();

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" s.title_score LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_namekh FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_nameen FROM `rms_branch` AS br WHERE br.br_id=s.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" s.note LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']>0){
			$where.= " AND g.degree =".$search['degree'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year =".$search['academic_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND `g`.`grade` =".$search['grade'];
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
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}

public function getScoreByID($id){
	   	$db = $this->getAdapter();
	   	$sql = "SELECT
				   	s.*
			   	FROM 
		   			rms_score AS s
		   		WHERE 
		   			s.id=".$id;
		$sql.=' AND s.teacherId='.$this->getUserExternalId();
	   	$sql.="  LIMIT 1 ";
	   	return $db->fetchRow($sql);
	}
	public function addStudentScore($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbGroup = new Application_Model_DbTable_DbExternal();
			$group_info = $dbGroup->getGroupDetailByID($_data['group']);
			$year_study = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			
			$_arr = array(
					'branch_id'			=>$_data['branch_id'],
					'title_score'		=>$_data['title'],
					'group_id'			=>$_data['group'],
			        'exam_type'			=>$_data['exam_type'],
					'date_input'		=>date("Y-m-d"),
					'note'				=>$_data['note'],
					
					'type_score'		=>1, // 1 => BacII score
					'for_academic_year'	=>$year_study,
					'for_semester'		=>$_data['for_semester'],
					'for_month'			=>$_data['for_month'],
					
					'teacherId'			=>$this->getUserExternalId(),
					'score_option'		=>2, //1 input normal,2 teacher input
					'createDate'		=>date("Y-m-d H:i:s"),
					'modifyDate'		=>date("Y-m-d H:i:s"),
			);
			$this->_name='rms_score';		
			$id=$this->insert($_arr);
			$dbpush = new Application_Model_DbTable_DbGlobal();
			$rs_groupscore = $dbpush->getSumCutScorebyGroup($_data['group']);
			$total_cutscore = $rs_groupscore['score_short'];
			
			//$dbpush->pushNotification(null,$_data['group'],2,4);
			
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
}