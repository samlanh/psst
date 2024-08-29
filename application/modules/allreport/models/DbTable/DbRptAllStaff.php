<?php

class Allreport_Model_DbTable_DbRptAllStaff extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';

    function getAllTeacherCard($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
		$colunmName='depart_nameen';
    	if($currentlang==1){
    		$label="name_kh";
			$colunmName='depart_namekh';
    	}
    	$sql = "SELECT 
	    			g.*,
			    	(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
			    	CASE
						WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
						WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
			    	END AS sex,
			    	CASE
						WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
						WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
			    	END AS staff_type_title,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=24 AND v.key_code=g.teacher_type LIMIT 1 ) AS teacher_type,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=3  AND v.key_code=g.degree LIMIT 1) AS degree,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=21 AND v.key_code=g.nationality LIMIT 1) AS nationality,
			    	(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS dept_name,
					(SELECT dept.$colunmName FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS deptName
    			FROM 
    				rms_teacher AS g 
    			WHERE 
    				1
    		";
    
    	$where='';
    	if(!empty($search['title'])){
    	$s_where = array();
    	$s_search = addslashes(trim($search['title']));
    	$s_where[] = " (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if($search['teacher_type']>-1){
			$where.=' AND teacher_type='.$search['teacher_type'];
		}
    	if(!empty($search['nationality'])){
    		$where.=' AND nationality='.$search['nationality'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['staff_type'])){
    		$where.=' AND staff_type='.$search['staff_type'];
    	}
    	$order_by=" ORDER BY id DESC ";
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbp->getAccessPermission('g.branch_id');
    	return $db->fetchAll($sql.$where.$order_by);
    }
	function getAllTeacher($search){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbg->currentlang();
		$label="name_en";
		$colunmName='depart_nameen';
		if($currentlang==1){
			$label="name_kh";
			$colunmName='depart_namekh';
		}
		$sql = "
			SELECT g.*, 
				(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
				CASE    
				WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
				CASE    
				WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
				WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
				END AS staff_type_title,
				(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type LIMIT 1) AS teacher_type,
				(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree LIMIT 1) AS degree,
				(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality LIMIT 1) AS nationality, 
				(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS dept_name,
				(SELECT dept.$colunmName FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS deptName,
				CASE
				   	WHEN  g.active_type = 1 THEN '".$tr->translate("STOP")."'
				   	WHEN  g.active_type = 0 THEN '".$tr->translate("ACTIVING")."'
			   	END AS active_type 
				FROM rms_teacher AS g WHERE 1
		";
		
		$where='';
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['degree'])){
			$where.=' AND degree='.$search['degree'];
		}
		if(!empty($search['department'])){
			$where.=' AND department='.$search['department'];
		}
		if($search['teacher_type']>-1){
			$where.=' AND teacher_type='.$search['teacher_type'];
		}
		if(!empty($search['nationality'])){
			$where.=' AND nationality='.$search['nationality'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND branch_id='.$search['branch_id'];
		}
		if(!empty($search['staff_type'])){
			$where.=' AND staff_type='.$search['staff_type'];
		}
		if($search['active_type']>-1){
			$where.=' AND active_type='.$search['active_type'];
		}
		$order_by=" ORDER BY id DESC";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.= $dbp->getAccessPermission('g.branch_id');		
		
		return $db->fetchAll($sql.$where.$order_by);
	}
    
    public function getAllStaffSelected($staff_id){
   		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbg->currentlang();
		$label="name_en";
		$colunmName='depart_nameen';
		if($currentlang==1){
			$label="name_kh";
			$colunmName='depart_namekh';
		}
		$sql = "
			SELECT g.*, 
				(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
				CASE    
					WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
					WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
				CASE    
					WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
					WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
				END AS staff_type_title,
				(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type LIMIT 1) AS teacher_type,
				(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree LIMIT 1) AS degree,
				(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality LIMIT 1) AS nationality, 
				(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS dept_name,
				(SELECT dept.depart_namekh FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS depart_namekh,
				(SELECT dept.$colunmName FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS deptName
				FROM rms_teacher AS g WHERE status = 1 and id = $staff_id
		";
		echo $staff_id;
    	return $db->fetchAll($sql);
    }
    public function getAllStaffSelectedGroupBy($staff_id){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
		$colunmName='depart_nameen';
    	if($currentlang==1){
    		$label="name_kh";
			$colunmName='depart_namekh';
    	}
    	$sql = "
    	SELECT g.*,
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
			CASE
				WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
			END AS sex,
			CASE
				WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
				WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
			END AS staff_type_title,
			(SELECT v.$label FROM rms_view AS v WHERE v.type=24 AND v.key_code=g.teacher_type LIMIT 1) AS teacher_type,
			(SELECT v.$label FROM rms_view AS v WHERE v.type=3  AND v.key_code=g.degree LIMIT 1) AS degree,
			(SELECT v.$label FROM rms_view AS v WHERE v.type=21 AND v.key_code=g.nationality LIMIT 1) AS nationality,
			(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS dept_name,
			(SELECT dept.$colunmName FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS deptName
    	FROM rms_teacher AS g WHERE status = 1 and id = $staff_id GROUP BY staff_type
    	";
    	return $db->fetchAll($sql);
    }
    function getTeachDocumentAlert($search){
    	$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
		$colunmName='depart_nameen';
    	if($currentlang==1){
    		$label="name_kh";
			$colunmName='depart_namekh';
    	}
		
    	$sql =" SELECT 
    				t.branch_id,
			    	(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=t.branch_id LIMIT 1) AS branch_name,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=2 	AND v.key_code=t.sex LIMIT 1) AS sex,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=24 AND v.key_code=t.teacher_type LIMIT 1) AS teacher_type,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=21 AND v.key_code=t.nationality LIMIT 1) AS nationality,
			    	(SELECT v.$label FROM rms_view AS v WHERE v.type=3 	AND v.key_code=t.degree LIMIT 1) AS degree,
			    	t.teacher_code,
			    	t.teacher_name_kh,
			    	t.tel,
			    	t.email,
			    	sd.*
    			FROM 
    				`rms_teacher_document` AS sd,
    				`rms_teacher` AS t
    			WHERE 
    				t.id = sd.stu_id
    				AND sd.is_receive=0 ";
    	$where ='';
    	$to_date = (empty($search['end_date']))? '1': " sd.date_end <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$to_date;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("t.branch_id");
    
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " teacher_code LIKE '%{$s_search}%'";
    		$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
    		$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['department'])){
    		$where.=' AND department='.$search['department'];
    	}
    	if($search['teacher_type']>-1){
    		$where.=' AND teacher_type='.$search['teacher_type'];
    	}
    	if(!empty($search['staff_type'])){
    		$where.=' AND staff_type='.$search['staff_type'];
    	}
    	if(!empty($search['nationality'])){
    		$where.=' AND nationality='.$search['nationality'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
	
	
	function getTeacherScheduleGroupAndStudent($search){
    	$db = $this->getAdapter();
		
		$teacherId = empty($search["teacherId"]) ? 0 : $search["teacherId"];
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
		$colunmName='depart_nameen';
    	if($currentlang==1){
    		$label="name_kh";
			$colunmName='depart_namekh';
    	}
		
    	$sql =" SELECT 
				gsd.stu_id
				,sch.group_id
				
				,t.teacher_name_en AS teacherNameEng
				,t.teacher_name_kh AS teacherNameKh
				
				,subj.subject_titlekh AS subjectNameKh
				,subj.subject_titleen AS subjectNameEng
				
				,s.sex
				,`s`.`stu_code` AS `stu_code`
				,`s`.`stu_khname` AS `kh_name`
				,`s`.`stu_enname` AS `en_name`
				,`s`.`last_name` AS `last_name`
				, CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullName
				
				,g.group_code
				, g.degree
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYearTitle
				,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName	 
					  
				,schDetail.*
			FROM rms_group_reschedule AS schDetail
				JOIN rms_group_schedule AS sch ON sch.id =schDetail.main_schedule_id
				JOIN `rms_group_detail_student` AS gsd ON gsd.group_id = sch.group_id AND gsd.stop_type=0
				LEFT JOIN `rms_student` AS s ON s.stu_id = gsd.stu_id 
				LEFT JOIN `rms_group` AS g ON g.id  = sch.group_id
				LEFT JOIN `rms_teacher` AS t ON t.id  = schDetail.techer_id
				LEFT JOIN rms_subject AS subj ON subj.id  = schDetail.subject_id
			WHERE 
				 g.status =1
				AND g.is_use =1
				AND g.is_pass =2
			 ";
    	$where="";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("g.branch_id");
    
    	if(!empty($search['academic_year'])){
    		$sql.=' AND g.academic_year='.$search['academic_year'];
    	}
		if(!empty($search['branch_id'])){
    		$sql.=' AND g.branch_id='.$search['branch_id'];
    	}
		if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
		if(!empty($search['group'])){
    		$sql.=' AND sch.group_id='.$search['group'];
    	}
		if(!empty($teacherId)){
    		$sql.=' AND schDetail.techer_id = '.$teacherId;
    	}
		if(!empty($search['department'])){
    		$sql.=' AND t.department= '.$search['department'];
    	}
    	$order=" 
				GROUP BY sch.group_id,schDetail.techer_id,schDetail.subject_id,gsd.stu_id ORDER BY schDetail.techer_id,sch.group_id ASC,schDetail.subject_id ASC
			";
			
		$stuOrderBy = empty($search['stuOrderBy'])?0:$search['stuOrderBy'];
		$orderCondiction= ' ,s.stu_khname ASC ';
		if ($stuOrderBy==1){
			$orderCondiction= " ,`s`.`stu_code` ASC ";
		}elseif($stuOrderBy==2){
			$orderCondiction= ' ,s.stu_khname ASC ';
		}elseif($stuOrderBy==3){
			$orderCondiction= " ,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) ASC ";
		}
		$order.=$orderCondiction;
	//	echo $sql.$order;
    	return $db->fetchAll($sql.$order);
    } 
	
	function getTeachingTimeByGroupAndSubject($search){
		$db = $this->getAdapter();
		
		$teacherId = empty($search["teacherId"]) ? 0 : $search["teacherId"];
		$groupId = empty($search["groupId"]) ? 0 : $search["groupId"];
		$subjectId = empty($search["subjectId"]) ? 0 : $search["subjectId"];
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
		$colunmName='depart_nameen';
    	if($currentlang==1){
    		$label="name_kh";
			$colunmName='depart_namekh';
    	}
		$sql="
			SELECT 
				frTime.title AS fromHourTitle
				,toTime.title AS toHourTitle
				,CONCAT(COALESCE(frTime.title,''),'-',COALESCE(toTime.title,'')) timeTitle
				,schDetail.*
			FROM rms_group_reschedule AS schDetail
				JOIN rms_group_schedule AS sch ON sch.id =schDetail.main_schedule_id
				LEFT JOIN `rms_timeseting` AS frTime ON frTime.value=schDetail.from_hour
				LEFT JOIN `rms_timeseting` AS toTime ON toTime.value=schDetail.to_hour
			WHERE 
				schDetail.techer_id =$teacherId 
				AND sch.group_id =$groupId
				AND schDetail.subject_id = $subjectId
		";
		$order=" ORDER BY schDetail.day_id ASC ";
	//	echo $sql.$order;
		return $db->fetchAll($sql.$order);
	}

	public function getCalendarHolidayEveryYear($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="title";
			
			if ($currentLang==2){
				$title="title_en";
			}
			
			$month = date('m',strtotime($search['mothHoliday']));
			$year_month = date('Y-m',strtotime($search['mothHoliday']));
			 
			 $sql="SELECT mc.$title AS holiday_name,
						  DATE_FORMAT(mc.date, '%d') AS holiday_day,
						  DATE_FORMAT(mc.date, '%a') AS holiday_string,
						  DATE_FORMAT(mc.date, '%m') AS holiday_month
				   FROM `mobile_calendar` AS mc 
					WHERE 
						mc.`active` =1 
						AND (( mc.`type_holiday` =1  AND DATE_FORMAT(mc.date, '%m')= ".$month.") 
							 OR  (mc.`type_holiday` =2  AND DATE_FORMAT(mc.date, '%Y-%m')='".$year_month."'))";
			 $sql.=" ORDER BY DATE_FORMAT(mc.date, '%d') ASC ";
			 
				$result = $db->fetchAll($sql);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				return false;
			}
	}	

	public function getCalendar($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
			 
			$sql=" SELECT c.*
				,CASE
					   WHEN $currentLang = 1 THEN c.title
					   WHEN $currentLang = 2 THEN c.title_en
				END AS titleHoliday 
			FROM `mobile_calendar` AS c
			WHERE c.`active` =1 ";
			if (!empty($search['type_holiday'])){
				$sql.=" AND c.`type_holiday` = ".$search['type_holiday'];
			}
		
			if (!empty($search['formatMonthDay'])){
				$sql.=" AND (CASE 
				WHEN c.type_holiday= 1 THEN  DATE_FORMAT(c.date, '%m-%d')
				ELSE  DATE_FORMAT(c.date, '%m-%d-%Y') 
				END ) = CASE WHEN c.type_holiday= 1 THEN '".date("m-d",strtotime($search['formatMonthDay']))."'
				 ELSE '".date("m-d-Y",strtotime($search['formatMonthDay']))."' END  ";
			}
			// if (!empty($search['formatMonthDayYear'])){
			// 	$sql.=" AND DATE_FORMAT(c.date, '%m-%d-%Y') = ".date("m-d-Y",strtotime($search['formatMonthDayYear']));
			// }

			if (!empty($search['degree'])){
				$sql.=" AND FIND_IN_SET(".$search['degree'].",c.dept)";
			}
			$sql_order= "  ORDER BY c.id ASC ";
		//	echo $sql.$sql_order;
			return $db->fetchRow($sql.$sql_order);
		
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}



