<?php

class Allreport_Model_DbTable_DbRptStudentScore extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
    public function getAllCar($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT carid,carname,drivername,tel,zone,note,status FROM rms_car ";
    	$where=' where 1';
    	$order=" order by id DESC";
    	
//     	if(empty($search)){
//     		return $db->fetchAll($sql.$order);
//     	}
//     	if(!empty($search['txtsearch'])){
//     		$s_where = array();
//     		$s_search = trim($search['txtsearch']);
//     		$s_where[] = " carid LIKE '%{$s_search}%'";
//     		$s_where[] = " carname LIKE '%{$s_search}%'";
//     		$s_where[] = " drivername LIKE '%{$s_search}%'";
//     		$where .=' AND ( '.implode(' OR ',$s_where).')';
//     	}
    	
//     	if(empty($search)){
//     		return $db->fetchAll($sql);
//     	}
// //     	if(!empty($search['txtsearch'])){
// //     		$where.=" AND rms_car.carid LIKE '%".$search['txtsearch']."%'";
// //     	}
		
//     	$searchs = $search['txtsearch'];
//     	if($search['searchby'] == 0){
//     		$where.= '';
//     	}
//     	if($search['searchby'] == 1){
//     		$where.= " AND carid = ".$search['txtsearch'];
//     	}
//     	if($search['searchby'] == 2){
//     		$where.= " AND carname LIKE '%".$searchs."%'";
//     	}
//     	if($search['searchby'] == 3){
//     		$where.= " AND drivername LIKE '%".$searchs."%'";
//     	}
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
    
    
    function getAllMonth(){
		$db = $this->getAdapter();
		$sql="select id , month_kh from rms_month where status=1 ";
		return $db->fetchAll($sql);
	}	
    
   
    function getAllTitle(){
    	$db=$this->getAdapter();
    	$sql="select major_id,major_enname from rms_major";
    	return $db->fetchAll($sql);
    }
    
    function getAllSession(){
    	$db=$this->getAdapter();
    	$sql="select key_code,name_en from rms_view where type=4";
    	return $db->fetchAll($sql);
    }
    
    function getAllStu($search){
    	$db= $this->getAdapter();
    	$sql="SELECT COUNT(gds.`stu_id`) AS amount,gds.stu_id,g.`academic_year`,
    		  (select from_academic from rms_tuitionfee where rms_tuitionfee.id=g.academic_year limit 1) as from_academic,
    		  (select to_academic from rms_tuitionfee where rms_tuitionfee.id=g.academic_year limit 1) as to_academic,
    		  (select generation from rms_tuitionfee where rms_tuitionfee.id=g.academic_year limit 1) as generation,
			  (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=g.session) as session,
			  (select major_enname from rms_major where rms_major.major_id=g.`grade`) as grade
			 FROM
			  `rms_group_detail_student` AS gds,
			  `rms_group` AS g 
			 WHERE g.id = gds.`group_id` 
			  ";
    	$groupby=" GROUP BY g.`academic_year`,g.`grade`,g.`session`";
    	$where  = '';
    	
   		 if(empty($search)){
    		return $db->fetchAll($sql.$groupby);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = trim($search['txtsearch']);
    		$s_where[] = " (select CONCAT(from_academic,'-',to_academic,' ',generation) from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
    		$s_where[] = " (select major_enname from rms_major where rms_major.major_id=g.grade) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=g.session) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	$row = $db->fetchAll($sql.$where.$groupby);
    	if($row){
    		return $row;
    	}
    }
    function getAllYearGeneration(){
    	$db= $this->getAdapter();
    	$sql="	SELECT  tf.id as id,tf.`from_academic`, tf.`to_academic`,tf.`generation`,tf.`time`,s.`stu_id`
				FROM
				  `rms_tuitionfee` AS tf,
				  `rms_student` AS s,
				  `rms_group_detail_student` AS gds 
				WHERE s.`stu_id` = gds.`stu_id` 
				  AND s.`academic_year` = tf.`id` GROUP BY tf.`from_academic`,tf.`to_academic`,tf.`generation`";
    	$row = $db->fetchAll($sql);
    	if($row){
    		return $row;
    	}
    }
   function getParentName(){
   		$db=$this->getAdapter();
   		$sql="select 
   				(select subject_titleen from rms_subject where rms_subject.id=rms_score.parent_id) As parent_id
   			  from 
   			  	rms_score,
   			  	rms_score_detail as sd 
              where 
   				sd.score_id=rms_score.id
   			  GROUP BY 
   				rms_score.academic_id,
   				rms_score.session_id,
   				rms_score.group_id,
   				rms_score.parent_id 
   				
   			";
        return $db->fetchAll($sql);
   } 
   function getSubjectdByParent(){
   	$db=$this->getAdapter();
   	$sql="SELECT subject_id,parent_id,(SELECT subject_titleen FROM rms_subject WHERE rms_subject.id=rms_score.subject_id) AS subject_name 
          FROM rms_score WHERE `status`=1 GROUP BY academic_id,session_id,group_id,parent_id,subject_id ,term_id";
    return $db->fetchAll($sql);
   }   
   function getAllSubjectByStudent(){
   		$db=$this->getAdapter();
   		$sql="SELECT
				  stu_id,
				  stu_enname,
				  stu_code,
				  SUM(sd.score) AS score,
				  sd.note,
				  sc.academic_id,
				    (SELECT CONCAT(from_academic,'-',to_academic,'-',generation) FROM rms_tuitionfee WHERE rms_tuitionfee.id=sc.academic_id ) AS academic_name,
				  sc.session_id,
				  sc.group_id,
				    (SELECT group_code FROM rms_group WHERE rms_group.id=sc.group_id) AS group_name,
				  sc.parent_id,
				     (SELECT subject_titleen FROM rms_subject WHERE rms_subject.id=sc.parent_id) AS parent_name,
				  sc.subject_id,
				     (SELECT subject_titleen FROM rms_subject WHERE rms_subject.id=sc.subject_id) AS subject_name
				FROM rms_student,
				  rms_score_detail AS sd,
				  rms_score AS sc
				WHERE sd.student_id = stu_id
				    AND sd.score_id = sc.id
				GROUP BY sc.subject_id,stu_id
				ORDER BY stu_id";
   		return $db->fetchAll($sql);
   }
   function getAcademic(){
   	    $db=$this->getAdapter();
   	    $sql=" SELECT id,(SELECT CONCAT(from_academic,'-',to_academic)  FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_score.academic_id) AS academic_id,
                     session_id,group_id,subject_id,term_id FROM  rms_score";
   	    return $db->fetchAll($sql);
   }
   function getStudenetGroupSubject(){
   		$db=$this->getAdapter();
   		$sql="SELECT s.stu_id,s.stu_enname,s.stu_code,g.*,g.id as group_id,
   		   (SELECT subject_titleen FROM rms_subject AS sj WHERE sj.id=gsd.subject_id LIMIT 1) AS subject_name,
   		    gsd.subject_id 
	 		FROM rms_student AS s,rms_group AS g,rms_group_detail_student AS gd,rms_group_subject_detail AS gsd
	 		WHERE s.stu_id=gd.stu_id AND  g.id=gd.group_id AND gsd.group_id=g.id  ORDER BY g.id,gsd.subject_id,s.stu_id";
   		return $db->fetchAll($sql);
   }
   function getScoreByGroupId($student_id,$subject_id,$group_id){
   	$db = $this->getAdapter();
   	$sql = "select (select subject_titleen from rms_subject where rms_subject.id=s.subject_id) as subject_name,
            SUM(sd.score) As total_score from rms_score as s,rms_score_detail as sd,rms_student as st
           where  s.id=sd.score_id and sd.student_id=st.stu_id and s.group_id=$group_id and s.parent_id=$subject_id GROUP BY s.subject_id ";
   	return $db->fetchAll($sql);
   	
   }
   function getSubjectItem($subject_id,$group_id){
   	$db = $this->getAdapter();
   	$sql = " select (select subject_titleen from rms_subject where rms_subject.id=s.subject_id) as subject_name
    from rms_score as s,rms_score_detail as sd
   	where s.id=sd.score_id and s.group_id=$group_id and s.parent_id=$subject_id GROUP BY s.subject_id ";
   	return $db->fetchAll($sql);
   
   }
   public function getStundetScoreGroup($search){ // fro rpt-score
   	$db = $this->getAdapter();
   	$sql="SELECT s.`id`, s.`group_id`, g.`group_code`,title_score,s.for_semester,s.note,
   		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') 
		FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year
 		,(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree, 
 	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
 	`g`.`semester` AS `semester`,
 	(SELECT month_kh FROM `rms_month` WHERE id=s.for_month  LIMIT 1) as for_month,
 	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name, 
 	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, 
 	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
 	(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month) AS for_month,
 	s.exam_type,
 	s.for_semester,
  	s.reportdate
   		FROM `rms_score` AS s, `rms_group` AS g 
   		WHERE  g.`id`=s.`group_id` AND s.status = 1 AND s.type_score=1 ";
   			$where='';
//    	$from_date =(empty($search['for_month']))? '1': " s.formonth >= '".$search['for_month']." 00:00:00'";
//    	$to_date = (empty($search['end_date']))? '1': " s.reportdate <= '".$search['end_date']." 23:59:59'";
//    	$where = " AND ".$from_date;
   	if(!empty($search['title'])){
   				$s_where=array();
   				$s_search=addslashes(trim($search['title']));
   				$s_where[]= " s.title_score LIKE '%{$s_search}%'";
   				$s_where[]=" s.note LIKE '%{$s_search}%'";
   				$s_where[]=" s.for_semester LIKE '%{$s_search}%'";
   				$where.=' AND ('.implode(' OR ', $s_where).')';
   	}
   	if(!empty($search['branch_id'])){
   		$where.= " AND s.branch_id =".$search['branch_id'];
   	}
   	if(!empty($search['group_name'])){
   		$where.= " AND g.id =".$search['group_name'];
   	}
   	if($search['degree']>0){
   		$where.=" AND `g`.`degree` =".$search['degree'];
   	}
   	if($search['for_month']>0){
   		$where.=" AND s.for_month =".$search['for_month'];
   	}
   	if($search['study_year']>0){
   		$where.=" AND s.for_academic_year =".$search['study_year'];
   	}
   	if($search['grade']>0){
   		$where.=" AND `g`.`grade` =".$search['grade'];
   	}
   	if($search['session']>0){
   		$where.=" AND `g`.`session` =".$search['session'];
   	}
   	if($search['room']>0){
   		$where.=" AND `g`.`room_id` =".$search['room'];
   	}
   	if($search['for_month']>0){
   		$where.= " AND s.for_month =".$search['for_month'];
   	}
   	if($search['exam_type']>0){
   		$where.= " AND s.exam_type =".$search['exam_type'];
   	}
   	$order = "  ORDER BY s.id DESC,g.`id` DESC ,s.for_academic_year,s.for_semester,s.for_month	";
   	return $db->fetchAll($sql.$where.$order);
   }
   
   public function getStundetScoreDetailGroup($search,$id,$limit){ // fro rpt-score
   	
   	$db = $this->getAdapter();
   	$sql="SELECT
		   	s.`id`,
		   	sd.`group_id`,
		   	g.`group_code`,
		   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_year,
		   	(SELECT from_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS start_year,
		   	(SELECT to_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS end_year,
		   	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
		   	
		   	`g`.`semester` AS `semester`,
		   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
		   	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		   	(select teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
		   	sd.`student_id`,
		   	st.`stu_code`,
			st.stu_khname,
			st.stu_enname,		
		   	st.`sex`,
		   	st.photo,
		   	(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
		   	s.exam_type,
		   	s.for_semester,
		   	s.reportdate,
		   	s.title_score,
		   	s.max_score,
		   	SUM(sd.`score`) AS total_score,
		   	total_score AS total_scoreallsubject,
		   	AVG(sd.score) AS average,
		   	(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amount_subject ,
		   	(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amount_subjectsem ,
		   	(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as average_pass
		   		   	   
   		FROM 
   			`rms_score` AS s,
		   	`rms_score_detail` AS sd,
		   	`rms_student` AS st,
		   	`rms_group` AS g
   		WHERE
		   	s.`id`=sd.`score_id`
		   	AND st.`stu_id`=sd.`student_id`
		   	AND g.`id`=s.`group_id`
		   	AND sd.`is_parent`=1
		   	AND s.status = 1
		   	AND s.type_score=1 AND s.id= $id ";
   	$where='';
   
   	if(!empty($search['group_name'])){
   		$where.= " AND sd.group_id =".$search['group_name'];
   	}
   	if(!empty($search['degree'])){
   		$where.=" AND `g`.`degree` =".$search['degree'];
   	}
   	if(!empty($search['study_year'])){
   		$where.=" AND s.for_academic_year =".$search['study_year'];
   	}
   	if(!empty($search['grade'])){
   		$where.=" AND `g`.`grade` =".$search['grade'];
   	}
   	if(!empty($search['session'])){
   		$where.=" AND `g`.`session` =".$search['session'];
   	}
   	$order = "  GROUP BY s.id,sd.`student_id`,sd.score_id,s.`reportdate` ORDER BY average DESC ,s.for_academic_year,s.for_semester,s.for_month,sd.`group_id`,sd.`student_id` ASC 	";
   	if($limit==2){
   		$limit = " limit 5";
   	}else{
   		$limit = " ";
   	}
   	return $db->fetchAll($sql.$where.$order.$limit);
   }
   public function getStundetScorebySemester($group_id,$semester){ // fro rpt-score by semester I+II
   	$db = $this->getAdapter();
   	$sql=" SELECT
			   	s.`id`,
			   	sd.`group_id`,
			   	g.`group_code`,
			   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
			   	(SELECT CONCAT(from_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS start_year,
			   	(SELECT CONCAT(to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS end_year,
			   	(SELECT rms_items.pass_average FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS pass_score,
			   	(SELECT rms_items.max_average FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) AS maxi_score,
			   	
			   	(SELECT rms_items.title  FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
			   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
			   	
			   	`g`.`semester` AS `semester`,
			   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			   	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
			   	(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id=g.teacher_id LIMIT 1) AS teacher_name,
			   	sd.`student_id`,
			   	st.`stu_code`,
			   	st.`stu_enname`,
			   	st.`stu_khname`,
			   	st.`sex`,
			   	st.photo,
			   	s.for_semester,
			   	(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amount_subject,
			   	(SELECT COUNT(ss.id) FROM `rms_score` AS ss WHERE ss.group_id=g.`id` AND ss.exam_type=1 AND ss.for_semester=$semester LIMIT 1) AS amount_month,

			   	(SELECT SUM(sdd.score) FROM rms_score_detail AS sdd,rms_score as sc 
			   		WHERE 
			   		sc.id=sdd.score_id
			   		AND sc.group_id=$group_id
			   		AND sc.for_semester =$semester
			   		AND sc.exam_type=2
			   		AND sdd.`is_parent`=1 
			   		AND sdd.student_id = sd.student_id
			   		GROUP BY sdd.student_id LIMIT 1) AS total_exam,
			   		SUM(sd.`score`) AS total_score,
			   		AVG(sd.`score`) as average,
			   	(SELECT COUNT(ss.id) FROM `rms_score` AS ss WHERE ss.group_id=$group_id AND ss.exam_type=1 AND for_semester = $semester) AS amount_month
		    FROM 
		    	`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_student` AS st,
			   	`rms_group` AS g
		   	WHERE
		   		s.`id`=sd.`score_id`
			   	AND st.`stu_id`=sd.`student_id`
			   	AND g.`id`=s.`group_id`
			   	AND sd.`is_parent`=1
			   	AND s.status = 1
			   	AND s.type_score=1
		   		AND g.id = $group_id
		   		AND s.for_semester=$semester
		   		AND s.exam_type=1 ";
   	
   	$where='';
   	$order = "GROUP BY sd.`student_id` ORDER BY (total_score+total_exam) DESC,s.for_academic_year,s.for_semester ASC ";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getStundetScorebyYear($group_id,$semester){ // score result for yearly
   	$db = $this->getAdapter();
		$sql="
		   	SELECT
		   	s.`id`,
		   	sd.`group_id`,
		   	g.`group_code`,
		   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
		   	(SELECT CONCAT(from_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS start_year,
			(SELECT CONCAT(to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS end_year,
		   	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
	
		   	`g`.`semester` AS `semester`,
		   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
		   	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		   	(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id=g.teacher_id LIMIT 1) AS teacher_name,
		   	sd.`student_id`,
		   	st.`stu_code`,
		   	st.`stu_enname`,
		   	st.`stu_khname`,
		   	st.`sex`,
		   	st.photo,
		   	s.for_semester,
		   	(SELECT AVG(sdd.score) FROM rms_score_detail AS sdd,rms_score as sc 
		   		WHERE 
		   		sc.id=sdd.score_id
		   		AND sc.group_id=$group_id
		   		AND sc.for_semester =$semester
		   		AND sc.exam_type=2
		   		AND sdd.`is_parent`=1 
		   		AND sdd.student_id = sd.student_id
		   		GROUP BY sdd.student_id LIMIT 1) AS avg_exam,
		   	SUM(sd.`score`) AS total_score,
		   	AVG(sd.score) as average,
		   	(SELECT COUNT(ss.id) FROM `rms_score` AS ss WHERE ss.group_id=$group_id AND ss.exam_type=1 AND for_semester = $semester) AS amount_month
		   	FROM `rms_score` AS s,
		   	`rms_score_detail` AS sd,
		   	`rms_student` AS st,
		   	`rms_group` AS g
		   	WHERE
		   		s.`id`=sd.`score_id`
			   	AND st.`stu_id`=sd.`student_id`
			   	AND g.`id`=s.`group_id`
			   	AND sd.`is_parent`=1
			   	AND s.status = 1
			   	AND s.type_score=1
		   		AND g.id= $group_id
		   		AND s.for_semester=$semester
		   	AND s.exam_type=1 ";
		 $where='';
		 $order = " GROUP BY sd.`student_id` ORDER BY sd.`student_id`,s.for_academic_year";
		 return $db->fetchAll($sql.$where.$order);
   }
   public function getAcadimicByStudentHeader($group_id,$student_id){ // fro ព្រឹត្តប័ត្រពិន្ទុឆ្នាំសិក្សា ក្បាល I+II
   	$db = $this->getAdapter();
   	$sql="
   	SELECT
   	s.`id`,
   	sd.`group_id`,
   	g.`group_code`,
   	st.`stu_code`,
   	st.`stu_enname`,
   	st.`stu_khname`,
   	st.`sex`,
   	st.dob,
	(SELECT COUNT(stu_id) FROM `rms_group_detail_student` WHERE group_id=s.group_id LIMIT 1) AS amount_student,
   	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
   	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
   	
   	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
	`g`.`semester` AS `semester`,
   	sd.`student_id`,
   	sd.subject_id,
   	(SELECT sj.subject_titleen  AS sj FROM `rms_subject` AS sj WHERE sj.id=sd.subject_id) as subject_name
   	FROM 
   	`rms_score` AS s,
   	`rms_score_detail` AS sd,
   	`rms_student` AS st,
   	`rms_group` AS g   	
   	WHERE
   	s.`id`=sd.`score_id`
   	AND st.`stu_id`=sd.`student_id`
   	AND g.`id`=s.`group_id`
   	AND sd.`is_parent`=1
   	AND s.status = 1
   	AND s.type_score=1
   	AND g.id= $group_id
   	AND sd.student_id=$student_id
   	AND s.exam_type=2 ";
   	$where='';
   	$order = " GROUP BY sd.subject_id ORDER BY s.for_semester ASC,sd.subject_id,s.for_academic_year,s.for_semester ASC ";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getAcadimicByStudentSubject($group_id,$semester_id,$subject_id,$student_id){ // fro ព្រឹត្តប័ត្រពិន្ទុឆ្នាំសិក្សា I+II លម្អិត
   	$db = $this->getAdapter();
   	$sql="SELECT 
   			score,
			sum(score) as total_score,
   			 FIND_IN_SET( score, (    
			SELECT GROUP_CONCAT( score
			ORDER BY score DESC ) 
			FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
				ss.`id`=dd.`score_id` 
				AND ss.exam_type=2
				AND ss.for_semester= $semester_id
				AND ss.group_id= $group_id
				AND dd.subject_id=$subject_id 
				AND dd.`is_parent`=1
			 	)
			) AS rank
			FROM 
				`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
			WHERE s.`id`=sd.`score_id` 
				AND g.`id`=s.`group_id`
			   	AND sd.`is_parent`=1
			   	AND s.status = 1
			   	AND s.type_score=1
			   	AND g.id= $group_id
			   	AND s.for_semester= $semester_id
			   	AND sd.subject_id= $subject_id
			   	AND sd.student_id= $student_id
			   	AND s.exam_type=2
				GROUP BY sd.subject_id ";
   	$where=' ';
   	return $db->fetchRow($sql.$where);
}
function getRankStudentbyGroupSemester($group_id,$semester,$student_id){//ចំណាត់ថ្នាក់ប្រឡង ឆមាសទី១/២ តាមសិស្ស(transcript)
	$sql="SELECT 
			SUM(score) AS total_score,
   			 FIND_IN_SET( score, (    
			SELECT GROUP_CONCAT( score
			ORDER BY score DESC ) 
			FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
				ss.`id`=dd.`score_id` 
				AND ss.exam_type=2
				AND ss.for_semester=$semester
				AND ss.group_id= $group_id
				AND dd.`is_parent`=1
			 	)
			) AS rank
			FROM 
				`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
			WHERE s.`id`=sd.`score_id` 
				AND g.`id`=s.`group_id`
			   	AND sd.`is_parent`=1
			   	AND s.status = 1
			   	AND s.type_score=1
			   	AND g.id= $group_id
			   	AND s.for_semester=$semester
			   	AND sd.student_id= $student_id
			   	AND s.exam_type=2";
	// 	$sql='SET @rnk=0; SET @rank=0; SET @curscore=0;
//  		SELECT score,student_id,rank FROM
//     	(
//         SELECT AA.*,BB.student_id,
//         (@rnk:=@rnk+1) rnk,
//         (@rank:=IF(@curscore=score,@rank,@rnk)) rank,
//         (@curscore:=score) newscore
//         FROM
//         (
//            SELECT * FROM
//            (SELECT COUNT(1) scorecount,score
//            FROM (SELECT SUM(score) AS score,score_id,group_id,student_id FROM `rms_score_detail` WHERE 
//            score_id=2 GROUP BY student_id) AS ST WHERE score_id=2  GROUP BY score
//         ) AAA
//          ORDER BY score DESC
//          ) AA LEFT JOIN (SELECT SUM(score) AS score,score_id,group_id,student_id FROM `rms_score_detail` WHERE score_id=2 GROUP BY student_id) BB USING (score) WHERE score_id=2 
//          ) A WHERE student_id=3';//return 
	$db = $this->getAdapter();
	return $db->fetchRow($sql);
}
   public function getStundetExamById($group_id,$semester,$student_id){ // ប្រើសំរាប់រកមធ្យមភាគ សម្រាប់សិស្ស ១ ប្រើព្រឹត្តប័ត្រពិន្ទុឆ្នាំ
   	$db = $this->getAdapter();
   	$sql="
	   	SELECT
		   	s.`id`,
		   	sd.`group_id`,
		   	g.`group_code`,
		   `g`.`semester` AS `semester`,
		   	s.for_semester,
		   	SUM(sd.`score`) AS total_score,
		   	AVG(sd.score) as average,
		   	(SELECT 
		   		AVG(sdd.score) FROM rms_score_detail AS sdd,rms_score as sc
			   	WHERE
			   	sc.id=sdd.score_id
			   	AND sc.group_id=$group_id
			   	AND sc.for_semester =$semester
			   	AND sc.exam_type=2
			   	AND sdd.`is_parent`=1
			   	AND sdd.student_id = $student_id
			   	GROUP BY sdd.student_id LIMIT 1
			) AS avg_exam
		
		   	FROM `rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
		   	WHERE
		   	s.`id`=sd.`score_id`
		   	AND g.`id`=s.`group_id`
		   	AND sd.`is_parent`=1
		   	AND s.status = 1
		   	AND s.type_score=1
		   	AND g.id= $group_id
		   	AND s.for_semester=$semester
		   	AND sd.student_id=$student_id
		   	AND s.exam_type=1 ";
	   	$where='';
	   		$order = " GROUP BY sd.`student_id`,s.for_semester ORDER BY sd.`student_id`";
   		return $db->fetchRow($sql.$where.$order);
   }
   function getRankingSemesterByStudent($group_id,$semester_id,$student_id){//ចំណាត់ថ្នាក់សំរាប់ឆមាសនីមួយៗ
   	//is good for ranks but it now replace duplicate rank
// 	   	$sql="SELECT * FROM (
// 			  SELECT s.*, @rank := @rank + 1 rank FROM (
// 			    SELECT student_id, SUM(score) totalPoints 
// 				FROM 
// 				`rms_score_detail` AS sd,
// 				 `rms_score` AS s
// 				WHERE 
// 						s.`id`=sd.`score_id`
// 					   	AND s.`group_id`=$group_id
// 					   	AND sd.`is_parent`=1
// 					   	AND s.status = 1
// 					   	AND s.type_score=1
// 					   	AND s.for_semester=$semester
// 					   	AND s.exam_type=2
// 				GROUP BY student_id 	
// 			  ) s, 
// 			  (SELECT @rank := 0) init
// 			  ORDER BY TotalPoints DESC
// 			) r
// 			WHERE student_id=$student_id	";
		$sql="SELECT 
   			 FIND_IN_SET( total_score, (    
			SELECT GROUP_CONCAT( total_score
			ORDER BY total_score DESC ) 
			FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
				ss.`id`=dd.`score_id` 
				AND ss.exam_type=1
				AND ss.for_semester= $semester_id
				AND ss.group_id= $group_id
				AND dd.`is_parent`=1
			 	)
			) AS rank,
			SUM(score) as total_score
			FROM 
				`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
			WHERE s.`id`=sd.`score_id` 
				AND g.`id`=s.`group_id`
			   	AND sd.`is_parent`=1
			   	AND s.status = 1
			   	AND s.type_score=1
			   	AND g.id=$group_id
			   	AND s.for_semester= $semester_id
			   	AND sd.student_id= $student_id
			   	AND s.exam_type=2
				GROUP BY sd.student_id ";
	   	return $this->getAdapter()->fetchRow($sql);
   }
   public function getSubjectScoreGroup($group_id,$teacher_id=null,$exam_type=1){
   	$db = new Foundation_Model_DbTable_DbScore();
//    	$sql = "SELECT
// 			 	s.`id`,
// 			 	sd.`group_id`,
// 			 	sd.`student_id`,
// 			 	sj.`subject_titlekh`,
// 			 	sj.`subject_titleen`,
// 			 	sj.shortcut,
// 			 	sd.`score`,
// 			 	sd.`subject_id`
// 			FROM `rms_score` AS s, 
// 			    `rms_score_detail` AS sd,
// 			    `rms_subject` AS sj
// 		   WHERE 
// 		   		s.`id`=sd.`score_id` 
// 		 		AND sj.`id`=sd.`subject_id` 
// 		 		AND sd.`is_parent`=1
// 		 		AND sd.`group_id`=$group_id 
// 		   GROUP BY 
// 		   		sd.`subject_id`	";
   	return $db->getSubjectByGroup($group_id,$teacher_id=null,$exam_type=1);
   }
   
   public function getScoreBySubject($score_id,$student_id,$subject_id){
   	$db = $this->getAdapter();
   	$sql="SELECT
     sd.`score`,sd.`subject_id`
	 FROM  `rms_score_detail` AS sd
	 WHERE sd.`score_id`=$score_id AND sd.`student_id`=$student_id  AND sd.`subject_id`=$subject_id
   	";
   	return $db->fetchRow($sql);
   }
   function getAllgroupStudyNotPass(){
   	$db = $this->getAdapter();
   	$sql ="SELECT `g`.`id` as id, CONCAT(`g`.`group_code`,' ',
   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
   	FROM `rms_group` AS `g` WHERE g.status =1 ";
   	return $db->fetchAll($sql);
   }

   //---------------gep score report
   public function getSubjectScoreGroupGEP($group_id,$type_score=null){// for gep
   	$db = $this->getAdapter();
   	$sql = "SELECT
   	s.`id`,sd.`group_id`,sd.`student_id`,sj.`subject_titlekh`,sd.`score`,s.`reportdate`,sd.`subject_id`
   	FROM `rms_score` AS s,
   	`rms_score_detail` AS sd,
   	`rms_subject` AS sj
   	WHERE s.`id`=sd.`score_id`
   	AND sj.`id`=sd.`subject_id` AND sd.`is_parent`=1
   	AND sd.`group_id`=$group_id
   	";
   	if($type_score==2 || $type_score==3){
   		$sql.=" AND sd.`subject_id` !=9";
   	}
   	$sql.=' GROUP BY sd.`subject_id`';
   	return $db->fetchAll($sql);
   }
   public function getStundentEnglishMonthlyScore($search){ // for rpt-gep-monthlyscore
   	$db = $this->getAdapter();
   	$sql="SELECT
		s.`id`,sd.`group_id`,g.`group_code`,
		(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
		(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
		
		`g`.`semester` AS `semester`, 
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		sd.`student_id`,st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`,s.`reportdate`,DATE_FORMAT(s.`reportdate`,'%Y-%m') AS month_of_semester,
		g.`amount_month`,g.`start_date`
		 FROM `rms_score` AS s, 
		 `rms_score_detail` AS sd,
		 `rms_student` AS st,
		 `rms_group` AS g
		 WHERE s.`id`=sd.`score_id` AND st.`stu_id`=sd.`student_id`
		 AND g.`id`=sd.`group_id` AND s.status = 1  AND `g`.`degree` NOT IN (1,2)
   	";
   	$where='';
   	$from_date =(empty($search['start_date']))? '1': " s.reportdate >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " s.reportdate <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   
   	if(!empty($search['group_name'])){
   		$where.= " AND sd.group_id =".$search['group_name'];
   	}
   	if(!empty($search['degree_english'])){
   		$where.=" AND `g`.`degree` =".$search['degree_english'];
   	}
   	if(!empty($search['study_year'])){
   		$where.=" AND g.academic_year =".$search['study_year'];
   	}
   	if(!empty($search['grade_english'])){
   		$where.=" AND `g`.`grade` =".$search['grade_english'];
   	}
   	if(!empty($search['session'])){
   		$where.=" AND `g`.`session` =".$search['session'];
   	}
   	$order = "  GROUP BY sd.`student_id`, DATE_FORMAT(s.`reportdate`,'%Y-%m') ORDER BY s.`reportdate`,sd.`group_id`,sd.`student_id` ASC";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getTotalWeeklyScoreBySubject($group_id,$student_id,$subject_id,$score_type,$report_date){
   	$db = $this->getAdapter();
   	$sql="SELECT
   	 sd.`student_id`,sd.`subject_id`,SUM(sd.`score`) AS score,DATE_FORMAT(s.`reportdate`,'%Y-%m') AS DATE
	 FROM  `rms_score_detail` AS sd,`rms_score` AS s
	WHERE 
	s.id=sd.`score_id` AND
	s.`type_score`=$score_type AND sd.`student_id`=$student_id AND s.`group_id`=$group_id AND sd.`subject_id`=$subject_id
	AND DATE_FORMAT(s.`reportdate`,'%Y-%m')=DATE_FORMAT('$report_date','%Y-%m')
	GROUP BY DATE_FORMAT(s.`reportdate`,'%Y-%m')
   	";
   	return $db->fetchRow($sql);
   }
   
   function getAmountWeeklyscoreByGroup($group_id,$monthly_score){
   	$db = $this->getAdapter();
   	$sql= "SELECT COUNT(s.id) AS amount_weeklyscore
	 FROM  	`rms_score` AS s 
	 WHERE s.`group_id`=$group_id AND s.`type_score`=1 AND DATE_FORMAT(s.`reportdate`,'%Y-%m')=DATE_FORMAT('$monthly_score','%Y-%m')"; 
//    	echo $sql;exit();
   	return $db->fetchOne($sql);
   }
   
   function getMonthlyScoreInSemester($student_id,$group_id,$monthly_score){
   	$db = $this->getAdapter();
   	$sql="SELECT
   		s.`group_id`, sd.`student_id`,sd.`subject_id`,sd.`score` AS score,DATE_FORMAT(s.`reportdate`,'%Y-%m') AS date,
   		s.`type_score`,s.`reportdate`
	 FROM  `rms_score_detail` AS sd,`rms_score` AS s
	WHERE 
	s.id=sd.`score_id` AND sd.`student_id`=$student_id AND s.`group_id`=$group_id
	AND  DATE_FORMAT(s.`reportdate`,'%Y-%m')=DATE_FORMAT('$monthly_score','%Y-%m') ORDER BY s.`type_score`,sd.`subject_id` ASC";
   	$result = $db->fetchAll($sql);
   	$subject_id = 0;
   	$weekly_score =0;
   	$sumUpweeklytest=0;
   	$month_test =0;
   	$sumUpMonthlytest=0;
   	$total_all =0;
	   	foreach($result as $row){
	   		$amount_test = $this->getAmountWeeklyscoreByGroup($row['group_id'], $row['reportdate']);
	   		if ($row['type_score']==1){
		   		if ($subject_id !=$row['subject_id']){
		   			$weekly_score =0;
		   		}
		   		$weekly_score = ($row['score']*5)/($amount_test*10);
		   		if ($row['subject_id']==9){
		   			$weekly_score = ($row['score']*10)/($amount_test*10);
		   		}
		   		$sumUpweeklytest = $sumUpweeklytest+$weekly_score;
	   		}else{
	   			if ($subject_id !=$row['subject_id']){
	   				$month_test =0;
	   			}
	   			$month_test = $month_test +$row['score'];
	   			$sumUpMonthlytest = $sumUpMonthlytest +$month_test;
	   		}
	   		$subject_id =$row['subject_id'];
	   	}
	   	$total_all = $sumUpweeklytest+$sumUpMonthlytest;
	   	return $total_all;
   }
   
   public function getStundentEnglishSemesterScore($search){ // for rpt-semester-evaluation
   	$db = $this->getAdapter();
   	$sql="SELECT
   	s.`id`,sd.`group_id`,g.`group_code`,
   	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
   	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`)  AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
   	
   	`g`.`semester` AS `semester`,
   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
   	(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
   	AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
   	sd.`student_id`,st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`,s.`reportdate`,DATE_FORMAT(s.`reportdate`,'%Y-%m') AS month_of_semester,
   	g.`amount_month`,g.`start_date`
   	FROM `rms_score` AS s,
   	`rms_score_detail` AS sd,
   	`rms_student` AS st,
   	`rms_group` AS g
   	WHERE s.`id`=sd.`score_id` AND st.`stu_id`=sd.`student_id`
   	AND g.`id`=sd.`group_id` AND s.status = 1  AND `g`.`degree` NOT IN (1,2)
   	";
   	$where='';
   	$from_date =(empty($search['start_date']))? '1': " s.reportdate >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " s.reportdate <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	 
   	if(!empty($search['group_name'])){
   		$where.= " AND sd.group_id =".$search['group_name'];
   	}
   	if(!empty($search['degree_english'])){
   		$where.=" AND `g`.`degree` =".$search['degree_english'];
   	}
   	if(!empty($search['study_year'])){
   		$where.=" AND g.academic_year =".$search['study_year'];
   	}
   	if(!empty($search['grade_english'])){
   		$where.=" AND `g`.`grade` =".$search['grade_english'];
   	}
   	if(!empty($search['session'])){
   		$where.=" AND `g`.`session` =".$search['session'];
   	}
   	$order = "  GROUP BY sd.`student_id` ORDER BY s.`reportdate`,sd.`group_id`,sd.`student_id` ASC";
   	return $db->fetchAll($sql.$where.$order);
   }
   
   public function getStundetScoreList($search){ // fro rpt-score
   	$db = $this->getAdapter();
   	$sql="SELECT s.`id`, s.`group_id`, g.`group_code`,title_score,s.for_month,s.for_semester,s.note,
	   	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')')
	   	FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year
	   	,(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
	   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1)  LIMIT 1 )AS grade,
	   	`g`.`semester` AS `semester`,
	   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
	   	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`, (SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month) AS for_month, s.for_semester,
	   	s.reportdate
	   	FROM `rms_teacherscore` AS s, `rms_group` AS g WHERE  g.`id`=s.`group_id` AND s.status = 1 AND s.type_score=1 ";
   	$where='';
   	//    	$from_date =(empty($search['for_month']))? '1': " s.formonth >= '".$search['for_month']." 00:00:00'";
   	//    	$to_date = (empty($search['end_date']))? '1': " s.reportdate <= '".$search['end_date']." 23:59:59'";
   	//    	$where = " AND ".$from_date;
   	if(!empty($search['title'])){
   		$s_where=array();
   		$s_search=addslashes(trim($search['title']));
   		$s_where[]= " s.title_score LIKE '%{$s_search}%'";
   		$s_where[]=" s.note LIKE '%{$s_search}%'";
   		$s_where[]=" s.for_semester LIKE '%{$s_search}%'";
   		$where.=' AND ('.implode(' OR ', $s_where).')';
   	}
   	if(!empty($search['group_name'])){
   		$where.= " AND g.id =".$search['group_name'];
   	}
   	if($search['degree']>0){
   		$where.=" AND `g`.`degree` =".$search['degree'];
   	}
   	if($search['for_month']>0){
   		$where.=" AND s.for_month =".$search['for_month'];
   	}
   	if($search['study_year']>0){
   		$where.=" AND s.for_academic_year =".$search['study_year'];
   	}
   	if($search['grade']>0){
   		$where.=" AND `g`.`grade` =".$search['grade'];
   	}
   	if($search['session']>0){
   		$where.=" AND `g`.`session` =".$search['session'];
   	}
   	if($search['room']>0){
   		$where.=" AND `g`.`room_id` =".$search['room'];
   	}
   	if($search['for_month']>0){
   		$where.= " AND s.for_month =".$search['for_month'];
   	}
   	$order = "  ORDER BY g.`id` DESC ,s.for_academic_year,s.for_semester,s.for_month	";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getStundetInfo($id,$group_id){ // fro student score by teacher input
   	$db = $this->getAdapter();
   	$sql="SELECT tsd.*,
	   	ts.`academic_id`,
	   	ts.`group_id`,
	   	g.`group_code`,
	   	(SELECT major_enname FROM `rms_major` WHERE (`rms_major`.`major_id`=`g`.`grade`) LIMIT 1 )AS grade,
	   	ts.`for_academic_year`,
	   	(SELECT month_kh FROM rms_month WHERE rms_month.id = ts.for_month LIMIT 1) AS for_month,
	   	ts.`for_month`,ts.`for_semester`,ts.`for_year`,
	   	ts.`date_input`,
	   	(SELECT CONCAT(from_academic,'-',to_academic)
	   	FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
	   	st.`stu_code`,
	   	(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS stu_khname,
	   	(CASE WHEN sj.`subject_titlekh` IS NULL THEN sj.`subject_titleen` ELSE sj.`subject_titlekh` END) AS subject_title
	   	FROM `rms_teacherscore_detail` AS tsd,
	   	`rms_teacherscore` AS ts,
	   	`rms_student` AS st,
	   	`rms_group` AS g,
	   	`rms_subject` AS sj
	   	WHERE ts.`id`=tsd.`score_id`
	   	AND st.`stu_id`=tsd.`student_id`
	   	AND g.`id`=ts.`group_id`
	   	AND sj.`id` = tsd.`subject_id`
	   	AND tsd.`student_id` = $id and tsd.group_id = $group_id";
   	$where='';
   	$order = "  GROUP BY sj.id ASC 	LIMIT 1";
   	return $db->fetchRow($sql.$where.$order);
   }
   public function getStundetScoreDetail($id,$group_id){ // fro student score by teacher input
   	$db = $this->getAdapter();
   	$sql="SELECT tsd.*,
		ts.`academic_id`,
		ts.`group_id`,
		g.`group_code`,
		ts.`for_academic_year`,
		(SELECT month_kh FROM rms_month WHERE rms_month.id = ts.for_month LIMIT 1) AS for_month,
		ts.`for_month`,ts.`for_semester`,ts.`for_year`,
		ts.`date_input`,
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS stu_khname,
		(CASE WHEN sj.`subject_titlekh` IS NULL THEN sj.`subject_titleen` ELSE sj.`subject_titlekh` END) AS subject_title,
		(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id= ts.`teacher_id` LIMIT 1) AS teacher_name
		FROM `rms_teacherscore_detail` AS tsd,
		`rms_teacherscore` AS ts,
		`rms_student` AS st,
		`rms_group` AS g,
		`rms_subject` AS sj
		WHERE ts.`id`=tsd.`score_id`
		AND st.`stu_id`=tsd.`student_id`
		AND g.`id`=ts.`group_id`
		AND sj.`id` = tsd.`subject_id`
		AND tsd.`student_id` = $id and tsd.group_id = $group_id";
   	$where='';
   	$order = "  GROUP BY sj.id ASC 	";
//    	if($limit==2){
//    		$limit = " limit 5";
//    	}else{
//    		$limit = " ";
//    	}
   	return $db->fetchAll($sql.$where.$order);
   }
   function getSubjectScorebystudentandgroup($group_id,$student_id){//certificate of foundation year
   	$db = $this->getAdapter();
   	$sql ="SELECT
			 	s.`id`,
			 	st.stu_enname,
				st.stu_khname,
				st.sex,
				st.stu_code,
			 	sd.`group_id`,
			 	sd.`student_id`,
			 	sj.`subject_titlekh`,
			 	sj.`subject_titleen`,
			 	sj.shortcut,
			 	sd.`score`,
			 	(SELECT from_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 ) AS from_academic,
			 	(SELECT to_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 ) AS to_academic
			FROM 
				`rms_score` AS s, 
			    `rms_score_detail` AS sd,
			    `rms_subject` AS sj,
			    `rms_student` AS st,
			    `rms_group` AS g
		   WHERE 
		   		st.stu_id=sd.student_id
		   		AND s.`id`=sd.`score_id` 
		   		AND s.exam_type=2
		 		AND sj.`id`=sd.`subject_id` 
		 		AND sd.`is_parent`=1
		 		AND g.`id`=s.`group_id`
		 		AND sd.`group_id`=$group_id 
		 		AND st.`stu_id`=$student_id 
		   ORDER BY 
		   		s.`for_semester` ASC ";
//    	$sql="SELECT tsd.*,
//    	ts.`academic_id`,
//    	ts.`group_id`,
//    	g.`group_code`,
//    	ts.`for_academic_year`,
//    	(SELECT month_kh FROM rms_month WHERE rms_month.id = ts.for_month LIMIT 1) AS for_month,
//    	ts.`for_month`,ts.`for_semester`,ts.`for_year`,
//    	ts.`date_input`,
//    	(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS stu_khname,
//    	(CASE WHEN sj.`subject_titlekh` IS NULL THEN sj.`subject_titleen` ELSE sj.`subject_titlekh` END) AS subject_title,
//    	(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id= ts.`teacher_id` LIMIT 1) AS teacher_name
//    	FROM `rms_teacherscore_detail` AS tsd,
//    	`rms_teacherscore` AS ts,
//    	`rms_student` AS st,
//    	`rms_group` AS g,
//    	`rms_subject` AS sj
//    	WHERE ts.`id`=tsd.`score_id`
//    	AND st.`stu_id`=tsd.`student_id`
//    	AND g.`id`=ts.`group_id`
//    	AND sj.`id` = tsd.`subject_id`
//    	AND tsd.`student_id` = $id and tsd.group_id = $group_id";
   	return $db->fetchAll($sql);
   }
}