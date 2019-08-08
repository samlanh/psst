<?php

class Allreport_Model_DbTable_DbRptStudentDrop extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_drop';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStudentDrop($search){
    	$db = $this->getAdapter();
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang_id=$session_lang->lang_id;
    	$str='name_en';
    	$grade="title_en";
    	$branch = "branch_nameen";
    	if($lang_id==1){//for kh
    		$str = 'name_kh';
    		$grade="title";
    		$branch = "branch_namekh";
    	}
    	
    	$sql = "SELECT st.stu_code as stu_id, 
					(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = stdp.branch_id LIMIT 1) AS branch_name,
					st.stu_khname,
					st.stu_enname,
					st.last_name,
					st.tel,
			    	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=stdp.academic_year) AS academic_year,
			    	(SELECT $str from rms_view where rms_view.type=4 and rms_view.key_code=stdp.session limit 1) AS session,
			    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=stdp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT $str FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex LIMIT 1 ) AS sex,
					(SELECT $str FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type`LIMIT 1 ) as type,
					(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=stdp.group LIMIT 1 ) AS group_name,
					stdp.note,stdp.date_stop,stdp.reason,
					(SELECT $str from `rms_view` where `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status` LIMIT 1) AS status
				 FROM 
				 	rms_student_drop as stdp,
		    		rms_student as st
		    	 WHERE 
		    		stdp.stu_id=st.stu_id 
		    		and stdp.status=1 
		    		AND type !=0 ";
    	$where="";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("stdp.branch_id");
    	
    	$order=" ORDER BY id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	$from_date =(empty($search['start_date']))? '1': " stdp.date_stop >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " stdp.date_stop <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " st.last_name LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = "  (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type` LIMIT 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND stdp.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND stdp.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade'])){
	   		$where.=' AND stdp.grade='.$search['grade'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `stdp`.`degree`='.$search['degree'];
	   	}
    	if(!empty($search['session'])){
    		$where.=' AND stdp.session='.$search['session'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function getAllRescheduleGroup($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$subject = "subject_titlekh";
    		$branch = "branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$subject = "subject_titleen";
    		$branch = "branch_nameen";
    	}
    	$sql = "SELECT 
			    	gr.group_id,
			    	(SELECT $branch FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
			    	(SELECT CONCAT(rms_tuitionfee.from_academic,'-',rms_tuitionfee.to_academic,'(',rms_tuitionfee.generation,')')
			    	 FROM rms_tuitionfee WHERE rms_tuitionfee.status=1 AND rms_tuitionfee.is_finished=0 AND rms_tuitionfee.id=gr.year_id LIMIT 1) AS years,
			    	(SELECT group_code FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AS group_code,
			    	(SELECT $label FROM rms_view WHERE rms_view.key_code=gr.day_id AND rms_view.type=18 LIMIT 1)AS days,
			    	gr.from_hour,gr.to_hour,
			    	(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1 )AS session_id,
			    	(SELECT $label FROM rms_view AS v WHERE v.key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AND v.type=4 LIMIT 1)AS `session`,
			    	(SELECT $subject FROM `rms_subject` WHERE is_parent=1 AND rms_subject.id = gr.subject_id AND subject_titlekh!='' LIMIT 1) AS subject_name,
			    	(SELECT CONCAT(teacher_name_kh,'-',teacher_name_en) FROM rms_teacher WHERE rms_teacher.id=gr.techer_id AND teacher_name_kh!='' LIMIT 1) AS teacher_name,
			    	
			    	DATE_FORMAT(gr.create_date,'%d-%m-%Y')As create_date, (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = gr.user_id) AS USER,
			    	(SELECT $label FROM rms_view WHERE rms_view.key_code=gr.status AND rms_view.type=1 LIMIT 1) AS STATUS
    		FROM 
    			rms_group_reschedule AS gr  
    		WHERE gr.status=1 ";
    	$where =' ';
    			$from_date =(empty($search['start_date']))? '1': "gr.create_date >= '".$search['start_date']." 00:00:00'";
    			$to_date = (empty($search['end_date']))? '1': "gr.create_date <= '".$search['end_date']." 23:59:59'";
    			$where.= " AND ".$from_date." AND ".$to_date;
    	$order =  ' ORDER BY `gr`.`group_id` DESC ,gr.day_id ASC ,from_hour ASC ' ;
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
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
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    
    //reschedule by group
    function getAllReschedulebygroup($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT gr.id,gr.year_id,
    		gr.group_id,
    		gr.branch_id,
    		(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
    		(SELECT group_code FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AS group_code,
    		gr.day_id,gr.from_hour,gr.to_hour,
    		gr.subject_id,gr.techer_id,
	    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
	    	(SELECT CONCAT(m.title,' (',(SELECT d.title FROM rms_items AS d WHERE m.items_id=d.id ),')')
    	FROM rms_itemsdetail AS m WHERE 1 AND title!='' AND m.id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1))AS grade_name,
					  
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
    		$where.=' AND (SELECT rms_group.room_id FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1)='.$search['room'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND (SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1)='.$search['grade'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND gr.year_id='.$search['study_year'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
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
			(SELECT name_en FROM rms_view WHERE rms_view.key_code=se.status AND rms_view.type=1 LIMIT 1) AS STATUS,
			(SELECT m.major_enname FROM rms_major AS m WHERE m.major_id=(SELECT s.grade FROM rms_student AS s WHERE s.stu_id=se.stu_id ) LIMIT 1)AS grade
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
    
    function getSubjectForCalculateTime($year_id,$group_id,$subject_id){
    	$db=$this->getAdapter();
    	$sql="SELECT gr.id,gr.year_id,gr.group_id,gr.day_id,gr.from_hour,gr.to_hour,gr.subject_id,gr.techer_id,
    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
    	(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
    	(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
    	(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
    	FROM rms_group_reschedule AS gr
    	WHERE gr.year_id=$year_id
    	AND gr.group_id=$group_id
    	AND gr.subject_id =$subject_id
    	ORDER BY subject_name,gr.subject_id DESC ";
    	$row = $db->fetchAll($sql);
    	$hour="";
    	$min="";
    	if (!empty($row)){
    		
	    	foreach ($row as $rs){
	    		$fromHour = explode(".", $rs['from_hour']);
	    		$to_hour = explode(".", $rs['to_hour']);
	    		
	    		$HourFro = $fromHour[0];
	    		$HourTo = $to_hour[0];
	    		
	    		$MinFro = end($fromHour);
	    		$MinTo = end($to_hour);
	    		
	    		$hour = $hour + ($HourTo - $HourFro);
	    		$min = $min+($MinTo - $MinFro);
	    		if (($MinTo - $MinFro)<0){
	    			 $hour = $hour -1;
	    			$min = 60 + $min;
	    		}else if ($min>=60){
	    			$min = $min%60;
	    			$hour = $hour+1;
	    		}
	    	}
    	}
    	$lblHour="Hr";
    	if ($hour>1){
    		$lblHour="Hrs";
    	}
    	$hourStudy = $hour." ".$lblHour." ";
    	if ($hour==0){
    		$hourStudy = "";
    	}
    	$minStudy=$min." min";
    	if ($min==0){
    		$minStudy ="";
    	}
    	return $hourStudy.$minStudy;
    }
}