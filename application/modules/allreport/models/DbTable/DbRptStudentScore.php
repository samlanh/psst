<?php

class Allreport_Model_DbTable_DbRptStudentScore extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_student';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllMonth()
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$month = "month_kh";
		} else { // English
			$month = "month_en";
		}
		$sql = "select id , $month as month from rms_month where status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllSession()
	{
		$db = $this->getAdapter();
		$sql = "select key_code,name_en from rms_view where type=4";
		return $db->fetchAll($sql);
	}


	function getSubjectdByParent()
	{
		$db = $this->getAdapter();
		$sql = "SELECT subject_id,parent_id,(SELECT subject_titleen FROM rms_subject WHERE rms_subject.id=rms_score.subject_id) AS subject_name 
          FROM rms_score WHERE `status`=1 GROUP BY academic_id,session_id,group_id,parent_id,subject_id ,term_id";
		return $db->fetchAll($sql);
	}
	function getAllSubjectByStudent()
	{
		$db = $this->getAdapter();
		$sql = "SELECT
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
	function getAcademic()
	{
		$db = $this->getAdapter();
		$sql = " SELECT id,(SELECT CONCAT(from_academic,'-',to_academic)  FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_score.academic_id) AS academic_id,
                     session_id,group_id,subject_id,term_id FROM  rms_score";
		return $db->fetchAll($sql);
	}
	function getStudenetGroupSubject()
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.stu_id,s.stu_enname,s.stu_code,g.*,g.id as group_id,
   		   (SELECT subject_titleen FROM rms_subject AS sj WHERE sj.id=gsd.subject_id LIMIT 1) AS subject_name,
   		    gsd.subject_id 
	 		FROM rms_student AS s,rms_group AS g,rms_group_detail_student AS gd,rms_group_subject_detail AS gsd
	 		WHERE gd.itemType=1 AND s.stu_id=gd.stu_id AND  g.id=gd.group_id AND gsd.group_id=g.id  ORDER BY g.id,gsd.subject_id,s.stu_id";
		return $db->fetchAll($sql);
	}
	function getScoreByGroupId($student_id, $subject_id, $group_id)
	{
		$db = $this->getAdapter();
		$sql = "select (select subject_titleen from rms_subject where rms_subject.id=s.subject_id) as subject_name,
            SUM(sd.score) As total_score from rms_score as s,rms_score_detail as sd,rms_student as st
           where  s.id=sd.score_id and sd.student_id=st.stu_id and s.group_id=$group_id and s.parent_id=$subject_id GROUP BY s.subject_id ";
		return $db->fetchAll($sql);
	}
	function getSubjectItem($subject_id, $group_id)
	{
		$db = $this->getAdapter();
		$sql = " select (select subject_titleen from rms_subject where rms_subject.id=s.subject_id) as subject_name
    		from rms_score as s,rms_score_detail as sd
   			where s.id=sd.score_id and s.group_id=$group_id and s.parent_id=$subject_id GROUP BY s.subject_id ";
		return $db->fetchAll($sql);
	}
	public function getStundetScoreGroup($search)
	{ // List លទ្ធផលដែលបានបញ្ចូលទាំងអស់មក
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$month = "month_kh";
			$branch = "branch_namekh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$month = "month_en";
			$branch = "branch_nameen";
		}
		$sql = "SELECT 
   				s.`id`,
   				s.`group_id`,
   				g.`group_code`,
		   		(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as examtype,
		   		title_score,s.for_semester,s.note,
		   		(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_year,
		 		(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree, 
			 	(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
			 	`g`.`semester` AS `semester`,
			 	(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name, 
			 	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1) AS `room_name`, 
			 	(SELECT $label FROM `rms_view`	WHERE (`rms_view`.`type` = 4 AND `rms_view`.`key_code` = `g`.`session`) LIMIT 1) AS `session`,
			 	(SELECT first_name FROM rms_users as u where u.id = s.user_id LIMIT 1) as user_name,
			 	s.date_input,
			 	CASE
					WHEN s.exam_type = 2 THEN ''
					ELSE (SELECT $month FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) 
				END as for_month,
			 	s.exam_type,
			 	s.for_semester,
			  	s.reportdate,
				s.date_input
				,s.isPublic
				,(SELECT first_name FROM rms_users as u where u.id = s.publicBy LIMIT 1) as publicByUserName
   			FROM 
   				`rms_score` AS s,
   				`rms_group` AS g 
   			WHERE 
   				g.`id`=s.`group_id` 
   				AND s.status = 1 ";

		$where = '';
		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " s.title_score LIKE '%{$s_search}%'";
			$s_where[] = " g.group_code LIKE '%{$s_search}%'";
			$s_where[] = " s.note LIKE '%{$s_search}%'";
			$s_where[] = " s.for_semester LIKE '%{$s_search}%'";
			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_id'])) {
			$where .= " AND s.branch_id =" . $search['branch_id'];
		}
		if (!empty($search['group'])) {
			$where .= " AND g.id =" . $search['group'];
		}
		if (!empty($search['academic_year'])) {
			$where .= " AND s.for_academic_year =" . $search['academic_year'];
		}
		if ($search['degree'] > 0) {
			$where .= " AND `g`.`degree` =" . $search['degree'];
		}
		if ($search['for_month'] > 0) {
			$where .= " AND s.for_month =" . $search['for_month'];
		}
		if ($search['grade'] > 0) {
			$where .= " AND `g`.`grade` =" . $search['grade'];
		}
		if ($search['session'] > 0) {
			$where .= " AND `g`.`session` =" . $search['session'];
		}
		if ($search['room'] > 0) {
			$where .= " AND `g`.`room_id` =" . $search['room'];
		}
		if ($search['exam_type'] > 0) {
			$where .= " AND s.exam_type =" . $search['exam_type'];
		}
		if ($search['for_semester'] > 0) {
			$where .= " AND s.for_semester =" . $search['for_semester'];
		}
		if ($search['score_result_status'] > -1) {
			$where .= " AND s.isPublic =" . $search['score_result_status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission('s.branch_id');
		$where .= $dbp->getDegreePermission('g.degree');

		$order = " ORDER BY s.id DESC,g.`id` DESC ,s.for_academic_year,s.for_semester,s.for_month ";
		return $db->fetchAll($sql . $where . $order);
	}
	public function getStundetScoreDetailGroup($id = null,$param=null)
	{ // លទ្ធផលប្រចាំខែលម្អិតតាមមុខវិជ្ជា//
		$db = $this->getAdapter();

		$group = empty($param['group_id']) ? 0 : $param['group_id'];
		$for_academic_year = empty($param['for_academic_year']) ? 0 : $param['for_academic_year'];
		$degree = empty($param['degree']) ? 0 : $param['degree'];
		$exam_type = empty($param['exam_type']) ? 0 : $param['exam_type'];
		$semesterTotalAverage = empty($param['semesterTotalAverage']) ? 0 : $param['semesterTotalAverage'];

		$sql = "SELECT
		   	sml.`score_id` AS id,
			sml.`student_id`,
		   	st.`stu_code`,
			st.stu_khname,
			CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,'')) AS stu_enname,	
		   	st.`sex`,
			sml.total_score,
			sml.`total_avg` AS average,
			sml.`monthlySemesterAvg`,
			sml.overallAssessmentSemester,
			sml.totalMaxScore,
			sml.`type`,
			vvv.`jsonScoreSubjectDetail`,
			(SELECT vs.overallAssessmentSemester FROM  v_overall_semester_score AS vs WHERE 
				vs.`for_semester` = 1 
				AND vs.student_id = sml.`student_id`
				AND vs.group_id = $group LIMIT 1) AS overalSemester1,	
			(SELECT vs.overallAssessmentSemester FROM  v_overall_semester_score AS vs WHERE 
				vs.`for_semester` = 2
				AND vs.student_id = sml.`student_id`
				AND vs.group_id = $group LIMIT 1) AS overalSemester2
		  ";
		  if($exam_type==1){
				$sql .= "	,(SELECT CONCAT(sd.metion_grade,',',sd.metion_in_khmer,',',sd.mention_in_english)
					FROM `rms_metionscore_setting_detail` AS sd,
						`rms_metionscore_setting` AS s
					WHERE s.id = sd.metion_score_id
						AND s.academic_year= $for_academic_year
						AND s.degree = $degree
						AND (sml.total_score/sml.totalMaxScore*100) >=sd.max_score 
						ORDER BY sd.max_score DESC
					LIMIT 1  ) AS mentionScore
				";
		  	}else{
				$sql .= "	,(SELECT CONCAT(sd.metion_grade,',',sd.metion_in_khmer,',',sd.mention_in_english)
					FROM `rms_metionscore_setting_detail` AS sd,
						`rms_metionscore_setting` AS s
					WHERE s.id = sd.metion_score_id
						AND s.academic_year= $for_academic_year
						AND s.degree = $degree
						AND (sml.overallAssessmentSemester/$semesterTotalAverage*100) >=sd.max_score 
						ORDER BY sd.max_score DESC
					LIMIT 1  ) AS mentionScore
				";
			}

		$sql .= " FROM 
   			`rms_score_monthly` AS sml
			LEFT JOIN  `rms_student` AS st ON st.`stu_id` = sml.`student_id`
			LEFT JOIN `v_student_scordetail` AS vvv ON vvv.`score_id` = sml.`score_id` AND vvv.`student_id` = sml.`student_id`
   		WHERE 1 ";
		   $sql .= " AND sml.score_id = $id ";
	
		$where = '';
		$order = "  ORDER BY  ";
		
		if ($exam_type == 1) {
			$order .= " sml.total_score  DESC ";
		} else {
			$order .= " sml.overallAssessmentSemester DESC ";
		}
		$order .= " ,sml.`student_id` ASC	";
	//	echo $sql . $where . $order; exit();
		return $db->fetchAll($sql . $where . $order);
	}
	public function getAllStudentIdByScoreResult($search, $scoreId = null, $limit = 0)
	{ // rptMonthlytranscript
		$db = $this->getAdapter();

		$sql = "SELECT
		   	sm.`student_id` AS stu_id,
		   	sm.score_id AS id
	   	FROM
		   	`rms_score_monthly` AS sm
	   	WHERE 1 ";

		if (!empty($scoreId)) {
			$sql .= " AND sm.score_id = $scoreId ";
		}
		if (!empty($search['stu_id'])) {
			$sql .= " AND sm.`student_id` IN (" . $search['stu_id'] . ")";
		}
		$order = "  GROUP BY 
	   						sm.score_id,
	   						sm.`student_id`
					   		ORDER BY total_score DESC ";
		if ($limit == 2) {
			$limit = " limit 5";
		} else {
			$limit = " ";
		}
		return $db->fetchAll($sql . $order . $limit);
	}
	public function getStudentScoreResult($search, $id = null, $limit = 0)
	{ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត/outstanding photo and no photo
		//for view in page assessment/ rptScoreResult/rptMonthlytranscript/monthlyOutstandingStudent/monthlyOutstandingStudentNophoto/examscorepdf/
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
			$month = "month_kh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$month = "month_en";
		}
		$search['exam_type'] = empty($search['exam_type']) ? 0 : $search['exam_type'];
		$sql = "
			SELECT
				(SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_name,
				(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_logo,
				(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh,
				(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng,
				
				g.`branch_id`,
				g.`group_code`,
				`g`.`degree` as degree_id,
				`g`.`semester` AS `semester`,
				
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS start_year,
				(SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS end_year,
				(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				(SELECT teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
				(SELECT signature from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher_sigature,

				
				CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,'')) AS stu_enname,
				st.`stu_id`,
				st.`sex`,
				st.photo,
				st.`stu_code`,
				st.stu_khname,
				st.last_name,
		 
		   	
			   	smd.`id`,
			   	smd.for_academic_year,
		   		smd.`group_id`,
		   		(SELECT month_kh FROM rms_month WHERE rms_month.id = smd.for_month LIMIT 1) AS for_month,
			   	smd.exam_type,
			   	smd.for_semester,
			   	smd.for_month as for_month_id,
			   	smd.reportdate,
				smd.date_input,
				smd.principalId,
				smd.academicStaffId,

				


			   	smd.title_score,
				smd.title_score_en,
			   	smd.max_score,
			   	
			smd.`student_id`,
		   	smd.total_score,
		    smd.total_avg,
		    smd.totalMaxScore,
		    (smd.totalMaxScore/2) as passScore
		 ";
		if ($search['exam_type'] == 2) {
			$sql .= "
			,g.semesterTotalAverage as semesterScal
			,(g.semesterTotalAverage/2) as passAverage
			,smd.totalKhAvg
			,smd.totalEnAvg
			,smd.totalChAvg
			,smd.OveralAvgKh
			,smd.OveralAvgEng
			,smd.OveralAvgCh
			,smd.monthlySemesterAvg
			,smd.overallAssessmentSemester
			,FIND_IN_SET( 
				COALESCE(smd.overallAssessmentSemester,'0'), 
				(    
					SELECT 
						GROUP_CONCAT( dd.overallAssessmentSemester ORDER BY dd.overallAssessmentSemester DESC ) 
					FROM rms_score_monthly AS dd 
					WHERE  dd.`score_id`= smd.id
				)
			) AS ranking
			,(SELECT CONCAT(sd.metion_grade,',',sd.metion_in_khmer,',',sd.mention_in_english)
				FROM `rms_metionscore_setting_detail` AS sd,
					`rms_metionscore_setting` AS s
				WHERE s.id = sd.metion_score_id
					AND s.academic_year= smd.for_academic_year
					AND s.degree = `g`.`degree`
					AND (smd.overallAssessmentSemester/g.semesterTotalAverage*100) >=sd.max_score 
					ORDER BY sd.max_score DESC
				LIMIT 1  ) AS mentionScore
			";
		} else if ($search['exam_type'] == 3) {
			$sql .= "
			,g.semesterTotalAverage as semesterScale
			,(SELECT vs.overallAssessmentSemester FROM  v_overall_semester_score AS vs WHERE 
				vs.`for_semester` = 1 
				AND vs.student_id = smd.`student_id`
				AND vs.group_id = smd.`group_id` LIMIT 1) AS overalSemester1
			,(SELECT vs.overallAssessmentSemester FROM  v_overall_semester_score AS vs WHERE 
				vs.`for_semester` = 2
				AND vs.student_id = smd.`student_id`
				AND vs.group_id = smd.`group_id` LIMIT 1) AS overalSemester2

			,(g.semesterTotalAverage/2) as passAverage
			,smd.overallAssessmentSemester
			,FIND_IN_SET( 
				COALESCE(smd.overallAssessmentSemester,'0'), 
				(    
					SELECT 
						GROUP_CONCAT( dd.overallAssessmentSemester ORDER BY dd.overallAssessmentSemester DESC ) 
					FROM rms_score_monthly AS dd 
					WHERE  dd.`score_id`= smd.id
				)
			) AS ranking
			,(SELECT CONCAT(sd.metion_grade,',',sd.metion_in_khmer,',',sd.mention_in_english)
				FROM `rms_metionscore_setting_detail` AS sd,
					`rms_metionscore_setting` AS s
				WHERE s.id = sd.metion_score_id
					AND s.academic_year= smd.for_academic_year
					AND s.degree = `g`.`degree`
					AND (smd.overallAssessmentSemester/g.semesterTotalAverage*100) >=sd.max_score 
					ORDER BY sd.max_score DESC
				LIMIT 1  ) AS mentionScore
			";
		}else if($search['exam_type'] == 1){
			$sql .= "
			,(SELECT CONCAT(sd.metion_grade,',',sd.metion_in_khmer,',',sd.mention_in_english)
				FROM `rms_metionscore_setting_detail` AS sd,
					`rms_metionscore_setting` AS s
				WHERE s.id = sd.metion_score_id
					AND s.academic_year= smd.for_academic_year
					AND s.degree = `g`.`degree`
					AND (smd.total_score/smd.totalMaxScore*100) >=sd.max_score 
					ORDER BY sd.max_score DESC
				LIMIT 1  ) AS mentionScore
			";
		}

		$sql .= " FROM
		   v_score_ft_scoredetail AS smd 
		   LEFT JOIN  `rms_student` AS st  ON st.`stu_id`=smd.`student_id`
		   LEFT JOIN `rms_group` AS g ON  g.`id` = smd.`group_id`
   		WHERE 1 ";


		if (!empty($id)) {
			$sql .= " AND smd.id = $id ";
		}
		$where = '';
		if (!empty($search['branch_id'])) {
			$where .= " AND g.`branch_id` =" . $search['branch_id'];
		}
		if (!empty($search['study_year'])) {
			$where .= " AND smd.for_academic_year =" . $search['study_year'];
		}
		if (!empty($search['group'])) {
			$where .= " AND smd.group_id =" . $search['group'];
		}
		if (!empty($search['exam_type'])) {
			$where .= " AND smd.exam_type =" . $search['exam_type'];
			if ($search['exam_type'] == 1) {
				if (!empty($search['for_month'])) {
					$where .= " AND smd.for_month =" . $search['for_month'];
				}
			} else if ($search['exam_type'] == 2) {
				if (!empty($search['for_semester'])) {
					$where .= " AND smd.for_semester =" . $search['for_semester'];
				}
			}
		}
		if (!empty($search['degree'])) {
			$where .= " AND `g`.`degree` =" . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= " AND `g`.`grade` =" . $search['grade'];
		}
		if (!empty($search['session'])) {
			$where .= " AND `g`.`session` =" . $search['session'];
		}
		if (!empty($search['stu_id'])) {
			$where .= " AND 	st.`stu_id` IN (" . $search['stu_id'] . ")";
		}

		$where .= $_db->getAccessPermission('st.branch_id');
		$where .= $_db->getDegreePermission('g.degree');

		$order = "  ORDER BY ";
		if ($search['exam_type'] == 2 or $search['exam_type'] == 3) {
			$order .= " smd.overallAssessmentSemester DESC";
		} else {
			$order .= " smd.total_score  DESC ";
		}
		$order .= " ,smd.for_academic_year,smd.for_semester,smd.for_month,smd.`group_id`,smd.`student_id` ASC ";

		if ($limit == 2) {
			$limit = " limit 6";
		} else {
			$limit = " ";
		}
			//echo $sql . $where . $order . $limit;exit();
		return $db->fetchAll($sql . $where . $order . $limit);
	}
	public function getStundetScorebySemester($group_id, $semester)
	{ // សម្រាប់ លទ្ធផលឆមាសទី១ និង 
		$db = $this->getAdapter();
		$sql = " SELECT
			   	st.`stu_code`,
			   	st.`stu_enname`,
			   	st.`last_name`,
			   	st.`stu_khname`,
			   	st.`sex`,
			   	st.photo,			   	 
			   	g.id AS `group_id`,
			   	g.`group_code`,
			   	g.`semester` AS `semester`,
			   	g.branch_id,
			   	g.max_average,
			    $semester as for_semester,
				(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_logo,
				(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS school_nameen,
				(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS school_namekh,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS start_year,
				(SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS end_year,
					
			   	(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id=g.teacher_id LIMIT 1) AS teacher_name,
			   	(SELECT s.date_input 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=$semester
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=2
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1) AS date_input,
			   	(SELECT sm.total_avg 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=$semester
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=2
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1) AS avg_forsemester,
			   			
			   	(SELECT AVG(sm.total_avg) 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=$semester
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=1
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1) AS avg_formonth
			   	FROM
				   	`rms_student` AS st,
				   	`rms_group` AS g,
				   	`rms_group_detail_student` AS gs
			   	WHERE
					gs.itemType=1 
					AND st.`stu_id` = gs.`stu_id`
				   	AND g.`id`= gs.`group_id`
				   	AND g.id = $group_id "; //only month
		$sql .= " AND gs.`stop_type`=0 ";

		$where = '';
		$order = "GROUP BY gs.`stu_id` ORDER BY ((avg_forsemester+avg_formonth)/2) DESC,g.academic_year,g.semester ASC ";
		return $db->fetchAll($sql . $where . $order);
	}
	public function getStundetScorebyYear($group_id)
	{ // score result for yearly
		$db = $this->getAdapter();
		$sql = " SELECT
			   	st.`stu_code`,
			   	st.`stu_enname`,
			   	st.`last_name`,
			   	st.`stu_khname`,
			   	st.`sex`,
			   	st.photo,			   	 
			   	g.id AS `group_id`,
			   	g.`group_code`,
			   	g.branch_id,
			   	g.max_average,
			    (SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_logo,
			    (SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS school_nameen,
				(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS school_namekh,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS start_year,	
				(SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS end_year,
				
			   	(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id=g.teacher_id LIMIT 1) AS teacher_name,
			   	(SELECT s.date_input 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=1
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=1
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1) as date_input,
			
			   	IFNULL((SELECT sm.total_avg 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=1
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=2
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1),0) AS avg_forsemester1,
			   			
			   	IFNULL((SELECT AVG(sm.total_avg) 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=1
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=1
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1),0) AS avg_formonthsemester1,
			   			
			   	IFNULL((SELECT sm.total_avg 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=2
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=2
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1),0) AS avg_forsemester2,
			   			
			   	IFNULL((SELECT AVG(sm.total_avg) 
			   		FROM `rms_score_monthly` AS sm,
			   			  rms_score s
			   			WHERE 
			   					s.id = sm.score_id 
			   					AND s.for_semester=2
			   					AND s.`group_id`=$group_id
			   					AND s.exam_type=1
			   					AND sm.student_id=gs.stu_id 
			   			LIMIT 1),0) AS avg_formonthsemester2
			   	FROM
				   	`rms_student` AS st,
				   	`rms_group` AS g,
				   	`rms_group_detail_student` AS gs
			   	WHERE
					gs.itemType=1 
					AND st.`stu_id` = gs.`stu_id`
				   	AND g.`id`= gs.`group_id`
				   	AND g.id = $group_id ";
		$sql .= " AND gs.`stop_type`=0 ";
		$where = '';
		$order = "GROUP BY gs.`stu_id` ORDER BY ((((avg_forsemester1+avg_formonthsemester1)/2)+((avg_forsemester2+avg_formonthsemester2)/2))/2) DESC,g.academic_year,g.semester ASC ";

		return $db->fetchAll($sql . $where . $order);
	}
	public function getAcadimicByStudentHeader($group_id, $student_id)
	{ // fro ព្រឹត្តប័ត្រពិន្ទុឆ្នាំសិក្សា ក្បាល I+II
		$db = $this->getAdapter();
		$sql = "
	   	SELECT
	   	s.`id`,
	   	sd.`group_id`,
	   	g.`group_code`,
	   	st.`stu_code`,
	   	st.`stu_enname`,
	   	st.`stu_khname`,
	   	st.`sex`,
	   	st.dob,
		(SELECT COUNT(stu_id) FROM `rms_group_detail_student` WHERE itemType=1 AND group_id=s.group_id LIMIT 1) AS amount_student,
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
	   	AND s.status = 1
	   	AND g.id= $group_id
	   	AND sd.student_id=$student_id ";
		$where = '';
		$order = " GROUP BY sd.subject_id ORDER BY s.for_semester ASC,sd.subject_id,s.for_academic_year,s.for_semester ASC ";
		return $db->fetchAll($sql . $where . $order);
	}
	public function getAcadimicByStudentSubject($group_id, $semester_id, $subject_id, $student_id)
	{ // fro ព្រឹត្តប័ត្រពិន្ទុឆ្នាំសិក្សា I+II លម្អិត
		$db = $this->getAdapter();
		$sql = "SELECT 
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
				 	)
				) AS rank
				FROM 
					`rms_score` AS s,
				   	`rms_score_detail` AS sd,
				   	`rms_group` AS g
				WHERE s.`id`=sd.`score_id` 
					AND g.`id`=s.`group_id`
				   	AND s.status = 1
				   	AND g.id= $group_id
				   	AND s.for_semester= $semester_id
				   	AND sd.subject_id= $subject_id
				   	AND sd.student_id= $student_id
				   	AND s.exam_type=2
					GROUP BY sd.subject_id ";
		$where = ' ';
		return $db->fetchRow($sql . $where);
	}
	function getRankStudentbyGroupSemester($group_id, $semester, $student_id)
	{ //ចំណាត់ថ្នាក់ប្រឡង ឆមាសទី១/២ តាមសិស្ស(transcript)
		$sql = "SELECT 
			SUM(score) AS total_score,
   			 FIND_IN_SET( score, (    
			SELECT GROUP_CONCAT( score
			ORDER BY score DESC ) 
			FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
				ss.`id`=dd.`score_id` 
				AND ss.exam_type=2
				AND ss.for_semester=$semester
				AND ss.group_id= $group_id
			 	)
			) AS rank
			FROM 
				`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
			WHERE s.`id`=sd.`score_id` 
				AND g.`id`=s.`group_id`
			   	AND s.status = 1
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
	public function getStundetExamById($group_id, $semester, $student_id)
	{ // ប្រើសំរាប់រកមធ្យមភាគ សម្រាប់សិស្ស ១ ប្រើព្រឹត្តប័ត្រពិន្ទុឆ្នាំ
		$db = $this->getAdapter();
		$sql = "
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
			   	AND sdd.student_id = $student_id
			   	GROUP BY sdd.student_id LIMIT 1
			) AS avg_exam
		
		   	FROM `rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
		   	WHERE
		   	s.`id`=sd.`score_id`
		   	AND g.`id`=s.`group_id`
		   	AND s.status = 1
		   	AND g.id= $group_id
		   	AND s.for_semester=$semester
		   	AND sd.student_id=$student_id
		   	AND s.exam_type=1 ";
		$where = '';
		$order = " GROUP BY sd.`student_id`,s.for_semester ORDER BY sd.`student_id`";
		// 	   		echo $sql.$where.$order;exit();
		return $db->fetchRow($sql . $where . $order);
	}
	function getRankingSemesterByStudent($group_id, $semester_id, $student_id)
	{ //ចំណាត់ថ្នាក់សំរាប់ឆមាសនីមួយៗ

		$sql = "SELECT 
   			 FIND_IN_SET( total_score, (    
			SELECT GROUP_CONCAT( total_score
			ORDER BY total_score DESC ) 
			FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
				ss.`id`=dd.`score_id` 
				AND ss.exam_type=1
				AND ss.for_semester= $semester_id
				AND ss.group_id= $group_id
			 	)
			) AS rank,
			SUM(score) as total_score
			FROM 
				`rms_score` AS s,
			   	`rms_score_detail` AS sd,
			   	`rms_group` AS g
			WHERE s.`id`=sd.`score_id` 
				AND g.`id`=s.`group_id`
			   	AND s.status = 1
			   	AND g.id=$group_id
			   	AND s.for_semester= $semester_id
			   	AND sd.student_id= $student_id
			   	AND s.exam_type=2
				GROUP BY sd.student_id ";
		return $this->getAdapter()->fetchRow($sql);
	}
	public function getSubjectScoreGroup($group_id, $teacher_id = null, $exam_type = 1)
	{
		$db = new Issue_Model_DbTable_DbScore();
		$data = array(
			'group_id' => $group_id,
			'teacher_id' => $teacher_id,
			'exam_type' => $exam_type
		);
		return $db->getSubjectScoreByGroup($data);
	}

	public function getScoreBySubjectTranscript($score_id, $student_id, $subject_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
	     sd.`score`,
	     sd.score_cut,
	     sd.`subject_id`
	     ,sd.amount_subject
	 FROM  `rms_score_detail` AS sd
	 	WHERE sd.`score_id`=$score_id 
   		AND sd.`student_id`=$student_id  
   		AND sd.`subject_id`=$subject_id ";
		return $db->fetchRow($sql);
	}
	public function getScoreBySubjectJason($score_id ,$student_id)
	{
		$db = $this->getAdapter();
		$sql=" SELECT jsonScoreSubjectDetail FROM  v_student_scordetail 
		WHERE `score_id`=$score_id 
		AND `student_id`=$student_id ";
		//echo $sql; exit();
		$result = $db->fetchOne($sql);
		//print_r($result);
		return  json_decode($result,true );
		
	}
	function getAllgroupStudyNotPass()
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
	   		`g`.`id` as id, CONCAT(`g`.`group_code`,' ',
	   		(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1)) AS name
	   	FROM `rms_group` AS `g` WHERE g.status =1 ";
		return $db->fetchAll($sql);
	}

	//---------------gep score report
	public function getSubjectScoreGroupGEP($group_id, $type_score = null)
	{ // for gep
		$db = $this->getAdapter();
		$sql = "SELECT
	   	s.`id`,sd.`group_id`,sd.`student_id`,sj.`subject_titlekh`,sd.`score`,s.`reportdate`,sd.`subject_id`
	   	FROM `rms_score` AS s,
	   	`rms_score_detail` AS sd,
	   	`rms_subject` AS sj
	   	WHERE s.`id`=sd.`score_id`
	   	AND sj.`id`=sd.`subject_id`
	   	AND sd.`group_id`=$group_id
	   	";
		if ($type_score == 2 || $type_score == 3) {
			$sql .= " AND sd.`subject_id` !=9";
		}
		$sql .= ' GROUP BY sd.`subject_id`';
		return $db->fetchAll($sql);
	}


	function getAmountWeeklyscoreByGroup($group_id, $monthly_score)
	{
		$db = $this->getAdapter();
		$sql = "SELECT COUNT(s.id) AS amount_weeklyscore
	 FROM  	`rms_score` AS s 
	 WHERE s.`group_id`=$group_id  AND DATE_FORMAT(s.`reportdate`,'%Y-%m')=DATE_FORMAT('$monthly_score','%Y-%m')";
		return $db->fetchOne($sql);
	}



	public function getStundentEnglishSemesterScore($search)
	{ // for rpt-semester-evaluation
		$db = $this->getAdapter();
		$sql = "SELECT
   	s.`id`,sd.`group_id`,g.`group_code`,
   	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
   	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`)  AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
   	
   	`g`.`semester` AS `semester`,
   	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
   	(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
   	AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
   	sd.`student_id`,st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`,s.`reportdate`,DATE_FORMAT(s.`reportdate`,'%Y-%m') AS month_of_semester,
   	g.`start_date`
   	FROM `rms_score` AS s,
   	`rms_score_detail` AS sd,
   	`rms_student` AS st,
   	`rms_group` AS g
   	WHERE s.`id`=sd.`score_id` AND st.`stu_id`=sd.`student_id`
   	AND g.`id`=sd.`group_id` AND s.status = 1  AND `g`.`degree` NOT IN (1,2)
   	";
		$where = '';
		$from_date = (empty($search['start_date'])) ? '1' : " s.reportdate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " s.reportdate <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['group_name'])) {
			$where .= " AND sd.group_id =" . $search['group_name'];
		}
		if (!empty($search['degree_english'])) {
			$where .= " AND `g`.`degree` =" . $search['degree_english'];
		}
		if (!empty($search['study_year'])) {
			$where .= " AND g.academic_year =" . $search['study_year'];
		}
		if (!empty($search['grade_english'])) {
			$where .= " AND `g`.`grade` =" . $search['grade_english'];
		}
		if (!empty($search['session'])) {
			$where .= " AND `g`.`session` =" . $search['session'];
		}
		$order = "  GROUP BY sd.`student_id` ORDER BY s.`reportdate`,sd.`group_id`,sd.`student_id` ASC";
		return $db->fetchAll($sql . $where . $order);
	}


	function getSubjectScorebystudentandgroup($group_id, $student_id)
	{ //certificate of foundation year
		$db = $this->getAdapter();
		$sql = "SELECT
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
		 		AND g.`id`=s.`group_id`
		 		AND sd.`group_id`=$group_id 
		 		AND st.`stu_id`=$student_id 
		   ORDER BY 
		   		s.`for_semester` ASC ";
		return $db->fetchAll($sql);
	}

	function getExamByExamIdAndStudent($data)
	{
		$db = $this->getAdapter();
		$sql = "
    	SELECT
	    	s.`id`,
	    	(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
	    	(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_monthen,
	    	s.exam_type,
	    	s.for_month AS for_month_id,
	    	s.for_semester,
	    	s.reportdate,
	    	s.title_score,
			s.title_score_en,
	    	s.max_score,
	    	sd.`student_id`,
	    	sm.total_score,
	    	sm.total_avg,
	    	FIND_IN_SET( total_avg, 
	    		(
			    	SELECT GROUP_CONCAT( total_avg ORDER BY total_avg DESC )
			    	FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
			    	ss.`id`=dd.`score_id`
			    	AND ss.group_id= s.`group_id`
			    	AND ss.id=s.`id`
	    		)
	    	) AS rank,
	    	(SELECT count(ss.`id`) FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
			    	ss.`id`=dd.`score_id`
			    	AND ss.group_id= s.`group_id`
			    	AND ss.id=s.`id` LIMIT 1) as amountStudent,
	    	vst.*,
	    	(SELECT rms_group.group_code FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) AS group_code,
	    	gds.group_id AS group_id,
	    	(SELECT t.`teacher_name_kh` FROM `rms_teacher` t WHERE t.id =(SELECT rms_group.teacher_id FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) LIMIT 1) AS teacher,
	    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gds.academic_year LIMIT 1) as academic_year,
	    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
	    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    	gds.degree AS degree_id,
	    	gds.academic_year AS for_academic_year,
	    	(SELECT br.school_namekh FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_namekh,
	    	(SELECT br.school_nameen FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_nameen,
	    	(SELECT br.photo FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS photo_branch
    	FROM
    	`rms_score` AS s,
    	`rms_score_detail` AS sd,
    	`rms_score_monthly` AS sm,
    
    	rms_student AS vst,
    	rms_group_detail_student AS gds
    	WHERE 
		gds.itemType=1 
		AND s.`id`=sd.`score_id`
    	AND vst.stu_id = sm.`student_id`
    	AND vst.stu_id = sd.`student_id`
    	AND vst.stu_id = gds.`stu_id`
    	AND s.group_id = gds.`group_id`
    
    	AND s.`id`=sm.`score_id`
    	AND s.status = 1
    	
    	";
		if (!empty($data['group_id'])) {
			$sql .= " AND gds.`group_id`=" . $data['group_id'];
		}
		if (!empty($data['stu_id'])) {
			$sql .= " AND vst.`stu_id`=" . $data['stu_id'];
		}
		if (!empty($data['exam_type'])) {
			$sql .= " AND s.exam_type=" . $data['exam_type'];

			if ($data['exam_type'] == 1) {
				if (!empty($data['for_month'])) {
					$sql .= " AND s.`for_month`=" . $data['for_month'];
				}
			} else if ($data['exam_type'] == 2) {
				if (!empty($data['for_semester'])) {
					$sql .= " AND s.`for_semester`=" . $data['for_semester'];
				}
			}
		}
		$sql .= " ORDER BY s.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getRankSubjectMonthlyExam($group_id, $stu_id, $subject_id, $formonth)
	{
		$db = $this->getAdapter();
		$sql = "
   			SELECT 
				score,
				SUM(score) AS total_score,
				 FIND_IN_SET( score, (    
				SELECT GROUP_CONCAT( score
				ORDER BY score DESC ) 
				FROM rms_score_detail AS dd ,rms_score AS ss WHERE  
					ss.`id`=dd.`score_id` 
					AND ss.exam_type=1
					AND ss.group_id= $group_id
					AND dd.subject_id=$subject_id
					AND ss.for_month=$formonth
					)
				) AS rank,
				(SELECT gs.max_score FROM rms_group_subject_detail as gs WHERE gs.group_id=$group_id AND gs.subject_id=$subject_id LIMIT 1) as subjectMaxScore
				FROM 
					`rms_score` AS s,
					`rms_score_detail` AS sd,
					`rms_group` AS g
				 WHERE s.`id`=sd.`score_id` 
					AND g.`id`=s.`group_id`
					AND s.status = 1
					AND s.exam_type=1
					AND g.id= $group_id
					AND sd.subject_id= $subject_id
					AND sd.student_id= $stu_id
					AND s.for_month=$formonth
			ORDER BY s.id DESC
   		";
		return $db->fetchRow($sql);
	}
	function getAverageMonthlyForSemester($group_id, $semester, $stu_id)
	{
		$db = $this->getAdapter();
		$sql = "
	   		SELECT 
				v.*,
				FIND_IN_SET( total_avg, (    
				SELECT GROUP_CONCAT( total_avg
				ORDER BY total_avg DESC ) 
				FROM v_average_semster_monthly_exam AS dd WHERE  
					
					 dd.group_id=v.group_id
					AND dd.for_semester = v.for_semester
					)
				) AS rank
				 FROM `v_average_semster_monthly_exam` AS v
				WHERE v.group_id=$group_id
				AND v.stu_id=$stu_id
				AND v.for_semester =$semester
   		";
		return $db->fetchRow($sql);
	}
	function getAverageSemesterFull($group_id, $semester, $stu_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT v.*,
			FIND_IN_SET( average_semester_score, (    
			SELECT GROUP_CONCAT( average_semester_score
			ORDER BY average_semester_score DESC ) 
			FROM v_average_semester_full AS dd WHERE  	
				 dd.group_id=v.group_id
				AND dd.for_semester =v.for_semester
				)
			) AS rank 
			FROM `v_average_semester_full` AS v
			WHERE v.for_semester = $semester
			AND v.group_id =$group_id
			AND v.stu_id = $stu_id
			LIMIT 1";
		return $db->fetchRow($sql);
	}
	function countStudentAttendenceBYtype($group_id, $student, $attendence_status, $monthly = null, $for_semester = null, $data = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
				COUNT(satd.id) AS attendence
			FROM 
				`rms_student_attendence` AS sat,
				`rms_student_attendence_detail` AS satd
			WHERE sat.id = satd.attendence_id
				AND sat.type=1
				AND satd.stu_id=$student 
				AND sat.group_id =$group_id
				AND satd.attendence_status=$attendence_status
			   		";
		if (!empty($for_semester)) {
			$sql .= " AND sat.for_semester=" . $for_semester;
		}

		if (!empty($data['semester'])) {
			$sql .= " AND sat.for_semester=" . $data['semester'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND satd.group_id=" . $data['groupId'];
		}
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
		}

		if (!empty($monthly)) {
			$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $monthly;
		}

		$sql .= " LIMIT 1";
		return $db->fetchOne($sql);
	}

	function getAllGroupOfStudent($stu_id)
	{
		$db = $this->getAdapter();
		$sql = "
   			SELECT 
				g.id,
				CONCAT(g.group_code,' (',(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1),')' ) AS `name`
				FROM 
				`rms_group_detail_student`  AS gds,
				`rms_group` AS g
				WHERE 
				gds.itemType=1 
				AND g.id = gds.group_id
				AND g.status=1
				AND gds.stu_id =$stu_id
   		";
		return $db->fetchAll($sql);
	}

	//for get score by id
	function getScoreExamByID($score_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
	   	s.*,
		g.degree,
		g.group_code,
		g.semesterTotalAverage,
		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS academic_year,
		(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonth,

		(SELECT teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
		(SELECT signature from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher_sigature

		 FROM
	   	`rms_score` AS s
		LEFT JOIN rms_group AS g ON g.id=s.group_id
	   	WHERE s.id = $score_id 
	    LIMIT 1";
		return $db->fetchRow($sql);
	}
	// (SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_name,
	// (SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_logo,
	// (SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh,
	// (SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng,	
  


	function getAllStudentPassed($search)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
		}
		$sql = "SELECT
				   	gds.`stu_id`,
				   	gscg.`from_group`,
				   	gscg.`to_group`,
				   	st.stu_code,
				   	st.stu_khname,
				   	st.last_name,
				   	st.stu_enname,
				   	(SELECT $label FROM rms_view WHERE rms_view.`type`=2 AND rms_view.`key_code`=st.sex Limit 1) AS sex,
				   	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (SELECT rms_group.academic_year FROM rms_group WHERE rms_group.id=gscg.`from_group` LIMIT 1) LIMIT 1) AS academic_year,
				   	(SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=(SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gscg.`from_group`) limit 1) AS grade,
				   	(select $label from rms_view where rms_view.type=4 and key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gscg.`from_group`) Limit 1) AS session,
				   	(SELECT group_code from rms_group WHERE rms_group.id=gscg.from_group limit 1) AS from_group_code,
				   
				   	gscg.`to_group` ,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (SELECT rms_group.academic_year FROM rms_group WHERE rms_group.id=gscg.`to_group` LIMIT 1) LIMIT 1) AS to_academic_year,
				   	
				   	
				   	(SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=g.grade limit 1) AS to_grade,
				   
				   	(select $label from rms_view where rms_view.type=4 and key_code=g.session Limit 1) AS to_session,
				   	(select $label from rms_view where type=17 and key_code=gscg.change_type Limit 1) as change_type,
				   	g.group_code as to_group_code
				FROM
				   `rms_group_detail_student` AS gds
				   	JOIN `rms_group_student_change_group` AS gscg ON  gds.`group_id` = gscg.`to_group` AND gds.`old_group` = gscg.`from_group`
				   	LEFT JOIN rms_group AS g ON gscg.to_group=g.id
				   	LEFT JOIN rms_student AS st ON gds.stu_id=st.stu_id
				WHERE
					gds.itemType=1 
				   	AND gds.`group_id` = gscg.`to_group`
				   	AND gds.`old_group` = gscg.`from_group`
				   	and gscg.to_group=g.id
				   	and gds.stu_id=st.stu_id
				   	and gscg.change_type=2
	   		";

		$order = " ORDER BY gscg.`id` ASC ";

		//$groupby=" GROUP BY g.`academic_year`,g.`grade`,g.`session`";
		$where = '';
		//echo $sql;exit();
		if (empty($search)) {
			return $db->fetchAll($sql . $order);
		}

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (select CONCAT(from_academic,'-',to_academic,' ',generation) from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=g.session) LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND st.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['grade_bac'])) {
			$where .= ' AND g.grade=' . $search['grade_bac'];
		}
		if (!empty($search['session'])) {
			$where .= ' AND g.session=' . $search['session'];
		}
		if (!empty($search['change_id'])) {
			$where .= ' AND gscg.id=' . $search['change_id'];
		}

		$row = $db->fetchAll($sql . $where . $order);
		if ($row) {
			return $row;
		}
	}

	function getAllStudentFailed($search, $from_group)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
		}
		$sql = "SELECT
			   		gds.`stu_id`,
			   		gscg.`from_group`,
			   		st.stu_code,
			   		st.stu_khname,
			   		st.last_name,
			   		st.stu_enname,
			   		(SELECT $label FROM rms_view WHERE rms_view.`type`=2 AND rms_view.`key_code`=st.sex Limit 1) AS sex
			   	FROM
			   		`rms_group_detail_student` AS gds,
			   		`rms_group_student_change_group` AS gscg,
			   		rms_group as g,
			   		rms_student as st
			   	WHERE
					gds.itemType=1 
			   		and gds.`group_id` = gscg.`from_group`
			   		and gds.`group_id` = $from_group
			   		and gscg.from_group=g.id
			   		and gds.stu_id=st.stu_id
			   		and gscg.change_type=2
			   		and gds.is_pass=0
			   		and stop_type=0
   			";

		$order = " ORDER BY gscg.`id` ASC";

		//$groupby=" GROUP BY g.`academic_year`,g.`grade`,g.`session`";
		$where = '';
		//echo $sql;exit();
		if (empty($search)) {
			return $db->fetchAll($sql . $order);
		}

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (select CONCAT(from_academic,'-',to_academic,' ',generation) from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=g.session) LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND st.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['change_id'])) {
			$where .= ' AND gscg.id=' . $search['change_id'];
		}
		//echo $sql.$where.$order;exit();
		$row = $db->fetchAll($sql . $where . $order);
		if ($row) {
			return $row;
		}
	}

	function getStudentEvaluationBYId($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT e.*,
   		e.for_type AS exam_type,
   		(SELECT month_kh FROM rms_month WHERE rms_month.id = e.for_month LIMIT 1) AS for_month,
		(SELECT month_en FROM rms_month WHERE rms_month.id = e.for_month LIMIT 1) AS for_monthen,
		e.for_month  as for_month_id ,
			vst.*
			 FROM 
			 `rms_student_evaluation` AS e,
			 `v_studentinfo_by_group_detail_student` AS vst
			WHERE 
			vst.group_id = e.group_id
			AND
			vst.stu_id = e.student_id
			AND
			 e.status=1";
		if (!empty($data['group_id'])) {
			$sql .= " AND e.group_id=" . $data['group_id'];
		}
		if (!empty($data['stu_id'])) {
			$sql .= " AND e.student_id=" . $data['stu_id'];
		}
		if (!empty($data['exam_type'])) {
			$sql .= " AND e.for_type=" . $data['exam_type'];

			if ($data['exam_type'] == 1) {
				if (!empty($data['for_month'])) {
					$sql .= " AND e.for_month=" . $data['for_month'];
				}
			} else if ($data['exam_type'] == 2) {
				if (!empty($data['for_semester'])) {
					$sql .= " AND e.for_semester=" . $data['for_semester'];
				}
			}
		}
		$sql .= " ORDER BY e.id DESC";
		return $db->fetchRow($sql);
	}
	function getStudentEvaluation($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT ed.*,
			(SELECT cm.comment FROM `rms_comment` AS cm WHERE cm.id = ed.comment_id LIMIT 1) AS comments,
			e.issue_date,
			e.return_date,
			e.teacher_comment
			 FROM `rms_student_evaluation_detail` AS ed,
			 `rms_student_evaluation` AS e
			WHERE 
			e.id = ed.evaluation_id 
			AND e.status=1
   		";
		if (!empty($data['group_id'])) {
			$sql .= " AND e.group_id=" . $data['group_id'];
		}
		if (!empty($data['stu_id'])) {
			$sql .= " AND e.student_id=" . $data['stu_id'];
		}
		if (!empty($data['exam_type'])) {
			$sql .= " AND e.for_type=" . $data['exam_type'];

			if ($data['exam_type'] == 1) {
				if (!empty($data['for_month'])) {
					$sql .= " AND e.for_month=" . $data['for_month'];
				}
			} else if ($data['exam_type'] == 2) {
				if (!empty($data['for_semester'])) {
					$sql .= " AND e.for_semester=" . $data['for_semester'];
				}
			}
		}
		$sql .= " ORDER BY e.id DESC";
		return $db->fetchAll($sql);
	}

	function getStudentGroupScoreEn($id, $search)
	{
		$session_lang = new Zend_Session_Namespace('lang');
		$lang_id = $session_lang->lang_id;
		$gender_str = 'name_en';
		$str_village = 'village_name';
		$str_commune = 'commune_name';
		$str_district = 'district_name';
		$str_province = 'province_en_name';
		if ($lang_id == 1) { //for kh
			$gender_str = 'name_kh';
			$str_village = 'village_namekh';
			$str_commune = 'commune_namekh';
			$str_district = 'district_namekh';
			$str_province = 'province_kh_name';
		}

		$db = $this->getAdapter();
		$sql = "SELECT 
				(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_name,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gr.academic_year LIMIT 1) AS academic_yeartitle,
				(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_logo,
				`se`.`group_id` AS `group_id`,
				`s`.`stu_id`   AS `stu_id`,
				`s`.`stu_code` AS `stu_code`,
				`s`.`stu_khname` AS `kh_name`,
				`s`.`stu_enname` AS `en_name`,
				`s`.`last_name` AS `last_name`,
				`s`.`address` AS `address`,
				s.pob,
				`s`.`tel` AS `tel`,
				`s`.`sex` AS `gender`,
				`s`.`dob` AS `dob`,
				s.father_enname AS father_name,
				(SELECT name_kh FROM rms_view WHERE TYPE=21 AND key_code=`s`.`nationality` LIMIT 1) AS nationality,
				(SELECT name_kh FROM rms_view WHERE TYPE=21 AND key_code=`s`.`nation` LIMIT 1) AS nation,
				 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job,
				 s.mother_enname AS mother_name,
				 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job,
				 
				 (SELECT
				        `rms_view`.$gender_str
				      FROM `rms_view`
				      WHERE ((`rms_view`.`type` = 2)
				             AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`,
				  s.home_num,
				  s.street_num,
				    (SELECT v.$str_village FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    	(SELECT c.$str_commune FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.$str_district FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
			    	(SELECT $str_province FROM rms_province WHERE rms_province.province_id = s.province_id LIMIT 1) AS province,
			    	(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = gr.teacher_id LIMIT 1) as teacher,
			    gr.academic_year,
			    gr.degree as degree_id,
			    	
				sed.*,
				SUM(sed.score) AS total_score 
				
				FROM 
					`rms_score_eng_detail` AS sed,
					`rms_score_eng` AS se,
					rms_student AS s,
					`rms_group` AS gr
				WHERE sed.score_id=se.id
				AND sed.student_id = s.stu_id
				AND se.group_id = gr.id
				
				
   		";
		if (!empty($id)) {
			$sql .= ' AND se.group_id=' . $id;
		}
		if (!empty($search['branch_id'])) {
			$sql .= ' AND se.branch_id = ' . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$sql .= ' AND gr.academic_year = ' . $search['academic_year'];
		}
		if (!empty($search['group'])) {
			$sql .= ' AND gr.id = ' . $search['group'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission("se.branch_id");
		$sql .= " GROUP BY sed.student_id
				ORDER BY SUM(sed.score) DESC";

		return $db->fetchAll($sql);
	}
	function getScoreStatistic($search)
	{ //using
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$branch = "branch_namekh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		} else { // English
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		$sql = "SELECT s.id,s.title_score,s.title_score_en, 
    			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS academicYear,
    			g.group_code, g.grade, g.degree,g.branch_id,
				(SELECT $branch FROM rms_branch WHERE rms_branch.br_id=g.branch_id  LIMIT 1) AS branch_name,
				(SELECT $degree FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
				(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
				(SELECT COUNT(sm.id) FROM `rms_score_monthly` sm WHERE sm.score_id=s.id AND sm.type=1 ) TotaStudent
			FROM `rms_score` AS s  
			INNER JOIN `rms_group` AS g ON  g.id = s.group_id  
    	";

		$where = ' WHERE 1 ';
		if (($search['branch_id']) > 0) {
			$where .= ' AND s.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['group'])) {
			$where .= ' AND s.group_id=' . $search['group'];
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND g.degree=' . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['exam_type'])) {
			$where .= ' AND s.exam_type=' . $search['exam_type'];
		}
		if (!empty($search['for_semester'])) {
			$where .= ' AND s.for_semester=' . $search['for_semester'];
		}
		if (!empty($search['for_month'])) {
			$where .= ' AND s.for_month=' . $search['for_month'];
		}
		if (!empty($search['sort_degree'])) {
			$where .= ' AND g.degree in(' . $search['sort_degree'] . ')';
		}

		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission("s.branch_id");
		$where .= $dbp->getDegreePermission("g.degree");

		$orderBy = "  ORDER BY g.degree ASC,g.grade ASC,g.group_code ASC ";
		$scoreInfo = $db->fetchAll($sql . $where . $orderBy);

		$resultInfo = array();
		if (!empty($scoreInfo)) {
			foreach ($scoreInfo as $key => $rs) {
				$resultInfo[$key]['title_score'] = $rs['title_score'];
				$resultInfo[$key]['title_score_en'] = $rs['title_score_en'];
				$resultInfo[$key]['academicYear'] = $rs['academicYear'];
				$resultInfo[$key]['group_code'] = $rs['group_code'];
				$resultInfo[$key]['grade'] = $rs['grade'];
				$resultInfo[$key]['degree'] = $rs['degree'];
				$resultInfo[$key]['branch_id'] = $rs['branch_id'];
				$resultInfo[$key]['branch_name'] = $rs['branch_name'];

				$resultInfo[$key]['degree_name'] = $rs['degree_name'];
				$resultInfo[$key]['grade_name'] = $rs['grade_name'];
				$resultInfo[$key]['TotaStudent'] = $rs['TotaStudent'];

				$resultInfo[$key]['Total_A'] = 0;
				$resultInfo[$key]['Total_B'] = 0;
				$resultInfo[$key]['Total_C'] = 0;
				$resultInfo[$key]['Total_D'] = 0;
				$resultInfo[$key]['Total_E'] = 0;
				$resultInfo[$key]['Total_F'] = 0;

				$search['degree'] = $rs['degree'];
				$search['scoreId'] = $rs['id'];

				$rsMention = $this->getCountStudentScore($search);
				if (!empty($rsMention))
					foreach ($rsMention as $rsGrade) {
						$resultInfo[$key]['Total_A'] = !empty($rsGrade['Total_A']) ? $rsGrade['Total_A'] : 0;
						$resultInfo[$key]['Total_B'] = !empty($rsGrade['Total_B']) ? $rsGrade['Total_B'] : 0;
						$resultInfo[$key]['Total_C'] = !empty($rsGrade['Total_C']) ? $rsGrade['Total_C'] : 0;
						$resultInfo[$key]['Total_D'] = !empty($rsGrade['Total_D']) ? $rsGrade['Total_D'] : 0;
						$resultInfo[$key]['Total_E'] = !empty($rsGrade['Total_E']) ? $rsGrade['Total_E'] : 0;
						$resultInfo[$key]['Total_F'] = !empty($rsGrade['Total_F']) ? $rsGrade['Total_F'] : 0;
					}
			}
		}
		return $resultInfo;
	}
	function getCountStudentScore($search)
	{ //using
		$db = $this->getAdapter();

		$mentionResult = $this->getMentoinGradeSetting($search);
		$mentionResultArr = array(
			"0" => 0,
			"1" => 0,
			"2" => 0,
			"3" => 0,
			"4" => 0,
			"5" => 0,
		);

		if (!empty($mentionResult)) {
			foreach ($mentionResult as $key => $row) {
				$mentionResultArr[$key] = $row["max_score"];
			}
		}
		if ($search['exam_type'] == 1) {
			$totalScore = "sm.total_score";
			$totalMaxScore = "sm.totalMaxScore";
		} else {
			$totalScore = "sm.overallAssessmentSemester";
			$totalMaxScore = "(SELECT  g.semesterTotalAverage FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 )";
		}

		$sql = "SELECT  
		 s.id,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['0'] . "'   , " . $totalScore . ", NULL)) AS Total_A,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['1'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['0'] . "'  , " . $totalScore . ", NULL)) AS Total_B,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['2'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['1'] . "'  , " . $totalScore . ", NULL)) AS Total_C,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['3'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['2'] . "'  , " . $totalScore . ", NULL)) AS Total_D,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['4'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['3'] . "'  , " . $totalScore . ", NULL)) AS Total_E,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['4'] . "'  , " . $totalScore . ", NULL)) AS Total_F
		   
		 FROM `rms_score` AS s  ,
			`rms_score_monthly` AS sm  
		WHERE s.`id` = sm.`score_id`  AND sm.`type` = 1 ";
		$where = ' ';
		if (($search['scoreId']) > 0) {
			$where .= ' AND s.id=' . $search['scoreId'];
		}
		$where .= " GROUP BY sm.`score_id`";
		return $db->fetchAll($sql . $where);
	}

	function getScoreSubjectByTeacher($search)
	{ //using
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$branch = "branch_namekh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		} else { // English
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		$sql = "SELECT sj.id,sj.title_score,sj.title_score_en, 
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = sj.for_academic_year LIMIT 1) AS academicYear,
    		g.group_code, g.grade, g.degree,g.branch_id,
			
			(SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=g.branch_id LIMIT 1) AS branch_name, 
			(SELECT rms_items.title FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
			(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name, 
			sj.`subject_id`,sj.`teacher_id`,
			(SELECT subject_titlekh FROM `rms_subject` WHERE rms_subject.id=sj.`subject_id` LIMIT 1) AS subject_name, 
			 t.teacher_name_kh  AS teacher_name, 
			(SELECT COUNT(sm.id) FROM `rms_score_monthly` sm WHERE sm.score_id=sj.id AND sm.type=1 ) TotaStudent 
			
			FROM v_score_ft_subjectscore AS sj
			LEFT JOIN `rms_group` AS g ON g.id = sj.group_id 
			LEFT JOIN  rms_teacher AS t ON t.`id` = sj.`teacher_id`
    	";
		

		$where = ' WHERE 1 ';
		if (($search['branch_id']) > 0) {
			$where .= ' AND sj.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['group'])) {
			$where .= ' AND sj.group_id=' . $search['group'];
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND g.degree=' . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['sort_degree'])) {
			$where .= ' AND g.degree in(' . $search['sort_degree'] . ')';
		}
		if (!empty($search['exam_type'])) {
			$where .= ' AND sj.exam_type=' . $search['exam_type'];
		}
		if (!empty($search['for_semester'])) {
			$where .= ' AND sj.for_semester=' . $search['for_semester'];
		}
		if (!empty($search['for_month'])) {
			$where .= ' AND sj.for_month=' . $search['for_month'];
		}
		if (!empty($search['sort_degree'])) {
			$where .= ' AND g.degree in(' . $search['sort_degree'] . ')';
		}
		if (!empty($search['department'])) {
			$where .= ' AND t.department =' . $search['department'];
		}
		if (!empty($search['teacher'])) {
			$where .= ' AND sj.teacher_id =' . $search['teacher'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission("sj.branch_id");
		$where .= $dbp->getDegreePermission("g.degree");

		$orderBy = " ORDER BY  sj.teacher_id,sj.subject_id,g.degree,g.grade,g.group_code  ASC ";
		$scoreInfo = $db->fetchAll($sql . $where . $orderBy);

		$resultInfo = array();
		if (!empty($scoreInfo)) {
			foreach ($scoreInfo as $key => $rs) {

				$resultInfo[$key]['teacher_id'] = $rs['teacher_id'];
				$resultInfo[$key]['subject_id'] = $rs['subject_id'];

				$resultInfo[$key]['title_score'] = $rs['title_score'];
				$resultInfo[$key]['title_score_en'] = $rs['title_score_en'];
				$resultInfo[$key]['academicYear'] = $rs['academicYear'];
				$resultInfo[$key]['group_code'] = $rs['group_code'];
				$resultInfo[$key]['grade'] = $rs['grade'];
				$resultInfo[$key]['degree'] = $rs['degree'];
				$resultInfo[$key]['branch_id'] = $rs['branch_id'];
				$resultInfo[$key]['branch_name'] = $rs['branch_name'];

				$resultInfo[$key]['subject_name'] = $rs['subject_name'];
				$resultInfo[$key]['teacher_name'] = $rs['teacher_name'];

				$resultInfo[$key]['degree_name'] = $rs['degree_name'];
				$resultInfo[$key]['grade_name'] = $rs['grade_name'];
				$resultInfo[$key]['TotaStudent'] = $rs['TotaStudent'];

				$resultInfo[$key]['Total_A'] = 0;
				$resultInfo[$key]['Total_B'] = 0;
				$resultInfo[$key]['Total_C'] = 0;
				$resultInfo[$key]['Total_D'] = 0;
				$resultInfo[$key]['Total_E'] = 0;
				$resultInfo[$key]['Total_F'] = 0;

				$search['subject_id'] = $rs['subject_id'];
				$search['teacher_id'] = $rs['teacher_id'];
				$search['degree'] = $rs['degree'];
				$search['scoreId'] = $rs['id'];

				$rsMention = $this->getStudentGradeBySubject($search);
				if (!empty($rsMention))
					foreach ($rsMention as $rsGrade) {
						$resultInfo[$key]['Total_A'] = !empty($rsGrade['Total_A']) ? $rsGrade['Total_A'] : 0;
						$resultInfo[$key]['Total_B'] = !empty($rsGrade['Total_B']) ? $rsGrade['Total_B'] : 0;
						$resultInfo[$key]['Total_C'] = !empty($rsGrade['Total_C']) ? $rsGrade['Total_C'] : 0;
						$resultInfo[$key]['Total_D'] = !empty($rsGrade['Total_D']) ? $rsGrade['Total_D'] : 0;
						$resultInfo[$key]['Total_E'] = !empty($rsGrade['Total_E']) ? $rsGrade['Total_E'] : 0;
						$resultInfo[$key]['Total_F'] = !empty($rsGrade['Total_F']) ? $rsGrade['Total_F'] : 0;
					}
			}
		}
		return $resultInfo;
	}
	

	function getStudentGradeBySubject($search)
	{ //using
		$db = $this->getAdapter();
		$mentionResult = $this->getMentoinGradeSetting($search);
		$mentionResultArr = array(
			"0" => 0,
			"1" => 0,
			"2" => 0,
			"3" => 0,
			"4" => 0,
			"5" => 0,
		);

		if (!empty($mentionResult)) {
			foreach ($mentionResult as $key => $row) {
				$mentionResultArr[$key] = $row["max_score"];
			}
		}
		if ($search['exam_type'] == 1) {
			$totalScore = "sd.orgScore";
			$totalMaxScore = "(SELECT  gs.max_score FROM `rms_group_subject_detail` AS gs WHERE gs.group_id = sd.group_id AND gs.subject_id= sd.`subject_id` AND gs.teacher= sd.`teacher_id` LIMIT 1)";
		} else {
			$totalScore = "sd.orgScore";
			$totalMaxScore = "(SELECT  gs.semester_max_score FROM `rms_group_subject_detail` AS gs WHERE gs.group_id = sd.group_id AND gs.subject_id= sd.`subject_id` AND gs.teacher= sd.`teacher_id` LIMIT 1)";
		}

		$sql = "SELECT  
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['0'] . "'   , " . $totalScore . ", NULL)) AS Total_A,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['1'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['0'] . "'  , " . $totalScore . ", NULL)) AS Total_B,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['2'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['1'] . "'  , " . $totalScore . ", NULL)) AS Total_C,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['3'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['2'] . "'  , " . $totalScore . ", NULL)) AS Total_D,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 >= '" . $mentionResultArr['4'] . "' AND " . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['3'] . "'  , " . $totalScore . ", NULL)) AS Total_E,
		  COUNT(IF(" . $totalScore . "/" . $totalMaxScore . "*100 < '" . $mentionResultArr['4'] . "'  , " . $totalScore . ", NULL)) AS Total_F
		   
		FROM `rms_score_detail` AS sd ";
		$where = ' where 1 ';
		if (($search['scoreId']) > 0) {
			$where .= ' AND sd.`score_id` =' . $search['scoreId'];
		}
		if (!empty($search['subject_id'])) {
			$where .= ' AND sd.subject_id =' . $search['subject_id'];
		}
		if (!empty($search['teacher_id'])) {
			$where .= ' AND sd.teacher_id= ' . $search['teacher_id'];
		}
		$where .= " GROUP BY sd.`score_id`";
		return $db->fetchAll($sql . $where);
	}

	function getMentoinGradeSetting($search){   /// Funtion Get Score Setting
		$db = $this->getAdapter();
		$sqlMention = "SELECT 
		md.`max_score`, 
		md.`metion_grade` FROM 
		`rms_metionscore_setting` AS m 
			INNER JOIN `rms_metionscore_setting_detail` AS md 
		ON m.`id`=md.`metion_score_id` 
		WHERE m.`degree`= " . $search['degree'] . " AND m.`academic_year`= " . $search['academic_year'] . "  ORDER BY md.`max_score` DESC";
		$mentionResult = $db->fetchAll($sqlMention);
		return $mentionResult;
	}


	public function getStatisticScoreResult($search, $id = null)
	{ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត/outstanding photo and no photo
		//for view in page assessment/ rptScoreResult/rptMonthlytranscript/monthlyOutstandingStudent/monthlyOutstandingStudentNophoto/examscorepdf/
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
			$month = "month_kh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$month = "month_en";
		}
		$search['exam_type'] = empty($search['exam_type']) ? 0 : $search['exam_type'];
		$sql = "
			SELECT
				(SELECT $branch FROM rms_branch as b WHERE b.br_id=s.`branch_id` LIMIT 1) AS branch_name,
				(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.`branch_id` LIMIT 1) AS branch_logo,
				(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=s.`branch_id` LIMIT 1) AS schoolNameKh,
				(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=s.`branch_id` LIMIT 1) AS schoolNameEng,
				
				g.`branch_id`,
				g.`group_code`,
				`g`.`degree` as degree_id,
				`g`.`semester` AS `semester`,
				
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS academic_year,
				(SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS start_year,
				(SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS end_year,
				(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				(SELECT teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
				(SELECT signature from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher_sigature,
				
				(SELECT st.stu_khname FROM `rms_student` AS st WHERE st.stu_id = sm.`student_id` LIMIT 1) AS StuNameKh,
				(SELECT CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,'')) FROM `rms_student` AS st WHERE st.stu_id = sm.`student_id` LIMIT 1) AS StuNameEn,
				(SELECT st.stu_code FROM `rms_student` AS st WHERE st.stu_id = sm.`student_id` LIMIT 1) AS stuCode,
				(SELECT st.sex FROM `rms_student` AS st WHERE st.stu_id = sm.`student_id` LIMIT 1) AS Sex,
		   	
			   	s.`id`,
			   	s.for_academic_year,
		   		s.`group_id`,
		   		(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
			   	s.exam_type,
			   	s.for_semester,
			   	s.for_month as for_month_id,
			   	s.reportdate,
				s.date_input,
				s.principalId,
				s.academicStaffId,
			   	s.title_score,
				s.title_score_en,
			   	s.max_score,
				sm.`student_id`,
				sm.total_score,
				sm.totalMaxScore,
		    (sm.totalMaxScore/2) as passScore";
		if ($search['exam_type'] == 1) {
			$sql .= " ,g.max_average as semesterScal
					,sm.total_avg as totalAverage ";
		} else if ($search['exam_type'] == 2) {
			$sql .= "
				,g.semesterTotalAverage as semesterScal
				,sm.overallAssessmentSemester as totalAverage
			";
		} else if ($search['exam_type'] == 3) {
			$sql .= "
				,g.semesterTotalAverage as semesterScale
				,sm.overallAssessmentSemester as totalAverage
			";
		}
		$sql .= " FROM `rms_score` AS s INNER JOIN `rms_score_monthly` AS sm
					 ON s.id = sm.`score_id`
					 JOIN `rms_group` AS g
					 ON g.`id` = s.`group_id`
				 WHERE  s.status = 1  AND sm.type = 1  ";

		if (!empty($id)) {
			$sql .= " AND s.id = $id ";
		}
		$where = '';
		if (!empty($search['branch_id'])) {
			$where .= " AND s.`branch_id` =" . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= " AND s.for_academic_year =" . $search['academic_year'];
		}
		if (!empty($search['group'])) {
			$where .= " AND s.group_id =" . $search['group'];
		}
		if (!empty($search['exam_type'])) {
			$where .= " AND s.exam_type =" . $search['exam_type'];
			if ($search['exam_type'] == 1) {
				if (!empty($search['for_month'])) {
					$where .= " AND s.for_month =" . $search['for_month'];
				}
			} else if ($search['exam_type'] == 2) {
				if (!empty($search['for_semester'])) {
					$where .= " AND s.for_semester =" . $search['for_semester'];
				}
			}
		}

		if (!empty($search['degree'])) {
			$where .= " AND `g`.`degree` =" . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= " AND `g`.`grade` =" . $search['grade'];
		}
		if (!empty($search['mention'])) {
			if ($search['exam_type'] == 1) {
				$avg = "sm.total_avg";
				$scale = "g.max_average";
			} else {
				$avg = "sm.overallAssessmentSemester";
				$scale = "g.semesterTotalAverage";
			}
			if ($search['mention'] == 1) {
				$where .= " AND " . $avg . " < " . $scale . "*50/100 ";
			} else if ($search['mention'] == 50) {
				$where .= " AND " . $avg . " >= " . $scale . "*50/100 AND " . $avg . "<" . $scale . "*60/100";
			} else if ($search['mention'] == 60) {
				$where .= " AND " . $avg . " >= " . $scale . "*60/100 AND " . $avg . "<" . $scale . "*70/100";
			} else if ($search['mention'] == 70) {
				$where .= " AND " . $avg . " >= " . $scale . "*70/100 AND " . $avg . "<" . $scale . "*80/100";
			} else if ($search['mention'] == 80) {
				$where .= " AND " . $avg . " >= " . $scale . "*80/100 AND " . $avg . "<" . $scale . "*90/100";
			} else if ($search['mention'] == 90) {
				$where .= " AND " . $avg . " > " . $scale . "*90/100 ";
			}
		}
		$where .= $_db->getAccessPermission('s.branch_id');
		$where .= $_db->getDegreePermission('g.degree');

		$order = "  
					GROUP BY s.id,sm.`student_id`,sm.score_id,s.`reportdate`
   				ORDER BY s.`group_id`, ";
		if ($search['exam_type'] == 2 or $search['exam_type'] == 3) {
			$order .= " sm.overallAssessmentSemester DESC";
		} else {
			$order .= " sm.total_score DESC ";
		}
		$order .= " ,s.for_academic_year,s.for_semester,s.for_month,sm.`student_id` ASC ";
		//	echo $sql . $where . $order ;
		return $db->fetchAll($sql . $where . $order);
	}
	function getSubjectScoreDetail($search)
	{ //transcript and score detail
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
			$month = "month_kh";
			$subject_name = "sj.subject_titlekh";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$subject_name = "sj.subject_titleen";
		}
		
		// $strCollect = 'amount_subject';
		// $strMaxScore = 'max_score';
		// if ($data['examType'] == 2 OR $data['examType'] == 3) { //semester
		// 	$strCollect = 'amount_subject_sem';
		// 	$strMaxScore = 'semester_max_score';
		// }

		// $strSubjecMaxScore = " (SELECT $strMaxScore FROM `rms_group_subject_detail` WHERE
		// group_id=sd.group_id AND
		// subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";

		// $strMultiSubject = " (SELECT $strCollect FROM `rms_group_subject_detail` WHERE
		// group_id=sd.group_id AND subject_id=sd.subject_id  ORDER BY rms_group_subject_detail.id ASC LIMIT 1) ";
		// //need to check this score is monthly or semester?

		// $subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];
		$sql = "SELECT
				(SELECT $branch FROM rms_branch as b WHERE b.br_id=s.`branch_id` LIMIT 1) AS branch_name,
				(SELECT st.stu_code FROM `rms_student` AS st WHERE st.stu_id = sd.`student_id` LIMIT 1) AS stuCode,
				(SELECT st.stu_khname FROM `rms_student` AS st WHERE st.stu_id = sd.`student_id` LIMIT 1) AS StuNameKh,
				(SELECT CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,'')) FROM `rms_student` AS st WHERE st.stu_id = sd.`student_id` LIMIT 1) AS StuNameEn,
				(SELECT st.sex FROM `rms_student` AS st WHERE st.stu_id = sd.`student_id` LIMIT 1) AS Sex,
				(SELECT $subject_name FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name,
				(SELECT subject_lang FROM `rms_subject` s WHERE s.id=sd.subject_id LIMIT 1) as subLang,

				g.`group_code`,
				`g`.`degree` as degree_id,
				`g`.`semester` AS `semester`,

				s.`branch_id`,
				s.`id`,
			   	s.for_academic_year,
		   		s.`group_id`,
		   		(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
			   	s.exam_type,
			   	s.for_semester,
			   	s.for_month as for_month_id,
			   	s.reportdate,
				s.date_input,
				s.principalId,
				s.academicStaffId,
			   	s.title_score,
				s.title_score_en,
				
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS academic_year,
				(SELECT ac.fromYear FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS start_year,
				(SELECT ac.toYear FROM `rms_academicyear` AS ac WHERE ac.id = s.for_academic_year LIMIT 1) AS end_year,
				(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,

				sd.`subject_id`,
				sd.`orgScore` AS totalAverage,
				sd.amount_subject, ";
		if ($search['exam_type'] == 1) {
			$sql .= "gsj.max_score as subMaxScore ";
		} else {
			$sql .= "gsj.semester_max_score as subMaxScore ";
		}

		$sql .= "FROM  `rms_score_detail` AS sd 
				INNER JOIN `rms_score` AS s ON s.id=sd.score_id
				LEFT JOIN rms_group AS g ON g.id=sd.group_id 
				LEFT JOIN `rms_group_subject_detail` AS gsj ON sd.subject_id=gsj.subject_id AND g.id=gsj.`group_id`
			WHERE 1
					";
		$where = '';
		if (!empty($search['branch_id'])) {
			$where .= " AND s.`branch_id` =" . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= " AND s.for_academic_year =" . $search['academic_year'];
		}
		if (!empty($search['group'])) {
			$where .= " AND s.group_id =" . $search['group'];
		}
		if (!empty($search['subjectId'])) {
			$where .= " AND sd.`subject_id` =" . $search['subjectId'];
		}
		if (!empty($search['exam_type'])) {
			$where .= " AND s.exam_type =" . $search['exam_type'];
			if ($search['exam_type'] == 1) {
				if (!empty($search['for_month'])) {
					$where .= " AND s.for_month =" . $search['for_month'];
				}
			} else if ($search['exam_type'] == 2) {
				if (!empty($search['for_semester'])) {
					$where .= " AND s.for_semester =" . $search['for_semester'];
				}
			}
		}

		if (!empty($search['degree'])) {
			$where .= " AND `g`.`degree` =" . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= " AND `g`.`grade` =" . $search['grade'];
		}
		if (!empty($search['mention'])) {
			if ($search['exam_type'] == 1) {
				$avg = "sd.orgScore";
				$scale = "gsj.max_score";
			} else {
				$avg = "sd.orgScore";
				$scale = "gsj.semester_max_score";
			}
			if ($search['mention'] == 1) {
				$where .= " AND " . $avg . " < " . $scale . "*50/100 ";
			} else if ($search['mention'] == 50) {
				$where .= " AND " . $avg . " >= " . $scale . "*50/100 AND " . $avg . "<" . $scale . "*60/100";
			} else if ($search['mention'] == 60) {
				$where .= " AND " . $avg . " >= " . $scale . "*60/100 AND " . $avg . "<" . $scale . "*70/100";
			} else if ($search['mention'] == 70) {
				$where .= " AND " . $avg . " >= " . $scale . "*70/100 AND " . $avg . "<" . $scale . "*80/100";
			} else if ($search['mention'] == 80) {
				$where .= " AND " . $avg . " >= " . $scale . "*80/100 AND " . $avg . "<" . $scale . "*90/100";
			} else if ($search['mention'] == 90) {
				$where .= " AND " . $avg . " > " . $scale . "*90/100 ";
			}
		}
		$where .= $_db->getAccessPermission('s.branch_id');
		$where .= $_db->getDegreePermission('g.degree');
		$order = " ORDER BY  s.`group_id`, subLang ASC ,totalAverage DESC ";
	//	echo $sql . $where . $order ; exit();
		return $db->fetchAll($sql . $where . $order);
	}

}
