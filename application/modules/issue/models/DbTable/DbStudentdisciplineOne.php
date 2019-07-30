<?php

class Issue_Model_DbTable_DbStudentdisciplineOne extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
//     function getAllDiscipline($search=null){
//     	$db=$this->getAdapter();
//     	$sql="SELECT sa.`id`,
//     	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS group_name,
//     	(SELECT (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS academy,
//     	(SELECT (SELECT en_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS degree,
//     	(SELECT (SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS grade,
//     	(SELECT g.semester FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS semester,
//     	(SELECT (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS room,
//     	(SELECT
//     	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS session,
//     	sa.`mistake_date`,
//     	sa.`status` FROM `rms_student_discipline` AS sa ";
//     	$where =' WHERE 1 ';
//     	$from_date =(empty($search['start_date']))? '1': " sa.date_create >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " sa.date_create <= '".$search['end_date']." 23:59:59'";
//     	$where.= " AND ".$from_date." AND ".$to_date;
    
//     	if(!empty($search['group_name'])){
//     		$where.= " AND sa.`group_id` =".$search['group_name'];
//     	}
//     	if(!empty($search['study_year'])){
//     		$where.=" AND (SELECT g.academic_year FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) =".$search['study_year'];
//     	}
//     	if(!empty($search['grade'])){
//     		$where.=" AND (SELECT g.grade FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['grade'];
//     	}
//     	if(!empty($search['session'])){
//     		$where.=" AND (SELECT  `g`.`session` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['session'];
//     	}
//    		if(!empty($search['room'])){
// 			$where.=" AND (select `g`.`room_id` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1 )=".$search['room'];
// 		}
//     	$order=" ORDER BY id DESC ";
// //     	echo $sql.$where.$order;exit();
//     	return $db->fetchAll($sql.$where.$order);
//     }
    
    function getAllDiscipline($search=null){
    	$db=$this->getAdapter();
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	$label="name_en";
    	if ($currentLang==1){
    		$colunmname='title';
    		$label="name_kh";
    	}
    	
    	$sql="SELECT 
    				sad.`id`,
			    	(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = sa.branch_id LIMIT 1) AS branch_name,
			    	(select stu_code from rms_student as s where s.stu_id = sad.stu_id limit 1) as stu_code,
			    	(select stu_khname from rms_student as s where s.stu_id = sad.stu_id limit 1) as stu_name,
			    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS group_name,
			    	(SELECT (SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS academy,
			    	(SELECT (SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS degree,
			    	(SELECT (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS grade,
			    	(SELECT (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS room,
			    	(SELECT
			    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS session,
	    			sa.`date_attendence`,
	    			(select $label from rms_view as v where v.type=1 and key_code = sa.status) as status
    			FROM 
    				`rms_student_attendence` AS sa,
    				rms_student_attendence_detail as sad
    			WHERE 
    				sa.id = sad.attendence_id
    				and sad.type = 2
    				and sa.type = 2	
    	";
    	
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " sa.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.= " AND sa.`branch_id` =".$search['branch_id'];
    	}
    	if(!empty($search['group_name'])){
    		$where.= " AND sa.`group_id` =".$search['group_name'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND (SELECT g.academic_year FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND (SELECT g.grade FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND (SELECT  `g`.`session` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1)=".$search['session'];
    	}
    	if(!empty($search['room'])){
    		$where.=" AND (select `g`.`room_id` FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1 )=".$search['room'];
    	}
    	$where.=$dbp->getAccessPermission('sa.`branch_id`');
    	$order=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addDisciplineOne($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		$branch = $_data['branch_id'];
		$group = $_data['group'];
		$date = $_data['discipline_date'];
		$for_semester = $_data['for_semester'];
		$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group and for_semester = $for_semester and date_attendence = '$date' and type=2 limit 1";
		$id = $db->fetchOne($sql);
		
		try{
			if(empty($id)){
				$_arr = array(
						'branch_id'		=>$_data['branch_id'],
						'group_id'		=>$_data['group'],
						'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
						'date_create'	=>date("Y-m-d"),
						'modify_date'	=>date("Y-m-d"),			
						'for_semester'	=>$_data['for_semester'],
						'note'			=>$_data['note'],
						'status'		=>1,
						'user_id'		=>$this->getUserId(),
						'type'			=>2, // mistake
				);
				$id=$this->insert($_arr);
			}
			
			$dbpush = new  Application_Model_DbTable_DbGlobal();
			$dbpush->getTokenUser($_data['stu_code'],null, 3);
			$arr = array(
					'attendence_id'	=>$id,
					'stu_id'		=>$_data['stu_code'],
					'attendence_status'=>$_data['mistake'],
					'description'	=>$_data['comment'],
					'type'			=>2, //from one student
			);
			$this->_name ='rms_student_attendence_detail';
			$this->insert($arr);
			
		  	$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   public function updateStudentAttendenceOne($_data,$id){
		//print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
					'modify_date'	=>date("Y-m-d"),
					'for_semester'	=> $_data['for_semester'],
					'note'			=>$_data['note'],
					//'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
					'type'			=>2, //for displince
			);
			$where="id=".$_data['att_id'];
			$db->getProfiler()->setEnabled(true);
			$this->update($_arr, $where);
			
			$this->_name ='rms_student_attendence_detail';
			if ($_data['status']==1){
				$arr = array(
						'attendence_id'	=>$_data['att_id'],
						'stu_id'		=>$_data['stu_code'],
						'attendence_status'=>$_data['mistake'],
						'description'	=>$_data['comment'],
				);
				$where = " id = $id ";
   				$this->update($arr, $where);
			}else{
				$where = " id = $id ";
				$this->delete($where);
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
	

	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS name FROM rms_tuitionfee WHERE `status`=1
		GROUP BY from_academic,to_academic,generation";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}

	function getGroupName($academic,$session){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM  rms_group WHERE  `session`=$session AND academic_year=$academic  ";
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
				  (SELECT CONCAT(`subject_titlekh`,'-',`subject_titleen`) FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_name,
				  (SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
				  sd.score ,
  				  sd.`is_parent`
				FROM
				  rms_score_detail AS sd 
				WHERE sd.score_id =$id ";
		return $db->fetchAll($sql);
	}

	function getAllGrade($degree){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,CONCAT(major_enname) As name FROM rms_major WHERE is_active=1 and dept_id=".$degree;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	
	function getStudent($year,$grade,$session){
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
	
	function getSubjectBygroup($group_id){
		$db=$this->getAdapter();
		$sql="SELECT gsd.`subject_id` as id,
		(SELECT CONCAT(sj.subject_titleen,' - ',sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsd.`subject_id` LIMIT 1) AS name
		FROM `rms_group_subject_detail` AS gsd
		WHERE gsd.`group_id`= $group_id";
		return $db->fetchAll($sql);
	}
	function getAttendencetByID($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					sad.*,
					sa.*
				FROM 
					`rms_student_attendence` sa ,
					rms_student_attendence_detail as sad 
				WHERE  
					sa.id = sad.attendence_id
					and sad.`id` = $id
					AND sa.type=2
					and sad.type=2 
			";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sa.branch_id');
		return $db->fetchRow($sql);
	}
	
	function getAttendeceStatus($attendence_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT sad.`attendence_status`,sad.`stu_id`,sad.`description`  FROM `rms_student_attendence_detail` AS sad WHERE sad.`attendence_id`=$attendence_id AND sad.`stu_id`=$stu_id";
		return $db->fetchRow($sql);
	}
	function getAllgroupStudy(){
		$db = $this->getAdapter();
		$sql ="SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name 
		FROM `rms_group` AS `g` WHERE g.status=1 and g.is_pass!=1";

		return $db->fetchAll($sql);
	}
	function updateDisiplinetoAtt(){
		$sql="SELECT id,branch_id,for_semester,note,group_id,mistake_date,date_create,
				modify_date,status,user_id FROM `rms_student_discipline`";
		$result = $this->getAdapter()->fetchAll($sql);
		foreach($result as $rs){
			$_arr = array(
					'branch_id'=>$rs['branch_id'],
					'group_id'=>$rs['group_id'],
					'date_attendence'=>date("Y-m-d",strtotime($rs['mistake_date'])),
					'date_create'=>$rs['date_create'],
					'modify_date'=>$rs['modify_date'],
					'for_semester'=> $rs['for_semester'],
					'note'=>$rs['note'],
					'status'=>$rs['status'],
					'user_id'=>$rs['user_id'],
					'type'=>2, //for displince
			);
			$this->_name ='rms_student_attendence';
			$id=$this->insert($_arr);
			
			$sql="SELECT * FROM `rms_student_discipline_detail` WHERE discipline_id = ".$rs['id'];
			$rows = $this->getAdapter()->fetchAll($sql);
			
			foreach($rows as $row){
				$arr1 = array(
						'attendence_id'=>$id,
						'stu_id'=>$row['stu_id'],
						'attendence_status'=>$row['mistake_type'],
						'description'=>$row['description'],
				);
				$this->_name ='rms_student_attendence_detail';
				$this->insert($arr1);
		   }
	  }
  }  
}

