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
				,(SELECT grading.title FROM `rms_scoreengsetting` AS grading WHERE grading.type=2 AND grading.id=g.gradingId LIMIT 1)AS gradingSystem
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
			$criterial = $dbExternal->getGradingSystemDetail($gradingId);
			
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				$total_score = 0;
				$criteriaAmount = count($criterial) ;
				if(!empty($ids))foreach ($ids as $i){
					
					foreach ($criterial as $rrw){
						
						if($total_score>0 AND $old_studentid!=$_data['student_id'.$i]){
							
							$arr=array(
								'gradingId'		=>$id,
								'studentId'		=>$_data['student_id'.$i],
								
								'subjectId'		=> $subjectId,
								'totalGrading'			=> $total_score,
								'totalAverage'	=> number_format(($total_score)/$criteriaAmount,2),
								'criteriaAmount'=> $criteriaAmount,
								'remark'		=>$_data['note_'.$i],
								
							);
							$this->_name='rms_grading_total';
							$this->insert($arr);
							$total_score = 0;
						}
						$old_studentid=$_data['student_id'.$i];
						$score = $_data["score_".$i."_".$rrw['exam_typeid']];
						$pecentageScore = $_data["pecentage_score".$i."_".$rrw['exam_typeid']];
						$totalAverage  = $score*($pecentageScore/100);
						
						$total_score = $total_score+$score;
						
							$arr=array(
								'gradingId'		=>$id,
								'studentId'		=>$old_studentid,
								'criteriaId'	=> $rrw['exam_typeid'],
								'totalGrading'	=> $score,
								'percentage'	=> $pecentageScore,
								'totalAverage'	=> $totalAverage,
								
							);
							
						$this->_name='rms_grading_detail';
						$this->insert($arr);
						
					}
				}
				
				if(!empty($ids)){
					if($total_score>0){
						$arr=array(
							'gradingId'		=>$id,
							'studentId'		=>$_data['student_id'.$i],
							
							'subjectId'		=> $subjectId,
							'totalGrading'			=> $total_score,
							'totalAverage'	=> number_format(($total_score)/$criteriaAmount,2),
							'criteriaAmount'=> $criteriaAmount,
							'remark'		=>$_data['note_'.$i],
							
						);
						$this->_name='rms_grading_total';
						$this->insert($arr);
						$this->insert($arr);
					}
				}
			}
		
		  $db->commit();
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
		$where="id=".$_data['gradingId'];
		$this->update($_arr, $where);
		
		if(!empty($_data['status'])){
			$id=$_data['gradingId'];
			$this->_name='rms_grading_detail';
			$this->delete("gradingId=".$id);
			
			$this->_name='rms_grading_total';
			$this->delete("gradingId=".$id);
			
			$gradingId = empty($group_info['gradingId'])?0:$group_info['gradingId'];
			$criterial = $dbExternal->getGradingSystemDetail($gradingId);
			
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				$total_score = 0;
				$criteriaAmount = count($criterial) ;
				if(!empty($ids))foreach ($ids as $i){
					
					foreach ($criterial as $rrw){
						
						if($total_score>0 AND $old_studentid!=$_data['student_id'.$i]){
							$arr=array(
								'gradingId'		=>$id,
								'studentId'		=>$old_studentid,
								
								'subjectId'		=> $subjectId,
								'totalGrading'	=> $total_score,
								'totalAverage'	=> number_format(($total_score)/$criteriaAmount,2),
								'criteriaAmount'=> $criteriaAmount,
								'remark'		=>$_data['note_'.$i],
								
							);
							$this->_name='rms_grading_total';
							$this->insert($arr);
							$total_score = 0;
						}
						$old_studentid=$_data['student_id'.$i];
						$score = $_data["score_".$i."_".$rrw['exam_typeid']];
						$pecentageScore = $_data["pecentage_score".$i."_".$rrw['exam_typeid']];
						$totalAverage  = $score*($pecentageScore/100);
						
						$total_score = $total_score+$score;
						
							$arr=array(
								'gradingId'		=>$id,
								'studentId'		=>$old_studentid,
								'criteriaId'	=> $rrw['exam_typeid'],
								'totalGrading'	=> $score,
								'percentage'	=> $pecentageScore,
								'totalAverage'	=> $totalAverage,
								
							);
							
						$this->_name='rms_grading_detail';
						$this->insert($arr);
						
					}
				}
				
				if(!empty($ids)){
					if($total_score>0){
						$arr=array(
							'gradingId'		=>$id,
							'studentId'		=>$_data['student_id'.$i],
							
							'subjectId'		=> $subjectId,
							'totalGrading'	=> $total_score,
							'totalAverage'	=> number_format(($total_score)/$criteriaAmount,2),
							'criteriaAmount'=> $criteriaAmount,
							'remark'		=>$_data['note_'.$i],
							
						);
						$this->_name='rms_grading_total';
						$this->insert($arr);
					}
				}
			}
		}
		
		
		
		  $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   
   
   
   function getStudentForIssueScore($data){
	   $dbExternal = new Application_Model_DbTable_DbExternal();
	   $students = $dbExternal->getStudentByGroup($data);
	 
	   $criterial = $dbExternal->getGradingSystemDetail($data);
	   
	   $tr=Application_Form_FrmLanguages::getCurrentlanguage();
	   $db=$this->getAdapter();
	   
	   $keyIndex = $data['keyIndex'];
	   $maxSubjectScore = $data['maxSubjectScore'];
	   $invalidesms = "rangeMessage:".$maxSubjectScore;
	   
	   $string='';
		$string.='<table class="collape responsiveTable" id="table" >';
			$string.='<thead>';
				$string.='<tr class="head-td" align="center">';
					$string.='<th scope="col" width="10px"  >'.$tr->translate('DEL').'</th>';
					$string.='<th scope="col" width="10px"  >'.$tr->translate('NUM').'</th>';
					$string.='<th scope="col"  style="width:150px;">'.$tr->translate('STUDENT').'</th>';
					$string.='<th scope="col" >'.$tr->translate('SEX').'</td>';
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$colspan=1;
						if($rowCri['timeInput']>1){
							$colspan=$rowCri['timeInput'];
						}
						
					$string.='<th  scope="col" >'.$rowCri['criterialTitle'].'</td>';
					}
					$string.='<th scope="col">'.$tr->translate('NOTE').'</th>';
					$string.='';
			$string.='</tr>';
		$string.='<thead>';
		
		if(!empty($students)) foreach($students AS $key => $stu){
			$key++;
			$keyIndex=$keyIndex+1;
			
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
				$string.='</td>';
				$string.='<td data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
				
				if(!empty($criterial)) foreach($criterial AS $rowCri){
					$criterialId=$rowCri['exam_typeid'];
					$string.='<td data-label="'."'".$rowCri['criterialTitle']."'".'" >';
					$string.='<div class="form-group">';
						for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
							$string.='<div class="col-md-12 col-sm-12 col-xs-12">';
							$string.='<input value="0" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" id="score_'.$keyIndex.'_'.$x.$criterialId.'"  name="score_'.$keyIndex.'_'.$x.$criterialId.'" />';
							$string.='</div>';
						}
					$string.='</div>';
					$string.='</td>';
				}
					
				
				
				$string.='<td data-label="'.$tr->translate("NOTE").'"><input dojoType="dijit.form.TextBox" class="fullside" name="note_'.$keyIndex.'"  value="" type="text" ></td>';
				$string.='';
			$string.='</tr>';
			
		}
		
		
		
		$string.='';
		$string.='</table>';
	   
	   $arrContent = array(
		'contentHtml'=>$string
		,'identity'=>""
		,'keyIndex'=>$keyIndex
	   );
	   
	   return $arrContent;
   }
   
   
}