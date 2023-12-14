<?php

class Issue_Model_DbTable_DbStudentEvaluation extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_evaluation';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	public function addStudentEvaluation($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		
			$branch=$_data['branch_id'];
			$group=$_data['group'];
			$for_type=$_data['for_type'];
			$for_month=$_data['for_month'];
			$for_semester=$_data['for_semester'];
			
			$sql="select 
						id 
					from 
						rms_evaluation 
					where 
						branch_id = $branch
						and group_id = $group
						and for_type = $for_type
						and for_month = $for_month
						and for_semester = $for_semester
					limit 1	
				";
			$result = $db->fetchOne($sql);
			if(!empty($result)){
				return -1;
			}

		$_arr = array(
				'branch_id'		=>$_data['branch_id'],
				'group_id'		=>$_data['group'],
				'for_type'		=>$_data['for_type'],
				'for_month'		=>$_data['for_month'],
				'for_semester'	=>$_data['for_semester'],
				'issue_date'	=>$_data['issue_date'],
				'note'			=>$_data['note'],
				'status'		=>1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=>$this->getUserId(),
		);
		$this->_name='rms_evaluation';
		$idev=$this->insert($_arr);

		if(!empty($_data['identity'])){
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){

			$_arr = array(
					'evalueId'	=>$idev,
					'groupId'	=>$_data['group'],
					'branch_id'		=>$_data['branch_id'],
			        'student_id'	=>$_data['student_id'.$i],
					'teacher_comment'=>$_data['coment_'.$i],
					'status'		=>1,
					'create_date'	=>date("Y-m-d H:i:s"),
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'		=>$this->getUserId(),
			);
			
			$this->_name='rms_student_evaluation';
			$idevd=$this->insert($_arr);
			
			
			if(!empty($_data['identity_cmt'])){
				$idcm = explode(',', $_data['identity_cmt']);
				foreach ($idcm as $j){
					$arr=array(
							'evalueId'	=>$idev,
							'studentEvaluationId'=>$idevd,
							'student_id'	=>$_data['student_id'.$i],
							'commentId'	=>$_data['comment_id_'.$i.'_'.$j],
							'rating_id'		=>$_data['rating_id_'.$i.'_'.$j],
					);
					$this->_name='rms_student_evaluation_detail';
					$this->insert($arr);
				}
			}
		  }
		}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   public function updateStudentEvaluation($_data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'for_type'		=>$_data['for_type'],
					'for_month'		=>$_data['for_month'],
					'for_semester'	=>$_data['for_semester'],
					'issue_date'	=>$_data['issue_date'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'create_date'	=>date("Y-m-d H:i:s"),
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'		=>$this->getUserId(),
				);
			$this->_name='rms_evaluation';
			$where=" id = $id ";
			$this->update($_arr, $where);

			$this->_name='rms_student_evaluation_detail';
			$where = " evalueId = $id ";
			$this->delete($where);

			$this->_name='rms_student_evaluation';
			$where = " evalueId = $id ";
			$this->delete($where);
		
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
	
				$_arr = array(
						'evalueId'	=>$id,
						'groupId'	=>$_data['group'],
						'branch_id'		=>$_data['branch_id'],
						'student_id'	=>$_data['student_id'.$i],
						'teacher_comment'=>$_data['coment_'.$i],
						'status'		=>1,
						'create_date'	=>date("Y-m-d H:i:s"),
						'modify_date'	=>date("Y-m-d H:i:s"),
						'user_id'		=>$this->getUserId(),
				);
				$this->_name='rms_student_evaluation';
				$idevd=$this->insert($_arr);
				
				if(!empty($_data['identity_cmt'])){
					$idcm = explode(',', $_data['identity_cmt']);
					foreach ($idcm as $j){
						$arr=array(
								'evalueId'	=>$id,
								'studentEvaluationId'=>$idevd,
								'student_id'	=>$_data['student_id'.$i],
								'commentId'	=>$_data['comment_id_'.$i.'_'.$j],
								'rating_id'		=>$_data['rating_id_'.$i.'_'.$j],
						);
						$this->_name='rms_student_evaluation_detail';
						$this->insert($arr);
					}
				}
			  }
			}

			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }

   function getAllGroupStudentEvaluation($search=null){
	$db=$this->getAdapter();
	
	$dbp = new Application_Model_DbTable_DbGlobal();
	$currentLang = $dbp->currentlang();
	if ($currentLang==1){// khmer
		$title='title';
		$view="name_kh";
		$branch="branch_namekh";
		$userName="CONCAT(first_name,' ',last_name) as name";
	}else{
		$title='title_en';
		$view="name_en";
		$branch="branch_nameen";
		$userName="CONCAT(first_name,' ',last_name) as name";
	}
	
	$sql="SELECT e.id,
		(SELECT $branch FROM `rms_branch` WHERE br_id = e.branch_id LIMIT 1) AS branchName,
		(SELECT group_code FROM `rms_group` WHERE id = se.groupId LIMIT 1) AS groupName,
		(SELECT name_kh FROM `rms_view` WHERE TYPE=19 AND key_code = e.for_type LIMIT 1) AS forType,
		CASE 
			WHEN e.for_semester = 1 THEN 'ឆមាសទី១'
			WHEN e.for_semester = 2 THEN 'ឆមាសទី២'
		END AS semester, 
		(SELECT month_kh FROM `rms_month` WHERE rms_month.id = e.for_month  LIMIT 1) AS forMonth,
		se.teacher_comment, e.create_date,
		(SELECT $userName FROM `rms_users` WHERE id = e.user_id LIMIT 1) AS userName,
		 e.status
		";
		$sql.=$dbp->caseStatusShowImage("e.status");
		$sql.="FROM  `rms_evaluation` AS e 
				INNER JOIN `rms_student_evaluation` AS se 
				WHERE e.id = se.evalueId 
				";
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " e.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['group'])){
			$where.=" AND se.groupId =".$search['group'];
		}
		$where.=$dbp->getAccessPermission('e.branch_id');
		$order=" GROUP BY e.id ORDER BY e.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getEvaluationById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				se.*,
				(SELECT is_pass FROM rms_group AS g WHERE g.id = se.group_id) AS is_pass 
			FROM 
				rms_evaluation AS se
			WHERE 
				id = $id
			LIMIT 1 
			";
		return $db->fetchRow($sql);
	}
	function getStudentEvaluationById($id){
		$db=$this->getAdapter();
		$sql="SELECT *
			FROM 
			`rms_student_evaluation` 
			WHERE 
				evalueId = $id
			";
		return $db->fetchAll($sql);
	}
	function getStudentEvaluationDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				sed.*,
				(SELECT COMMENT FROM rms_comment WHERE rms_comment.id = sed.commentId) AS COMMENT
				FROM 
					rms_student_evaluation_detail AS sed
				WHERE 
				sed.evalueId= $id 
			";
		return $db->fetchAll($sql);
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
					sgh.itemType=1 
					AND s.stu_id = sgh.stu_id
					
					and sgh.is_pass = 0
					AND sgh.stop_type = 0
					and sgh.group_id = $group_id
			";
		$order=" ORDER BY s.stu_code ASC ";
		return $db->fetchAll($sql.$order);
	}
	
	function getCommentByDegree($data){
		$db=$this->getAdapter();
		$strRate='';
		if(!empty($data['studentId']) AND !empty($data['evalueId'])){
			$strRate="(SELECT ed.rating_id FROM `rms_student_evaluation_detail` AS ed WHERE c.id = ed.commentId AND ed.evalueId = ".$data['evalueId']." AND ed.student_id= ".$data['studentId']."  LIMIT 1) AS ratingId";
		}
		$sql="SELECT
					dc.comment_id as id,
					c.comment ";
			if(!empty($strRate)){
				$sql.=','.$strRate;
			}
		$sql.=" FROM
					rms_degree_comment as dc,
					rms_comment as c
				WHERE
					dc.comment_id = c.id
			";
		if(!empty($data['degree'])){
           $sql.=" and dc.degree_id =".$data['degree'];
		}

		return $db->fetchAll($sql);
	}
	
}


