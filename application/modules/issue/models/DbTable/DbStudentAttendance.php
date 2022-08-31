<?php

class Issue_Model_DbTable_DbStudentAttendance extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllAttendence($search=null){
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	$label="name_en";
    	$branch = "branch_nameen";
    	if ($currentLang==1){
    		$colunmname='title';
    		$label="name_kh";
    		$branch = "branch_namekh";
    	}
    	$sql="SELECT sa.`id`,
			    	(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = sa.branch_id LIMIT 1) AS branch_name,
			    	g.group_code AS group_name,
			    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id,
			    	(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1)  AS degree,
			    	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 ) AS grade,
			    	(SELECT g.semester FROM `rms_group` AS g WHERE g.id = sa.`group_id` LIMIT 1) AS semester,
			    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS room,
			    	(SELECT`rms_view`.$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS session,
			    	sa.`date_attendence`,
		    		(SELECT first_name FROM rms_users WHERE sa.user_id=rms_users.id LIMIT 1 ) AS user_name ";
    	$sql.=$dbp->caseStatusShowImage("sa.`status`");
    	$sql.=" FROM `rms_student_attendence` AS sa,rms_group as g ";
    	$where =' WHERE g.id=sa.group_id ANd sa.`type` = 1 ';
    	$from_date =(empty($search['start_date']))? '1': " sa.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['branch_id'])){
    		$where.= " AND sa.`branch_id` =".$search['branch_id'];
    	}
    	if(!empty($search['group'])){
    		$where.= " AND sa.`group_id` =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND  g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND g.grade =".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session` =".$search['session'];
    	}
   		if(!empty($search['room'])){
			$where.=" AND `g`.`room_id` =".$search['room'];
		}
    	$order=" ORDER BY id DESC ";
    	$where.=$dbp->getAccessPermission('sa.branch_id');
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addStudentAttendece($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch = $_data['branch_id'];
			$group = $_data['group'];
			$date = $_data['attendence_date'];
			$for_semester = $_data['for_semester'];
			$session = $_data['session_type'];
			$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group and for_semester = $for_semester and for_session = $session and date_attendence = '$date' and type=1 limit 1";
			$id = $db->fetchOne($sql);
			
			if(empty($id)){
				$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['attendence_date'])),
					'date_create'	=>date("Y-m-d"),
					'modify_date'	=>date("Y-m-d"),
					'subject_id'	=>$_data['subject'],
					'for_semester'	=> $_data['for_semester'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'user_id'		=>$this->getUserId(),
					'for_session'	=>$_data['session_type'],
					'type'			=>1, //for attendence
				);
				$id=$this->insert($_arr);
				
// 				$dbpush = new Application_Model_DbTable_DbGlobal();
// 				$dbpush->pushNotification(null,$_data['group'],2,2);
				
			}
			$stu_come='';$stu_absent='';
			$dbpush = new Application_Model_DbTable_DbGlobal();
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if ($_data['attedence'.$i]!=1){//ក្រៅពីមក sent all
						
						if(empty($stu_absent)){
							$stu_absent=$_data['student_id'.$i];
						}else{
							$stu_absent=$stu_absent.','.$_data['student_id'.$i];
						}
						
						$arr = array(
							'attendence_id'	=>$id,
							'stu_id'		=>$_data['student_id'.$i],
							'attendence_status'=>$_data['attedence'.$i],
							'description'	=>$_data['comment'.$i],
						);
						$this->_name ='rms_student_attendence_detail';
						$this->insert($arr);
					}
					else{
						if(empty($stu_come)){
							$stu_come=$_data['student_id'.$i];
						}else{
							$stu_come=$stu_come.','.$_data['student_id'.$i];
						}
						
					}
				}
				
				$dbpush->pushNotification($stu_absent,null,3,2);//absent
				$dbpush->pushNotification($stu_come,null,3,2);//come
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			$db->rollBack();
		}
   }
   public function updateStudentAttendence($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'=>$_data['branch_id'],
					'group_id'=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['attendence_date'])),
					'modify_date'=>date("Y-m-d"),
					'subject_id'=> $_data['subject'],
					'status'=>$_data['status'],
					'for_semester'=> $_data['for_semester'],
					'note'=>$_data['note'],
					'user_id'=>$this->getUserId(),
					'for_session'=>$_data['session_type'],
					'type'=>1, //for attendence
			);
			$where="id=".$_data['id'];
			$db->getProfiler()->setEnabled(true);
			$id=$this->update($_arr, $where);
			
		   $this->_name='rms_student_attendence_detail';
		   $this->delete("attendence_id=".$_data['id']);
		  
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if ($_data['attedence'.$i]!=1){
						$arr = array(
							'attendence_id'=>$_data['id'],
							'stu_id'=>$_data['student_id'.$i],
							'attendence_status'=>$_data['attedence'.$i],
							'description'=>$_data['comment'.$i],
						);
						$this->_name ='rms_student_attendence_detail';
						$this->insert($arr);
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
	
	function getStudent($year,$grade,$session){
		$db=$this->getAdapter();
		$sql="SELECT stu_id,stu_code,CONCAT(stu_enname,' - ',stu_khname) AS stu_name,sex
	    	FROM rms_student AS s 
		WHERE 
			academic_year = $year 
			and grade=$grade 
			and session=$session";
		$order=" ORDER BY stu_code DESC";
		return $db->fetchAll($sql.$order);
	}
	function getStudentByGroup($group_id,$data=null){
		$db=$this->getAdapter();
		if(!empty($data['checkescan'])){
			$sql="SELECT
					sgh.`stu_id`,
					s.stu_code AS stu_code,
					CONCAT(s.stu_khname,' ',s.last_name,' ' ,s.stu_enname) AS stu_name,
					s.sex AS sex,
					(SELECT name_kh from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS gender,
					(SELECT id FROM `rms_scan_transaction` st WHERE st.stu_id=s.stu_id  AND st.group_id=$group_id AND st.scan_type=1 AND st.is_converted=0 LIMIT 1) AS isCome,
					(SELECT create_date FROM `rms_scan_transaction` st WHERE st.stu_id=s.stu_id  AND st.group_id=$group_id AND st.scan_type=1 AND st.is_converted=0 LIMIT 1) AS scanDate,
					(SELECT id FROM `rms_scan_transaction` st WHERE st.stu_id=s.stu_id  AND st.group_id=$group_id AND st.scan_type=1 AND st.is_converted=0 LIMIT 1) AS transcan_id
					
						FROM
							`rms_group_detail_student` AS sgh,
							rms_student as s
						WHERE
							sgh.itemType=1 
							AND sgh.`stu_id`=s.stu_id
							AND s.status=1
							AND sgh.status = 1
							AND sgh.stop_type=0
							AND sgh.`group_id`=".$group_id;
			$order=" ORDER BY s.stu_khname ASC";
		}else{
			$sql="SELECT 
					sgh.`stu_id`,
					 s.stu_code AS stu_code,
					CONCAT(s.stu_khname,' ',s.last_name,' ' ,s.stu_enname) AS stu_name,
					s.sex AS sex,
					(SELECT name_kh from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS gender
				 FROM 
				 	`rms_group_detail_student` AS sgh,
				 	rms_student as s
				WHERE 
					sgh.itemType=1 
					AND sgh.`stu_id`=s.stu_id
					AND s.status=1
					AND sgh.status = 1
					
					and sgh.stop_type=0
					AND sgh.`group_id`=".$group_id;
			$order=" ORDER BY s.stu_khname ASC";
			
		}
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
		$sql="SELECT * FROM `rms_student_attendence` sa WHERE  sa.`id`=".$id." AND sa.type=1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sa.branch_id');
		return $db->fetchRow($sql);
	}
	function getDisciplineStatus($discipline_id,$stu_id){
		$db = $this->getAdapter();
		$sql="SELECT sdd.`attendence_status`,sdd.`stu_id`,sdd.`description`  FROM `rms_student_attendence_detail` AS sdd WHERE sdd.`attendence_id`=$discipline_id AND sdd.`stu_id`=$stu_id";
		return $db->fetchRow($sql);
	}
	
	function getAttendeceStatus($att_id , $stu_id){
		$db = $this->getAdapter();
		$sql = "select 
					attendence_status,
					description 
				from 
					rms_student_attendence_detail as sad,
					rms_student_attendence as sa 
				where 
					sad.attendence_id = sa.id
					and sa.type = 1
					and sa.id = $att_id
					and sad.stu_id = $stu_id ";
		return $db->fetchRow($sql);
	}
}