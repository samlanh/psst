<?php

class Application_Model_DbTable_DbIssueAttendance extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_grading';
    	
	public static function getUserExternalId(){
		$dbExternal= new Application_Model_DbTable_DbExternal();
		$userId = $dbExternal->getUserExternalId();
		$userId = empty($userId) ? 0 :$userId;
		return $userId;
	}
	function getClassScheduleForIssueAttendance($search = array())
	{
		$db = $this->getAdapter();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();

		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		$branchName = "branch_nameen";
		$subjectTitle = "subject_titleen";

		if ($lang == 1) { // khmer
			$subjectTitle = "subject_titlekh";
			$branchName = "branch_namekh";
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}
		$day = 0;
			
		$date = new DateTime();
		$fullDate = $date->format('Y-m-d');
		$dayofweek = $date->format('w');
		$currentDay=$dayofweek;
		$nextDay=$dayofweek+1;
		if($dayofweek==0){
			$currentDay=7;
		}
		$day = $currentDay;
		
		$sql = "
		SELECT 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT sj." . $subjectTitle . " FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			,(SELECT v.$label FROM rms_view AS v WHERE v.key_code=schDetail.day_id AND v.type=18 LIMIT 1)AS daysTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			
			,(SELECT b." . $branchName . " FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
			,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,`g`.`branch_id` AS branchId
			,`g`.`id` AS groupId
			,`g`.`group_code` AS groupCode
			,`g`.`degree` AS degree_id
			,`g`.`grade` AS gradeId
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
			
			,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle	
			,schDetail.*
			,schDetail.id AS scheduleDetailId

			,(SELECT CONCAT(DATE_FORMAT(crp.start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(crp.end_date, '%d/%m/%Y')) FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS semesterPeriond
			,(SELECT crp.`forSemester` FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS forSemester
			,(SELECT 
				CASE 
					WHEN crp.`forSemester` = 1 THEN '".$tr->translate("SEMESTER1")."' 
					WHEN crp.`forSemester` = 2 THEN '".$tr->translate("SEMESTER2")."' 
				ELSE '' END
				FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS forSemesterTitle
			,COALESCE((SELECT att.id FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = attd.attendence_id AND g.id = att.group_id AND schDetail.subject_id = attd.subjectId AND schDetail.to_hour = attd.toHour  AND schDetail.from_hour = attd.fromHour  AND   att.date_attendence = DATE_FORMAT('$fullDate', '%Y/%m/%d') LIMIT 1),0) AS isIssuedAtt
		FROM 
			rms_group_reschedule AS schDetail
			,rms_group_schedule AS sch
			,rms_group AS g
		WHERE 
			sch.id =schDetail.main_schedule_id
			AND g.id =sch.group_id
			AND g.is_use =1
			AND g.is_pass =2
		";
		
		if (!empty($day)) {
			$sql .= " AND schDetail.day_id=" . $day;
		}
		if(!empty($data["alternativeTeacher"])){ //  គ្រូជំនួស
			//$row  = $this->getAlternativeTeacherInfo();
			//$sql .= " AND schDetail.subject_id =" . $row["teacherId"];
		}else{
			$sql .= " AND schDetail.techer_id=" . $this->getUserExternalId();
		}

		$sql .= " ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
		return $db->fetchAll($sql);
	}
	
	function getTeacherScheduleDetailById($data=array()){
		$db = $this->getAdapter();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$lang = $_db->currentlang();

		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		$branchName = "branch_nameen";
		$subjectTitle = "subject_titleen";

		if ($lang == 1) { // khmer
			$subjectTitle = "subject_titlekh";
			$branchName = "branch_namekh";
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}
		$date = new DateTime();
		$fullDate = $date->format('Y-m-d');
		$dayofweek = $date->format('w');
		$currentDay=$dayofweek;
		$nextDay=$dayofweek+1;
		if($dayofweek==0){
			$currentDay=7;
		}
		$day = $currentDay;
		
		$sql = "
		SELECT 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT sj." . $subjectTitle . " FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			,(SELECT v.$label FROM rms_view AS v WHERE v.key_code=schDetail.day_id AND v.type=18 LIMIT 1)AS daysTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			
			,(SELECT b." . $branchName . " FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
			,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,`g`.`branch_id` AS branchId
			,`g`.`id` AS groupId
			,`g`.`group_code` AS groupCode
			,`g`.`degree` AS degree_id
			,`g`.`grade` AS gradeId
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
			
			,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle	
			,schDetail.*
			,schDetail.id AS scheduleDetailId

			,(SELECT CONCAT(DATE_FORMAT(crp.start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(crp.end_date, '%d/%m/%Y')) FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS semesterPeriond
			,(SELECT crp.`forSemester` FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS forSemester
			,(SELECT 
				CASE 
					WHEN crp.`forSemester` = 1 THEN '".$tr->translate("SEMESTER1")."' 
					WHEN crp.`forSemester` = 2 THEN '".$tr->translate("SEMESTER2")."' 
				ELSE '' END
				FROM rms_startdate_enddate AS crp WHERE crp.status=1 AND crp.forDepartment=2 AND FIND_IN_SET(`g`.`degree`,crp.`degreeId`) 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d')  <= DATE_FORMAT(crp.end_date, '%Y/%m/%d') 
					AND DATE_FORMAT('$fullDate', '%Y/%m/%d') >= DATE_FORMAT(crp.start_date, '%Y/%m/%d') 
					ORDER BY crp.`id` ASC,crp.title ASC LIMIT 1) AS forSemesterTitle
		
		";
		if(!empty($data["attendanceId"])){
			$sql.=",COALESCE((SELECT att.id FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = attd.attendence_id AND g.id = att.group_id AND schDetail.subject_id = attd.subjectId AND schDetail.to_hour = attd.toHour  AND schDetail.from_hour = attd.fromHour  AND   att.date_attendence = DATE_FORMAT('$fullDate', '%Y/%m/%d') LIMIT 1),0) AS isIssuedAtt";
		}
		$sql.="
		FROM 
			rms_group_reschedule AS schDetail
			,rms_group_schedule AS sch
			,rms_group AS g
		WHERE 
			sch.id =schDetail.main_schedule_id
			AND g.id =sch.group_id
			AND g.is_use =1
			AND g.is_pass =2
		";
		
		if (!empty($day)) {
			$sql .= " AND schDetail.day_id=" . $day;
		}
		if(!empty($data["scheduleDetailId"])){
			$sql .= " AND schDetail.id=" . $data["scheduleDetailId"];
		}
		if(!empty($data["groupId"])){
			$sql .= " AND sch.group_id =" . $data["groupId"];
		}
		if(!empty($data["subjectId"])){
			$sql .= " AND schDetail.subject_id =" . $data["subjectId"];
		}
		if(!empty($data["alternativeTeacher"])){ //  គ្រូជំនួស
			//$row  = $this->getAlternativeTeacherInfo();
			//$sql .= " AND schDetail.subject_id =" . $row["teacherId"];
		}else{
			$sql .= " AND schDetail.techer_id=" . $this->getUserExternalId();
		}

		$sql .= " ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function checkAvailableTimeInput($data = array()){
		$data["degree"] = empty($data["degree"]) ? 0 : $data["degree"];
		$data["fromHourValue"] = empty($data["fromHourValue"]) ? 0 : $data["fromHourValue"];
		$data["toHourValue"] = empty($data["toHourValue"]) ? 0 : $data["toHourValue"];
		$value = 1;
		if($data["degree"]!="1" || $data["degree"]!="4"){
			$date = new DateTime();
			$hour = $date->format("H");
			$min = $date->format("i");

			$currentTimeValue = floatval($hour.".".$min);
			$delayValue = 0.2;
			$fromHourValue = floatval($data["fromHourValue"])-$delayValue;
			$toHourValue = floatval($data["toHourValue"])+$delayValue;
			if($currentTimeValue >= $fromHourValue && $currentTimeValue <= $toHourValue ){
				//available
				$value =1;
			}else if($currentTimeValue > $fromHourValue ){ 
				//expired
				$value =2;
			}else{
				//unavailable
				$value =0;
			}
			$value = 1;
		}
		return $value;
	}
	
	function checkClassTodayHasAttendance($data){
		$db=$this->getAdapter();
		$today = new DateTime($data['attendenceDate']);
		$attendenceDate =  $today->format("Y-m-d");
		$groupId = empty($data['groupId']) ? 0 : $data['groupId'];
		$sql="
			SELECT 
				att.id
			FROM `rms_student_attendence` AS att
			WHERE att.`date_attendence` = DATE_FORMAT('$attendenceDate', '%Y/%m/%d') 
				AND att.`status` = 1
				AND att.`group_id` = $groupId
		";
		return $db->fetchOne($sql);
	}
	function getStudentForIssueAttendance($data){
	   $dbExternal = new Application_Model_DbTable_DbExternal();
	   
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	  
	   $today = new DateTime();
	   $attendenceDate =  $today->format("Y-m-d");
	   
	   $keyIndex = $data['keyIndex'];
	   $data['forIssueAttendance'] = "1";
	   $data['attendenceDate'] = $attendenceDate;

	   if(empty($data["attendanceId"])){
			$thisAttendanceId = $this->checkClassTodayHasAttendance($data);
			$data['thisAttendanceId'] =$thisAttendanceId;
	   }

	   $identity="";
	   $arrClassCol = array(
			2=>"col-md-6 col-sm-6 col-xs-12"
   			,3=>"col-md-4 col-sm-4 col-xs-12"
   			,4=>"col-md-3 col-sm-3 col-xs-12"
   			,5=>"col-md-2 col-sm-2 col-xs-12"
   			,6=>"col-md-2 col-sm-2 col-xs-12"
	   		,1=>"col-md-12 col-sm-12 col-xs-1"
	   );
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px">ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
					$string.='<th scope="col" >អត្តលេខ<small class="lableEng" >Student Id</small></th>';
					$string.='<th scope="col"  style="width:150px;">ឈ្មោះសិស្ស<small class="lableEng" >Student Name</small></th>';
					$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
					
					
					$string.='<th scope="col">ប្រភេទអវត្តមាន<small class="lableEng" >Attendance Type</small></th>';
					$string.='<th scope="col">សម្គាល់<small class="lableEng" >Remark</small></th>';
					
					$string.='';
			$string.='</tr>';
		$string.='</thead>';

		$students = $dbExternal->getStudentByGroupExternal($data);
		if(!empty($students)) foreach($students AS $key => $stu){
			
			$key++;
			$keyIndex=$keyIndex+1;
			
			if (empty($identity)){
				$identity=$keyIndex;
			}else{
				$identity=$identity.",".$keyIndex;
			}
			
			$rowClasss="odd";
			if(($keyIndex%2)==0){
				$rowClasss= "regurlar";
			}
			$gender = $tr->translate('MALE');
			if($stu['sex']==2){
				$gender = $tr->translate('FEMALE');
			}
			
			$requestValueAtt = empty($stu['attendenceStatus']) ? "1" : $stu['attendenceStatus'];
			$reason = empty($stu['reason']) ? "" : $stu['reason'];
			$permissionRecordId = empty($stu['permissionRecordId']) ? "0" : $stu['permissionRecordId'];
			$detailIdAtt = empty($stu['detailIdAtt']) ? "0" : $stu['detailIdAtt'];
			
			$checked1="checked";
			$checked2="";
			$checked3="";
			$checked4="";
			$checked5="";
			if($requestValueAtt==2){
				$checked1="";
				$checked2="checked";
				$checked3="";
				$checked4="";
				$checked5="";
			}else if($requestValueAtt==3){
				$checked1="";
				$checked2="";
				$checked3="checked";
				$checked4="";
				$checked5="";
			}else if($requestValueAtt==4){
				$checked1="";
				$checked2="";
				$checked3="";
				$checked4="checked";
				$checked5="";
			}else if($requestValueAtt==5){
				$checked1="";
				$checked2="";
				$checked3="";
				$checked4="";
				$checked5="checked";
			}
			$string.='<tr class="rowData '.$rowClasss.'" id="row'.$keyIndex.'">';
				$string.='<td data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
				$string.='<td data-label="'.$tr->translate("STUDENT_id").'" >'.$stu['stuCode'].'</td>';
				$string.='<td data-label="'.$tr->translate("STUDENT_NAME").'"  align="left">';
					$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong>';
					$string.='<small class="lableEng text-dark">'.$stu['stuEnName'].'</small>';
					$string.='<input dojoType="dijit.form.TextBox" id="studentId'.$keyIndex.'" name="studentId'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
					$string.='<input dojoType="dijit.form.TextBox" id="permissionRecordId'.$keyIndex.'" name="permissionRecordId'.$keyIndex.'" value="'.$permissionRecordId.'" type="hidden" >';
					$string.='<input dojoType="dijit.form.TextBox" id="detailIdAtt'.$keyIndex.'" name="detailIdAtt'.$keyIndex.'" value="'.$detailIdAtt.'" type="hidden" >';
				$string.='</td>';
				
				$string.='<td data-label="'.$tr->translate("SEX").'" >'.$gender;
				$string.='</td>';
				
				
					
				$string.='<td data-label="ប្រភេទអវត្តមាន / Attendance Type" class="text-center">';
				$string.='
						<div class="btn-group" role="group" aria-label="radio toggle button group">
							  <input type="radio" class="btn-check" name="attendanceType'.$keyIndex.'" id="attendanceType'.$keyIndex.'1" '.$checked1.'  value="1" >
							  <label class="btn btn-outline-primary waves-effect" for="attendanceType'.$keyIndex.'1">'.$tr->translate("PRESENT").'</label>

							  <input type="radio" class="btn-check" name="attendanceType'.$keyIndex.'" id="attendanceType'.$keyIndex.'2" '.$checked2.' value="2">
							  <label class="btn btn-outline-primary waves-effect" for="attendanceType'.$keyIndex.'2">'.$tr->translate("ABSENT").'</label>

							  <input type="radio" class="btn-check" name="attendanceType'.$keyIndex.'" id="attendanceType'.$keyIndex.'3" '.$checked3.' value="3">
							  <label class="btn btn-outline-primary waves-effect" for="attendanceType'.$keyIndex.'3">'.$tr->translate("PERMISSION").'</label>
							  
							  <input type="radio" class="btn-check" name="attendanceType'.$keyIndex.'" id="attendanceType'.$keyIndex.'4" '.$checked4.' value="4">
							  <label class="btn btn-outline-primary waves-effect" for="attendanceType'.$keyIndex.'4">'.$tr->translate("LATE").'</label>
							  
							  <input type="radio" class="btn-check" name="attendanceType'.$keyIndex.'" id="attendanceType'.$keyIndex.'5" '.$checked5.' value="5">
							  <label class="btn btn-outline-primary waves-effect" for="attendanceType'.$keyIndex.'5">'.$tr->translate("EARLY_LEAVE").'</label>
						</div>';
				$string.='</td>';
				$string.='<td data-label="សម្គាល់/Remark"><input dojoType="dijit.form.TextBox" class="fullside" name="note_'.$keyIndex.'"  value="'.$reason.'" type="text" ></td>';
				$string.='';
			$string.='</tr>';
			
		}
		
		$string.='';
		$string.='</table>';
		
	   
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>$identity
		,'keyIndex'=>$keyIndex
		
	   );
	   
	   return $arrContent;
   }

   function checkingTodayAttendanceMain($data){
		$branchId = empty($data['branchId']) ? 0 : $data['branchId'];
		$groupId = empty($data['groupId']) ? 0 : $data['groupId'];
		$forSemester = empty($data['forSemester']) ? 0 : $data['forSemester'];
		
		$dateObj = new DateTime();
		if(!empty($data['attendenceDate'])){
			$dateObj = new DateTime($data['attendenceDate']);
		}
		$attendenceDate =  $dateObj->format("Y-m-d");
	
		$sql="SELECT 
				att.id 
			FROM rms_student_attendence AS att
			WHERE att.branch_id = $branchId 
				AND att.group_id = $groupId 
				AND att.for_semester = $forSemester 
				AND att.date_attendence = '$attendenceDate' 
				AND att.type=1 
			LIMIT 1";
		$db = $this->getAdapter();
		$id = $db->fetchOne($sql);
		return $id;
   }
   function submitAttendance($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{

			$date = new DateTime();
			$attendenceDate = $date->format('Y-m-d');
			
			$data['attendenceDate'] = $attendenceDate;
			$idAttendance = $this->checkingTodayAttendanceMain($data);
			if(empty($idAttendance)){
				$_arr = array(
					'branch_id'			=>$data['branchId'],
					'group_id'			=>$data['groupId'],
					'date_attendence'	=>$attendenceDate,
					
					'for_semester'		=> $data['forSemester'],
					'status'			=>1,
					'date_create'		=>date("Y-m-d H:i:s"),
					'modify_date'		=>date("Y-m-d H:i:s"),
					'type'				=>1, //for attendence
				);
				$this->_name ='rms_student_attendence';
				$idAttendance = $this->insert($_arr);
			}
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				if(!empty($ids)) foreach ($ids as $i){

					$studentRequestId=0;
					if(!empty($data['permissionRecordId'.$i])){
						$_arr = array(
							'isCompleted'		=>1,
						);
						$this->_name ='rms_student_attendence_detail';
						$where="id=".$data['permissionRecordId'.$i];
						$this->update($_arr, $where);
						
						$studentRequestId=$data['permissionRecordId'.$i];
					}

					if($data['attendanceType'.$i]!=1){
						$arr = array(
							'attendence_id'		=>$idAttendance,
							// 'attendanceDate'	=>$attendenceDate,
							'stu_id'			=>$data['studentId'.$i],
							'attendence_status'	=>$data['attendanceType'.$i],
							'description'		=>$data['note_'.$i],
							'subjectId'			=>$data['subjectId'],
							'fromHour'			=>$data['from_hour'],
							'toHour'			=>$data['to_hour'],
							'studentRequestId'	=>$studentRequestId,
							'byTeacherId'		=>$this->getUserExternalId(),
							'platform'			=>2,
							'createDate'		=>date("Y-m-d H:i:s"),
							'modifyDate'		=>date("Y-m-d H:i:s"),
						);
						$this->_name ='rms_student_attendence_detail';
						$this->insert($arr);
					}
				}
			}
			$db->commit();
			return $idAttendance;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }

   function updateAttendance($data){
	$db = $this->getAdapter();
	$db->beginTransaction();
	try{

		$date = new DateTime();
		$attendenceDate = $date->format('Y-m-d');
		
		$data['attendenceDate'] = $attendenceDate;
		$idAttendance = $data['attendanceId'];

		if(!empty($data['identity'])){
			$ids = explode(',', $data['identity']);

			$detailidlist = '';
			foreach ($ids as $i){
				if($data['attendanceType'.$i]!=1){
					if (empty($detailidlist)){
						if (!empty($data['detailIdAtt'.$i])){
							$detailidlist= $data['detailIdAtt'.$i];
						}
					}else{
						if (!empty($data['detailIdAtt'.$i])){
							$detailidlist = $detailidlist.",".$data['detailIdAtt'.$i];
						}
					}
				}
				
			}
			$this->_name="rms_student_attendence_detail";
    		$where2=" attendence_id = ".$idAttendance." AND subjectId =".$data['subjectId'];
    		$where2.=" AND fromHour = ".$data['from_hour']." AND toHour =".$data['to_hour'];
    		if (!empty($detailidlist)){ // check if has old payment detail  detail id
    			$where2.=" AND id NOT IN (".$detailidlist.")";
    		}
			$this->delete($where2);

			if(!empty($ids)) foreach ($ids as $i){
				if (!empty($data['detailIdAtt'.$i])){
					if($data['attendanceType'.$i]!=1){
						$arrs = array(
							'attendence_id'		=>$idAttendance,
							'stu_id'			=>$data['studentId'.$i],
							'attendence_status'	=>$data['attendanceType'.$i],
							'description'		=>$data['note_'.$i],
							'subjectId'			=>$data['subjectId'],
							'fromHour'			=>$data['from_hour'],
							'toHour'			=>$data['to_hour'],
							'byTeacherId'		=>$this->getUserExternalId(),
							'platform'			=>2,
							'modifyDate'		=>date("Y-m-d H:i:s"),
						);
						$this->_name ='rms_student_attendence_detail';
						$where=" id= ".$data['detailIdAtt'.$i];
	    				$this->update($arrs, $where);
					}
				}else{
					$studentRequestId=0;
					if(!empty($data['permissionRecordId'.$i])){
						$_arr = array(
							'isCompleted'		=>1,
						);
						$this->_name ='rms_student_attendence_detail';
						$where="id=".$data['permissionRecordId'.$i];
						$this->update($_arr, $where);
						
						$studentRequestId=$data['permissionRecordId'.$i];
					}
	
					if($data['attendanceType'.$i]!=1){
						$arr = array(
							'attendence_id'		=>$idAttendance,
							'stu_id'			=>$data['studentId'.$i],
							'attendence_status'	=>$data['attendanceType'.$i],
							'description'		=>$data['note_'.$i],
							'subjectId'			=>$data['subjectId'],
							'fromHour'			=>$data['from_hour'],
							'toHour'			=>$data['to_hour'],
							'studentRequestId'	=>$studentRequestId,
							'byTeacherId'		=>$this->getUserExternalId(),
							'platform'			=>2,
							'createDate'		=>date("Y-m-d H:i:s"),
							'modifyDate'		=>date("Y-m-d H:i:s"),
						);
						$this->_name ='rms_student_attendence_detail';
						$this->insert($arr);
					}
				}
			}
		}
		$db->commit();
		return $idAttendance;
	}catch (Exception $e){
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		$db->rollBack();
	}
}
   
}