<?php

class Issue_Model_DbTable_DbScore extends Zend_Db_Table_Abstract
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
			$group_info = $dbGroup->getGroupById($_data['group']);
			$year_study = empty($group_info['academic_year']) ? 0 : $group_info['academic_year'];
			$scale_for_month =  $group_info['max_average'];
			$scale_for_semester = empty($group_info['semesterTotalAverage']) ? 100 : $group_info['semesterTotalAverage'];
			$semesterPercentage = empty($group_info['semesterPercentage']) ? 1 : $group_info['semesterPercentage'];
			

			$_arr = array(
				'branch_id' => $_data['branch_id'],
				'title_score' => $_data['title'],
				'title_score_en' => $_data['title_en'],
				'group_id' => $_data['group'],
				'exam_type' => $_data['exam_type'],
				'date_input' => date("Y-m-d"),
				'note' => $_data['note'],
				'user_id' => $this->getUserId(),
				'for_academic_year' => $year_study,
				'for_semester' => $_data['for_semester'],
				'for_month' => $_data['for_month'],
				'score_option' => $_data['score_option'],
			);
			
			$_data['publicNow'] = empty($_data['publicNow']) ? 0 : 1;
			$_arr["isPublic"] = $_data['publicNow'];
			$_arr["publicBy"] = ($_data['publicNow']==1) ? $this->getUserId() : 0;
			$_arr["publicDate"] = ($_data['publicNow']==1) ? date("Y-m-d H:i:s") : "";
			
			$id = $this->insert($_arr);
			$scoreId = $id;

			 $dbpush = new Application_Model_DbTable_DbGlobal();

			$old_studentid = 0;

			if (!empty($_data['identity'])) {
				$ids = explode(',', $_data['identity']);
				$rssubject = $_data['selector'];
				
				$total_score = 0;
				$totalMutiAll = 0;
				$totalMaxScore = 0;
			
				$monthlySemesterAvg = 0;
				$overallAssessmentSemester = 0;
				
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
							if ($_data['exam_type'] == 2) { //semester exam
								$totalMutiAll = $totalMaxScore / $scale_for_semester;
								$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
								$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
								$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

								$dataparam = array(
									'groupId'      => $_data['group'],
									'acadmicYear' => $year_study,
									'forSemester' => $_data['for_semester'],
									'studentId'   => $old_studentid
								);
								$rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);

								$totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
								$totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
								$totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;

							}else{

								if(!empty($scale_for_month)) {
									$totalMutiAll = $totalMaxScore / $scale_for_month;
									$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
									$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
									$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
								}
								
								
							}
							$avg = $total_score / $totalMutiAll;
							$avgkh = $totalScoreKh / $totalAmountSubjectKh;
							$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0; 
							$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;
					
							$arr = array(
								'score_id' => $id,
								'student_id' => $old_studentid,

								'total_score' => $total_score,
								'amount_subject' => $totalMutiAll,
								'total_avg' => $avg,
								'totalMaxScore' => $totalMaxScore,
								'type' => $type ,

							);
							if ($_data['exam_type'] == 2) {
								if (!empty($rsMonthlysemesterAvg)) {

									$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;

									$monthlySemesterAvgKh = ($avgkh + $totalKhAvg) / 2;
									$monthlySemesterAvgEn = ($avgeEn + $totalEnAvg) / 2;
									$monthlySemesterAvgCh = ($avgCh + $totalChAvg) / 2;

									$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
									$arr["overallAssessmentSemester"] = $overallAssessmentSemester;

									$arr["totalKhAvg"] = $totalKhAvg;
									$arr["totalEnAvg"] = $totalEnAvg;
									$arr["totalChAvg"] = $totalChAvg;

									$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
									$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
									$arr["OveralAvgCh"] = $monthlySemesterAvgCh;
									
								}
							}else{
								$arr["totalKhAvg"] = $avgkh;
								$arr["totalEnAvg"] = $avgeEn;
								$arr["totalChAvg"] = $avgCh;
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
						$type = $_data['type' . $i];

						$dataScore = array(
							'groupId' => $_data['group'],
							'examType' => $_data['exam_type'],
							'forMonth' => $_data['for_month'],
							'forSemester' => $_data['for_semester'],
							'subjectId' => $subject,
							'studentId' => $_data['student_id' . $i],
						);

						$resultScore = $this->getGradingScoreData($dataScore);

						$gradingId = '';
						if (!empty($resultScore)) {
							$gradingId = $resultScore['gradingId'];
						}

						$param = array(
							'groupId' => $_data['group'],
							'subjectId' => $subject,
						);

						$rsGroupSubject = $dbpush->getGroupSubjectDetail($param); //// call cut score

						if ($_data['exam_type'] == 1) { //month
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

						if($_data['subject_lang' .$subject]==1){
							$totalScoreKh = $totalScoreKh+ ($_data["score_" . $i . "_" . $subject]*$totalMulti);
							$totalMaxScoreKh = $totalMaxScoreKh+ ($maxScore*$totalMulti);
							$totalAmountSubjectKh = $totalAmountSubjectKh + $totalMulti;
						}else if($_data['subject_lang' .$subject]==2){
							$totalScoreEng = $totalScoreEng+ ($_data["score_" . $i . "_" . $subject]*$totalMulti);
							$totalMaxScoreEng = $totalMaxScoreEng+ ($maxScore*$totalMulti);
							$totalAmountSubjectEng = $totalAmountSubjectEng + $totalMulti;
						}else if($_data['subject_lang' .$subject]==3){
							$totalScoreCh = $totalScoreCh+($_data["score_" . $i . "_" . $subject]*$totalMulti);
							$totalMaxScoreCh = $totalMaxScoreCh+ ($maxScore*$totalMulti);
							$totalAmountSubjectCh = $totalAmountSubjectCh + $totalMulti;
						}

						$totalMaxScore = $totalMaxScore + ($maxScore * $totalMulti);

						$totalMutiAll = $totalMutiAll + $totalMulti;

						$arr = array(
							'score_id' 		 => $id,
							'group_id'		 => $_data['group'],
							'gradingTotalId' => $gradingId,
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

						if ($_data['exam_type'] == 2) { //semester exam
								$totalMutiAll = $totalMaxScore / $scale_for_semester;
								$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
								$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
								$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;

								$dataparam = array(
									'groupId'      => $_data['group'],
									'acadmicYear' => $year_study,
									'forSemester' => $_data['for_semester'],
									'studentId'   => $old_studentid
								);
								$rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);

								$totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
								$totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
								$totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;

							}else{

								if(!empty($scale_for_month)) {
									$totalMutiAll = $totalMaxScore / $scale_for_month;
									$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
									$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
									$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
								}
								
								
							}
							$avg = $total_score / $totalMutiAll;
							$avgkh = $totalScoreKh / $totalAmountSubjectKh;
							$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0; 
							$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;
					
							$arr = array(
								'score_id' => $id,
								'student_id' => $old_studentid,

								'total_score' => $total_score,
								'amount_subject' => $totalMutiAll,
								'total_avg' => $avg,
								'totalMaxScore' => $totalMaxScore,
								
								'type' => $type ,
								

							);
							if ($_data['exam_type'] == 2) {
								if (!empty($rsMonthlysemesterAvg)) {

									$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;

									$monthlySemesterAvgKh = ($avgkh + $totalKhAvg) / 2;
									$monthlySemesterAvgEn = ($avgeEn + $totalEnAvg) / 2;
									$monthlySemesterAvgCh = ($avgCh + $totalChAvg) / 2;

									$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
									$arr["overallAssessmentSemester"] = $overallAssessmentSemester;

									$arr["totalKhAvg"] = $totalKhAvg;
									$arr["totalEnAvg"] = $totalEnAvg;
									$arr["totalChAvg"] = $totalChAvg;

									$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
									$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
									$arr["OveralAvgCh"] = $monthlySemesterAvgCh;
									
								}
							}else{
								$arr["totalKhAvg"] = $avgkh;
								$arr["totalEnAvg"] = $avgeEn;
								$arr["totalChAvg"] = $avgCh;
							}
							$this->_name = 'rms_score_monthly';
							$this->insert($arr);
					}
				}
			
				if($_data['score_option']== 1 ){
					$this->_name = 'rms_grading';
					foreach ($rssubject as $subject) {
						$where = 'groupId=' . $_data['group'] . ' AND subjectId=' . $subject . ' AND forSemester=' . $_data['for_semester'] . ' AND examType =' . $_data['exam_type'];
						if ($_data['exam_type'] == 1) {
							$where .= ' AND formonth=' . $_data['for_month'];
						}
						$arr = array(
							'isLock' => 1,
							'lockBy' => $this->getUserId()
						);
						$this->update($arr, $where);
					}

				}
				

				// is combine

				$this->_name = 'rms_score';
				if ($_data['exam_type'] == 2) {
					$where = 'group_id=' . $_data['group'] . '  AND for_semester=' . $_data['for_semester'] . ' AND exam_type = 1 ';
					$arr = array(
						'isCombineSemester' => 1,
					);
					$this->update($arr, $where);
				}
				
			}
			$db->commit();
			return $scoreId;
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function updateStudentScore($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($_data['group']);
			$year_study = empty($group_info['academic_year']) ? 0 : $group_info['academic_year'];
			$scale_for_month =  $group_info['max_average'];
			$scale_for_semester = empty($group_info['semesterTotalAverage']) ? 100 : $group_info['semesterTotalAverage'];
			$semesterPercentage = empty($group_info['semesterPercentage']) ? 1 : $group_info['semesterPercentage'];
		
			$status = $_data['status'];
			$_arr = array(
				'branch_id' => $_data['branch_id'],
				'title_score' => $_data['title'],
				'title_score_en' => $_data['title_en'],
				'group_id' => $_data['group'],
				'exam_type' => $_data['exam_type'],
				'date_input' => date("Y-m-d"),
				'note' => $_data['note'],
				'user_id' => $this->getUserId(),
				'for_academic_year' => $year_study,
				'for_semester' => $_data['for_semester'],
				'for_month' => $_data['for_month'],
				'status' => $status,
			);
			
			$_data['publicNow'] = empty($_data['publicNow']) ? 0 : 1;
			$_arr["isPublic"] = $_data['publicNow'];
			$_arr["publicBy"] = ($_data['publicNow']==1) ? $this->getUserId() : 0;
			$_arr["publicDate"] = ($_data['publicNow']==1) ? date("Y-m-d H:i:s") : "";
			
			$where = "id=" . $_data['score_id'];
			$this->update($_arr, $where);

			if($status == 1) {
				$id = $_data['score_id'];
				$this->_name = 'rms_score_detail';
				$this->delete("score_id=" . $_data['score_id']);

				$this->_name = 'rms_score_monthly';
				$this->delete("score_id=" . $_data['score_id']);
				$old_studentid = 0;
				$type=1;
				$dbpush = new Application_Model_DbTable_DbGlobal();
				// $dbpush->pushNotification(null, $_data['group'], 2, 4);

				if (!empty($_data['identity'])) {
					$ids = explode(',', $_data['identity']);
					$rssubject = $_data['selector'];
					
					$total_score = 0;
					$totalMutiAll = 0;
					$totalMaxScore = 0;
				
					$monthlySemesterAvg = 0;
					$overallAssessmentSemester = 0;
					
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
								if ($_data['exam_type'] == 2) { //semester exam
									$totalMutiAll = $totalMaxScore / $scale_for_semester;
									$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
									$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
									$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;
	
									$dataparam = array(
										'groupId'      => $_data['group'],
										'acadmicYear' => $year_study,
										'forSemester' => $_data['for_semester'],
										'studentId'   => $old_studentid
									);
									$rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);
	
									$totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
									$totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
									$totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;
	
								}else{
	
									if(!empty($scale_for_month)) {
										$totalMutiAll = $totalMaxScore / $scale_for_month;
										$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
										$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
										$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
									}
									
									
								}
								$avg = $total_score / $totalMutiAll;
								$avgkh = $totalScoreKh / $totalAmountSubjectKh;
								$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0; 
								$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;
						
								$arr = array(
									'score_id' => $id,
									'student_id' => $old_studentid,
	
									'total_score' => $total_score,
									'amount_subject' => $totalMutiAll,
									'total_avg' => $avg,
	
									'totalMaxScore' => $totalMaxScore,
									'type' => $type ,
								);
								if ($_data['exam_type'] == 2) {
									if (!empty($rsMonthlysemesterAvg)) {
	
										$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;
	
										$monthlySemesterAvgKh = ($avgkh + $totalKhAvg) / 2;
										$monthlySemesterAvgEn = ($avgeEn + $totalEnAvg) / 2;
										$monthlySemesterAvgCh = ($avgCh + $totalChAvg) / 2;
	
										$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
										$arr["overallAssessmentSemester"] = $overallAssessmentSemester;
	
										$arr["totalKhAvg"] = $totalKhAvg;
										$arr["totalEnAvg"] = $totalEnAvg;
										$arr["totalChAvg"] = $totalChAvg;
	
										$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
										$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
										$arr["OveralAvgCh"] = $monthlySemesterAvgCh;
										
									}
								}else{
									$arr["totalKhAvg"] = $avgkh;
									$arr["totalEnAvg"] = $avgeEn;
									$arr["totalChAvg"] = $avgCh;
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
							$type = $_data['type' . $i];
	
							$dataScore = array(
								'groupId' => $_data['group'],
								'examType' => $_data['exam_type'],
								'forMonth' => $_data['for_month'],
								'forSemester' => $_data['for_semester'],
								'subjectId' => $subject,
								'studentId' => $_data['student_id' . $i],
							);
	
							$resultScore = $this->getGradingScoreData($dataScore);
	
							$gradingId = '';
							if (!empty($resultScore)) {
								$gradingId = $resultScore['gradingId'];
							}
	
							$param = array(
								'groupId' => $_data['group'],
								'subjectId' => $subject,
							);
	
							$rsGroupSubject = $dbpush->getGroupSubjectDetail($param); //// call cut score
	
							if ($_data['exam_type'] == 1) { //month
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
	
							if($_data['subject_lang' .$subject]==1){
								$totalScoreKh = $totalScoreKh+ ($_data["score_" . $i . "_" . $subject]*$totalMulti);
								$totalMaxScoreKh = $totalMaxScoreKh+ ($maxScore*$totalMulti);
								$totalAmountSubjectKh = $totalAmountSubjectKh + $totalMulti;
							}else if($_data['subject_lang' .$subject]==2){
								$totalScoreEng = $totalScoreEng+ ($_data["score_" . $i . "_" . $subject]*$totalMulti);
								$totalMaxScoreEng = $totalMaxScoreEng+ ($maxScore*$totalMulti);
								$totalAmountSubjectEng = $totalAmountSubjectEng + $totalMulti;
							}else if($_data['subject_lang' .$subject]==3){
								$totalScoreCh = $totalScoreCh+($_data["score_" . $i . "_" . $subject]*$totalMulti);
								$totalMaxScoreCh = $totalMaxScoreCh+ ($maxScore*$totalMulti);
								$totalAmountSubjectCh = $totalAmountSubjectCh + $totalMulti;
							}
	
							$totalMaxScore = $totalMaxScore + ($maxScore * $totalMulti);
	
							$totalMutiAll = $totalMutiAll + $totalMulti;
	
							$arr = array(
								'score_id' 		 => $id,
								'group_id'		 => $_data['group'],
								'gradingTotalId' => $gradingId,
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
	
							if ($_data['exam_type'] == 2) { //semester exam
									$totalMutiAll = $totalMaxScore / $scale_for_semester;
									$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_semester;
									$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_semester;
									$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_semester;
	
									$dataparam = array(
										'groupId'      => $_data['group'],
										'acadmicYear' => $year_study,
										'forSemester' => $_data['for_semester'],
										'studentId'   => $old_studentid
									);
									$rsMonthlysemesterAvg = $this->getMonthlySemesterAvg($dataparam);
	
									$totalKhAvg = $rsMonthlysemesterAvg['totalKhAvg'] / $semesterPercentage;
									$totalEnAvg = $rsMonthlysemesterAvg['totalEnAvg'] / $semesterPercentage;
									$totalChAvg = $rsMonthlysemesterAvg['totalChAvg'] / $semesterPercentage;
	
								}else{
									
									if(!empty($scale_for_month)) {
										$totalMutiAll = $totalMaxScore / $scale_for_month;
										$totalAmountSubjectKh = $totalMaxScoreKh / $scale_for_month;
										$totalAmountSubjectEng = $totalMaxScoreEng / $scale_for_month;
										$totalAmountSubjectCh = $totalMaxScoreCh / $scale_for_month;
									}
								}
								$avg = $total_score / $totalMutiAll;
								$avgkh = $totalScoreKh / $totalAmountSubjectKh;
								$avgeEn = $totalAmountSubjectEng > 0 ? ($totalScoreEng / $totalAmountSubjectEng) : 0; 
								$avgCh = $totalAmountSubjectCh > 0 ? ($totalScoreCh / $totalAmountSubjectCh) : 0;
						
								$arr = array(
									'score_id' => $id,
									'student_id' => $old_studentid,
	
									'total_score' => $total_score,
									'amount_subject' => $totalMutiAll,
									'total_avg' => $avg,
									'totalMaxScore' => $totalMaxScore,
									'type' => $type ,
	
								);
								if ($_data['exam_type'] == 2) {
									if (!empty($rsMonthlysemesterAvg)) {
	
										$overallAssessmentSemester = ($avg + $monthlySemesterAvg) / 2;
	
										$monthlySemesterAvgKh = ($avgkh + $totalKhAvg) / 2;
										$monthlySemesterAvgEn = ($avgeEn + $totalEnAvg) / 2;
										$monthlySemesterAvgCh = ($avgCh + $totalChAvg) / 2;
	
										$arr["monthlySemesterAvg"] = $monthlySemesterAvg;
										$arr["overallAssessmentSemester"] = $overallAssessmentSemester;
	
										$arr["totalKhAvg"] = $totalKhAvg;
										$arr["totalEnAvg"] = $totalEnAvg;
										$arr["totalChAvg"] = $totalChAvg;
	
										$arr["OveralAvgKh"] = $monthlySemesterAvgKh;
										$arr["OveralAvgEng"] = $monthlySemesterAvgEn;
										$arr["OveralAvgCh"] = $monthlySemesterAvgCh;
										
									}
								}else{
									$arr["totalKhAvg"] = $avgkh;
									$arr["totalEnAvg"] = $avgeEn;
									$arr["totalChAvg"] = $avgCh;
								}
								$this->_name = 'rms_score_monthly';
								$this->insert($arr);
						}
					}
					$this->_name = 'rms_grading';
					foreach ($rssubject as $subject) {
						$where = 'groupId=' . $_data['group'] . ' AND subjectId=' . $subject . ' AND forSemester=' . $_data['for_semester'] . ' AND examType =' . $_data['exam_type'];
						if ($_data['exam_type'] == 1) {
							$where .= ' AND formonth=' . $_data['for_month'];
						}
						$arr = array(
							'isLock' => 1,
							'lockBy' => $this->getUserId()
						);
						$this->update($arr, $where);
					}
	
					// is combine
	
					$this->_name = 'rms_score';
					if ($_data['exam_type'] == 2) {
						$where = 'group_id=' . $_data['group'] . '  AND for_semester=' . $_data['for_semester'] . ' AND exam_type = 1 ';
						$arr = array(
							'isCombineSemester' => 1,
						);
						$this->update($arr, $where);
					}
					
				}
			} else {  // Void Score

				$this->_name = 'rms_grading';
				$where = 'groupId=' . $_data['group'] . '  AND examType =' . $_data['exam_type'];
				if ($_data['exam_type'] == 1) {
					$where .= ' AND formonth=' . $_data['for_month'];
				} else {
					$where .= ' AND forSemester=' . $_data['for_semester'];
				}
				$arr = array(
					'isLock' => 0,
					'lockBy' => $this->getUserId()
				);
				$this->update($arr, $where);

				$this->_name = 'rms_score';
				if ($_data['exam_type'] == 2) {
					$where = 'group_id=' . $_data['group'] . '  AND for_semester=' . $_data['for_semester'] . ' AND exam_type = 1 ';
					$arr = array(
						'isCombineSemester' => 0,
					);
					$this->update($arr, $where);
				}
			}
			$db->commit();
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollback();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	// function getMonthlySemesterAvg($data)
	// {
	// 	$db = $this->getAdapter();
	// 	$sql = "SELECT * FROM `v_monthly_semester_avg_lang` AS vms WHERE 1 ";
	// 	$sql .= " AND vms.`groupId`     =" . $data['groupId'];
	// 	$sql .= " AND vms.`acadmicYear` =" . $data['acadmicYear'];
	// 	$sql .= " AND vms.`forSemester` =" . $data['forSemester'];
	// 	$sql .= " AND vms.`studentId`   =" . $data['studentId'];

	// 	return $db->fetchRow($sql);
	// }
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

	function getAllScore($search = null)
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
		$sql = "SELECT s.id,
			(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) As branch_name,
			s.title_score,
			CONCAT(COALESCE(`title_score`,''),' ',COALESCE(`title_score_en`,'')) AS title_score,
			(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as exam_type,
			s.for_semester,
			CASE
				WHEN s.exam_type = 2 THEN ''
			ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
			END 
			as for_month,
			
			(SELECT group_code FROM rms_group WHERE id=s.group_id limit 1 ) AS  group_id,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id,
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id,
			(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, 
			(SELECT first_name FROM rms_users WHERE s.user_id=rms_users.id LIMIT 1 ) AS user_name
		";
		//s.max_score,
		$sql .= $dbp->caseStatusShowImage("s.status");
		$sql .= " FROM rms_score AS s,rms_group AS g WHERE s.group_id=g.id "; //AND s.status=1

		$where = '';
		$from_date = (empty($search['start_date'])) ? '1' : " s.date_input >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " s.date_input <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " s.title_score LIKE '%{$s_search}%'";
			$s_where[] = " s.note LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if ($search['degree'] > 0) {
			$where .= " AND g.degree =" . $search['degree'];
		}
		if (!empty($search['academic_year'])) {
			$where .= " AND g.academic_year =" . $search['academic_year'];
		}
		if (!empty($search['grade'])) {
			$where .= " AND `g`.`grade` =" . $search['grade'];
		}
		if (!empty($search['group'])) {
			$where .= " AND `s`.`group_id` =" . $search['group'];
		}
		if (!empty($search['branch_id'])) {
			$where .= " AND `s`.`branch_id` =" . $search['branch_id'];
		}
		if ($search['for_month'] > 0) {
			$where .= " AND s.for_month =" . $search['for_month'];
		}
		if ($search['exam_type'] > 0) {
			$where .= " AND s.exam_type =" . $search['exam_type'];
		}
		if ($search['for_semester'] > 0) {
			$where .= " AND s.for_semester =" . $search['for_semester'];
		}
		$where .= $dbp->getAccessPermission('s.branch_id');
		$order = " ORDER BY id DESC ";
		return $db->fetchAll($sql . $where . $order);
	}

	function getScoreById($score_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.*,
				(SELECT g.is_pass 
					FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) as is_pass 
			FROM rms_score AS s 
			WHERE s.id=$score_id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}

	function getStudentByGroup($group_id, $data = array())
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();

		$examType = empty($data['examType']) ? 1 : $data['examType'];

		$currentLang = $dbp->currentlang();
		$studentName = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName = $studentName;

		if ($currentLang == 1) {
			$studentName = 's.stu_khname';
		}

		$sql = "SELECT
					sgh.`stu_id`,
					(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_code,
					(SELECT " . $studentName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name,
					(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuKhName,
					(SELECT " . $studentEnName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuEnName,
					(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex ";
		$sqlstr =	" , 0 AS monthlySemesterAvg ";
		if ($examType == 2) {
			$sqlstr =	" ,COALESCE( FORMAT((SELECT SUM(sm.total_avg)/COUNT(sm.total_avg)/g.`semesterPercentage` FROM `rms_score_monthly` AS sm 
						INNER JOIN `rms_score` AS s  ON s.`id` =sm.`score_id` 
						WHERE sm.`student_id` = sgh.`stu_id` 
						AND s.for_semester=" . $data['forSemester'] . "
						AND s.exam_type=1 
						AND s.group_id=sgh.group_id LIMIT 1 ),2), 0) AS monthlySemesterAvg ";
		}
		$sql .=	$sqlstr;
		$sql .=	" FROM `rms_group_detail_student` AS sgh 
					INNER JOIN `rms_group` AS g ON sgh.`group_id`=g.`id`
				WHERE 
					sgh.itemType=1 
					AND sgh.stop_type=0
					and sgh.`group_id` = " . $group_id;
		$order = " ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
		if (!empty($data['sortStundent'])) {
			if ($data['sortStundent'] == 1) {
				$order = " ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			} else if ($data['sortStundent'] == 2) {
				$order = " ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			} else if ($data['sortStundent'] == 3) {
				$order = " ORDER BY (SELECT " . $studentEnName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			}
		}

		$studentResult =  $db->fetchAll($sql . $order);
		if (!empty($data['groupId'])) {
			$groupId = $data['groupId'];
		}
		if (!empty($data['examType'])) {
			$examType = $data['examType'];
		}
		$resultSubject = $this->getSubjectByGroupScore($data['groupId'], null, $examType);
		$results = array();
		if (!empty($studentResult)) {
			foreach ($studentResult as $key => $rs) {
				$results[$key]['stu_id'] = $rs['stu_id'];
				$results[$key]['stu_code'] = $rs['stu_code'];
				$results[$key]['stu_name'] = $rs['stu_name'];
				$results[$key]['stuKhName'] = $rs['stuKhName'];
				$results[$key]['stuEnName'] = $rs['stuEnName'];
				$results[$key]['sex'] = $rs['sex'];
				$results[$key]['monthlySemesterAvg'] = $rs['monthlySemesterAvg'];

				$data['studentId'] = $rs['stu_id'];
				$gradingScore = array();
				if (!empty($resultSubject)) {
					foreach ($resultSubject as $index => $rsGroup) {
						$data['subjectId'] = $rsGroup['subject_id'];
						$gradingScore[$index] = $this->getGradingScoreData($data);
					}
				}
				$results[$key]['gradingScore'] = $gradingScore;
			}
		}
		return $results;
	}
	function getSubjectByGroupScore($groupId, $teacher_id = null, $exam_type = 1)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
		}
		$sql = "SELECT 
				gsjd.*,
				gsjd.subject_id,
				CASE
					  	WHEN $exam_type =1 THEN max_score
					  	WHEN $exam_type =2 THEN semester_max_score
					  ELSE ''
				END max_subjectscore,
				
				(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
				(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name,
				(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
				(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut,
				(gsjd.amount_subject) amtsubject_month,
				(gsjd.amount_subject_sem) amtsubject_semester,
				(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen,
				(SELECT sj.subject_lang FROM `rms_subject` AS sj WHERE sj.id=gsjd.subject_id LIMIT 1) AS subjectLang,
				(SELECT CONCAT(sj.$colunmname,
			  		CASE
					  	WHEN subject_lang =1 THEN '(ខ្មែរ)'
					  	WHEN subject_lang =2 THEN '(English)'
					  ELSE ''
				END) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name
			FROM 
		 		rms_group_subject_detail AS gsjd ,
		 		rms_group as g
			WHERE 
				g.id = gsjd.group_id
				and gsjd.group_id = " . $groupId;

		if ($teacher_id != null) {
			$sql .= " AND gsjd.teacher = " . $teacher_id;
		}
		if ($exam_type == 1) { //for month
			$sql .= " AND gsjd.amount_subject >0 ";
		} else { //for semester
			$sql .= " AND gsjd.amount_subject_sem >0 ";
		}
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE 
						s.id=gsjd.subject_id LIMIT 1) ";

		$sql .= " ORDER BY $strSubjectLange ASC ,gsjd.id ASC ";

		return $db->fetchAll($sql);
	}


	function getStudentSccoreforEdit($score_id,$sort=null)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$studentName = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName = $studentName;

		if ($currentLang == 1) {
			$studentName = 's.stu_khname';
		}

		$sql = "SELECT 
			  sd.student_id,
			 (SELECT " . $studentName . " FROM `rms_student` AS s WHERE s.stu_id = sd.`student_id` LIMIT 1) AS student_name,
			 (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sd.`student_id` LIMIT 1) AS stuKhName,
			 (SELECT " . $studentEnName . " FROM `rms_student` AS s WHERE s.stu_id = sd.`student_id` LIMIT 1) AS stuEnName,
			  (SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
			  (SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
			  score,note,
			  (SELECT amount_subject FROM `rms_score_monthly` WHERE score_id=sd.score_id AND student_id=sd.student_id LIMIT 1) AS amount_subject,
			  (SELECT monthlySemesterAvg FROM `rms_score_monthly` WHERE score_id=sd.score_id AND student_id=sd.student_id LIMIT 1) AS monthlySemesterAvg,
			  (SELECT type FROM `rms_score_monthly` WHERE score_id=sd.score_id AND student_id=sd.student_id LIMIT 1) AS type
		FROM
	 	 	rms_score_detail AS sd 
		WHERE sd.score_id =$score_id 
			GROUP BY sd.`student_id` ";
	if(!empty($sort)){
		if($sort==1){
			$sql .= " order by stu_code ASC";
		}elseif($sort==2){
			$sql .= " order by stuKhName ASC";
		}elseif($sort==3){
			$sql .= " order by stuEnName ASC";
		}
	}else{
		$sql .= " order by stu_code ASC";
	}	
	$sql .= " ,(SELECT " . $studentEnName . " FROM `rms_student`AS s 
			WHERE s.`stu_id`=sd.`student_id`) ASC  ";
		return $db->fetchAll($sql);
	}


	function getGroupStudent($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id,group_id,status FROM rms_score WHERE id=$id LIMIT 1";
		return $db->fetchRow($sql);
	}

	function checkSubjectScore($score_id, $subject)
	{
		$db = $this->getAdapter();
		$sql = " SELECT
			sd.student_id,	
			sd.subject_id,
			(SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
			(SELECT `subject_titlekh` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titlekh,
			sd.score 
			FROM
			rms_score_detail AS sd
			WHERE sd.score_id =$score_id	
			AND sd.`subject_id` =$subject	
			GROUP BY sd.`subject_id` LIMIT 1";
		return $db->fetchRow($sql);
	}

	function getStudentScoreBySubjectID($score_id, $student_id, $suj_id)
	{
		if ($student_id == null) {
			return false;
		}
		$db = $this->getAdapter();
		$sql = "SELECT
			sd.student_id,
			(SELECT CONCAT(s.`stu_khname`,'-',`stu_enname`) FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS student_name,
			(SELECT s.`stu_code` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS stu_code,
			(SELECT s.`sex` FROM `rms_student`AS s WHERE s.`stu_id`=sd.`student_id`) AS sex,
			sd.subject_id,
			(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id=sd.`subject_id` LIMIT 1) AS parent,
			(SELECT CONCAT(`subject_titlekh`,'-',`subject_titleen`) FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_name,
			(SELECT `subject_titleen` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titleen,
			(SELECT `subject_titlekh` FROM `rms_subject` AS s WHERE s.`id`=sd.`subject_id`) AS subject_titlekh,
			sd.orgScore,
			sd.score 
		FROM
		rms_score_detail AS sd
		WHERE sd.score_id = $score_id
		AND sd.`subject_id` = $suj_id
		AND sd.`student_id`= $student_id ORDER BY sd.subject_id ASC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getGradingScoreData($data)
	{
		$sql = "SELECT 
				gt.gradingId,
				gt.totalAverage 
				FROM `rms_grading_total` gt,
					`rms_grading` gd
				 WHERE gd.id=gt.gradingId ";
		if (!empty($data['groupId'])) {
			$sql .= " AND gd.groupId=" . $data['groupId'];
		}
		if (!empty($data['examType'])) {
			if ($data['examType'] == 1) { //month
				if (!empty($data['forMonth'])) {
					$sql .= " AND gd.formonth= " . $data['forMonth'];
				}
			}
			$sql .= " ANd gd.examType = " . $data['examType'];
		}
		if (!empty($data['forSemester'])) {
			$sql .= " AND gd.forSemester = " . $data['forSemester'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND gd.subjectId = " . $data['subjectId'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND gt.studentId = " . $data['studentId'];
		}
		if (isset($data['isLock'])) {
			$sql .= " AND gd.isLock = " . $data['isLock'];
		}
		$sql .= " ORDER BY gd.subjectId ASC ";
		return $this->getAdapter()->fetchRow($sql);
	}
	function getSubjectScoreByGroup($data)
	{

		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE
		s.id=gsjd.subject_id LIMIT 1) ";

		$db = $this->getAdapter();
		$sql = "SELECT
			gsjd.*,
			g.amount_subject AS amount_subjectdivide,
			gsjd.max_score AS max_subjectscore,
			gsjd.score_short as cut_score,
			(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
			(SELECT CONCAT(sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name,
			(SELECT CONCAT(sj.subject_titleen) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS sub_name_en,
			(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
			(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut,
			$strSubjectLange AS subjectLang,
			(gsjd.amount_subject) amtsubject_month,
			(gsjd.amount_subject_sem) amtsubject_semester,
			(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen,
			(SELECT dsd.score_in_class from rms_dept_subject_detail as dsd where dsd.dept_id = g.degree and dsd.subject_id = gsjd.subject_id LIMIT 1) as max_score
		FROM
			rms_group_subject_detail AS gsjd ,
			rms_group as g
		WHERE
		g.id = gsjd.group_id
		";
		if (!empty($data['group_id'])) {
			$sql .= " and gsjd.group_id = " . $data['group_id'];
		}
		if (!empty($data['teacher_id'])) {
			$sql .= " AND gsjd.teacher = " . $data['teacher_id'];
		}
		if (!empty($data['teacher_id'])) {
			$sql .= " AND gsjd.teacher = " . $data['teacher_id'];
		}
		if (!empty($data['exam_type'])) {
			if($data['exam_type']==1){
				$sql .= " AND gsjd.amount_subject > 0 ";
			}else{
				$sql .= " AND gsjd.amount_subject_sem > 0 ";
			}
		} 
		$sql .= " ORDER  BY $strSubjectLange ";
		return $db->fetchAll($sql);
	}

	public function publicAllResult($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$this->_name='rms_score';
			// print_r($_data);
			// exit();
	    	if (!empty($_data['id_selected'])) {
				$ids = explode(',', $_data['id_selected']);
				foreach ($ids as $rs){
					$_arr = array(
						'isPublic'	 	=>1,
						'publicDate'	=>date("Y-m-d"),
						'publicBy'		=>$this->getUserId(),
						);
					$where = " id = ".$rs;
					$this->update($_arr, $where);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
	
}
