<?php

class Allreport_Model_DbTable_DbRptAllStudent extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStudent($search){
    	$db = $this->getAdapter();
    	$sql ='select stu_id,
    	      (SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	       CONCAT(stu_khname," - ",stu_enname) as name,
    	       stu_enname,stu_khname,nationality,
    	       tel,email,stu_code,home_num,street_num,
    	       village_name,
    		   commune_name,district_name,is_subspend,dob,degree as dept,
    		   (select CONCAT(from_academic,"-",to_academic) from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as academic_year,
    		   (select from_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as start_year,
    		   (select to_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as end_year,
    		   (select end_date from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as end_date,
    		   (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1)AS session,
    		   (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1)AS grade,
    		   (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1)AS degree,
    		   (select name_en from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,
    		   (select province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,	   	
    		   (select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex,photo
    		   from rms_student ';
    	$where=' where 1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;    	
    	$order="  order by stu_id,degree,grade,academic_year DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		
    		$s_where[]=" stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
    			
    		$s_where[]=" father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" remark LIKE '%{$s_search}%'";
    		$s_where[]=" home_num LIKE '%{$s_search}%'";
    		$s_where[]=" street_num LIKE '%{$s_search}%'";
    		$s_where[]=" village_name LIKE '%{$s_search}%'";
    		$s_where[]=" commune_name LIKE '%{$s_search}%'";
    		$s_where[]=" district_name LIKE '%{$s_search}%'";
    		
    		$s_where[] = " (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	if($search['stu_type']!=-1){
    		$where.=' AND is_stu_new = '.$search['stu_type'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getAllStudentSelected($stu_id){
    	$db = $this->getAdapter();
    	$sql ='select stu_id,
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_khname," - ",stu_enname) as name,stu_enname,stu_khname,nationality,tel,email,stu_code,home_num,street_num,village_name,
    	commune_name,district_name,is_subspend,dob,degree as dept,
    	(select CONCAT(from_academic,"-",to_academic,"(",generation,")") from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as academic_year,
    	(select from_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as start_year,
    		   (select to_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as end_year,
    	(select end_date from rms_tuitionfee where rms_tuitionfee.id=academic_year limit 1) as end_date,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) AS session,
    	(select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) AS grade,
    	(select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) AS degree,
    	(select name_en from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,
    	(select province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,
    	(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex,photo
    	from rms_student where stu_id ='.$stu_id;

    	return $db->fetchAll($sql);
    }
    
    public function getAllAmountStudent($search){
    	$db = $this->getAdapter();
    	$sql ='select stu_id,
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_enname)AS name,nationality,tel,email,stu_code,home_num,street_num,village_name,
    	commune_name,district_name,is_subspend,
    	(select CONCAT(from_academic,"-",to_academic,"(",generation,")") from rms_tuitionfee where rms_tuitionfee.id=academic_year) as academic_year,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1)AS session,
    	(select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1)AS grade,
    	(select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1)AS degree,    
    	(select name_en from rms_view where type=5 and key_code=is_subspend) as status,    
    	(select province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,
    	(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex
    	from rms_student ';
    	$where=' where 1 ';
    	 
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	
    	$order="  order by academic_year DESC,degree ASC,grade DESC,session ASC,stu_id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}if($search['degree']>0){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if($search['grade_all']>0){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getStudentStatistic($search){
    	$db = $this->getAdapter();
    	$sql ="SELECT 
					s.stu_id,
					(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
					CONCAT(stu_enname)AS NAME,
					(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year) AS academic_year_name,
					(SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=g.session LIMIT 1)AS `session_name`,
					(SELECT major_enname FROM rms_major WHERE rms_major.major_id=g.grade LIMIT 1)AS grade_name,
					(SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=g.degree LIMIT 1)AS degree_name,
					g.academic_year,
					g.degree,
					g.grade,
					g.session,
					s.is_stu_new,
					s.`is_subspend`
				FROM 
					rms_student AS s , 
					rms_group AS g,
					rms_group_detail_student AS gds
				WHERE 
					g.id = gds.group_id
					AND s.stu_id = gds.stu_id
    		";
    	
    	$group_by = " GROUP BY gds.`stu_id`,g.`academic_year`,g.`degree`,g.`grade`,g.`session`";
		$order_by = " ORDER BY g.`academic_year` ASC,g.`degree` ASC,g.`grade` ASC,g.`session` ASC";	
    	
    	$where=' ';
    
//     	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
//     	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	 
    	//$order="  order by academic_year DESC,degree ASC,grade DESC,session ASC,stu_id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$group_by.$order_by);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
//     		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND g.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND g.grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND g.session='.$search['session'];
    	}if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND s.branch_id='.$search['branch_id'];
    	}
//     	echo $sql.$where;exit();
    	return $db->fetchAll($sql.$where.$group_by.$order_by);
    }
    
    
    public function getAllStudyHistory($search){
    	//print_r($search);
    	//echo $search['stu_type'];
    	$db = $this->getAdapter();
//     	if(!empty($search['stu_id'])){
	    	$sql = 'SELECT 
					  h.`stu_id`,s.`stu_code`,is_subspend,s.`stu_enname`,
					  s.`stu_khname`,s.`sex`,s.`group_id`,
						(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = s.`group_id` LIMIT 1) AS group_code,
					  h.is_finished,h.finished_date,
					  (SELECT branch_nameen FROM `rms_branch` WHERE br_id=h.branch_id LIMIT 1) AS branch_name,
					  (SELECT CONCAT(from_academic,"-",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=h.academic_year LIMIT 1) AS academic_year,
					  (SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=h.session LIMIT 1)AS session,
					  (SELECT major_enname FROM rms_major WHERE rms_major.major_id=h.grade LIMIT 1)AS grade,
					  (SELECT en_name FROM rms_dept WHERE rms_dept.dept_id=h.degree LIMIT 1)AS degree,
					  (SELECT teacher_name_en FROM rms_teacher AS t WHERE t.id = h.teacher_id ) AS teacher
					FROM
					  `rms_student` AS s,
					  `rms_study_history` AS h 
					WHERE s.`stu_id` = h.`stu_id` 
	    		   ';
	    	$where="";
	    	
	    	$from_date =(empty($search['start_date']))? '1': "h.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "h.create_date <= '".$search['end_date']." 23:59:59'";
	    	$where .= " AND ".$from_date." AND ".$to_date;
	    	
	    	$order="  order by s.`stu_id`,h.degree,h.grade,h.academic_year DESC";
	    	if(empty($search)){
	    		return $db->fetchAll($sql.$order);
	    	}
	    	if(!empty($search['title'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
	    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
	    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
	    		$s_where[] = " (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = s.`group_id` LIMIT 1) LIKE '%{$s_search}%'";
	    		$s_where[] = " CONCAT(stu_enname,stu_enname) LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    	}
	    	if(!empty($search['study_year'])){
	    		$where.=' AND h.academic_year='.$search['study_year'];
	    	}
	    	if(!empty($search['degree'])){
	    		$where.=' AND h.degree='.$search['degree'];
	    	}
	    	if(!empty($search['branch_id'])){
	    		$where.=' AND h.branch_id='.$search['branch_id'];
	    	}
	    	if(!empty($search['grade_all'])){
	    		$where.=' AND h.grade='.$search['grade_all'];
	    	}
	    	if(!empty($search['session'])){
	    		$where.=' AND h.session='.$search['session'];
	    	}
// 	    	if($search['stu_type']!=-1){
// 	    		$where.=' AND is_stu_new = '.$search['stu_type'];
// 	    	}
	    	return $db->fetchAll($sql.$where.$order);
//     	}else{
//     		return 0;
//     	}
    }
    
    function getAllStudentID(){
    	$db = $this->getAdapter();
    	$sql="select stu_id as id,stu_code from rms_student ";
    	return $db->fetchAll($sql);
    }
    function getAllStudentName(){
    	$db = $this->getAdapter();
    	$sql="select stu_id as id,stu_enname as name from rms_student ";
    	return $db->fetchAll($sql);
    }
    
    
    public function getAllStudentDetail($search){
    	$db = $this->getAdapter();
    	$sql ='select stu_id,CONCAT(stu_khname," - ",stu_enname)AS name,nationality,tel,email,stu_code,home_num,street_num,village_name,
    	commune_name,district_name,CONCAT(father_enname," - ",father_khname)AS father_name,father_nation,father_phone,
    	CONCAT(mother_enname," - ",mother_khname)AS mother_name,mother_nation,mother_phone,
    	CONCAT(guardian_enname," - ",guardian_khname)AS guardian_name,guardian_nation,guardian_document,guardian_tel,guardian_email,
    
    	(select name_en from rms_view where type=5 and key_code=is_subspend) as status,
    
    	(select occu_enname from rms_occupation where rms_occupation.occupation_id=rms_student.father_job limit 1)AS father_job,
    	(select occu_enname from rms_occupation where rms_occupation.occupation_id=rms_student.mother_job limit 1)AS mother_job,
    	(select occu_enname from rms_occupation where rms_occupation.occupation_id=rms_student.guardian_job limit 1)AS guardian_job,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1)AS session,
    	(select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1)AS grade,
    	(select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1)AS degree,
    	(select province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,
    	(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex
    	from rms_student ';
    	$where=' where degree IN (2,3,4)  ';
    	 
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	$order=" order by stu_id DESC";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (select en_name from rms_dept where rms_dept.dept_id=rms_student.degree limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=rms_student.grade limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
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
    	return $db->fetchAll($sql.$where.$order);
    
    }
     
    function getStudentAttendance($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
		    	 	(SELECT b.branch_namekh FROM `rms_branch` AS b WHERE b.br_id=s.`branch_id` LIMIT 1) AS branch_name,
			    	s.`stu_id`,
			    	s.`stu_code`,
			    	s.`stu_khname`,
			    	s.`stu_enname`,
			    	s.`sex`,
			    	(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
			    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = (SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) LIMIT 1) AS group_code, 
			    	(SELECT kh_name FROM `rms_dept` WHERE `rms_dept`.`dept_id`= s.`degree` LIMIT 1) AS degree,
			    	s.`degree` as degree_id,
					(SELECT major_enname FROM `rms_major` WHERE `rms_major`.`major_id`=s.`grade` LIMIT 1) AS grade,
					(SELECT `r`.`room_name`	FROM `rms_room` `r` WHERE `r`.`room_id` = s.`room`LIMIT 1) AS room,
			    	s.`group` 
			    FROM 
			    	`rms_student` AS s 
			    WHERE 
			    	s.`status`=1 
		    ";
    	
    	$where='';
    	
 		if(!empty($search['group'])){
    		$where.= " AND (SELECT sgh.group_id FROM `rms_student_group_history` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.id DESC LIMIT 1) =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND s.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND s.`degree`=".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND s.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND s.session=".$search['session'];
    	}
    	$order=" ORDER BY degree_id,s.`group`,s.`stu_id` DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function CountAttendence($stu_id,$att_status,$fromdate,$todate,$group_id){
    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(sad.`id`) AS count_att FROM `rms_student_attendence_detail` AS sad WHERE sad.`stu_id` =$stu_id AND sad.`attendence_status`=$att_status AND (SELECT sa.status FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)=1 
    	AND (SELECT sa.group_id FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)=$group_id
    	";
    	$from_date =" (SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1) >=  '".date("Y-m-d",strtotime($fromdate))." 00:00:00'";
    	$to_date = "(SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)  <= '".date("Y-m-d",strtotime($todate))." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchOne($sql.$where);
    }
    
    function getStudentAttendence($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT 
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
					(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) AS degree,
					(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )AS grade,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					gsd.`stu_id`,
					st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`
				FROM 
					`rms_group_detail_student` AS gsd,
					`rms_group` AS g,
					`rms_student` AS st,
					rms_student_attendence AS sta
				WHERE 
				sta.type=1
				AND gsd.status=1
				AND gsd.type=1
				AND sta.type=1
    			AND g.`id` = gsd.`group_id`
				 AND sta.group_id = g.id 
				 AND st.`stu_id` = gsd.`stu_id` 
				 AND sta.status=1 
				 AND g.is_pass!=1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND `g`.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$order =" GROUP BY sta.group_id,gsd.stu_id 
    		ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getStatusAttendence($stu_id,$date_att,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql='SELECT
		sat.`group_id`,satd.`attendence_status`,sat.`date_attendence`
		FROM `rms_student_attendence` AS sat,
		`rms_student_attendence_detail` AS satd 
		WHERE sat.`id`= satd.`attendence_id`
		AND sat.type=1
		AND satd.`stu_id`='.$stu_id.' AND sat.`date_attendence`="'.$date_att.'" AND sat.`group_id`='.$group;
    	$where='';
    	if (!empty($subject)){ // high school student
    		$where.=" AND sat.`subject_id`=".$subject;
    	}
		return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    function checkDateAttendence($date_att,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql="SELECT sat.`id` FROM `rms_student_attendence` AS sat 
			WHERE sat.`date_attendence`='$date_att' AND sat.`group_id`=$group";
    	$where='';
    	if (!empty($subject)){// high school student
    		$where.=" AND sat.`subject_id`=".$subject;
    	}
//     	echo $sql.$where.' LIMIT 1';
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    
	function getStudentMistake($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year, 
					(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) AS degree,
					 (SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )AS grade, 
					 (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, 
					 `g`.`semester` AS `semester`,
					  (SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					  sdd.`stu_id`, st.`stu_code`, st.`stu_enname`, st.`stu_khname`, st.`sex` 
				FROM 
					 `rms_group` AS g, `rms_student` AS st, 
					 rms_student_attendence AS sd, 
					 `rms_student_attendence_detail` AS sdd 
				WHERE 
					 (sd.type=2 OR sdd.`attendence_status` IN (4,5)) 
					 AND sd.`id` = sdd.`attendence_id` 
					 AND sd.group_id = g.id AND sd.status=1 
					 AND st.`stu_id` = sdd.`stu_id` AND st.is_subspend = 0 
    			";
    	
    	$from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND `g`.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$order =" GROUP BY g.id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function checkDateMistake($mistake_date,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    				sd.`id` 
    			FROM 
    				`rms_student_discipline` AS sd
    			WHERE 
    				sd.`mistake_date`= $mistake_date  
    				AND sd.`group_id`=$group
    		";
    	$where='';
    	    	echo $sql.$where.' LIMIT 1';//exit();
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    function getStatusMistake($stu_id,$date_att,$group){//old
    	$db = $this->getAdapter();
//     	$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`mistake_date` = '".$date_att."' 
//     				AND sd.`group_id` = $group
//     		";
    	$sql="SELECT
	    	sd.`group_id`,
	    	sd.`type`,
	    	sdd.`attendence_status` as mistake_type,
	    	sdd.description,
	    	sd.`date_attendence` as mistake_date
	    	FROM
	    	`rms_student_attendence` AS sd,
	    	`rms_student_attendence_detail` AS sdd
	    	WHERE
	    	(sd.type=2 OR sdd.`attendence_status` IN (4,5))
	    	AND sd.`id` = sdd.`attendence_id`
	    	AND sdd.`stu_id` = $stu_id
	    	AND sd.`date_attendence` = '".$date_att."'
	    	AND sd.`group_id` = $group
    	";
    	
    	$where='';
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    function getStatusMistakeByStudent($stu_id,$group,$start_date,$end_date){
    	$db = $this->getAdapter();
    	$sql="SELECT
    	sd.`group_id`,
    	sd.`type`,
    	sdd.`attendence_status` as mistake_type,
    	sdd.description,
    	sd.`date_attendence` as mistake_date,
    	sd.for_session
    	FROM
    	`rms_student_attendence` AS sd,
    	`rms_student_attendence_detail` AS sdd
    	WHERE
    	(sd.type=2 OR sdd.`attendence_status` IN (4,5))
    	AND sd.`id` = sdd.`attendence_id`
    	AND sdd.`stu_id` = $stu_id
    	AND sd.`group_id` = $group ";
    	$from_date =(empty($start_date))? '1': " sd.`date_attendence` >= '".$start_date." 00:00:00'";
    	$to_date = (empty($end_date))? '1': " sd.`date_attendence` <= '".$end_date." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchAll($sql.$where);
    }
    function getTotalStatusMistake($stu_id,$date_att,$group){
    	$db = $this->getAdapter();
// old    	$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`mistake_date` = '".$date_att."' 
//     				AND sd.`group_id` = $group
//     		";
    	
//     	$where='';
// //     	echo $sql.$where.' LIMIT 1';//exit();
//     	return $db->fetchRow($sql.$where.' LIMIT 1');
// 		$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`,
// 			    	sdd.`stu_id`,
// 			    	COUNT(sdd.`mistake_type`) AS count_mistack			    	
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`group_id` = $group
//     			GROUP BY mistake_type
// 			";
    	$sql="SELECT
	    	sd.`group_id`,
	    	sdd.`attendence_status` as mistake_type,
	    	sdd.description,
	    	sd.`date_attendence` as mistake_date,
	    	sdd.`stu_id`,
	    	COUNT(sdd.`attendence_status`) AS count_mistack
	    	FROM
	    	`rms_student_attendence` AS sd,
	    	`rms_student_attendence_detail` AS sdd
	    	WHERE
	    	sd.`type` =2
	    	AND sd.`id` = sdd.`attendence_id`
	    	AND sdd.`stu_id` = $stu_id
	    	AND sd.`group_id` = $group
	    	GROUP BY attendence_status
    	";
		return $db->fetchAll($sql);
    }
    function getAttendenceFoul($group_id,$stu_id){//កំហុស មកយឺត និងចេញមុន
    	$db = $this->getAdapter();
    	$sql="SELECT sade.*,sta.`date_attendence`,sta.`group_id`,COUNT(sade.`attendence_status`) AS count_foul_att
    	FROM rms_student_attendence_detail AS sade,
    	`rms_student_attendence` AS sta
    	WHERE sta.`id` = sade.`attendence_id`
    	AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id AND sade.`attendence_status` IN (4,5) LIMIT 1
			";
    	$where="";
    	return $db->fetchRow($sql.$where);
    }
    function getStudentAttendenceHighschool($search){
    	$db = $this->getAdapter();
    	$sql="
	    	SELECT
	    	g.id AS group_id,
	    	g.`group_code`,
	    	gsj.`subject_id`,
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = gsj.`subject_id` LIMIT 1) AS subject_name,
	    	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
	    	(SELECT kh_name FROM `rms_dept` WHERE (`rms_dept`.`dept_id`=`g`.`degree`) LIMIT 1) AS degree,
	    	(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )AS grade,
	    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
	    	`g`.`semester` AS `semester`,
	    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
	    	AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
	    	gsd.`stu_id`,
	    	st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`
	    	FROM `rms_group_detail_student` AS gsd,
	    	`rms_group` AS g,
	    	`rms_student` AS st,`rms_group_subject_detail` AS gsj
	    	WHERE g.`id` = gsd.`group_id` AND st.`stu_id` = gsd.`stu_id`
			AND gsj.`group_id` = g.`id`    	
	    	 AND `g`.`degree` =2
    	";
    	//     	$from_date =" (SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1) >=  '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	//     	$to_date = "(SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)  <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where='';
    	//     	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
//     	if(!empty($search['degree'])){
//     		$where.=" AND `g`.`degree` =".$search['degree'];
//     	}
    	if(!empty($search['grade_highschool'])){
    		$where.=" AND `g`.`grade`=".$search['grade_highschool'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$order ="  ORDER BY `g`.`degree`,g.id,gsj.`subject_id` DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getGroupHighschoolSearch(){
    	$db=$this->getAdapter();
    	$sql="SELECT g.`id` as id,g.`group_code` AS `name` 
    	FROM `rms_group` AS g WHERE g.`status`=1 AND g.`degree`=2";
    	return $db->fetchAll($sql);
    }
    function getSubjectByGroup($group_id){
    	$db=$this->getAdapter();
    	$sql="SELECT 	
		gsjd.`subject_id` AS id,	
		(SELECT CONCAT(sj.subject_titleen,'-',sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS `name` 
		FROM rms_group_subject_detail AS gsjd WHERE gsjd.group_id = ".$group_id;
    	$rs = $db->fetchAll($sql);
    	return $rs;
    }
}



