<?php

class Issue_Model_DbTable_DbMonitorAssessment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
		$dbp = new Application_Model_DbTable_DbGlobal();
		return $dbp->getUserId();
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
		
		$sql = "
			SELECT 
				s.`title_score` AS titleScore
				,(SELECT b.".$branch." FROM `rms_branch` AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
				,(SELECT v.".$label." FROM `rms_view` AS v WHERE v.type=19 AND v.key_code =s.exam_type LIMIT 1) AS examTypeTitle
				,s.`for_semester` AS forSemester
				,s.`exam_type` AS examType
				,CASE
					WHEN s.exam_type = 2 THEN CONCAT('Semester ',s.`for_semester` )
					ELSE (SELECT mth.month_kh FROM `rms_month` AS mth WHERE mth.id=s.for_month  LIMIT 1) 
				END  AS scoreType
				,(SELECT CONCAT(acd.fromYear,'-',acd.toYear) FROM rms_academicyear AS acd WHERE acd.id=g.academic_year LIMIT 1) AS acadmicYearTitle
				,g.`group_code` AS groupCode
				,i.$colunmname AS degreeTitle
				,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS r	WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
				,COALESCE((SELECT t.`teacher_name_kh` FROM `rms_teacher` AS t WHERE t.id = `g`.`teacher_id` LIMIT 1),'N/A') AS teacherNameKh
				,COALESCE((SELECT t.`teacher_name_en` FROM `rms_teacher` AS t WHERE t.id = `g`.`teacher_id` LIMIT 1),'N/A') AS teacherNameEng
				,g.`teacher_id`
				,COALESCE(ass.id,'0') AS assessmentId
				,COALESCE(ass.`isLock`,'0') AS isLockAssessment
		";
		$sql.=" FROM `rms_score` AS s
				JOIN `rms_group` AS g ON g.`id` = s.`group_id`
					LEFT JOIN `rms_studentassessment` AS ass ON ass.scoreId = s.id 
					LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = `g`.`degree`
		";
		
		$where =' WHERE s.`status` =1 ';
		if(!empty($search['evaluationStatus'])){
			if($search['evaluationStatus']==1){
				$where.=" AND COALESCE(ass.`id`,'0') > 0  ";
			}else if($search['isLockType']==2){
				$where.=" AND COALESCE(ass.`isLock`,'0') = 0 ";
			}
		}
		if(!empty($search['isLockType'])){
			if($search['isLockType']==1){
				$where.=" AND COALESCE(ass.`isLock`,'0') = 1  ";
			}else if($search['isLockType']==2){
				$where.=" AND COALESCE(ass.`isLock`,'0') = 0 ";
			}
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
		$where.=$dbp->getAccessPermission('s.branch_id');
		$where.= $dbp->getSchoolOptionAccess('i.schoolOption');
		$order =  " ORDER BY COALESCE(ass.id,'0') ASC, COALESCE(ass.`isLock`,'0') ASC,s.id DESC " ;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAssessmentInfo($assessmentId){
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
		
		$sql="
			SELECT 
				ass.`id` AS assessmentId
				,ass.branchId
				,(SELECT b.".$branch." FROM `rms_branch` AS b WHERE b.br_id=ass.branchId LIMIT 1) AS branch_name
				,(SELECT v.".$label." FROM `rms_view` AS v WHERE v.type=19 AND v.key_code =ass.forType LIMIT 1) AS examTypeTitle
				,ass.`forSemester` AS forSemester
				,ass.`forType` AS forType
				,CASE
					WHEN ass.forType = 2 THEN ''
					ELSE ass.forMonth
				END  AS forMonth
				,(SELECT CONCAT(acd.fromYear,'-',acd.toYear) FROM rms_academicyear AS acd WHERE acd.id=g.academic_year LIMIT 1) AS academicYearTitle 
				,g.`group_code` AS groupCode
				,COALESCE((SELECT `r`.`room_name` FROM `rms_room` AS r	WHERE `r`.`room_id` = `g`.`room_id` LIMIT 1),'N/A') AS `roomName`
				,COALESCE((SELECT t.`teacher_name_kh` FROM `rms_teacher` AS t WHERE t.id = `g`.`teacher_id` LIMIT 1),'N/A') AS teacherNameKh
				,COALESCE((SELECT t.`teacher_name_en` FROM `rms_teacher` AS t WHERE t.id = `g`.`teacher_id` LIMIT 1),'N/A') AS teacherNameEng
				
		";
		$sql.="
		FROM `rms_studentassessment` AS ass	
			JOIN `rms_group` AS g ON g.`id` = ass.`groupId`
		";
		
		$where =" WHERE ass.status = 1 AND ass.isLock = 0 ";
		$where.=" AND ass.`id` = ".$assessmentId;
		$where.=$dbp->getAccessPermission('ass.branchId');
		$where.=" LIMIT 1 ";
		return $db->fetchRow($sql.$where);
	}
	function getAssessmentDetailList($assessmentId){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				assd.id AS detailId
				,assd.`assessmentId`
				,assd.`studentId`
				,assd.`teacherComment`
				
				,st.`stu_code` AS stuCode
				,st.stu_khname AS stuKhName
				,CONCAT(COALESCE(TRIM(st.last_name),''),' ',COALESCE(TRIM(st.stu_enname),'')) AS stuEnName 
				,st.`sex`
		";
		$sql.="
		FROM 
			`rms_studentassessment_detail` AS assd 
			JOIN `rms_studentassessment` AS ass ON ass.`id`= assd.`assessmentId`
			LEFT JOIN `rms_student` AS st ON st.`stu_id` = assd.`studentId`
		";
		
		$where =" WHERE ass.status = 1 ";
		$where.=" AND assd.`assessmentId` = ".$assessmentId;
		$where.=" GROUP BY assd.`studentId`  ";
		$order =  " ORDER BY assd.`studentId` ASC,assd.teacherComment DESC " ;
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	public function approvedAssessmentByCheckBox($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dept = "";
			$this->_name='rms_studentassessment';
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
				$_arr = array(
					'isLock'	 	=>1,
					'lockBy'		=>$this->getUserId(),
					);
				$where = " id = ".$rs;
				$this->update($_arr, $where);
	    	}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   public function approvedAssessmentAndEditTeacherComment($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$assessmentID = empty($_data["assessmentId"]) ? 0 : $_data["assessmentId"];
			$this->_name='rms_studentassessment';
			$_arr = array(
					'isLock'	 	=>1,
					'lockBy'		=>$this->getUserId(),
					);
			$where = " id = ".$assessmentID;
			$this->update($_arr, $where);
				
	    	if (!empty($_data['identity'])) {
				$ids = explode(',', $_data['identity']);
				$this->_name='rms_studentassessment_detail';
				if(!empty($ids))foreach ($ids as $i){
					
					$_arrDetail = array(
						'teacherComment' 	=>$_data["teacherComment".$i],
					);
					$where = " studentId =".$_data['studentId'.$i]." AND assessmentId = ".$assessmentID;
					$this->update($_arrDetail, $where);	
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
	
}