<?php

class Api_Model_DbTable_DbActions extends Zend_Db_Table_Abstract
{
	
	public function loginAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentLogin($_data);
			if ($row['status']){
				if(!empty($row['value'])){
					$row['value']['deviceType'] = empty($_data['deviceType'])?1:$_data['deviceType'];
					$row['mobileToken'] = empty($_data['mobileToken'])?1:$_data['mobileToken'];
					$token = $db->generateToken($row['value']);
					
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
			$stu_id = empty($search['stu_id'])?46:$search['stu_id'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentInformation($stu_id,$currentLang,$action='profile');
// 			print_r($row);
// 			print(Zend_Json::encode($row));exit();
		
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
		$stu_id = empty($search['stu_id'])?46:$search['stu_id'];
		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		$db = new Api_Model_DbTable_DbApi();
		$row = $db->getSchedule($stu_id,$search);
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
	
}