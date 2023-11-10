<?php

class Application_Model_DbTable_DbGradingScore extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_grading';
    	
	public static function getUserExternalId(){
		$sessionUserExternal=new Zend_Session_Namespace(TEACHER_AUTH);
		$userId = $sessionUserExternal->userId;
		$userId = empty($userId) ? 0 :$userId;
		return $userId;
	}
	
	function getAllGradingScore($search=null){
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
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
				
				,(SELECT CONCAT(es.title,es.title_en) FROM `rms_exametypeeng` AS es WHERE es.id = grd.criteriaId LIMIT 1) AS criterialTitle 
				
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.examType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,g.id AS  groupId
				,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_grading_tmp AS grd,
					rms_group AS g 
			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
		
		$where ='';
		$from_date =(empty($search['start_date']))? '1': " grd.dateInput >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " grd.dateInput <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['externalAuth'])){
			$where.=' AND grd.teacherId='.$this->getUserExternalId();
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
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
		$where.= " AND grd.teacherId =".$dbp->getTeacherUserId();
		$order=" ORDER BY grd.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	public function addScoreGradingByClass($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$dbExternal = new Application_Model_DbTable_DbExternal();
			$group_info = $dbExternal->getGroupDetailByIDExternal($_data['group']);
			$academicYear = empty($group_info['academic_year'])?0:$group_info['academic_year'];
			$subjectId = $_data['subjectId'];
			$maxSubjectScore = $_data['maxSubjectScore'];
			$gradingSettingId = empty($group_info['gradingId'])?0:$group_info['gradingId'];
				
			$_arr = array(
					'branchId'			=>$_data['branch_id'],
					'gradingSettingId'	=>$gradingSettingId,
					'academicYear'		=>$academicYear,
					'groupId'			=>$_data['group'],
			        'examType'			=>$_data['examType'],
					
					'forMonth'			=>$_data['forMonth'],
					'forSemester'		=>$_data['forSemester'],
					
					'subjectId'			=>$subjectId,
					'criteriaId'=> $_data['criteriaId'],
					
					'inputOption'		=>2, //1 normal,2 teache input
					
					'note'				=>$_data['note'],
					'status'			=>1,
					
					'teacherId'			=>$this->getUserExternalId(),
					'createDate'		=>date("Y-m-d H:i:s"),
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'dateInput'			=>date("Y-m-d"),
			);
			$this->_name='rms_grading_tmp';		
			$id=$this->insert($_arr);
			
			$criteriaSubmitSettingId=9;//for final score submit
			
			$arrSearch  = array(
					'gradingId'=>$gradingSettingId
					,'subjectId'=>$subjectId
					,'examType'=>$_data['examType']
			);
			if($_data['criteriaId']==$criteriaSubmitSettingId){//submit final score
				$_arr = array(
						'branchId'			=>$_data['branch_id'],
						'gradingSettingId'	=>$gradingSettingId,
						'groupId'			=>$_data['group'],
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
				$idGrading=$this->insert($_arr);
			}else{
				$arrSearch['criteriaId']=$_data['criteriaId'];
			}
			
			
			$criterial = $dbExternal->getGradingCriteriaItems($arrSearch);
			
			$old_studentid = 0;
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				
				$totalScoreAverage = 0;
				$totalGrading =0;
				if(!empty($ids))foreach ($ids as $i){
					
					if(!empty($criterial)) foreach($criterial AS $rowCri){
						$criterialId=$rowCri['criteriaId'];			
						
						if($_data['criteriaId']==$criteriaSubmitSettingId AND $totalScoreAverage>0 AND $old_studentid!=$_data['student_id'.$i]){//submit final score
								
							$arr=array(
									'gradingId'			=>$idGrading,
									'studentId'			=>$old_studentid,
									'subjectId'			=> $subjectId,
									'totalAverage'		=> number_format($totalScoreAverage,2),
									'remark'			=> $_data['note_'.$i],
									'maxScore'			=> $_data['maxSubjectScore'],
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
								foreach ($subCriterial AS $keyV => $subCriTitle){ 
									
									$indexSub++;
									
									if($subcriteriaAmount>1){
										$titleSubCriterial = $subCriTitle;
										$titleSubCriteriaEng = $subCriterialEng[$keyV];
									}
									
									$score = $_data['score_'.$i.'_'.$criterialId.'_'.$indexSub];
									
									if($criterialId==$_data['criteriaId']){
										$arr=array(
											'gradingId'				=> $id,
											'studentId'				=> $old_studentid,
											'totalGrading'			=> $score,
											'subCriterialTitleKh'	=> $titleSubCriterial,
											'subCriterialTitleEng'	=> $titleSubCriteriaEng,
										);
										
										$this->_name='rms_grading_detail_tmp';
										$this->insert($arr);
									}
									
									if($_data['criteriaId']==$criteriaSubmitSettingId){//submit final score
										
										$totalGrading = $totalGrading+$score;
										$arr=array(
												'gradingId'				=> $idGrading,
												'studentId'				=> $old_studentid,
												'subjectId'				=> $subjectId,
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
									
								}
								if($_data['criteriaId']==$criteriaSubmitSettingId){
									$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
									$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
									$totalScoreAverage = $totalScoreAverage+$totalGrading;
								}
								
							}
						}else{
							
							$idCriterials = explode(',', $_data['criteriaList_'.$i.'_'.$criterialId]);
							$totalGrading=0;
							if(!empty($idCriterials)) foreach($idCriterials as $innerId){
								$score = $_data['score_'.$i.'_'.$criterialId.'_'.$innerId];//problem or not ?score_2_11score_1_1
								if($criterialId==$_data['criteriaId']){
									$arr=array(
										'gradingId'			=> $id,
										'studentId'			=> $old_studentid,
										'note'				=> $_data['note_'.$i],
										'totalGrading'		=> $score,
									);
										
									$this->_name='rms_grading_detail_tmp';
									$this->insert($arr);
								}
								
								
								if($_data['criteriaId']==$criteriaSubmitSettingId){//submit final score
								
									$totalGrading = $totalGrading+$score;
									
									$arr=array(
											'gradingId'			=> $idGrading,
											'studentId'			=> $old_studentid,
											'subjectId'			=> $subjectId,
											'criteriaId'		=> $criterialId,
											'criteriaAmount'	=> count($idCriterials),
											'totalGrading'		=> $score,
											'percentage'		=> $pecentageScore,
									);
								
									$this->_name='rms_grading_detail';
									$this->insert($arr);
								}
							}
							
							if($_data['criteriaId']==$criteriaSubmitSettingId){//submit final score
								$subcriteriaAmount= count($idCriterials);
								$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
								$totalScoreAverage = $totalScoreAverage+$totalGrading;
							}
						}
					}
				}
				
				if($totalScoreAverage>0 AND $_data['criteriaId']==$criteriaSubmitSettingId){//submit final score
					$arr=array(
							'gradingId'		=> $idGrading,
							'studentId'		=> $_data['student_id'.$i],
							'subjectId'		=> $subjectId,
							'totalAverage'	=> number_format($totalScoreAverage,2),
							'remark'		=> $_data['note_'.$i],
							'maxScore'		=> $_data['maxSubjectScore'],
					);
				
					$this->_name='rms_grading_total';
					$this->insert($arr);
				}
			}
// 			$this->submittoGradingTotal($data);
		
		  $db->commit();
		  return $id;
		}catch(Exception $e){
			echo $e->getMessage();exit();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
   function submittoGradingTotal($_data){
   		$sql="
   			SELECT 
				branchId,
				gradingSettingId,
				academicYear,
				groupId,
				examType,
				forSemester,
				forMonth,
				subjectId,
				criteriaId,
				teacherId,
				gradingId,
				gradingId AS subcriteriaAmount,
				totalGrading AS avgTotalGrading,
				gradingId,
				studentId,
				subCriterialTitleKh,
				subCriterialTitleEng
			FROM 
				`rms_grading_tmp` gt,
				`rms_grading_detail_tmp` gtd
			WHERE gt.id=gtd.gradingId ";
   		
   			if(!empty($_data['gradingSettingId'])){
   				$sql.=" AND gradingSettingId=".$_data['gradingSettingId'];
   			}
   			if(!empty($_data['groupId'])){
   				$sql.=" AND groupId=".$_data['groupId'];
   			}
   			if(!empty($_data['examType'])){
   				$sql.=" AND examType=".$_data['examType'];
   				
   				if($_data['examType']==1){
   					if(!empty($_data['forMonth'])){
   						$sql.=" AND forMonth=".$_data['forMonth'];
   					}	
   				}
   			}
   			
   			if(!empty($_data['subjectId'])){
   				$sql.=" AND subjectId=".$_data['subjectId'];
   			}
   			if(!empty($_data['studentId'])){
   				$sql.=" AND studentId=".$_data['studentId'];
   			}
   			if(!empty($_data['criteriaId'])){
   				$sql.=" AND criteriaId=".$_data['criteriaId'];
   			}
   			//$sql.=" GROUP BY  ORDER BY criteriaId ASC ";
   			
   			return $result = $this->getAdapter()->fetchAll($sql);
   			
//    			$dbExternal = new Application_Model_DbTable_DbExternal();
//    			$group_info = $dbExternal->getGroupDetailByIDExternal($_data['group']);
//    			$academicYear = empty($group_info['academic_year'])?0:$group_info['academic_year'];
//    			$gradingSettingId = empty($group_info['gradingId'])?0:$group_info['gradingId'];
   			
//    			$_arr = array(
//    					'branchId'			=>$_data['branch_id'],
//    					'gradingSettingId'	=>$gradingSettingId,
//    					'groupId'			=>$_data['group'],
//    					'dateInput'			=>date("Y-m-d"),
//    					'examType'			=>$_data['examType'],
//    					'forMonth'			=>$_data['forMonth'],
//    					'forSemester'		=>$_data['forSemester'],
//    					'academicYear'		=>$academicYear,
//    					'subjectId'			=>$_data['subjectId'],
//    					'inputOption'		=>2, //1 normal,2 teache input
//    					'note'				=>$_data['note'],
//    					'status'			=>1,
//    					'teacherId'			=>$this->getUserExternalId(),
//    					'createDate'		=>date("Y-m-d H:i:s"),
//    					'modifyDate'		=>date("Y-m-d H:i:s"),
//    			);
//    			$this->_name='rms_grading';
//    			$id=$this->insert($_arr);
   			
//    			$old_studentid = 0;
//    			if(!empty($result)){
//    				$oldCritrial=0;
//    				$oldStudentid=0;
//    				$score=0;
//    				$subcriteriaAmount=0;
//    				$pecentageScore = 0;
//    				foreach ($result as $rs){
   					   		
//    					   		   		$arrSearch  = array(
//    					   		   				'gradingId'=>$gradingSettingId
//    					   		   				,'subjectId'=>$_data['subjectId']
//    					   		   				,'examType'=>$_data['examType']
//    					   		   		);
//    					   		   		$criterial = $dbExternal->getGradingCriteriaItems($arrSearch);
   					   		   		
//    					   		   		   $arr=array(
//    					   		   		   										'gradingId'				=>$id,
//    					   		   		   										'studentId'				=>$oldStudentid,
//    					   		   		   										'subjectId'				=>$_data['subjectId'],
//    					   		   		   										'criteriaId'			=>$oldCritrial,
//    					   		   		   										'totalGrading'			=> $rs['avgTotalGrading'],
//    					   		   		   										'criteriaAmount'		=> $rs['subcriteriaAmount'],
// //    					   		   		   										'percentage'			=> $pecentageScore,
// //    					   		   		   										'subCriterialTitleKh'	=> $titleSubCriterial,
// //    					   		   		   										'subCriterialTitleEng'	=> $titleSubCriteriaEng,
//    					   		   		   								);
   					   		   		
//    					   		   		   								$this->_name='rms_grading_detail';
//    					   		   		   								$this->insert($arr);
   					   		   		   								
//    					   		   		   								$oldStudentid = $rs['studentId'];
//    					   		   		   								$oldCritrial = $rs['criteriaId'];
//    				}
//    			}
   		




//    		$old_studentid = 0;
//    		if(!empty($_data['identity'])){
//    			$ids = explode(',', $_data['identity']);
   		
//    			$totalScoreAverage = 0;
//    			$criteriaAmount = count($criterial) ;
//    			if(!empty($ids))foreach ($ids as $i){
   					
//    				if(!empty($criterial)) foreach($criterial AS $rowCri){
//    					$criterialId=$rowCri['criteriaId'];
//    					if($totalScoreAverage>0 AND $old_studentid!=$_data['student_id'.$i]){
   							
//    						$arr=array(
//    								'gradingId'			=>$id,
//    								'studentId'			=>$old_studentid,
//    								'subjectId'			=> $subjectId,
//    								'totalAverage'		=> number_format($totalScoreAverage,2),
//    								'remark'			=> $_data['note_'.$i],
//    								'maxScore'			=> $_data['maxSubjectScore'],
//    						);
//    						$this->_name='rms_grading_total';
//    						$this->insert($arr);
//    						$totalScoreAverage = 0;
//    					}
   		
//    					$old_studentid=$_data['student_id'.$i];
//    					$pecentageScore = $rowCri['pecentage_score'];
//    					if(!empty($rowCri['subjectId'])){
//    						if(!empty($rowCri['subCriterialTitleKh'])){
//    							$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
//    							$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
//    							$subcriteriaAmount = count($subCriterial);
   		
//    							$indexSub=0;
//    							$titleSubCriterial="";
//    							$titleSubCriteriaEng="";
   		
//    							$totalGrading =0;
//    							foreach ($subCriterial AS $keyV => $subCriTitle){
//    								$indexSub++;
   		
   									
//    								if($subcriteriaAmount>1){
//    									$titleSubCriterial = $subCriTitle;
//    									$titleSubCriteriaEng = $subCriterialEng[$keyV];
//    								}
   									
//    								$score = $_data['score_'.$i.'_'.$indexSub.$criterialId];
   									
//    								$totalGrading = $totalGrading+$score;
   									
//    								$arr=array(
//    										'gradingId'				=>$id,
//    										'studentId'				=>$old_studentid,
//    										'subjectId'				=> $subjectId,
//    										'criteriaId'			=> $criterialId,
//    										'totalGrading'			=> $score,
//    										'criteriaAmount'		=> $subcriteriaAmount,
//    										'percentage'			=> $pecentageScore,
//    										'subCriterialTitleKh'	=> $titleSubCriterial,
//    										'subCriterialTitleEng'	=> $titleSubCriteriaEng,
//    								);
   									
//    								$this->_name='rms_grading_detail';
//    								$this->insert($arr);
   		
//    							}
   		
//    							$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
//    							$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
   								
//    							$totalScoreAverage = $totalScoreAverage+$totalGrading;
//    						}
//    					}else{
//    						$subcriteriaAmount=0;
//    						$totalGrading =0;
//    						for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
   		
//    							if($rowCri['timeInput']>1){
//    								if(empty($_data['checkAll'.$x.$criterialId])){
//    									$subcriteriaAmount=$subcriteriaAmount+1;
   		
//    									$score = $_data['score_'.$i.'_'.$x.$criterialId];
//    									$totalGrading = $totalGrading+$score;
   		
//    									$arr=array(
//    											'gradingId'			=> $id,
//    											'studentId'			=> $old_studentid,
//    											'subjectId'			=> $subjectId,
//    											'criteriaId'		=> $criterialId,
//    											'criteriaAmount'	=> $subcriteriaAmount,
//    											'totalGrading'		=> $score,
//    											'percentage'		=> $pecentageScore,
//    									);
   		
//    									$this->_name='rms_grading_detail';
//    									$this->insert($arr);
   										
//    								}
//    							}else{
//    								$subcriteriaAmount = $rowCri['timeInput'];
//    								$score = $_data['score_'.$i.'_'.$x.$criterialId];
//    								$pecentageScore = $rowCri['pecentage_score'];
   									
   									
//    								$totalGrading = $totalGrading+$score;
   									
   									
//    								$arr=array(
//    										'gradingId'			=> $id,
//    										'studentId'			=> $old_studentid,
//    										'subjectId'			=> $subjectId,
//    										'criteriaId'		=> $criterialId,
//    										'criteriaAmount'	=> $subcriteriaAmount,
//    										'totalGrading'		=> $score,
//    										'percentage'		=> $pecentageScore,
//    								);
   									
//    								$this->_name='rms_grading_detail';
//    								$this->insert($arr);
   		
//    							}
   		
//    						}
//    						$subcriteriaAmount= empty($subcriteriaAmount)?1:$subcriteriaAmount;
//    						$totalGrading = ($totalGrading/$subcriteriaAmount)*($pecentageScore/100);
   							
//    						$totalScoreAverage = $totalScoreAverage+$totalGrading;
//    					}
   						
//    				}
   					
//    			}
   		
//    			if(!empty($ids)){
//    				if($totalScoreAverage>0){
//    					$arr=array(
//    							'gradingId'		=>$id,
//    							'studentId'		=>$_data['student_id'.$i],
//    							'subjectId'		=> $subjectId,
//    							'totalAverage'	=> number_format($totalScoreAverage,2),
//    							'remark'		=>$_data['note_'.$i],
//    							'maxScore'			=> $_data['maxSubjectScore'],
//    					);
   		
//    					$this->_name='rms_grading_total';
//    					$this->insert($arr);
//    				}
//    			}
//    		}
   }
   
 
   function getGradingScoreById($gradingId){
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
		   grd.id,
		   grd.status,
		   grd.criteriaId,
		   grd.groupId,
		   grd.subjectId,
		   grd.examType,
		   grd.forMonth,
		   grd.forSemester,
		   grd.criteriaId,
		   grd.note,
		   	(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
		   	,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
		   	,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
		   	,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
		   	,CASE
		   	WHEN grd.examType = 2 THEN grd.forSemester
		   	ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1)
		   	END AS forMonthTitle
		   	,g.group_code AS  groupCode
		   	,g.gradingId AS  gradingId
		   	,g.academic_year AS  academicYearId
		   	,g.grade AS  gradeId
		   	,g.degree AS  degreeId
		   	,(SELECT te.signature from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeacherSigature
		   	,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameKh
		   	,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameEng
		   	,(SELECT gsjd.max_score FROM rms_group_subject_detail AS gsjd WHERE g.id = gsjd.group_id AND gsjd.subject_id =grd.subjectId LIMIT 1 ) AS maxSubjectscore
		   	,(SELECT te.signature from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teacherSigature
		   	,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameKh
		   	,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameEng
		   	,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
		   	,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitleKh
		   	,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitleEng
		   	,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
		   	,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
		   	,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
		   	,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
		   	,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		   	
   			";
   			$sql.=" FROM rms_grading_tmp AS grd,
   					rms_group AS g
   			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
   
   			$where ='';
   			$where.=' AND grd.teacherId='.$this->getUserExternalId();
   			$where.=' AND grd.id='.$gradingId;
		   	$where.=' LIMIT 1 ';
		   	return $db->fetchRow($sql.$where);
   }
   
   function getStudentForGradingScore($data){//single entry by criteria
   	$dbExternal = new Application_Model_DbTable_DbExternal();
   	$students = $dbExternal->getStudentByGroupExternal($data);
   	 
   	if(!empty($data['criteriaId']) AND $data['criteriaId']==9){
   		unset($data['criteriaId']);
   		$data['getExistingData']=1;
   	}
   	$criterial = $dbExternal->getGradingCriteriaItems($data);
   	 
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
   			,5=>"col-md-2 col-sm-2 col-xs-12"
   			,6=>"col-md-2 col-sm-2 col-xs-12"
   	);
   	$string='';
   	$string.='<table class="collape responsiveTable" id="table" >';
   	$string.='<thead>';
   	$string.='<tr class="head-td" align="center">';
   	$string.='<th scope="col" width="10px">ល.រ<small class="lableEng" >N<sup>o</sup></small></th>';
   	$string.='<th scope="col"  style="width:150px;">អត្តលេខសិស្ស<small class="lableEng" >Student ID</small></th>';
   	$string.='<th scope="col"  style="width:150px;">សិស្ស<small class="lableEng" >Student</small></th>';
   	$string.='<th scope="col" >ភេទ<small class="lableEng" >Gender</small></td>';
   	 
   	if(!empty($criterial)) foreach($criterial AS $rowCri){
   		$criterialId=$rowCri['criteriaId'];
   		$string.='<th class="criterialTitle" scope="col" >'.$rowCri['criterialTitle'].'<small class="lableEng" >'.$rowCri['criterialTitleEng'].'</small>';
   		$classCol = "col-md-12 col-sm-12 col-xs-12";
   		 
   		if(!empty($rowCri['subjectId'])){//for subject
   			if(!empty($rowCri['subCriterialTitleKh'])){
   				$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
   				$subCriterialEng = explode(',', $rowCri['subCriterialTitleEng']);
   				$coutnSubCriterial = count($subCriterial);
   				$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
   				$indexSub=0;
   				 
   				$titleSubCriteria="";
   				$titleSubCriteriaEng="";
   				foreach ($subCriterial AS $keyV => $subCriTitle){
   					$indexSub++;
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
   				
   			//$classCol = empty($arrClassCol[$rowCri['timeInput']])?$classCol:$arrClassCol[$rowCri['timeInput']];
   			//for ($x = 1; $x <= $rowCri['timeInput']; $x++) {
   			for ($x = 1; $x <= 1; $x++) {
   				$string.='<div class="'.$classCol.'">';
   				$string.='<strong  >'.$maxSubjectScore.'</strong>';
   				$string.='</div>';
   			}
   		}
   		 
   		$string.='</th>';
   	}
   	$string.='<th scope="col">សម្គាល់<small class="lableEng" >Remark</small></th>';
   	 
   	$string.='';
   	$string.='</tr>';
   	$string.='</thead>';
   	
   	$resultScoreAtt = $dbExternal->getAttScoreSetting($data['gradingId']);
   	 
   	if(!empty($students)) foreach($students AS $key => $stu){
   		
   		$reductPercentage = $dbExternal->calculateScoreByAtt($stu['stu_id'],$data,$resultScoreAtt);
   		
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
   		$string.='<td data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$key.'</td>';
   		$string.='<td data-label="'.$tr->translate("NUM").'"  align="center">&nbsp;'.$stu['stuCode'].'</td>';
   		$string.='<td data-label="'.$tr->translate("STUDENT").'"  align="left">';
   		$string.='<strong class="text-dark">'.$stu['stuKhName'].'</strong><br />';
   		$string.='<strong class="text-dark">'.$stu['stuEnName'].'</strong><br />';
   		$string.='<input dojoType="dijit.form.TextBox" name="student_id'.$keyIndex.'" value="'.$stu['stu_id'].'" type="hidden" >';
   		$string.='</td>';
   		$string.='<td data-label="'.$tr->translate("SEX").'" >'.$gender.'</td>';
   		 
   		 
   		 
   		if(!empty($criterial)) foreach($criterial AS $rowCri){
   
   			$rsScore = array();
   			if(!empty($data['gradingRowId'])){
   				$gradingId = $data['gradingId'];
   				
   				$param =array(
   						'gradingId'=>$data['gradingId'],
   						'studentId'=>$stu['stu_id'],
   						'gradingRowId'=>$data['gradingRowId'],
   				);
   				$rsScore = $dbExternal->getGradingByCriterial($param);
   			}
   
   			$criterialId=$rowCri['criteriaId'];
   			$classCol = "col-md-12 col-sm-12 col-xs-12";
   				
   			$string.='<td data-label="'.$rowCri['criterialTitle'].'" >';
   			$string.='<div class="form-group">';
   			if(!empty($rowCri['subjectId'])){
   					
   				if(!empty($rowCri['subCriterialTitleKh'])){
   					$subCriterial = explode(',', $rowCri['subCriterialTitleKh']);
   					$coutnSubCriterial = count($subCriterial);
   					$classCol = empty($arrClassCol[$coutnSubCriterial])?$classCol:$arrClassCol[$coutnSubCriterial];
   					$indexSub=0;
   
   					if(!empty($rsScore)){
   						foreach($rsScore AS $score){
   							$indexSub++;
   							$string.='<div class="'.$classCol.'">';
   							$string.='<input value="'.$score['totalGrading'].'" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$criterialId.'_'.$indexSub.'"  name="score_'.$keyIndex.'_'.$criterialId.'_'.$indexSub.'" />';
   							$string.='</div>';
   						}
   					}else{
   						foreach($subCriterial as $subCriTitle){
   							$indexSub++;
   							$string.='<div class="'.$classCol.'">';
   							$string.='<input value="0" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" id="score_'.$keyIndex.'_'.$criterialId.'_'.$indexSub.'"  name="score_'.$keyIndex.'_'.$criterialId.'_'.$indexSub.'" />';
   							$string.='</div>';
   						}
   					}
   				}
   			}else{
   				
   				$param  = array(
   						'groupId'=>$data['groupId'],
   						'criteriaId'=>$criterialId,
   						'examType'=>$data['examType'],
   						'forSemester'=>$data['forSemester'],
   						'forMonth'=>$data['forMonth'],
   						'subjectId'=>$data['subjectId'],
   						'studentId'=>$stu['stu_id']
   				);
   				$resultEntry = $this->submittoGradingTotal($param);
   				$count =  !empty($data['getExistingData'])?count($resultEntry):1;
   				$count = ($count==0)?1:$count;
   				
   				$classCol = empty($arrClassCol[$count])?$classCol:$arrClassCol[$count];
   				$criterialList='';
   				for ($x = 1; $x <= $count; $x++) {
   					if($x==1){
   						$criterialList=$x;
   					}else{
   						$criterialList=$criterialList.",".$x;
   					}
   					$attScore=0;
   					$readonly="";
   					$resultScore = empty($rsScore)?0:$rsScore[0]['totalGrading'];
   					if($criterialId==1){
   						$resultScore = $maxSubjectScore-($maxSubjectScore*$reductPercentage/100);
   						$resultScore = ($resultScore<0)?0:$resultScore;
   						$readonly="readonly";
   					}
   					elseif(!empty($data['getExistingData'])){ 
   						
   						$readonly="readonly";
   						$resultScore = empty($resultEntry[$x-1]['avgTotalGrading'])?0:$resultEntry[$x-1]['avgTotalGrading'];//check more
   					}
   					 
   					$string.='<div class="'.$classCol.'">';
   					$string.='<input '.$readonly.' value="'.$resultScore.'" data-dojo-props="constraints:{min:0,max:'.$maxSubjectScore.'},'.$invalidesms.'" required="1" class="fullside" dojoType="dijit.form.NumberTextBox" type="text" onKeyup="calculateAverage('.$keyIndex.')" name="score_'.$keyIndex.'_'.$criterialId.'_'.$x.'"  id="score_'.$keyIndex.'_'.$criterialId.'_'.$x.'" />';
   					$string.='</div>';
   				}
   				
   				}
   			$string.='</div>';
   			$string.='<input dojoType="dijit.form.TextBox" class="fullside" name="criteriaList_'.$keyIndex.'_'.$criterialId.'"  value="'.$criterialList.'" type="hidden" >';
   			
   			$string.='</td>';
   		}
   		$string.='<td data-label="សម្គាល់/Remark">';
   		$string.='<input dojoType="dijit.form.TextBox" class="fullside" name="note_'.$keyIndex.'"  value="" type="text" ></td>';
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
   	$htmlGradingInfo.='<li class="opt-items titleEx"><h4 class="text-dark mb-10">ព័ត៌មានប្រព័ន្ធដាក់ពិន្ទុ / Grading Info.</h4></li>';
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
}