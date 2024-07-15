<?php

class Api_Model_DbTable_DbActions extends Zend_Db_Table_Abstract
{
	
	public function loginAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentLogin($_data);
			if ($row['status']){
				if(!empty($row['value'])){
					$disableAccount=0;
					if($row['value']["isDisbleAccount"]==1){
						$date = new DateTime($row['value']["disableValidDate"]);
						$disableValidDate =  $date->format("Y-m-d H:i:s");
						if($disableValidDate < date("Y-m-d H:i:s") ){
							$disableAccount=1;
						}
					}
					
					if($disableAccount==1){
						$arrResult = array(
							"result" => $row['value'],
							"code" => "FAIL",
						);
					}else{
						$condictionArray = array();
						$condictionArray['id'] = empty($row['value']["id"])?0:$row['value']["id"];
						$condictionArray['deviceType'] = empty($_data['deviceType'])?1:$_data['deviceType'];
						$condictionArray['mobileToken'] = empty($_data['mobileToken'])?1:$_data['mobileToken'];
						$condictionArray['currentStudentId'] = empty($_data['currentStudentId'])?0:$_data['currentStudentId'];
						$condictionArray['tokenType'] = empty($_data['tokenType'])?0:$_data['tokenType'];
						$token = $db->generateToken($condictionArray);
						if($row['value']["isDisbleAccount"]==1){
							$condictionArray['studentId'] = empty($row['value']["id"])?0:$row['value']["id"];
							$db->enableMyAccount($condictionArray);
						}
						
						$arrResult = array(
							"result" => $row['value'],
							"code" => "SUCCESS",
						);
					}
					
					
					
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
	public function loginWebAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentLogin($_data);
			if ($row['status']){
				if(!empty($row['value'])){
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
			$db = new Api_Model_DbTable_DbApi();
			$_data['oldPassword'] = empty($_data['oldPassword'])?0:$_data['oldPassword'];
			$_data['stu_id'] = empty($_data['stu_id'])?0:$_data['stu_id'];
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
			$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentInformation($search);
	
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
	public function paymentAction($search){
		try{
			$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getDailyReport($search);
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
	
	public function paymentDetailAction($search){
		try{
			$payment_id = empty($search['payment_id'])?1:$search['payment_id'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getPayment($payment_id,$currentLang);
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
	public function scheduleAction($search){
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$db = new Api_Model_DbTable_DbApi();
		$row = $db->getSchedule($search);
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
	}
	public function scorelAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getMainScore($search);
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
	}
	public function scoredetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getScoreExame($search);
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
	}
	public function envaluationAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentEvaluation($search);
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
	}
	public function attendanceAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAttendenceBydate($search);
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
	}
	public function attendanceDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['currentMonth'] = empty($search['currentMonth'])?1:$search['currentMonth'];
		$search['groupId'] = empty($search['groupId'])?1:$search['groupId'];
		
		$row = $db->getAttendenceDetail($search);
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
	}
	public function disciplineAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getDisciplineBydate($search);
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
	}
	public function disciplineDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['currentMonth'] = empty($search['currentMonth'])?1:$search['currentMonth'];
		$search['groupId'] = empty($search['groupId'])?1:$search['groupId'];
	
		$row = $db->getDisciplineDetail($search);
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
	}
	public function newsAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllNews($search);
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
	}
	public function notificationAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllNotification($search);
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
	}
	public function contactUsAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllContact($search);
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
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function SinglecontactAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSingleContact($search);
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
	}
	public function elearningAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllCategoryLearning($search);
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
	}
	public function getAllVideoLearningAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllVideoLearning($search);
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
	}
	public function slidshowAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllSlider($search);
		
		$dbPush = new Api_Model_DbTable_DbPushNotification();
		$dbPush->updateDeviceInfo($search);
			
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
	}
	
	public function coursePostAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllMobileCouse($search);
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
	}
	public function calendarAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['degree_id'] = empty($search['degree_id'])?1:$search['degree_id'];
		$row = $db->getCalendar($search);
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
	}
	public function holidayEveryYearAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['degree_id'] = empty($search['degree_id'])?1:$search['degree_id'];
		$row = $db->getCalendarHolidayEveryYear($search);
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
	}
	public function addTokenAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$recordId = $db->addAppTokenId($_data);
			if(!empty($recordId)){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$recordId,
				);
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "INVALID_OLD_PASSWORD",
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
	public function introductionHomeAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$result = array();
		$search['keyName'] = "lbl_introduction";
		$result['lbl_introduction']= $db->getMobileLabel($search);
		$search['keyName'] = "lbl_introduction_i";
		$result['lbl_introduction_i']= $db->getMobileLabel($search);
		$search['keyName'] = "introduction_image";
		$result['introduction_image']= $db->getMobileLabel($search);
		
		$search['keyName'] = "lbl_videointro";
		$result['lbl_videointro']= $db->getMobileLabel($search);
		
		$search['keyName'] = "lbl_howtouse";
		$result['lbl_howtouse']= $db->getMobileLabel($search);
		
		
		$dbPush = new Api_Model_DbTable_DbPushNotification();
		$dbPush->updateDeviceInfo($search);
		
		$row = array(
				'status' =>true,
				'value' =>$result,
		);
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
	}
	public function gradingSystemAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getGradingSystem($search);
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
	}
	public function disciplinePolicyAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getDisciplinePolicy($search);
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
	}
	public function attendancePolicyAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getAttendancePolicy($search);
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
	}
	public function schoolBranchListAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getSchoolBranchList($search);
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
	}
	
	public function studentEvaluationAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getStudentEnvaluation($search);
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
	}
	public function studentEvaluationDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getStudentEnvaluationDetail($search);
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
	}
	
	
	public function studentAttendanceAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getStudentAttendance($search);
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
		
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function studentAttendanceDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getStudentAttendanceDetail($search);
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
	}
	
	public function studentScheduleAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getStudentSchedule($search);
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
	}
	public function studentScoreAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentScore($search);
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
		
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	public function studentPaymentAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentPaymentHistory($search);
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
		
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function studentPaymentInfoAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentPaymentInfo($search);
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
	}
	public function studentPaymentDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentPaymentDetail($search);
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
	}
	public function newsDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getNewsDetail($search);
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
	}
	public function systemLanguageAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSystemLanguage($search);
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
	}
	public function systemViewTypeAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSystemViewType($search);
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
	}
	public function monthOfTheYearAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['stu_id'] = empty($search['stu_id'])?46:$search['stu_id'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getMonthOfTheYear($search);
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
	}
	
	public function systemAcademicYearAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSystemAcademicYear($search);
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
	}
	public function systemStudyDegreeAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSystemStudyDegree($search);
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
	}
	
	public function systemSettingKeycodeAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSystemSettingKeycode($search);
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
	}
	
	public function unreadAction($search){
		$db = new Api_Model_DbTable_DbApi();
		
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['unreadRecord'] = empty($search['unreadRecord'])?1:$search['unreadRecord'];
		$search['notificationType'] = empty($search['notificationType'])?"":$search['notificationType'];
		$search['unreadSection'] = empty($search['unreadSection'])?"newsUnread":$search['unreadSection'];

		if($search['unreadSection']=="notificationUnread"){
			$row = $db->getUnreadNotification($search);
		}else{
			$row = $db->getUnreadNews($search);
		}
		
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
	}
	
	public function newsReadAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['newsId'] = empty($search['newsId'])?0:$search['newsId'];
			$search['unreadRecord'] = empty($search['unreadRecord'])?1:$search['unreadRecord'];
			$search['recordType'] = empty($search['recordType'])?"":$search['recordType'];
			
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->updateNewsRead($search);
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
	
	public function notificationReadAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['unreadRecord'] = empty($search['unreadRecord'])?1:$search['unreadRecord'];
			$search['recordType'] = empty($search['recordType'])?"markAllRead":$search['recordType'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->updateNotificationRead($search);
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
	

	public function mobileNotifyAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['isCounting'] = empty($search['isCounting'])?0:$search['isCounting'];
		$row = $db->getAllMobileNotification($search);
		$result = $row['value'];
		if(!empty($search['isCounting'])){
			$result = count($row['value']);
		}
		if ($row['status']){
			$arrResult = array(
					"result" => $result,
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
	}
	public function setReadNotificationAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['readType'] = empty($search['readType'])?"markAllRead":$search['readType'];
			$search['recordType'] = empty($search['recordType'])?"0":$search['recordType'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->setReadNotification($search);
			
			$dbPush = new Api_Model_DbTable_DbPushNotification();
			$dbPush->updateDeviceInfo($search);
							
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
	
	public function removeTokenAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->removeAppTokenId($search);
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
	
	public function mobileNotificationDetailAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['notificationId'] = empty($search['notificationId'])?0:$search['notificationId'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		
		$row = $db->getMobileNotificationDetail($search);
		$result = $row['value'];
		
		if ($row['status']){
			$arrResult = array(
					"result" => $result,
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
	}
	
	public function getFormOptionSelectAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
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
	
	public function checkExistingStudentAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$_data['phoneNumber'] = empty($_data['phoneNumber'])?"":$_data['phoneNumber'];
			$_data['countryCode'] = empty($_data['countryCode'])?"":$_data['countryCode'];
			$_data['countryISOCode'] = empty($_data['countryISOCode'])?"":$_data['countryISOCode'];
			$_data['emailAddress'] = empty($_data['emailAddress'])?"":$_data['emailAddress'];
			
			$row = $db->getCheckExistingRegisterStudent($_data);
			if ($row['status']){
				$arrResult = array(
						"result" =>  $row['value'],
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
	
	public function submitNewRegisterAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
			$resultSubmit = $db->submitNewRegister($search);
			if ($resultSubmit['status']){
				if(!empty($resultSubmit['value'])){
					$row = $db->getPreRegisterInfo($resultSubmit['value']);
					if ($row['status']){
						if(!empty($row['value'])){
							$arrResult = array(
								"result" => $row['value'],
								"code" => "SUCCESS",
							);
							
							$dbPush = new Api_Model_DbTable_DbPushNotification();
							$notify = array(
								"typeNotify" => "successfulRegister",
							);
							$notify["notificationId"]  = $resultSubmit['value'];
							$dbPush->pushNotificationAPI($notify);
							
						}else{
							$arrResult = array(
								"code" => "ERR_",
								"message" => $row['value'],
							);
						}
					}else{
						$arrResult = array(
							"code" => "ERR_",
							"message" => $row['value'],
						);
					}
				
				}else{
					$arrResult = array(
						"code" => "ERR_",
						"message" => $row['value'],
					);
				}
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $resultSubmit['value'],
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
	
	public function studentCreditMemoAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
		$row = $db->getAllStudentCreditMemo($search);
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
	}
	public function studentCreditMemoTotalAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
		$row = $db->getTotalStudentCreditMemo($search);
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
	}
	
	public function metionScoreSettingAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getMentionGradeSetting($search);
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
	}
	
	public function studentTranscriptAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getStudentTranscriptExam($search);
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
	}
	
	public function studentSummaryAttendanceAndDisciplineAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
		$row = $db->getTotalStudentAttendance($search);
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
	}
	
	
	public function getStudentRequestPermissionAction($search){
		$db = new Api_Model_DbTable_DbApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
		$row = $db->getStudentRequestPermission($search);
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
	}
	
	public function studentRequestPermissionAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
			$resultSubmit = $db->submitStudentRequestPermission($search);
			if ($resultSubmit['status']){
				$arrResult = array(
								"result" => $resultSubmit,
								"code" => "SUCCESS",
							);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $resultSubmit['value'],
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
	
	public function studentRequestPermissionEditAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
			$resultSubmit = $db->editStudentRequestPermission($search);
			if ($resultSubmit['status']){
				$arrResult = array(
								"result" => $resultSubmit,
								"code" => "SUCCESS",
							);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $resultSubmit['value'],
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
	
	public function loginSchoolBusAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getSchoolBusLogin($_data);
			
			if ($row['status']){
				if(!empty($row['value'])){
					$condictionArray = array();
					$condictionArray['id'] = empty($row['value']["id"])?0:$row['value']["id"];
					$condictionArray['deviceType'] = empty($_data['deviceType'])?1:$_data['deviceType'];
					$condictionArray['mobileToken'] = empty($_data['mobileToken'])?1:$_data['mobileToken'];
					$condictionArray['currentStudentId'] = empty($_data['currentStudentId'])?0:$_data['currentStudentId'];
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
	public function onlineOfflineSchoolBusAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->onlineOfflineSchoolBus($_data);
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
	
	public function checkCurrentTokenAccountAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->checkCurrentTokenScoolBusAccount($_data);		
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
	
	public function getSchoolBusForStudentAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getSchoolBusForStudent($_data);
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
	
	public function getSchoolBusProfileAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getSchoolBusProfile($_data);
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
	
	public function getInstructionArticleAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getInstructionArticle($_data);
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
	
	public function disableMyAccountAction($search){
		try{
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Api_Model_DbTable_DbApi();
			$rs = $db->disableMyAccount($search);
			if($rs){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$rs,
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
	
	public function getSchoolBusScheduleAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getSchoolBusSchedule($_data);
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
	
	public function getAllStudentListForSchoolBusAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getAllStudentListForSchoolBus($_data);
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
	
	public function getStudentAchievementAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentAchievement($_data);
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
	
	public function getSystemStatusAction($_data){
		try{
			$_data['currentLang'] = empty($_data['currentLang'])?1:$_data['currentLang'];
			
			$title = "ប្រព័ន្ធកំពុងធ្វើការជួសជុល និងថែទាំ";
			$desc = "យើងខ្ញុំកំពុងរៀបចំ និងកែសម្រួលលើប្រព័ន្ធ កម្មវិធីរបស់យើងនឹងត្រឡប់មកវិញឆាប់ៗនេះ។ សូមអរគុណសម្រាប់ការអត់ធ្មត់រង់ចាំ។";
			if($_data['currentLang']==2){
				$title = "System Maintenance";
				$desc = "We are currently updating and improving our application. We expect to be back shortly. Thank you for your patience.";
			}
			$row = array();
			$row['value'] = array("status"=>1,"title"=>$title,"description"=>$desc);
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
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
	public function getSpecialFeatureAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getSpecialFeature($_data);
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
	
	public function getStudentCriteriaScoreAction($_data){
		try{
			
			$search['studentId'] = empty($search['studentId'])?0:$search['studentId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentCriteriaScore($_data);
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
}