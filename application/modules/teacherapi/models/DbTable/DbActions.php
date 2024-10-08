<?php

class Teacherapi_Model_DbTable_DbActions extends Zend_Db_Table_Abstract
{
	
	public function loginAction($_data){
		try{
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getTeacherLogin($_data);
			if ($row['status']){
				if(!empty($row['value'])){
					
					$condictionArray = array();
					$condictionArray['id'] = empty($row['value']["id"])?0:$row['value']["id"];
					$condictionArray['deviceType'] = empty($_data['deviceType'])?1:$_data['deviceType'];
					$condictionArray['mobileToken'] = empty($_data['mobileToken'])?1:$_data['mobileToken'];
					$condictionArray['currentUserId'] = empty($_data['currentUserId'])?0:$_data['currentUserId'];
					$condictionArray['tokenType'] = empty($_data['tokenType'])?0:$_data['tokenType'];
					$token = $db->generateToken($condictionArray);
					
					$arrResult = array(
						"result" => $row['value'],
						"code" => "SUCCESS",
					);
				}else{
					$arrResult = array(
						"result" => $row['value'],
						"code" => "FAIL",
					);
				}
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($_data);

			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function changePasswordAction($_data){
		try{
			$db = new Teacherapi_Model_DbTable_DbApi();
			$_data['oldPassword'] = empty($_data['oldPassword'])?0:$_data['oldPassword'];
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$row = $db->checkChangePassword($_data);
			if (!$row){
				$arrResult = array(
					"code" => "FAIL",
					"message" => "INVALID_OLD_PASSWORD",
				);
			}else{
				$row = $db->changePassword($_data);
				if (!$row){
					$arrResult = array(
						"code" => "FAIL",
						"message" => "UNABLE_TO_CHANGE_PASSWORD",
					);
				}else{
					$arrResult = array(
						"code" => "SUCCESS",
						"message" => "",
					);
				}
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
					"code" => "ERR_",
					"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function profileAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getTeacherInformation($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getTeachingScheduleAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getTeachingSchedule($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getIssueScoreListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getIssueScoreListByClass($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getScoreAvailableForIssueAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getSubjectOfClassAvailableForIssue($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function getSubjectCriteriaOfClassAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getSubjectCriteriaOfClassByTeacher($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getStudentListOfClassAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getStudentList($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function submitCriteriaScoreAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Teacherapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitCriteriaScore($search);
			
			if($submitRequest["status"]){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest["status"],
				);	

				/*
				$dbPush = new Api_Model_DbTable_DbPushNotification();
				$gradingTmpId = empty($submitRequest['value']) ? 0 : $submitRequest['value'];
				$notify = array(
					"typeNotify" => "criteriaStudentScore",
					"optNotification" => 3,
					"notificationId" => $gradingTmpId,
					"studentId" => 3,
				);
				
				$stTmpScore = $dbPush->getGradingTmpStudentList($gradingTmpId);
				if(!empty($stTmpScore)) foreach($stTmpScore as $st){
					$notify["studentId"] = empty($st["studentId"]) ? 0 : $st["studentId"];
					$title = $st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]." ថ្នាក់ ".$st["groupCode"];
					$title = $title." - ".$st["criteriaTitle"]." Of ".$st["subjectTitle"]." Class ".$st["groupCode"];
					$subTitle = "ពិន្ទូទទូលបាន ".$st["totalGrading"]." ពិន្ទុ លើ".$st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]."។";
					$subTitle = $subTitle." Score ".$st["totalGrading"]." Pt(s) for ".$st["criteriaTitle"]." Of ".$st["subjectTitle"].".";
					
					$notify["title"] = $title;
					$notify["subTitle"] = $subTitle;
					$dbPush->pushNotificationAPI($notify);	
				}	
				*/	
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function submitEditCriteriaScoreAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Teacherapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitEditCriteriaScore($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
				);		
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getCriteriaScoreListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getCriteriaScoreList($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function getInfoForMonthlyScoreAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getPolicyInfoForIssueMonthlyScore($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function submitMonthlyScoreAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Teacherapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitMonthlyScore($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
				);		
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getGroupMonthlyScoreDetailAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getGroupMonthlyScoreDetail($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getFormOptionSelectAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getFormOptionSelect($search);
			if ($row['status']){
				$arrResult = array(
						"result" => $row['value'],
						"code" => "SUCCESS",
					);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	
	public function getCountingClassAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getCountingClassByTeacher($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getTeachingClassListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getTeachingClassList($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getScoreResultOfClassAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getScoreResultOfClass($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getClassAvailableForEvaluationAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getClassAvailableForEvaluation($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getEvaluationCommentByDegreeAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getEvaluationCommentByDegree($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function submitEvaluationStudentAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Teacherapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitEvaluationRatingStudent($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
				);		
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getAssessmentListOfClassAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getAssessmentListOfClass($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getVideoTeacherTutorialAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Teacherapi_Model_DbTable_DbApi();
			$row = $db->getVideoTeacherTutorial($search);
	
			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
				
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
			
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	
}