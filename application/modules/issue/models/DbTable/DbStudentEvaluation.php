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
		///////////////////////////// check before submit //////////////////////////////////	
			$branch=$_data['branch_id'];
			$group=$_data['group'];
			$student=$_data['student'];
			$for_type=$_data['for_type'];
			$for_month=$_data['for_month'];
			$for_semester=$_data['for_semester'];
			
			$sql="select 
						id 
					from 
						rms_student_evaluation 
					where 
						branch_id = $branch
						and group_id = $group
						and student_id = $student
						and for_type = $for_type
						and for_month = $for_month
						and for_semester = $for_semester
					limit 1	
				";
			$result = $db->fetchOne($sql);
			if(!empty($result)){
				return -1;
			}
			
		////////////////////////////////////////////////////////////////////////////////////////	
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
					'status'		=>1,
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
   public function updateStudentScore($_data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{			
			$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
			        'student_id'	=>$_data['student'],
					'for_type'		=>$_data['for_type'],
					'for_month'		=>$_data['for_month'],
					'for_semester'	=>$_data['for_semester'],
					'issue_date'	=>$_data['issue_date'],
					'return_date'	=>$_data['return_date'],
					'teacher_comment'=>$_data['teacher_comment'],
					'feedback'		=>$_data['feedback'],
					'note'			=>$_data['note'],
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'		=>$this->getUserId(),
					'status'		=>$_data['status']
				);
			$where=" id = $id ";
			$this->update($_arr, $where);
			
			$this->_name='rms_student_evaluation_detail';
			$where = " evaluation_id = $id ";
			$this->delete($where);
		
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
	
	function getAllStudentEvaluation($search=null){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		if ($currentLang==1){// khmer
			$title='title';
			$view="name_kh";
			$branch="school_namekh";
			$student="stu_khname as name";
		}else{
			$title='title_en';
			$view="name_en";
			$branch="school_nameen";
			$student="CONCAT(last_name,'',stu_enname) as name";
		}
		
		$sql="SELECT se.id,
					(SELECT $branch FROM `rms_branch` WHERE br_id = se.branch_id LIMIT 1) As branch_name,
					(SELECT $view FROM `rms_view` WHERE TYPE=19 AND key_code = se.for_type LIMIT 1) as for_type,
					se.for_semester,
					(SELECT month_kh FROM `rms_month` WHERE rms_month.id = se.for_month  LIMIT 1) as for_month,
					s.stu_code,
					$student,
					group_code AS  group_id,
					(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_id,
					(SELECT rms_items.$title FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$title FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT CONCAT(name_en,'-',name_kh) FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`
				
			";
		$sql.=$dbp->caseStatusShowImage("se.status");
		$sql.=" FROM 
					rms_student_evaluation AS se,
					rms_group AS g,
					rms_student as s
				WHERE 
					se.group_id=g.id 
					and s.stu_id = se.student_id ";
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " se.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " se.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[]=" s.stu_code LIKE '%{$s_search}%'";
			$s_where[]=" s.last_name LIKE '%{$s_search}%'";
			$s_where[]=" s.stu_khname LIKE '%{$s_search}%'";
			$s_where[]=" s.stu_enname LIKE '%{$s_search}%'";
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
			$where.=" AND `se`.`group_id` =".$search['group'];
		}
		$where.=$dbp->getAccessPermission('se.branch_id');
		$order=" ORDER BY se.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getStudentEvaluationById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					se.*,
					(select is_pass from rms_group as g where g.id = se.group_id) as is_pass 
				from 
					rms_student_evaluation as se
				where 
					id = $id 
				limit 1 
			";
		return $db->fetchRow($sql);
	}
	function getStudentEvaluationDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					sed.*,
					(select comment from rms_comment where rms_comment.id = sed.comment_id) as comment
				from 
					rms_student_evaluation_detail as sed
				where 
					sed.evaluation_id = $id 
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


