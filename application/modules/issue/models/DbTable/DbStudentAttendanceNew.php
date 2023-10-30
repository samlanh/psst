<?php

class Issue_Model_DbTable_DbStudentAttendanceNew extends Zend_Db_Table_Abstract
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
			
			$dateObj = new DateTime($date);
			$attendenceDate =  $dateObj->format("Y-m-d");
		
			$for_semester = $_data['for_semester'];
			$session = $_data['session_type'];
			$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group and for_semester = $for_semester and for_session = $session and date_attendence = '$date' and type=1 limit 1";
			$id = $db->fetchOne($sql);
			$checking = $id;
			
			if(empty($id)){
				$_arr = array(
					'branch_id'			=>$_data['branch_id'],
					'group_id'			=>$_data['group'],
					'date_attendence'	=>$attendenceDate,
					
					'for_semester'		=> $_data['for_semester'],
					'note'				=>$_data['note'],
					'status'			=>1,
					'date_create'		=>date("Y-m-d H:i:s"),
					'modify_date'		=>date("Y-m-d H:i:s"),
					'user_id'			=>$this->getUserId(),
					'for_session'		=>$_data['session_type'],
					'type'				=>1, //for attendence
				);
				$id=$this->insert($_arr);
			}
			
			if(!empty($checking)){
				$this->_name='rms_student_attendence_detail';
				$this->delete("attendence_id=".$id);
			}
			
			$_data['attendenceDate'] = $date;
			$scheduleTime =$this->getScheduleTimeStudty($_data);
			$dbpush = new Application_Model_DbTable_DbGlobal();
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					
					if(!empty($_data['permissionRecordId'.$i])){
						$_arr = array(
							'isCompleted'		=>1,
						);
						$this->_name ='rms_student_attendence_detail';
						$where="id=".$_data['permissionRecordId'.$i];
						$this->update($_arr, $where);
					}
					
					if(!empty($scheduleTime)) {
						foreach($scheduleTime as $keyTime => $rowTime){
							$indexKeyTime = $keyTime+1;
							if($_data['attendenceStatus'.$i.'_'.$indexKeyTime]!=1){
								$arr = array(
									'attendence_id'		=>$id,
									'stu_id'			=>$_data['student_id'.$i],
									'attendence_status'	=>$_data['attendenceStatus'.$i.'_'.$indexKeyTime],
									'description'		=>$_data['comment'.$i.'_'.$indexKeyTime],
									'subjectId'			=>$_data['subjectId'.$i.'_'.$indexKeyTime],
									'fromHour'			=>$_data['fromHour'.$i.'_'.$indexKeyTime],
									'toHour'			=>$_data['toHour'.$i.'_'.$indexKeyTime],
								);
								$this->_name ='rms_student_attendence_detail';
								$this->insert($arr);
							}
						}
					}else{
						if($_data['attendenceStatus'.$i]!=1){
							$arr = array(
								'attendence_id'		=>$id,
								'stu_id'			=>$_data['student_id'.$i],
								'attendence_status'	=>$_data['attendenceStatus'.$i],
								'description'		=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_attendence_detail';
							$this->insert($arr);
						}
						
					}
				}
			}
		  $db->commit();
		  return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   public function updateStudentAttendence($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$date = $_data['attendence_date'];
			$dateObj = new DateTime($date);
			$attendenceDate =  $dateObj->format("Y-m-d");
			
			$status = empty($_data['status'])?0:1;
			$_arr = array(
					'branch_id'			=>$_data['branch_id'],
					'group_id'			=>$_data['group'],
					'date_attendence'	=>$attendenceDate,
					'modify_date'		=>date("Y-m-d H:i:s"),
					'status'			=>$status,
					'for_semester'		=>$_data['for_semester'],
					'note'				=>$_data['note'],
					'user_id'			=>$this->getUserId(),
					'for_session'		=>$_data['session_type'],
					'type'				=>1, //for attendence
			);
			$id = $_data['id'];
			$where="id=".$id;
			$this->update($_arr, $where);
			
			$this->_name='rms_student_attendence_detail';
			$this->delete("attendence_id=".$_data['id']);
		  
			$_data['attendenceDate'] = $date;
			$scheduleTime =$this->getScheduleTimeStudty($_data);
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					if(!empty($scheduleTime)) {
						foreach($scheduleTime as $keyTime => $rowTime){
							$indexKeyTime = $keyTime+1;
							if($_data['attendenceStatus'.$i.'_'.$indexKeyTime] !=1){
								$arr = array(
									'attendence_id'		=>$id,
									'stu_id'			=>$_data['student_id'.$i],
									'attendence_status'	=>$_data['attendenceStatus'.$i.'_'.$indexKeyTime],
									'description'		=>$_data['comment'.$i.'_'.$indexKeyTime],
									'subjectId'			=>$_data['subjectId'.$i.'_'.$indexKeyTime],
									'fromHour'			=>$_data['fromHour'.$i.'_'.$indexKeyTime],
									'toHour'			=>$_data['toHour'.$i.'_'.$indexKeyTime],
								);
								$this->_name ='rms_student_attendence_detail';
								$this->insert($arr);
							}
						}
					}else{
						if($_data['attendenceStatus'.$i]!=1){
							$arr = array(
								'attendence_id'		=>$id,
								'stu_id'			=>$_data['student_id'.$i],
								'attendence_status'	=>$_data['attendenceStatus'.$i],
								'description'		=>$_data['comment'.$i],
							);
							$this->_name ='rms_student_attendence_detail';
							$this->insert($arr);
						}
						
					}
				}
			}
		  $db->commit();
		  return $id;
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
		$strCmt='';
		if(!empty($data['evalueId'])){
			$strCmt="(SELECT ed.teacher_comment FROM `rms_student_evaluation` AS ed WHERE s.stu_id = ed.student_id AND ed.evalueId = ".$data['evalueId']." AND ed.groupId= ".$group_id." LIMIT 1) AS teacherCmt";
		}
		if(!empty($data['checkescan'])){
			$sql="SELECT
					sgh.`stu_id`,
					s.stu_code AS stu_code,
					s.stu_khname AS stuNameKH,
					CONCAT(s.last_name,' ' ,s.stu_enname) AS stuNameLatin,
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
							AND sgh.`group_id`=".$group_id." AND s.stu_id=".$data['stuId'];
							
		
		}else{
			$sql="SELECT 
					sgh.`stu_id`,
					 s.stu_code AS stu_code,
					 s.stu_khname AS stuNameKH,
					 CONCAT(s.last_name,' ' ,s.stu_enname) AS stuNameLatin,
					CONCAT(s.stu_khname,' ',s.last_name,' ' ,s.stu_enname) AS stu_name,
					s.sex AS sex,
					(SELECT name_kh from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS gender";
					if(!empty($strCmt)){
						$sql.=','.$strCmt;
					}
		$sql.="	 FROM 
				 	`rms_group_detail_student` AS sgh,
				 	rms_student as s
				WHERE 
					sgh.itemType=1 
					AND sgh.`stu_id`=s.stu_id
					AND s.status=1
					AND sgh.status = 1
					
					and sgh.stop_type=0
					AND sgh.`group_id`=".$group_id." AND s.stu_id=".$data['stuId'];
			
		}
		if(!empty($data['sortStundent'])){
			if($data['sortStundent']==1){
				$order=" ORDER BY s.stu_khname ASC";
			}elseif($data['sortStundent']==2){
				$order=" ORDER BY s.stu_code ASC";
			}elseif($data['sortStundent']==3){
				$order=" ORDER BY s.stu_khname ASC";
			}elseif($data['sortStundent']==4){
				$order=" ORDER BY s.last_name ASC";
			}
		}else{
			$order=" ORDER BY s.last_name ASC";
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
	
	function getStudentByGroupHTML($data=array()){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$textareastyle=" font-family: 'Khmer OS Battambang' ";
		
		$groupId = empty($data['group']) ? 0 : $data['group'];
		date("Y-m-d",strtotime($data['attendenceDate']));
		$scheduleTime =$this->getScheduleTimeStudty($data);
		
		$index = empty($data['keyrow'])?0:$data['keyrow'];
		$strHeadRow = "";
		$strStudent = "";
		$identity="";
		
		$strHeadRow.='<tr class="head-td" align="center">';
			$strHeadRow.='<th scope="col" >'.$tr->translate("NUM").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("STUDENT_ID").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("STUDEN_NAME").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("GENDER").'</th>';
			if(!empty($scheduleTime)) {
				foreach($scheduleTime as $key => $rowTime){
					$strHeadRow.='<th scope="col" >';
					$strHeadRow.=$rowTime["subjectSortcut"].'</br>';
					$strHeadRow.=$rowTime["timeTitle"];
					$strHeadRow.='</th>';
				}
			}else{
				$strHeadRow.='<th scope="col" >'.$tr->translate("ATTENDANCE").'</th>';
			}
		$strHeadRow.='</tr>';
		
		
		if(!empty($data['attendanceId'])){
			$attendanceDateOld = "";
			$attendanceRow = $this->getAttendencetByID($data['attendanceId']);
			if(!empty($attendanceRow)){
				$attendanceRowDate = new DateTime($attendanceRow["date_attendence"]);
				$attendanceDateOld =  $attendanceRowDate->format("Y-m-d");
			}
			
			$date = new DateTime($data['attendenceDate']);
			$currentAttendenceDate =  $date->format("Y-m-d");
			if($attendanceDateOld==$currentAttendenceDate){
				$template = $this->getTemplateStudentAttendanceEdit($data);
				$strStudent = $template["rowStudentHTML"];
				$index = $template["keyrow"];
				$identity = $template["identity"];
			}else{
				$template = $this->getTemplateStudentAttendance($data);
				$strStudent = $template["rowStudentHTML"];
				$index = $template["keyrow"];
				$identity = $template["identity"];
			}
			
		}else{
			$template = $this->getTemplateStudentAttendance($data);
			$strStudent = $template["rowStudentHTML"];
			$index = $template["keyrow"];
			$identity = $template["identity"];
		}
		
		$arr = array(
   			'tableHeadHTML' => $strHeadRow,
   			'rowStudentHTML' => $strStudent,
   			'identity' => $identity,
   			'keyrow' =>$index,
   			'scheduleTime' =>$scheduleTime,
		);
		return $arr;
	
	}

	function getScheduleGroupHTML($data=array()){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$textareastyle=" font-family: 'Khmer OS Battambang' ";
		
		$groupId = empty($data['group']) ? 0 : $data['group'];
		date("Y-m-d",strtotime($data['attendenceDate']));
		$scheduleTime =$this->getScheduleTimeStudty($data);
		
		$index = empty($data['keyrow'])?0:$data['keyrow'];
		$strHeadRow = "";
		$identity="";
		
		$strHeadRow.='<tr class="head-td" align="center">';
			$strHeadRow.='<th scope="col" >'.$tr->translate("NUM").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("STUDENT_ID").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("STUDEN_NAME").'</th>';
			$strHeadRow.='<th scope="col" >'.$tr->translate("GENDER").'</th>';
			if(!empty($scheduleTime)) {
				foreach($scheduleTime as $key => $rowTime){
					$strHeadRow.='<th scope="col" >';
					$strHeadRow.=$rowTime["subjectSortcut"].'</br>';
					$strHeadRow.=$rowTime["timeTitle"];
					$strHeadRow.='</th>';
				}
			}else{
				$strHeadRow.='<th scope="col" >'.$tr->translate("ATTENDANCE").'</th>';
			}
		$strHeadRow.='</tr>';
		$arr = array(
   			'tableHeadHTML' => $strHeadRow,
   			'identity' => $identity,
   			'keyrow' =>$index,
   			'scheduleTime' =>$scheduleTime,
		);
		return $arr;
	}

	function getStudentRowHTML($data=array()){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$textareastyle=" font-family: 'Khmer OS Battambang' ";
		
		$groupId = empty($data['group']) ? 0 : $data['group'];
		date("Y-m-d",strtotime($data['attendenceDate']));
		$scheduleTime =$this->getScheduleTimeStudty($data);
		
		$index = empty($data['keyrow'])?0:$data['keyrow'];
		
		$strStudent = "";
		$identity="";

	
		$template = $this->getTemplateStudentAttendance($data);
		$strStudent = $template["rowStudentHTML"];
		$index = $template["keyrow"];
		$identity = $template["identity"];
		
		$arr = array(
   			'rowStudentHTML' => $strStudent,
   			'identity' => $identity,
   			'keyrow' =>$index,
   			'scheduleTime' =>$scheduleTime,
		);
		return $arr;
	
	}
	
	function getTemplateStudentAttendance($data){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$textareastyle=" font-family: 'Khmer OS Battambang' ";
		
		$attendanceType = array(
				array("id"=>1,"name"=>$tr->translate("PRESENT")),
				array("id"=>2,"name"=>$tr->translate("ABSENT")),
				array("id"=>3,"name"=>$tr->translate("PERMISSION")),
				array("id"=>4,"name"=>$tr->translate("LATE")),
				array("id"=>5,"name"=>$tr->translate("EARLY_LEAVE")),
		);
		
		
		$groupId = empty($data['group']) ? 0 : $data['group'];
		date("Y-m-d",strtotime($data['attendenceDate']));
		$row = $this->getStudentByGroup($groupId,$data);
		$scheduleTime =$this->getScheduleTimeStudty($data);
		
		$index = empty($data['keyrow'])?0:$data['keyrow'];
		
		$strStudent = "";
		$identity="";
		if(!empty($rowStudent)) {
			foreach($rowStudent as $key => $row){
				$index = $index+1;
				if (empty($identity)){
					$identity = $index;
				}else{ 
					$identity = $identity.",".$index;
				}
		
				$num = $key+1;
				$gender=$tr->translate("FEMALE");
				if($row["sex"]==1){
					$gender=$tr->translate("MALE");
				}
				
				$data["studentId"] = $row["stu_id"];
				$attendanceTypeValue=0;
				$permissionRecordId=0;
				$reason="";
				$checkStuP = $this->getCheckStudentPermission($data);
				if(!empty($checkStuP)){
					$attendanceTypeValue= $checkStuP["attendence_status"];
					$permissionRecordId= $checkStuP["id"];
					$reason= $checkStuP["description"];
				}
				
				$strStudent.='<tr class="rowData" >';
					$strStudent.='<td data-label="'.$tr->translate("NUM").'">'.$num.'</td>';
					$strStudent.='<td data-label="'.$tr->translate("STUDENT_ID").'">'.$row["stu_code"].'</td>';
					$strStudent.='<td data-label="'.$tr->translate("STUDEN_NAME").'">';
						$strStudent.=$row["stuNameKH"].'<br />';
						$strStudent.=$row["stuNameLatin"];
						$strStudent.='
						<input type="hidden" name="student_id'.$index.'" id="student_id'.$index.'"  value="'.$row["stu_id"].'" >
						<input type="hidden" name="permissionRecordId'.$index.'" id="permissionRecordId'.$index.'"  value="'.$permissionRecordId.'" >
						';
					$strStudent.='</td>';
					$strStudent.='<td data-label="'.$tr->translate("GENDER").'">'.$gender.'</td>';
					if(!empty($scheduleTime)) {
						foreach($scheduleTime as $keyTime => $rowTime){
							
							$indexKeyTime = $keyTime+1;
							$strStudent.='<td data-label="'.$rowTime["subjectSortcut"].' '.$rowTime["timeTitle"].'">';
								$strStudent.='
									<select dojoType="dijit.form.FilteringSelect" class="fullside" onChange="_changeSetNextTimeValue('.$index.','.$indexKeyTime.');" name="attendenceStatus'.$index.'_'.$indexKeyTime.'" placeHolder="'.$tr->translate("attendanceType").'" id="attendenceStatus'.$index.'_'.$indexKeyTime.'" autoComplete="false" queryExpr="*${0}*">
									';
									
									if (!empty($attendanceType)) foreach ($attendanceType as $attRow){
										$selected="";
										if($attendanceTypeValue==$attRow['id']){
											$selected = "selected";
										}
										$strStudent.='<option '.$selected.' value="'.$attRow['id'].'">'.$attRow['name'].'</option>';
									}
								$strStudent.='</select>
								</br>';
								$strStudent.='
								<input dojoType="dijit.form.Textarea"  class="fullside" onKeyup="_setNextTimeReason('.$index.','.$indexKeyTime.');" id="comment'.$index.'_'.$indexKeyTime.'"  name="comment'.$index.'_'.$indexKeyTime.'" value="'.$reason.'" type="text" style="'.$textareastyle.'">
								<input type="hidden" name="subjectId'.$index.'_'.$indexKeyTime.'" id="subjectId'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["subject_id"].'" >
								<input type="hidden" name="scheduleDetailId'.$index.'_'.$indexKeyTime.'" id="scheduleDetailId'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["id"].'" >
								<input type="hidden" name="fromHour'.$index.'_'.$indexKeyTime.'" id="fromHour'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["from_hour"].'" >
								<input type="hidden" name="toHour'.$index.'_'.$indexKeyTime.'" id="toHour'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["to_hour"].'" >
								';
							$strStudent.='</td>';
						}
					}else{
						$strStudent.='<td data-label="'.$tr->translate("ATTENDANCE").'">';
							$strStudent.='
									<select dojoType="dijit.form.FilteringSelect" class="fullside" name="attendenceStatus'.$index.'" placeHolder="'.$tr->translate("attendanceType").'" id="attendenceStatus'.$index.'" autoComplete="false" queryExpr="*${0}*">
									';
									if (!empty($attendanceType)) foreach ($attendanceType as $attRow){
										$selected="";
										if($attendanceTypeValue==$attRow['id']){
											$selected = "selected";
										}
										$strStudent.='<option '.$selected.' value="'.$attRow['id'].'">'.$attRow['name'].'</option>';
									}
								$strStudent.='</select>
								</br>';
								$strStudent.='
								<input dojoType="dijit.form.Textarea"  class="fullside" name="comment'.$index.'" type="text" value="'.$reason.'" style="'.$textareastyle.'">
								';
						$strStudent.='</td>';
					}
			
				$strStudent.='</tr>';
			}
		}
		$arr = array(
   			'rowStudentHTML' => $strStudent,
   			'identity' => $identity,
   			'keyrow' =>$index,
		);
		return $arr;
	}
	
	function getTemplateStudentAttendanceEdit($data){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$textareastyle=" font-family: 'Khmer OS Battambang' ";
		
		$attendanceType = array(
				array("id"=>1,"name"=>$tr->translate("PRESENT")),
				array("id"=>2,"name"=>$tr->translate("ABSENT")),
				array("id"=>3,"name"=>$tr->translate("PERMISSION")),
				array("id"=>4,"name"=>$tr->translate("LATE")),
				array("id"=>5,"name"=>$tr->translate("EARLY_LEAVE")),
		);
		
		
		$groupId = empty($data['group']) ? 0 : $data['group'];
		date("Y-m-d",strtotime($data['attendenceDate']));
		$rowStudent = $this->getStudentByGroup($groupId,$data);
		$scheduleTime =$this->getScheduleTimeStudty($data);
		
		$index = empty($data['keyrow'])?0:$data['keyrow'];
		
		$strStudent = "";
		$identity="";
		if(!empty($rowStudent)) {
			foreach($rowStudent as $key => $row){
				$index = $index+1;
				if (empty($identity)){
					$identity = $index;
				}else{ 
					$identity = $identity.",".$index;
				}
		
				$num = $key+1;
				$gender=$tr->translate("FEMALE");
				if($row["sex"]==1){
					$gender=$tr->translate("MALE");
				}
				
				$data["studentId"] = $row["stu_id"];
				
				$strStudent.='<tr class="rowData" >';
					$strStudent.='<td data-label="'.$tr->translate("NUM").'">'.$num.'</td>';
					$strStudent.='<td data-label="'.$tr->translate("STUDENT_ID").'">'.$row["stu_code"].'</td>';
					$strStudent.='<td data-label="'.$tr->translate("STUDEN_NAME").'">';
						$strStudent.=$row["stuNameKH"].'<br />';
						$strStudent.=$row["stuNameLatin"];
						$strStudent.='
							<input type="hidden" name="student_id'.$index.'" id="student_id'.$index.'"  value="'.$row["stu_id"].'" >
						';
					$strStudent.='</td>';
					$strStudent.='<td data-label="'.$tr->translate("GENDER").'">'.$gender.'</td>';
					if(!empty($scheduleTime)) {
						foreach($scheduleTime as $keyTime => $rowTime){
							
							$data["fromHour"] = $rowTime["from_hour"];
							$data["toHour"] = $rowTime["to_hour"];
							$data["subjectId"] = $rowTime["subject_id"];
							$attendanceTypeValue=0;
							$detailId=0;
							$reason="";
							$checkStuP = $this->getCheckAttendaceDetailForEditAction($data);
							if(!empty($checkStuP)){
								$attendanceTypeValue= $checkStuP["attendence_status"];
								$reason= $checkStuP["description"];
								$detailId= $checkStuP["id"];
							}
							
							$indexKeyTime = $keyTime+1;
							$strStudent.='<td data-label="'.$rowTime["subjectSortcut"].' '.$rowTime["timeTitle"].'">';
								$strStudent.='
									<select dojoType="dijit.form.FilteringSelect" class="fullside" onChange="_changeSetNextTimeValue('.$index.','.$indexKeyTime.');" name="attendenceStatus'.$index.'_'.$indexKeyTime.'" placeHolder="'.$tr->translate("attendanceType").'" id="attendenceStatus'.$index.'_'.$indexKeyTime.'" autoComplete="false" queryExpr="*${0}*">
									';
									
									if (!empty($attendanceType)) foreach ($attendanceType as $attRow){
										$selected="";
										if($attendanceTypeValue==$attRow['id']){
											$selected = "selected";
										}
										$strStudent.='<option '.$selected.' value="'.$attRow['id'].'">'.$attRow['name'].'</option>';
									}
								$strStudent.='</select>
								</br>';
								$strStudent.='
								<input dojoType="dijit.form.Textarea"  class="fullside" onKeyup="_setNextTimeReason('.$index.','.$indexKeyTime.');" id="comment'.$index.'_'.$indexKeyTime.'" name="comment'.$index.'_'.$indexKeyTime.'" value="'.$reason.'" type="text" style="'.$textareastyle.'">
								<input type="hidden" name="subjectId'.$index.'_'.$indexKeyTime.'" id="subjectId'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["subject_id"].'" >
								
								<input type="hidden" name="detailId'.$index.'_'.$indexKeyTime.'" id="detailId'.$index.'_'.$indexKeyTime.'"  value="'.$detailId.'" >
								<input type="hidden" name="fromHour'.$index.'_'.$indexKeyTime.'" id="fromHour'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["from_hour"].'" >
								<input type="hidden" name="toHour'.$index.'_'.$indexKeyTime.'" id="toHour'.$index.'_'.$indexKeyTime.'"  value="'.$rowTime["to_hour"].'" >
								';
							$strStudent.='</td>';
						}
					}else{
						$attendanceTypeValue=0;
						$detailId=0;
						$reason="";
						
						$data["noSetSchdeule"] ="1";
						$checkStuP = $this->getCheckAttendaceDetailForEditAction($data);
						if(!empty($checkStuP)){
							$attendanceTypeValue= $checkStuP["attendence_status"];
							$reason= $checkStuP["description"];
							$detailId= $checkStuP["id"];
						}
						$strStudent.='<td data-label="'.$tr->translate("ATTENDANCE").'">';
							$strStudent.='
									<select dojoType="dijit.form.FilteringSelect" class="fullside" name="attendenceStatus'.$index.'" placeHolder="'.$tr->translate("attendanceType").'" id="attendenceStatus'.$index.'" autoComplete="false" queryExpr="*${0}*">
									';
									if (!empty($attendanceType)) foreach ($attendanceType as $attRow){
										$selected="";
										if($attendanceTypeValue==$attRow['id']){
											$selected = "selected";
										}
										$strStudent.='<option '.$selected.' value="'.$attRow['id'].'">'.$attRow['name'].'</option>';
									}
								$strStudent.='</select>
								</br>';
								$strStudent.='
								<input type="hidden" name="detailId'.$index.'" id="detailId'.$index.'"  value="'.$detailId.'" >
								<input dojoType="dijit.form.Textarea"  class="fullside" name="comment'.$index.'" type="text" value="'.$reason.'" style="'.$textareastyle.'">
								';
						$strStudent.='</td>';
					}
			
				$strStudent.='</tr>';
			}
		}
		$arr = array(
   			'rowStudentHTML' => $strStudent,
   			'identity' => $identity,
   			'keyrow' =>$index,
		);
		return $arr;
	}
	
	function getCheckAttendaceDetailForEditAction($data){
		$db = $this->getAdapter();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$attendanceId = empty($data["attendanceId"]) ? 0 : $data["attendanceId"];
		$subjectId = empty($data["subjectId"]) ? 0 : $data["subjectId"];
		$fromHour = empty($data["fromHour"]) ? 0 : $data["fromHour"];
		$toHour = empty($data["toHour"]) ? 0 : $data["toHour"];
		$sql = "
			SELECT 
				attd.*
		";
		$sql.= "
			FROM `rms_student_attendence_detail` AS attd 
			WHERE attd.type=1 
				AND attd.attendence_id=$attendanceId 
				AND attd.stu_id =$studentId
				
		";
		if(!empty($data["noSetSchdeule"])){
		}else{
			$sql.= " 
				AND attd.subjectId =$subjectId
				AND attd.fromHour =$fromHour
				AND attd.toHour =$toHour 
			";
		}
    	$sql.= " LIMIT 1 ";
    	return $db->fetchRow($sql);
	}
	
	function getScheduleTimeStudty($data){
		$db = $this->getAdapter();
		
		$date = new DateTime($data['attendenceDate']);
		$dayValue =  $date->format("w");
		if($dayValue==0){
			$dayValue = 7;
		}
		$groupId = empty($data['group']) ? 0 : $data['group'];

		$sql = "
			SELECT 
				subj.subject_titleen,
				subj.subject_titlekh,
				subj.shortcut AS subjectSortcut,
				gSchD.id AS id,
				CONCAT(COALESCE(subj.shortcut,''),' ',COALESCE(frTime.title,''),'-',COALESCE(toTime.title,'')) AS `name`,
				CONCAT(COALESCE(frTime.title,''),'-',COALESCE(toTime.title,'')) AS `timeTitle`,
				gSchD.*
		";
		$sql.= "
		FROM 
			`rms_group_reschedule` AS gSchD 
			JOIN `rms_group_schedule` AS gSch ON gSchD.main_schedule_id = gSch.id 
			LEFT JOIN `rms_subject` AS subj ON subj.id = gSchD.subject_id 
			LEFT JOIN `rms_timeseting` AS frTime ON frTime.value = gSchD.from_hour
			LEFT JOIN `rms_timeseting` AS toTime ON toTime.value = gSchD.to_hour
		";
		$sql.= "
			WHERE 1 
				AND gSch.group_id = $groupId 
				AND gSchD.day_id = $dayValue
				AND subj.type_subject = 1
		";
		$sql.= "
			GROUP BY 
				gSchD.day_id,
				gSchD.from_hour,
				gSchD.to_hour
		";
    	return $db->fetchAll($sql);
	}
	
	function getCheckStudentPermission($data){
		$db = $this->getAdapter();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$sql = "
			SELECT 
				attD.*
		";
		$sql.= "
			FROM `rms_student_attendence_detail` AS attD
			WHERE attD.type=2
		";
		
		$date = new DateTime($data['attendenceDate']);
		$attendenceDate =  $date->format("Y-m-d");
									
    	$sql.= " AND attD.stu_id = ".$studentId;
    	$sql.= " AND attD.attendanceDate = '".$attendenceDate."' ";
    	$sql.= " LIMIT 1 ";
    	return $db->fetchRow($sql);
	}
}