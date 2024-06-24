<?php

class Issue_Model_DbTable_DbScoreRefill extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_score';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	public function addStudentScore($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$dbpush = new Application_Model_DbTable_DbGlobal();
			$group_info = $dbGroup->getGroupById($_data['groupId']);
			$year_study = empty($group_info['academic_year']) ? 0 : $group_info['academic_year'];
			$scale_for_month =  $group_info['max_average'];
			$scale_for_semester = empty($group_info['semesterTotalAverage']) ? 100 : $group_info['semesterTotalAverage'];
			$semesterPercentage = empty($group_info['semesterPercentage']) ? 1 : $group_info['semesterPercentage'];

			$old_studentid = 0;
			$type = 1;

			if (!empty($_data['identity'])) {
				$ids = explode(',', $_data['identity']);
				$rssubject = $_data['selector'];
				$total_score = 0;
				$totalMutiAll = 0;
				$totalMaxScore = 0;

				$monthlySemesterAvg = 0;
				$overallAssessmentSemester = 0;
				$monthlySemesterAvg_kh=0;
				$monthlySemesterAvg_en=0;

				$totalScoreKh = 0;
				$totalScoreEng = 0;
				$totalScoreCh = 0;

				$totalMaxScoreKh = 0;
				$totalMaxScoreEng = 0;
				$totalMaxScoreCh = 0;

				$totalAmountSubjectKh = 0;
				$totalAmountSubjectEng = 0;
				$totalAmountSubjectCh = 0;

				if (!empty($ids)) foreach ($ids as $keyValue => $i) {

					foreach ($rssubject as $subject) {

						if ($total_score > 0 and $old_studentid != $_data['student_id' . $i]) {
							if ($_data['examTypeId'] == 2) {  //semester exam

								$totalMutiAll = $totalMaxScore / $scale_for_semester;
								$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
								$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
								$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

								// $dataparam = array(
								// 	'groupId'      => $_data['groupId'],
								// 	'acadmicYear' => $year_study,
								// 	'forSemester' => $_data['semesterId'],
								// 	'studentId'   => $old_studentid
								// );
								// $rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);

								// $totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
								// $totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
								// $totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;

							} else if ($_data['examTypeId'] == 1) { //For Month

								if (!empty($scale_for_month)) {
									$totalMutiAll = $totalMaxScore / $scale_for_month;
									$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
									$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
									$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
								}
							} else { 	// year

								$totalMutiAll = $totalMaxScore / $scale_for_semester;
								$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
								$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
								$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

								$dataparam = array(
									'groupId'      => $_data['groupId'],
									'acadmicYear' => $year_study,
									'studentId'   => $old_studentid
								);
								$rsSemesterAvg = $this->getAnnaulSemesterAvg($dataparam);

								$anaulKhAvg = $rsSemesterAvg['anaulKhAvg'];
								$annaulEnAvg = $rsSemesterAvg['annaulEnAvg'];
								$annaulChAvg = $rsSemesterAvg['annaulChAvg'];
								$annaulOveralAvg = $rsSemesterAvg['annaulOveralAvg'];
							}
							$avg = $total_score / $totalMutiAll;
							$avgkh = $totalScoreKh / $totalAmountSubjectKh;
							$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0;
							$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;

							$arr = array(
								'score_id' => $_data['scoreId'],
								'student_id' => $old_studentid,

								'total_score' => $total_score,
								'amount_subject' => $totalMutiAll,
								'total_avg' => $avg,
								'totalMaxScore' => $totalMaxScore,
								'type' => $type,

							);
							if ($_data['examTypeId'] == 2) {  //Semester

									$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;

									$monthlySemesterAvgKh = ($avgkh + $monthlySemesterAvg_kh) / 2;
									$monthlySemesterAvgEn = ($avgeEn + $monthlySemesterAvg_en) / 2;

									$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
									$arr["overallAssessmentSemester"] = $overallAssessmentSemester;

									$arr["totalKhAvg"] = $monthlySemesterAvg_kh;
									$arr["totalEnAvg"] = $monthlySemesterAvg_en;
								
									$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
									$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
								
							} else if ($_data['examTypeId'] == 1) {  //   Month

								$arr["totalKhAvg"] = $avgkh;
								$arr["totalEnAvg"] = $avgeEn;
								$arr["totalChAvg"] = $avgCh;
							} else {   ///  Year

								if (!empty($rsSemesterAvg)) {

									$arr["totalKhAvg"] = $avgkh;
									$arr["totalEnAvg"] = $avgeEn;
									$arr["totalChAvg"] = $avgCh;

									$arr["OveralAvgKh"] = $anaulKhAvg;
									$arr["OveralAvgEng"] = $annaulEnAvg;
									$arr["OveralAvgCh"] = $annaulChAvg;

									$arr["overallAssessmentSemester"] = $annaulOveralAvg;
								}
							}
							$this->_name = 'rms_score_monthly';
							$this->insert($arr);

							//Reset Variable
							$total_score = 0;
							$totalMutiAll = 0;
							$totalMaxScore = 0;

							$totalScoreKh = 0;
							$totalScoreEng = 0;
							$totalScoreCh = 0;

							$totalMaxScoreKh = 0;
							$totalMaxScoreEng = 0;
							$totalMaxScoreCh = 0;

							$totalAmountSubjectKh = 0;
							$totalAmountSubjectEng = 0;
							$totalAmountSubjectCh = 0;
						} else if ($keyValue > 0 and $old_studentid != $_data['student_id' . $i]) { // Check ករណីសិស្សដែលបានបញ្ចូលពិន្ទុ 0 គ្រប់មុខវិជ្ជាដោយមិន លុបឬដក Student ចេញ
							$total_score = 0;
							$totalMutiAll = 0;
							$totalMaxScore = 0;

							$totalScoreKh = 0;
							$totalScoreEng = 0;
							$totalScoreCh = 0;

							$totalMaxScoreKh = 0;
							$totalMaxScoreEng = 0;
							$totalMaxScoreCh = 0;

							$totalAmountSubjectKh = 0;
							$totalAmountSubjectEng = 0;
							$totalAmountSubjectCh = 0;
						}

						$old_studentid = $_data['student_id' . $i];
						$monthlySemesterAvg = empty($_data['monthlySemesterAvg' . $i]) ? 0 : $_data['monthlySemesterAvg' . $i];

						$monthlySemesterAvg_kh = empty($_data['totalKhAvg' . $i]) ? 0 : $_data['totalKhAvg' . $i];
						$monthlySemesterAvg_en = empty($_data['totalEnAvg' . $i]) ? 0 : $_data['totalEnAvg' . $i];
					

						$param = array(
							'groupId' => $_data['group'],
							'subjectId' => $subject,
						);

						$rsGroupSubject = $dbpush->getGroupSubjectDetail($param); //// call cut score

						if ($_data['examTypeId'] == 1) { //month
							$maxScore = $rsGroupSubject['maxScoreMonth'];
							$totalMulti = $rsGroupSubject['totalSubjectMonth'];
							$total_score = $total_score + ($_data["score_" . $i . "_" . $subject] * $totalMulti);
							$score_cut = 0;
						} else { //semester
							$maxScore = $rsGroupSubject['maxScoreSemester'];
							$totalMulti = 1; //$rsGroupSubject['totalSubjectSemester'];
							if ($rsGroupSubject['score_short'] <= 0) { //=មិនកាត់ពិន្ទុតាមមុខវិជ្ជា
								$total_score = $total_score + ($_data["score_" . $i . "_" . $subject] * $totalMulti);
								$score_cut = 0;
							} else {
								if (($_data["score_" . $i . "_" . $subject] - $rsGroupSubject['score_short']) <= 0) {
									$score = 0;
								} else {
									$score = $_data["score_" . $i . "_" . $subject] - $rsGroupSubject['score_short'];
								}
								$total_score = $total_score + ($score * $totalMulti);
								$score_cut = $rsGroupSubject['score_short'];
							}
						}

						if ($_data['subject_lang' . $subject] == 1) {
							$totalScoreKh = $totalScoreKh + ($_data["score_" . $i . "_" . $subject] * $totalMulti);
							$totalMaxScoreKh = $totalMaxScoreKh + ($maxScore * $totalMulti);
							$totalAmountSubjectKh = $totalAmountSubjectKh + $totalMulti;
						} else if ($_data['subject_lang' . $subject] == 2) {
							$totalScoreEng = $totalScoreEng + ($_data["score_" . $i . "_" . $subject] * $totalMulti);
							$totalMaxScoreEng = $totalMaxScoreEng + ($maxScore * $totalMulti);
							$totalAmountSubjectEng = $totalAmountSubjectEng + $totalMulti;
						} else if ($_data['subject_lang' . $subject] == 3) {
							$totalScoreCh = $totalScoreCh + ($_data["score_" . $i . "_" . $subject] * $totalMulti);
							$totalMaxScoreCh = $totalMaxScoreCh + ($maxScore * $totalMulti);
							$totalAmountSubjectCh = $totalAmountSubjectCh + $totalMulti;
						}

						$totalMaxScore = $totalMaxScore + ($maxScore * $totalMulti);

						$totalMutiAll = $totalMutiAll + $totalMulti;

						$arr = array(
							'score_id' 		 => $_data['scoreId'],
							'group_id'		 => $_data['groupId'],
							'student_id'     => $_data['student_id' . $i],
							'subject_id'     => $subject,

							'orgScore'       => $_data["score_" . $i . "_" . $subject],
							'subjectExam'    => $_data['amount_subject' . $i],

							'score'          => $totalMulti * $_data["score_" . $i . "_" . $subject],
							'amount_subject' => $totalMulti,
							'score_cut'      => $score_cut,
							'status'         => 1,
						);
						$this->_name = 'rms_score_detail';
						$this->insert($arr);
					}
				}

				if (!empty($ids)) {
					if ($total_score > 0) {

						if ($_data['examTypeId'] == 2) {  //semester exam

							$totalMutiAll = $totalMaxScore / $scale_for_semester;
							$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
							$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
							$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

							$dataparam = array(
								'groupId'      => $_data['groupId'],
								'acadmicYear' => $year_study,
								'forSemester' => $_data['semesterId'],
								'studentId'   => $old_studentid
							);
							//$rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);

							// $totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
							// $totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
							// $totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;
						} else if ($_data['examTypeId'] == 1) { //For Month

							if (!empty($scale_for_month)) {
								$totalMutiAll = $totalMaxScore / $scale_for_month;
								$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
								$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
								$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
							}
						} else { 	// year

							$totalMutiAll = $totalMaxScore / $scale_for_semester;
							$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
							$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
							$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

							$dataparam = array(
								'groupId'      => $_data['groupId'],
								'acadmicYear' => $year_study,
								'studentId'   => $old_studentid
							);
							$rsSemesterAvg = $this->getAnnaulSemesterAvg($dataparam);

							$anaulKhAvg = $rsSemesterAvg['anaulKhAvg'];
							$annaulEnAvg = $rsSemesterAvg['annaulEnAvg'];
							$annaulChAvg = $rsSemesterAvg['annaulChAvg'];
							$annaulOveralAvg = $rsSemesterAvg['annaulOveralAvg'];
						}
						$avg = $total_score / $totalMutiAll;
						$avgkh = $totalScoreKh / $totalAmountSubjectKh;
						$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0;
						$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;

						$arr = array(
							'score_id' => $_data['scoreId'],
							'student_id' => $old_studentid,

							'total_score' => $total_score,
							'amount_subject' => $totalMutiAll,
							'total_avg' => $avg,
							'totalMaxScore' => $totalMaxScore,
							'type' => $type,

						);
						if ($_data['examTypeId'] == 2) { 
							 //Semester
						
								$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;

								$monthlySemesterAvgKh = ($avgkh + $monthlySemesterAvg_kh) / 2;
								$monthlySemesterAvgEn = ($avgeEn + $monthlySemesterAvg_en) / 2;
					

								$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
								$arr["overallAssessmentSemester"] = $overallAssessmentSemester;

								$arr["totalKhAvg"] = $monthlySemesterAvg_en;
								$arr["totalEnAvg"] = $monthlySemesterAvg_en;
							

								$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
								$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
							

						} else if ($_data['examTypeId'] == 1) {  //   Month

							$arr["totalKhAvg"] = $avgkh;
							$arr["totalEnAvg"] = $avgeEn;
							$arr["totalChAvg"] = $avgCh;
						} else {   ///  Year

							if (!empty($rsSemesterAvg)) {

								$arr["totalKhAvg"] = $avgkh;
								$arr["totalEnAvg"] = $avgeEn;
								$arr["totalChAvg"] = $avgCh;

								$arr["OveralAvgKh"] = $anaulKhAvg;
								$arr["OveralAvgEng"] = $annaulEnAvg;
								$arr["OveralAvgCh"] = $annaulChAvg;

								$arr["overallAssessmentSemester"] = $annaulOveralAvg;
							}
						}
						$this->_name = 'rms_score_monthly';
						$this->insert($arr);
					}
				}
			
			}
			$db->commit();
			
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			exit();
		}
	}
	
	function getMonthlySemesterAvg($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 

		ROUND( SUM(sm.`totalKhAvg`)/ COUNT(sm.`totalKhAvg`),2) AS totalKhAvg,
		ROUND(SUM(sm.`totalEnAvg`)/COUNT(sm.`totalEnAvg`),2)  AS totalEnAvg,
		ROUND(SUM(sm.`totalChAvg`)/COUNT(sm.`totalChAvg`),2) AS totalChAvg
		
		 FROM `rms_score_monthly` AS sm INNER JOIN `rms_score` AS s ON s.`id` = sm.`score_id` WHERE 1 AND s.`exam_type`= 1 ";
		$sql .= " AND s.`group_id` = " . $data['groupId'];
		$sql .= " AND s.`for_academic_year`= " . $data['acadmicYear'];
		$sql .= " AND s.`for_semester` =" . $data['forSemester'];
		$sql .= " AND sm.`student_id`   =" . $data['studentId'];

		return $db->fetchRow($sql);
	}
	function getAnnaulSemesterAvg($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
		ROUND(SUM(sm.`OveralAvgKh`)/2,2) AS anaulKhAvg,
		ROUND(SUM(sm.`OveralAvgEng`)/2,2)  AS annaulEnAvg,
		ROUND(SUM(sm.`OveralAvgCh`)/2,2) AS annaulChAvg,
		ROUND(SUM(sm.`overallAssessmentSemester`)/2,2) AS annaulOveralAvg
				
		FROM `rms_score_monthly` AS sm INNER JOIN `rms_score` AS s ON s.`id` = sm.`score_id` WHERE 1 
		AND s.`status` = 1 AND s.`exam_type`= 2  ";
		$sql .= " AND s.`group_id` = " . $data['groupId'];
		$sql .= " AND s.`for_academic_year`= " . $data['acadmicYear'];
		$sql .= " AND sm.`student_id`   =" . $data['studentId'];

		return $db->fetchRow($sql);
	}

	function getScoreResultByGroup($data)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$title = 's.title_score_en';
		$label = 'name_en';
		if ($currentLang == 1) {
			$title = 's.title_score';
			$label = 'name_kh';
		}

		$sql = "SELECT 
				s.`id` AS id,
				CONCAT($title,'(',(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1),')' )  AS name
			FROM rms_score AS s 
			WHERE 1 ";

			if (!empty($data['branch_id'])) {
				$sql .= " AND `s`.`branch_id` =" . $data['branch_id'];
			}
			if (!empty($data['group'])) {
				$sql .= " AND `s`.`group_id` =" . $data['group'];
			}
			if (!empty($data['examType'])) {
				$sql .= " AND `s`.`exam_type` =" . $data['examType'];
			}
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql .= $dbp->getAccessPermission('s.branch_id');
		return $db->fetchAll($sql);
	}

	function getScoreInforByID($scoreId){
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$title = 's.title_score_en';
		$colunmname = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang == 1) {
			$title = 's.title_score';
			$colunmname = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
	
		$sql ="SELECT s.id,
			(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as examTypeTitle,
			s.exam_type,
			s.for_semester,
			s.for_month,
			s.group_id,
			CASE
				WHEN s.exam_type = 2 THEN ''
			ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
			END 
			as forMonth,
			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  groupCode,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.for_academic_year LIMIT 1) AS academicYear,
			s.for_academic_year
			FROM rms_score AS  s WHERE s.id = $scoreId LIMIT 1";
		$row =  $db->fetchRow($sql);
		return $row;
	}
	function getStudentScoreById($data)
	{
		$db = $this->getAdapter();

		$studentName = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$sql = "SELECT *,
					s.`stu_id`,
					s.stu_code  AS stu_code,
					s.stu_khname AS stuKhName,
					$studentName AS stuEnName,
					s.sex
				FROM rms_student AS  s where s.stu_id= ".$data['studentId'];
		$studentRow=  $db->fetchRow($sql);
		return $studentRow;
	}
	function getStudentNoScore($data){
		$db=$this->getAdapter();
		$scoreId = $data['scoreId'];
		$sql="SELECT
					sgh.`stu_id` as id,
					CONCAT(s.stu_code,' - ',(CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END)) as name
				FROM 
					`rms_group_detail_student` AS sgh,
					rms_student as s
				WHERE 
					sgh.itemType=1 
					AND s.stu_id = sgh.stu_id
					
					and sgh.is_pass = 0
					AND sgh.stop_type = 0
			";
			if (!empty($data['group'])) {
				$sql .= " AND sgh.group_id =" . $data['group'];
			}
			if (!empty($data['scoreId'])) {
				$sql .= " AND sgh.`stu_id`  NOT IN (SELECT sm.student_id FROM rms_score_monthly AS sm WHERE sm.score_id= $scoreId ) ";
			}
		$order=" ORDER BY s.stu_code ASC ";
		return $db->fetchAll($sql.$order);
	}
	
}
