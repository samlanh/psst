<?php

class Allreport_Model_DbTable_DbRptStudentDrop extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_drop';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStudentDrop($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT st.stu_code as stu_id, 
		
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name,
    	(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=st.academic_year) as academic_year,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=st.session limit 1)AS session,
    	(select major_enname from rms_major where rms_major.major_id=st.grade limit 1)AS grade,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex )AS sex,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type`) as type,stdp.note,stdp.date_stop,
		(select name_kh from `rms_view` where `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status`)AS status
		 from rms_student_drop as stdp,rms_student as st where stdp.stu_id=st.stu_id and stdp.status=1 ";

    	$from_date =(empty($search['start_date']))? '1': " date_stop >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date_stop <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$order=" order by id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = "  (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type`) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_bac'])){
    		$where.=' AND grade='.$search['grade_bac'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	
//     	$searchs=$search['txtsearch'];
    	
//     	if($search['searchby']==0){
//     		$where.='';
//     	}
//     	if($search['searchby']==1){
//     		$where.=" AND stu_id  LIKE  '%".$searchs."%' ";
//     	}
//     	if($search['searchby']==2){
//     		$where.=" AND (SELECT CONCAT(stu_khname,' - ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id`) LIKE '%".$searchs."%'";
//     	}
//     	if($search['searchby']==3){
//     		$where.=" AND (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`rms_student_drop`.`type`) LIKE '%".$searchs."%'";
//     	}
    	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   
    function getAllRescheduleGroup($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT gr.group_id,
    	(SELECT CONCAT(rms_tuitionfee.from_academic,'-',rms_tuitionfee.to_academic,'(',rms_tuitionfee.generation,')')
    	FROM rms_tuitionfee WHERE rms_tuitionfee.status=1 AND rms_tuitionfee.is_finished=0 AND rms_tuitionfee.id=gr.year_id LIMIT 1) AS years,
    	(SELECT group_code FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AS group_code,
    	(SELECT name_en FROM rms_view WHERE rms_view.key_code=gr.day_id AND rms_view.type=18 LIMIT 1)AS days,
    	gr.from_hour,gr.to_hour,(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id )AS session_id,
    	(SELECT v.name_en FROM rms_view AS v WHERE v.key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AND v.type=4 LIMIT 1)AS `session`,
    	(SELECT subject_titlekh FROM `rms_subject` WHERE is_parent=1 AND rms_subject.id = gr.subject_id AND subject_titlekh!='' LIMIT 1) AS subject_name,
    	(SELECT CONCAT(teacher_name_kh,'-',teacher_name_en) FROM rms_teacher WHERE rms_teacher.id=gr.techer_id AND teacher_name_kh!='' LIMIT 1) AS teacher_name,
    	
    	DATE_FORMAT(gr.create_date,'%d-%m-%Y')As create_date, (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = gr.user_id) AS USER,
    	(SELECT name_en FROM rms_view WHERE rms_view.key_code=gr.status AND rms_view.type=1 LIMIT 1) AS STATUS
    	FROM rms_group_reschedule AS gr  WHERE gr.status=1";
    	$where =' ';
    			$from_date =(empty($search['start_date']))? '1': "gr.create_date >= '".$search['start_date']." 00:00:00'";
    			$to_date = (empty($search['end_date']))? '1': "gr.create_date <= '".$search['end_date']." 23:59:59'";
    			$where.= " AND ".$from_date." AND ".$to_date;
    	$order =  ' ORDER BY `gr`.`id` DESC ' ;
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND gr.year_id='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND  gr.from_hour '.$search['session'];
    	}
    	if(!empty($search['subject'])){
    		$where.=' AND  gr.subject_id ='.$search['subject'];
    	}
    	if(!empty($search['teacher'])){
    		$where.=' AND  gr.techer_id ='.$search['teacher'];
    	}
    	if(!empty($search['day'])){
    		$where.=' AND  gr.day_id ='.$search['day'];
    	}
    	 
    	return $db->fetchAll($sql.$where.$order);
    }
    
    //reschedule by group
    function getAllReschedulebygroup($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
    	 
    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
    	(SELECT CONCAT(m.major_enname,' (',(SELECT d.en_name FROM rms_dept AS d WHERE m.dept_id=d.dept_id ),')')
    	FROM rms_major AS m WHERE 1 AND major_enname!='' AND m.major_id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1))AS grade_name,
    	 
    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
    	FROM rms_group_reschedule AS gr";
    	 
    	$where =' where 1';
    	$from_date =(empty($search['start_date']))? '1': "gr.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "gr.create_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	$order=" GROUP BY gr.year_id,gr.group_id
    	ORDER BY gr.year_id,gr.group_id,times DESC ";
    	 
    	if(!empty($search['room'])){
    		$where.=' AND (SELECT rms_group.room_id FROM rms_group WHERE rms_group.id=gr.group_id )='.$search['room'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND (SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gr.group_id )='.$search['grade'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND gr.year_id='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
    
//     function getSubleByYGS($year,$group){//old
//     	$db=$this->getAdapter();
//     	$sql="SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
//     	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
//     	(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
//     	(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
//     	(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
//     	FROM rms_group_reschedule AS gr
    	 
//     	WHERE gr.year_id=$year
//     	AND gr.group_id=$group
    	 
//     	ORDER BY times ASC ";
//     	return $db->fetchAll($sql);
//     }
    function getTimeSchelduleByYGS($year,$group){ /* get Time for show in schedule VD*/
    	$db=$this->getAdapter();
    	$sql="
    		SELECT gr.from_hour,
			REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
			FROM rms_group_reschedule AS gr 
			WHERE gr.year_id=$year AND gr.group_id=$group
			GROUP BY REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','')
			ORDER BY times ASC";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    function getSubjectTeacherByScheduleAndGroup($year,$group,$time,$day){
    	$db=$this->getAdapter();
    	$sql="SELECT gr.from_hour,
			REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
			(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
			(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
			(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
			FROM rms_group_reschedule AS gr 
			WHERE gr.year_id=$year AND gr.group_id=$group
			AND REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') ='$time'
			AND gr.`day_id` =$day LIMIT 1";
    	return $db->fetchRow($sql);
    }
//     function getSubleByHour($year,$group,$hour){
//     $db=$this->getAdapter();
//     $sql="SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
//     REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
//     (SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
//     (SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
//     (SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
//     FROM rms_group_reschedule AS gr
    
//     WHERE gr.year_id=$year
//     AND gr.group_id=$group
    
//     AND REPLACE(CONCAT(gr.from_hour,'-',gr.to_hour),' ','')='$hour'
//     GROUP BY gr.day_id
//     ORDER BY gr.day_id,times ASC ";
//     return $db->fetchAll($sql);
//     }
    
    function getSubjectListByYG($year,$group){
    $db=$this->getAdapter();
    $sql="SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
    REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
    (SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
    (SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
    (SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone,
    COUNT(*)AS total_hour
    FROM rms_group_reschedule AS gr
    WHERE gr.year_id=$year
    AND gr.group_id=$group
    GROUP BY gr.subject_id
    ORDER BY subject_name,gr.subject_id DESC ";
    return $db->fetchAll($sql);
    }
    //end reschedule by group
    
    function getStartDateEndDateStu($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT se.* ,(SELECT s.stu_enname FROM rms_student AS s WHERE s.stu_id=se.stu_id LIMIT 1)AS stu_name,
    		(SELECT s.stu_code FROM rms_student AS s WHERE s.stu_id=se.stu_id LIMIT 1)AS stu_code,
			(SELECT s.stu_code FROM rms_student AS s WHERE s.stu_id=se.stu_id LIMIT 1)AS stu_code,
			(SELECT  name_en AS NAME FROM rms_view WHERE rms_view.type=6 AND rms_view.key_code=se.term LIMIT 1 ) AS term,
			(SELECT first_name FROM `rms_users` WHERE rms_users.id=se.user_id LIMIT 1) AS user_name ,
			(SELECT name_en FROM rms_view WHERE rms_view.key_code=se.status AND rms_view.type=1 LIMIT 1) AS STATUS
        	 FROM rms_startdate_enddate_stu AS se ";
    	$where =' where 1';
    	$from_date =(empty($search['start_date']))? '1': "se.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "se.create_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[] = "REPLACE(se.`note`,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	 
    	if(!empty($search['stuname_con'])){
    		$where.=' AND se.stu_id='.$search['stuname_con'];
    	}
    	if(!empty($search['term'])){
    		$where.=' AND se.term='.$search['term'];
    	}
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
}