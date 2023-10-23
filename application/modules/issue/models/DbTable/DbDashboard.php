<?php

class Issue_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	function getGradingScoreData($data){
		$sql="SELECT 
				gt.gradingId,
				gt.totalAverage 
				FROM `rms_grading_total` gt,
					`rms_grading` gd
				 WHERE gd.id=gt.gradingId ";
		if(!empty($data['groupId'])){
			$sql.=" AND gd.groupId=".$data['groupId'];
		}
		if(!empty($data['examType'])){
			if($data['examType']==1){//month
				if(!empty($data['forMonth'])){
					$sql.=" AND gd.formonth= ".$data['forMonth'];
				}
			}
			$sql.=" ANd gd.examType = ".$data['examType'];
		}
		if(!empty($data['forSemester'])){
			$sql.=" AND gd.forSemester = ".$data['forSemester'];
		}
		if(!empty($data['subjectId'])){
			$sql.=" AND gd.subjectId = ".$data['subjectId'];
		}
		$from_date =(empty($data['start_date']))? '1': "gd.dateInput >= '".$data['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "gd.dateInput <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		
		$sql.=" ORDER BY gd.subjectId ASC ";
		return $this->getAdapter()->fetchRow($sql);
	}

	function getSubjectScoreByGroup($data){
		
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE
		s.id=sd.subject_id LIMIT 1) ";
		
		$db=$this->getAdapter();
		$sql="SELECT
			sd.*, 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name,
			(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name_en,
			(SELECT t.teacher_name_kh FROM `rms_teacher` AS t WHERE t.id = sd.teacher LIMIT 1) AS teacher_name_kh,
			(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id = sd.teacher LIMIT 1) AS teacher_name_en,
			$strSubjectLange AS subjectLang
			FROM
			rms_group_subject_detail AS sd   WHERE sd.`group_id` = ".$data['groupId'];
		$sql.=" ORDER  BY $strSubjectLange ";
		$subjectDetail= $db->fetchAll($sql);

		$results = array();
		if(!empty($subjectDetail)){
			foreach($subjectDetail as $key=>$rs){
				$results[$key]['sub_name'] = $rs['sub_name'];
				$results[$key]['sub_name_en'] = $rs['sub_name_en'];
				$results[$key]['teacher_name_kh'] = $rs['teacher_name_kh'];
				$results[$key]['teacher_name_en'] = $rs['teacher_name_en'];
				$results[$key]['subjectLang'] = $rs['subjectLang'];
				$data['subjectId']=$rs['subject_id'];
				$rsGrading=$this->getGradingScoreData($data);
				$results[$key]['gradingScore']=$rsGrading['totalAverage'];
				$results[$key]['gradingId']=$rsGrading['gradingId'];
			}
		}
		return $results ;
	
	}
    
	function getAllGroups($search){
		$db = $this->getAdapter();
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
		
		$sql = "SELECT `g`.`id`,
			(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
			`g`.`group_code` AS `group_code`,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS tuitionfee_id,	
			 `g`.`semester` AS `semester`, 
			i.$colunmname AS degree,
			(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade,
			(SELECT`rms_view`.$label FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
			(select teacher_name_kh from rms_teacher where rms_teacher.id = g.teacher_id limit 1 ) as teaccher,
			(select count(subject_id) from rms_group_subject_detail where rms_group_subject_detail.group_id = g.id limit 1 ) as totalSubject,
			time,
			`g`.`note`,
			(select $label from rms_view where type=9 and key_code=is_pass) as group_status ";
		
		$sql.=$dbp->caseStatusShowImage("g.status");
		$sql.=" FROM `rms_group` AS `g` 
				LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = `g`.`degree`
		";
		
		$where =' WHERE 1 ';
		$from_date =(empty($search['start_date']))? '1': "g.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "g.date <= '".$search['end_date']." 23:59:59'";
		//$where.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]="(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['academic_year'])){
			$where.=' AND g.academic_year='.$search['academic_year'];
		}
		if(!empty($search['grade'])){
			$where.=' AND g.grade='.$search['grade'];
		}
		if(!empty($search['degree'])){
			$where.=' AND `g`.`degree`='.$search['degree'];
		}
		$where.=$dbp->getAccessPermission('g.branch_id');
		$where.= $dbp->getSchoolOptionAccess('i.schoolOption');
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		return $db->fetchAll($sql.$where.$order);
	}
	
}