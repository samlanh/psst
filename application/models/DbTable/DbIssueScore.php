<?php

class Application_Model_DbTable_DbIssueScore extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_grading';
    	
	public static function getUserExternalId(){
		$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
		$userId = $sessionUserExternal->userId;
		return $userId;
	}
	
	
	function getAllSubjectScoreByClass($search=null){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.examType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_grading AS grd,
					rms_group AS g 
			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
		
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " grd.dateInput >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " grd.dateInput <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$where.=' AND grd.teacherId='.$this->getUserExternalId();

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" grd.titleScore LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_namekh FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT br.branch_nameen FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" grd.note LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['degree']>0){
			$where.= " AND g.degree =".$search['degree'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year =".$search['academic_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND `g`.`grade` =".$search['grade'];
		}

		if($search['for_month']>0){
			$where.=" AND grd.forMonth =".$search['for_month'];
		}
		if($search['exam_type']>0){
			$where.= " AND grd.examType =".$search['exam_type'];
		}
		if($search['for_semester']>0){
			$where.= " AND grd.forSemester =".$search['for_semester'];
		}
		$order=" ORDER BY grd.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function checkingDuplicate($_data){
		$db = $this->getAdapter();
		$sql=" SELECT grd.* ";
		$sql.="FROM rms_grading As grd ";
		$sql.=" WHERE grd.status =1 ";
	//	$sql.=" AND grd.teacherId =".$this->getUserExternalId();
		$sql.=" AND grd.groupId=".$_data['groupId'];
		$sql.=" AND grd.subjectId=".$_data['subjectId'];
		$sql.=" AND grd.examType=".$_data['examType'];
		if($_data['examType']==1){
			$sql.=" AND grd.forMonth=".$_data['forMonth'];
		}else{
			$sql.=" AND grd.forSemester=".$_data['forSemester'];
		}
		$row = $db->fetchRow($sql);
		if(!empty($row)){
			return 1;
		}
		return 0;
	}
	public function getSubjectScoreByID($id){
	   	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.examType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,g.is_pass AS  is_pass
				,g.gradingId AS  gradingId
				,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
				,(SELECT grading.title FROM `rms_scoreengsetting` AS grading WHERE grading.schoolOption=1 AND grading.id=g.gradingId LIMIT 1)AS gradingSystem
		";
		
		
	   	$sql.= "
			   	FROM rms_grading AS grd,
					rms_group AS g
		   		WHERE grd.groupId=g.id 
		   			AND grd.id=".$id;
		$sql.=' AND grd.teacherId='.$this->getUserExternalId();
	   	$sql.="  LIMIT 1 ";
	   	return $db->fetchRow($sql);
	}
	public function addSubjectScoreByClass($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$group_info = $dbExternal->getGroupDetailByID($_data['group']);
			$academicYear = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			$subjectId = $_data['subjectId'];
			$maxSubjectScore = $_data['maxSubjectScore'];
			$_arr = array(
					'branchId'			=>$_data['branch_id'],
					'groupId'			=>$_data['group'],
					'titleScore'		=>$_data['title'],
					'dateInput'			=>date("Y-m-d"),
			        'examType'			=>$_data['examType'],
					
					'forMonth'			=>$_data['forMonth'],
					'forSemester'		=>$_data['forSemester'],
					'academicYear'		=>$academicYear,
					
					'subjectId'			=>$subjectId,
					'inputOption'		=>2, //1 normal,2 teache input
					
					'note'				=>$_data['note'],
					'status'			=>1,
					
					'teacherId'			=>$this->getUserExternalId(),
					'createDate'		=>date("Y-m-d H:i:s"),
					'modifyDate'		=>date("Y-m-d H:i:s"),
			);
			$this->_name='rms_grading';		
			$id=$this->insert($_arr);
			
			$gradingId = empty($group_info['gradingId'])?0:$group_info['gradingId'];
			$arrSearch  = array(
				'gradingId'=>$gradingId
				,'subjectId'=>$subjectId
			);
			$criterial = $dbExternal->getGradingSystemDetail($arrSearch);
			
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				$totalScoreAverage = 0;
				$criteriaAmount = count($criterial) ;
				if(!empty($ids))foreach ($ids as $i){
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['exam_typeid'];						
						if($totalScoreAverage>0 AND $old_studentid!=$_data['student_id'.$i]){
							
							$arr=array(
								'gradingId'			=>$id,
								'studentId'			=>$old_studentid,
									
								'subjectId'			=> $subjectId,
								'totalAverage'		=> number_format($totalScoreAverage,2),
								'remark'			=> $_data['note_'.$i],
								
							);
							$this->_name='rms_grading_total';
							$this->insert($arr);
							$totalScoreAverage = 0;
						}
						
						$old_studentid=$_data['student_id'.$i];
						$pecentageScore = $rowCri['pecentage_score'];
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
								$subcriteriaAmount = count($subCriterial);
								
								$indexSub=0;
								$titleSubCriterial="";
								$titleSubCriteriaEng="";
								
								$totalGrading =0;
								foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
								
									
									if($subcriteriaAmount>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									
									$score = $_data['score_'.$i.'_'.$indexSub.$criterialId];
									
									$totalGrading = $totalGrading+$score;
									
									$arr=array(
										'gradingId'				=>$id,
										'studentId'				=>$old_studentid,
										'criteriaId'			=> $criterialId,
										'totalGrading'			=> $score,
										'criteriaAmount'		=> $subcriteriaAmount,
										'percentage'			=> $pecentageScore,
										
										
										'subCriterialTitleKh'	=> $titleSubCriterial,
										'subCriterialTitleEng'	=> $titleSubCriteriaEng,
										);
									
									$this->_name='rms_grading_detail';
									$this->insert($arr);
								
								}
								
								$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
								$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
								$totalScoreAverage = $totalScoreAverage+$totalGrading;
							}
						}else{
							$subcriteriaAmount=0;
							$totalGrading =0;
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								
								if($rowCri['timeInput']>1){
									if(empty($_data['checkAll'.$x.$criterialId])){
										$subcriteriaAmount=$subcriteriaAmount+1;
										
										$score = $_data['score_'.$i.'_'.$x.$criterialId];
										$totalGrading = $totalGrading+$score;
										
										
										$arr=array(
											'gradingId'			=> $id,
											'studentId'			=> $old_studentid,
											'criteriaId'		=> $criterialId,
											'criteriaAmount'	=> $subcriteriaAmount,
											'totalGrading'		=> $score,
											'percentage'		=> $pecentageScore,
											
											);
										
										$this->_name='rms_grading_detail';
										$this->insert($arr);
									
									}
								}else{
									$subcriteriaAmount = $rowCri['timeInput'];
									$score = $_data['score_'.$i.'_'.$x.$criterialId];
									$pecentageScore = $rowCri['pecentage_score'];
									
									
									$totalGrading = $totalGrading+$score;
									
									
									$arr=array(
										'gradingId'			=> $id,
										'studentId'			=> $old_studentid,
										'criteriaId'		=> $criterialId,
										'criteriaAmount'	=> $subcriteriaAmount,
										'totalGrading'		=> $score,
										'percentage'		=> $pecentageScore,
										
										);
									
									$this->_name='rms_grading_detail';
									$this->insert($arr);
								
								}
								
						
								
							}
							$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
							$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
							$totalScoreAverage = $totalScoreAverage+$totalGrading;
							
						}
					
					}
					
				}
				
				if(!empty($ids)){
					if($totalScoreAverage>0){
						$arr=array(
							'gradingId'		=>$id,
							'studentId'		=>$_data['student_id'.$i],
							
							'subjectId'		=> $subjectId,
							'totalAverage'	=> number_format($totalScoreAverage,2),
							
							'remark'		=>$_data['note_'.$i],
							
						);
						
						
						$this->_name='rms_grading_total';
						$this->insert($arr);
					}
				}
			}
		
		  $db->commit();
		  return $id;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   
   function getStudentSubjectSccoreforEdit($gradingId){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$studentName="CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName=$studentName;
		
		if ($currentLang==1){
			$studentName='s.stu_khname';
		}
		
		$sql="SELECT 
			sd.*
			,(SELECT ".$studentName." FROM `rms_student` AS s WHERE s.stu_id = sd.`studentId` LIMIT 1) AS student_name
			,(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sd.`studentId` LIMIT 1) AS stuKhName
			,(SELECT ".$studentEnName." FROM `rms_student` AS s WHERE s.stu_id = sd.`studentId` LIMIT 1) AS stuEnName
			,(SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`studentId`) AS stu_code
			,(SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`studentId`) AS sex		
		FROM
			rms_grading_detail AS sd 
		WHERE 
			sd.gradingId =$gradingId 
		GROUP BY sd.`studentId` ORDER BY 
		(SELECT ".$studentEnName." FROM `rms_student`AS s  WHERE s.`stu_id`=sd.`studentId`) ASC ";
		return $db->fetchAll($sql);
	}
	
   function getSubjectScoreByCaterialID($gradingId,$studentId,$criteriaId){
		if($studentId==null){
			return false;
		}
		$db = $this->getAdapter();
		$sql="
		SELECT
			sd.*
		FROM
			rms_grading_detail AS sd
		WHERE sd.gradingId = $gradingId
		AND sd.`criteriaId` = $criteriaId
		AND sd.`studentId`= $studentId ORDER BY sd.criteriaId ASC LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	public function updateSubjectScoreByClass($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{		
			
			
			$status = empty($_data['status'])?0:1;
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$group_info = $dbExternal->getGroupDetailByID($_data['group']);
			$academicYear = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			$subjectId = $_data['subjectId'];
			$maxSubjectScore = $_data['maxSubjectScore'];
			$_arr = array(
					'branchId'			=>$_data['branch_id'],
					'groupId'			=>$_data['group'],
					'titleScore'		=>$_data['title'],
					'dateInput'			=>date("Y-m-d"),
			        'examType'			=>$_data['examType'],
					
					'forMonth'			=>$_data['forMonth'],
					'forSemester'		=>$_data['forSemester'],
					'academicYear'		=>$academicYear,
					
					'subjectId'			=>$subjectId,
					'inputOption'		=>2, //1 normal,2 teache input
					
					'note'				=>$_data['note'],
					'status'			=>$status ,
					
					'teacherId'			=>$this->getUserExternalId(),
					'modifyDate'		=>date("Y-m-d H:i:s"),
			);
			
			$id=$_data['id'];
			
			$where="id=".$id;
			$this->update($_arr, $where);
		
		if(!empty($_data['status'])){
			
			$this->_name='rms_grading_detail';
			$this->delete("gradingId=".$id);
			
			$this->_name='rms_grading_total';
			$this->delete("gradingId=".$id);
			
			$gradingId = empty($group_info['gradingId'])?0:$group_info['gradingId'];
			$arrSearch  = array(
				'gradingId'=>$gradingId
				,'subjectId'=>$subjectId
			);
			$criterial = $dbExternal->getGradingSystemDetail($arrSearch);
			
			
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				$totalScoreAverage = 0;
				$criteriaAmount = count($criterial) ;
				if(!empty($ids))foreach ($ids as $i){
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['exam_typeid'];						
						if($totalScoreAverage>0 AND $old_studentid!=$_data['student_id'.$i]){
							
							$arr=array(
								'gradingId'			=>$id,
								'studentId'			=>$old_studentid,
									
								'subjectId'			=> $subjectId,
								'totalAverage'		=> number_format($totalScoreAverage,2),
								'remark'			=> $_data['note_'.$i],
								
							);
							$this->_name='rms_grading_total';
							$this->insert($arr);
							$totalScoreAverage = 0;
						}
						
						$old_studentid=$_data['student_id'.$i];
						$pecentageScore = $rowCri['pecentage_score'];
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
								$subcriteriaAmount = count($subCriterial);
								
								$indexSub=0;
								$titleSubCriterial="";
								$titleSubCriteriaEng="";
								
								$totalGrading =0;
								foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
								
									
									if($subcriteriaAmount>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									
									$score = $_data['score_'.$i.'_'.$indexSub.$criterialId];
									
									$totalGrading = $totalGrading+$score;
									
									$arr=array(
										'gradingId'				=>$id,
										'studentId'				=>$old_studentid,
										'criteriaId'			=> $criterialId,
										'totalGrading'			=> $score,
										'criteriaAmount'		=> $subcriteriaAmount,
										'percentage'			=> $pecentageScore,
										
										
										'subCriterialTitleKh'	=> $titleSubCriterial,
										'subCriterialTitleEng'	=> $titleSubCriteriaEng,
										);
									
									$this->_name='rms_grading_detail';
									$this->insert($arr);
								
								}
								
								$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
								$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
								$totalScoreAverage = $totalScoreAverage+$totalGrading;
							}
						}else{
							$subcriteriaAmount=0;
							$totalGrading =0;
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								
								if($rowCri['timeInput']>1){
									if(empty($_data['checkAll'.$x.$criterialId])){
										$subcriteriaAmount=$subcriteriaAmount+1;
										
										$score = $_data['score_'.$i.'_'.$x.$criterialId];
										$totalGrading = $totalGrading+$score;
										
										
										$arr=array(
											'gradingId'			=> $id,
											'studentId'			=> $old_studentid,
											'criteriaId'		=> $criterialId,
											'criteriaAmount'	=> $subcriteriaAmount,
											'totalGrading'		=> $score,
											'percentage'		=> $pecentageScore,
											
											);
										
										$this->_name='rms_grading_detail';
										$this->insert($arr);
									
									}
								}else{
									$subcriteriaAmount = $rowCri['timeInput'];
									$score = $_data['score_'.$i.'_'.$x.$criterialId];
									$pecentageScore = $rowCri['pecentage_score'];
									
									
									$totalGrading = $totalGrading+$score;
									
									
									$arr=array(
										'gradingId'			=> $id,
										'studentId'			=> $old_studentid,
										'criteriaId'		=> $criterialId,
										'criteriaAmount'	=> $subcriteriaAmount,
										'totalGrading'		=> $score,
										'percentage'		=> $pecentageScore,
										
										);
									
									$this->_name='rms_grading_detail';
									$this->insert($arr);
								
								}
								
						
								
							}
							$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
							$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
							$totalScoreAverage = $totalScoreAverage+$totalGrading;
							
						}
					
					}
					
				}
				
				if(!empty($ids)){
					if($totalScoreAverage>0){
						$arr=array(
							'gradingId'		=>$id,
							'studentId'		=>$_data['student_id'.$i],
							
							'subjectId'		=> $subjectId,
							'totalAverage'	=> number_format($totalScoreAverage,2),
							
							'remark'		=>$_data['note_'.$i],
							
						);
						
						
						$this->_name='rms_grading_total';
						$this->insert($arr);
					}
				}
			}
		}
		
		
		
		  $db->commit();
		  return $id;
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   
   
   
   function getStudentForIssueScoreEdit($data){
	   $dbExternal = new Application_Model_DbTable_DbExternal();
	   
	   $students = $dbExternal->getStudentByGroup($data);
	   $criterial = $dbExternal->getGradingSystemDetail($data);
	   
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	   
	   $gradingId = empty($data['currentID'])?0:$data['currentID'];
	   
	   $keyIndex = $data['keyIndex'];
	   $maxSubjectScore = $data['maxSubjectScore'];
	   $invalidesms = "rangeMessage: '"."ពិន្ទុធំបំផុតត្រឹម  ".$maxSubjectScore." / Maximum Score is ".$maxSubjectScore."'";
	   
	   $identity="";
	   $arrClassCol = array(
			2=>"col-md-6 col-sm-6 col-xs-12"
			,3=>"col-md-4 col-sm-4 col-xs-12"
			,4=>"col-md-3 col-sm-3 col-xs-12"
	   );
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px"  >លុប<small class="lableEng" >Delete</small></th>';
					$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
					$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
					$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['exam_typeid'];
						$string.='<th class="criterialTitle" scope="col" >'.$rowCri['criterialTitle'].'<small class="lableEng" >'.$rowCri['criterialTitleEng'].'</small>';
						$classCol = "col-md-12 col-sm-12 col-xs-12";
						
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
								$coutnSubCriterial = count($subCriterial);
								$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
								$indexSub=0;
								
								$titleSubCriteria="";
								$titleSubCriteriaEng="";
								foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
									if($coutnSubCriterial>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									$string.='<div class="'.$classCol.'">';
										$string.='<strong  >'.$maxSubjectScore.'</strong>';
										$string.='<span class="titleSubCriterial">'.$titleSubCriterial.'</span>';
										$string.='<small class="lableEng" >'.$titleSubCriteriaEng.'</small>';
										
									$string.='</div>';
								}
							}
						}else{
							
							$classCol = empty($arrClassCol[$rowCri['timeInput']])?$classCol:$arrClassCol[$rowCri['timeInput']];
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								$string.='<div class="'.$classCol.'">';
								$string.='<strong  >'.$maxSubjectScore.'</strong>';
								if($rowCri['timeInput']>1){
									$string.='<div class="custom-control custom-checkbox ">';
											$string.='<input type="checkbox" class="checkbox custom-control-input" name="checkAll'.$x.$criterialId.'" id="checkAll'.$x.$criterialId.'" value="all" OnChange="checkAllCaterial('.$x.$criterialId.'); calculateAverage();"  >';
											$string.='<label class="custom-control-label" for="checkAll'.$x.$criterialId.'">';
												$string.='<small class="small-label">មិនបញ្ចូល<br />No Entry</small>';
											$string.='</label>';
										$string.='</div>';
								}		
								$string.='</div>';
							}
						}
						
					$string.='</th>';
					}
					$string.='<th scope="col">មធ្យមភាគ<small class="lableEng" >Average</small></th>';
					$string.='<th scope="col">សម្គាល់<small class="lableEng" >Remark</small></th>';
					$string.='';
			$string.='</tr>';
		$string.='</thead>';
		
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
					
			$string.='<tr class="rowData '.$rowClasss.'" id="row'.$keyIndex.'">';
				$string.='<td data-label="'.$tr->translate("REMOVE_RECORD").'"  align="center"><span title="'.$tr->translate("REMOVE_RECORD").'" class="removeRow" onclick="deleteRecord('.$keyIndex.')" ><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></span></td>';
				$string.='<td data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
				$string.='<td data-label="'.$tr->translate("STUDENT").'"  align="left">';
					$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
					$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
				$string.='</td>';
				$string.='<td data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
				
				if(!empty($criterial)) foreach($criterial AS $rowCri){
					$criterialId=$rowCri['exam_typeid'];
					
					$classCol = "col-md-12 col-sm-12 col-xs-12";
					
					
					$string.='<td data-label="'.$rowCri['criterialTitle'].'" >';
					$string.='<div class="form-group">';
					
					
						$rsScore = $dbExternal->getScoreByCriterial($gradingId,$stu['stu_id'],$criterialId);
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$coutnSubCriterial = count($subCriterial);
								$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
								$indexSub=0;
								
								if(!empty($rsScore)) foreach($rsScore AS $score){ $indexSub++;
									$string.='<div class="'.$classCol.'">';
										$string.='<input value="'.$score['totalGrading'].'" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$indexSub.$criterialId.'"  name="score_'.$keyIndex.'_'.$indexSub.$criterialId.'" />';
									$string.='</div>';
								}
								
							}
						}else{
							
							$classCol = empty($arrClassCol[$rowCri['timeInput']])?$classCol:$arrClassCol[$rowCri['timeInput']];
							$k=0;
							if(!empty($rsScore)) foreach($rsScore AS $score){$k++;
								$string.='<div class="'.$classCol.'">';
									$string.='<input value="'.$score['totalGrading'].'" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$k.$criterialId.'"  name="score_'.$keyIndex.'_'.$k.$criterialId.'" />';
								$string.='</div>';
							}
							$currentTimeInput = count($rsScore);
							if($rowCri['timeInput']>$currentTimeInput){
								for ($x = $currentTimeInput+1; $x <= $rowCri['timeInput']; $x++) {
									
									$string.='<div class="'.$classCol.'">';
									$string.='<input value="0" readOnly data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" id="score_'.$keyIndex.'_'.$x.$criterialId.'"  name="score_'.$keyIndex.'_'.$x.$criterialId.'" />';
									$string.='</div>';
								}
							}
						}
					$string.='</div>';
					$string.='</td>';
				}
					
				$string.='<td data-label="មធ្យមភាគ/Average"><input readOnly dojoType="dijit.form.TextBox" class="fullside" id="average'.$keyIndex.'" name="average'.$keyIndex.'"  value="0" type="text" ></td>';
				$string.='<td data-label="'.$tr->translate("NOTE").'"><input dojoType="dijit.form.TextBox" class="fullside" name="note_'.$keyIndex.'"  value="" type="text" ></td>';
				$string.='';
			$string.='</tr>';
			
		}
		
		
		
		$string.='';
		$string.='</table>';
		
		
		$htmlGradingInfo='';
				$htmlGradingInfo.='<div class="card-info bg-gradient-directional-notice">';
					$htmlGradingInfo.='<div class="card-content">';
						$htmlGradingInfo.='<div class="card-body">';
							$htmlGradingInfo.='<div class="media d-flex">';
								$htmlGradingInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
								
									$htmlGradingInfo.='<ul class="optListRow gradingInfo">';
										$htmlGradingInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">'.$tr->translate("GRADING_INFO").'</h4></li>';
										if(!empty($criterial)) foreach($criterial AS $rowCri){
											$htmlGradingInfo.='<li class="opt-items two-column"><div class="col-md-8 col-sm-8 col-xs-12">'.$rowCri['criterialTitle'].'<small class="lableEng">'.$rowCri['criterialTitleEng'].'</small></div><div class="col-md-4 col-sm-4 col-xs-12">: <span class="text-value">'.$rowCri['pecentage_score'].' %</span></div></li>';
										}
									$htmlGradingInfo.='</ul>';
								$htmlGradingInfo.='</div>';
								$htmlGradingInfo.='<div class="align-self-top">';
									$htmlGradingInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
								$htmlGradingInfo.='</div>';
							$htmlGradingInfo.='</div>';
						$htmlGradingInfo.='</div>';
					$htmlGradingInfo.='</div>';
				$htmlGradingInfo.='</div>';
	   
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>$identity
		,'keyIndex'=>$keyIndex
		,'htmlGradingInfo'=>$htmlGradingInfo
	   );
	   
	   return $arrContent;
   }
   
   
   function getStudentForIssueScore($data){
	   $dbExternal = new Application_Model_DbTable_DbExternal();
	   $students = $dbExternal->getStudentByGroup($data);
	 
	   $criterial = $dbExternal->getGradingSystemDetail($data);
	   
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	   
	   $keyIndex = $data['keyIndex'];
	   $maxSubjectScore = $data['maxSubjectScore'];
	   $invalidesms = "rangeMessage: '"."ពិន្ទុធំបំផុតត្រឹម  ".$maxSubjectScore." / Maximum Score is ".$maxSubjectScore."'";
	   
	   $identity="";
	   $arrClassCol = array(
			2=>"col-md-6 col-sm-6 col-xs-12"
			,3=>"col-md-4 col-sm-4 col-xs-12"
			,4=>"col-md-3 col-sm-3 col-xs-12"
	   );
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px"  >លុប<small class="lableEng" >Delete</small></th>';
					$string.='<th scope="col" width="10px"  >ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
					$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
					$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['exam_typeid'];
						$string.='<th class="criterialTitle" scope="col" >'.$rowCri['criterialTitle'].'<small class="lableEng" >'.$rowCri['criterialTitleEng'].'</small>';
						$classCol = "col-md-12 col-sm-12 col-xs-12";
						
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
								$coutnSubCriterial = count($subCriterial);
								$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
								$indexSub=0;
								
								$titleSubCriteria="";
								$titleSubCriteriaEng="";
								foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
									if($coutnSubCriterial>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									$string.='<div class="'.$classCol.'">';
										$string.='<strong  >'.$maxSubjectScore.'</strong>';
										$string.='<span class="titleSubCriterial">'.$titleSubCriterial.'</span>';
										$string.='<small class="lableEng" >'.$titleSubCriteriaEng.'</small>';
										
									$string.='</div>';
								}
							}
						}else{
							
							$classCol = empty($arrClassCol[$rowCri['timeInput']])?$classCol:$arrClassCol[$rowCri['timeInput']];
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								$string.='<div class="'.$classCol.'">';
								$string.='<strong  >'.$maxSubjectScore.'</strong>';
								if($rowCri['timeInput']>1){
									$string.='<div class="custom-control custom-checkbox ">';
											$string.='<input type="checkbox" class="checkbox custom-control-input" name="checkAll'.$x.$criterialId.'" id="checkAll'.$x.$criterialId.'" value="all" OnChange="checkAllCaterial('.$x.$criterialId.'); calculateAverage();"  >';
											$string.='<label class="custom-control-label" for="checkAll'.$x.$criterialId.'">';
												$string.='<small class="small-label">មិនបញ្ចូល<br />No Entry</small>';
											$string.='</label>';
										$string.='</div>';
								}		
								$string.='</div>';
							}
						}
						
					$string.='</th>';
					}
					$string.='<th scope="col">មធ្យមភាគ<small class="lableEng" >Average</small></th>';
					$string.='<th scope="col">សម្គាល់<small class="lableEng" >Remark</small></th>';
					
					$string.='';
			$string.='</tr>';
		$string.='</thead>';
		
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
					
			$string.='<tr class="rowData '.$rowClasss.'" id="row'.$keyIndex.'">';
				$string.='<td data-label="'.$tr->translate("REMOVE_RECORD").'"  align="center"><span title="'.$tr->translate("REMOVE_RECORD").'" class="removeRow" onclick="deleteRecord('.$keyIndex.')" ><i class="glyphicon glyphicon-trash" aria-hidden="true"></i></span></td>';
				$string.='<td data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
				$string.='<td data-label="'.$tr->translate("STUDENT").'"  align="left">';
					$string.='<strong class="text-dark">'.$stu['stuCode'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
					$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
					$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
				$string.='</td>';
				$string.='<td data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
				
				if(!empty($criterial)) foreach($criterial AS $rowCri){
					$criterialId=$rowCri['exam_typeid'];
					
					$classCol = "col-md-12 col-sm-12 col-xs-12";
					
					
					$string.='<td data-label="'.$rowCri['criterialTitle'].'" >';
					$string.='<div class="form-group">';
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$coutnSubCriterial = count($subCriterial);
								$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
								$indexSub=0;
								foreach ($subCriterial as $subCriTitle){ $indexSub++;
									$string.='<div class="'.$classCol.'">';
										$string.='<input value="0" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$indexSub.$criterialId.'"  name="score_'.$keyIndex.'_'.$indexSub.$criterialId.'" />';
									$string.='</div>';
								}
							}
						}else{
							$classCol = empty($arrClassCol[$rowCri['timeInput']])?$classCol:$arrClassCol[$rowCri['timeInput']];
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								
								$string.='<div class="'.$classCol.'">';
								$string.='<input value="0" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$x.$criterialId.'"  name="score_'.$keyIndex.'_'.$x.$criterialId.'" />';
								$string.='</div>';
							}
						}
					$string.='</div>';
					$string.='</td>';
				}
					
				
				$string.='<td data-label="មធ្យមភាគ/Average"><input readOnly dojoType="dijit.form.TextBox" class="fullside" id="average'.$keyIndex.'" name="average'.$keyIndex.'"  value="0" type="text" ></td>';
				$string.='<td data-label="សម្គាល់/Remark"><input dojoType="dijit.form.TextBox" class="fullside" name="note_'.$keyIndex.'"  value="" type="text" ></td>';
				$string.='';
			$string.='</tr>';
			
		}
		
		
		
		$string.='';
		$string.='</table>';
		
		
		$htmlGradingInfo='';
				$htmlGradingInfo.='<div class="card-info bg-gradient-directional-notice">';
					$htmlGradingInfo.='<div class="card-content">';
						$htmlGradingInfo.='<div class="card-body">';
							$htmlGradingInfo.='<div class="media d-flex">';
								$htmlGradingInfo.='<div class="media-body text-dark text-left align-self-bottom ">';
								
									$htmlGradingInfo.='<ul class="optListRow gradingInfo">';
										$htmlGradingInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">'.$tr->translate("GRADING_INFO").'</h4></li>';
										if(!empty($criterial)) foreach($criterial AS $rowCri){
											$htmlGradingInfo.='<li class="opt-items two-column"><div class="col-md-8 col-sm-8 col-xs-12">'.$rowCri['criterialTitle'].'<small class="lableEng">'.$rowCri['criterialTitleEng'].'</small></div><div class="col-md-4 col-sm-4 col-xs-12">: <span class="text-value">'.$rowCri['pecentage_score'].' %</span></div></li>';
										}
									$htmlGradingInfo.='</ul>';
								$htmlGradingInfo.='</div>';
								$htmlGradingInfo.='<div class="align-self-top">';
									$htmlGradingInfo.='<i class="fa fa-info-circle icon-opacity2 text-dark font-large-4 float-end"></i>';
								$htmlGradingInfo.='</div>';
							$htmlGradingInfo.='</div>';
						$htmlGradingInfo.='</div>';
					$htmlGradingInfo.='</div>';
				$htmlGradingInfo.='</div>';
	   
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>$identity
		,'keyIndex'=>$keyIndex
		,'htmlGradingInfo'=>$htmlGradingInfo
	   );
	   
	   return $arrContent;
   }
   
   public function calculateAverage($_data){
		$db = $this->getAdapter();
		
		try{
			$dbExternal = new Application_Model_DbTable_DbExternal();
			
			$subjectId = $_data['subjectId'];
			$maxSubjectScore = $_data['maxSubjectScore'];
			$gradingId = empty($_data['gradingId'])?0:$_data['gradingId'];
			$arrSearch  = array(
				'gradingId'=>$gradingId
				,'subjectId'=>$subjectId
			);
			$criterial = $dbExternal->getGradingSystemDetail($arrSearch);
			
			if(!empty($_data['indexKey'])){
				$i=$_data['indexKey'];
				$totalScoreAverage = 0;
				$criteriaAmount = count($criterial) ;
				
				if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['exam_typeid'];						
						
						$pecentageScore = $rowCri['pecentage_score'];
						
						
						
						if(!empty($rowCri['subjectId'])){
							if(!empty($rowCri['subCriterialTitleKh'])){
								$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
								$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
								$subcriteriaAmount = count($subCriterial);
								
								$indexSub=0;
								$titleSubCriterial="";
								$titleSubCriteriaEng="";
								
								$totalGrading =0;
								foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
								
									
									if($subcriteriaAmount>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									
									$score = $_data['score_'.$i.'_'.$indexSub.$criterialId];
									
									$totalGrading = $totalGrading+$score;
									
								}
								
								$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
								$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
								$totalScoreAverage = $totalScoreAverage+$totalGrading;
							}
						}else{
							$subcriteriaAmount=0;
							$totalGrading =0;
							for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
								
								if($rowCri['timeInput']>1){
									if(empty($_data['checkAll'.$x.$criterialId])){
										$subcriteriaAmount=$subcriteriaAmount+1;
										
										$score = $_data['score_'.$i.'_'.$x.$criterialId];
										$totalGrading = $totalGrading+$score;
									
									}
								}else{
									$subcriteriaAmount = $rowCri['timeInput'];
									$score = $_data['score_'.$i.'_'.$x.$criterialId];
									$pecentageScore = $rowCri['pecentage_score'];
									
									
									$totalGrading = $totalGrading+$score;
						
								
								}
								
						
								
							}
							$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
							$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
							
							$totalScoreAverage = $totalScoreAverage+$totalGrading;
							
						}
					
					}
					
				return number_format($totalScoreAverage,2);
			}else{
				$oldIndexI=0;
				$arrAverageOfIdentity = array();
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					
					$totalScoreAverage = 0;
					$criteriaAmount = count($criterial) ;
					if(!empty($ids))foreach ($ids as $i){
						
						if(!empty($criterial)) foreach($criterial AS $rowCri){
							$criterialId=$rowCri['exam_typeid'];						
							if($totalScoreAverage>0 AND $old_studentid!=$_data['student_id'.$i]){
								
								$arrAverageOfIdentity[$oldIndexI]=number_format($totalScoreAverage,2);
								$totalScoreAverage = 0;
							}
							
							$oldIndexI=$i;
							$old_studentid=$_data['student_id'.$i];
							
							$pecentageScore = $rowCri['pecentage_score'];
							if(!empty($rowCri['subjectId'])){
								if(!empty($rowCri['subCriterialTitleKh'])){
									$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
									$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
									$subcriteriaAmount = count($subCriterial);
									
									$indexSub=0;
									$titleSubCriterial="";
									$titleSubCriteriaEng="";
									
									$totalGrading =0;
									foreach ($subCriterial AS $keyV => $subCriTitle){ $indexSub++;
									
										
										if($subcriteriaAmount>1){
											$titleSubCriterial = $subCriTitle;
											$titleSubCriteriaEng = $subCriterialEng[$keyV];
										}
										
										$score = $_data['score_'.$i.'_'.$indexSub.$criterialId];
										
										$totalGrading = $totalGrading+$score;
										
									
									}
									
									$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
									$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
								
									$totalScoreAverage = $totalScoreAverage+$totalGrading;
								}
							}else{
								$subcriteriaAmount=0;
								$totalGrading =0;
								for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
									
									if($rowCri['timeInput']>1){
										if(empty($_data['checkAll'.$x.$criterialId])){
											$subcriteriaAmount=$subcriteriaAmount+1;
											
											$score = $_data['score_'.$i.'_'.$x.$criterialId];
											$totalGrading = $totalGrading+$score;
											
											
										
										}
									}else{
										$subcriteriaAmount = $rowCri['timeInput'];
										$score = $_data['score_'.$i.'_'.$x.$criterialId];
										$pecentageScore = $rowCri['pecentage_score'];
										
										
										$totalGrading = $totalGrading+$score;
										
										
									
									}
									
							
									
								}
								$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
								$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
								
								$totalScoreAverage = $totalScoreAverage+$totalGrading;
								
							}
						
						}
						
					}
					
					if(!empty($ids)){
						if($totalScoreAverage>0){
							$arrAverageOfIdentity[$oldIndexI]=number_format($totalScoreAverage,2);
						}
					}
				}
			
				return $arrAverageOfIdentity;
			
			}
			
		
		 
		}catch (Exception $e){
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
}