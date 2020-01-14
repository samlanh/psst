<?php

class Api_Model_DbTable_DbActions extends Zend_Db_Table_Abstract
{

	public function loginAction($_data){
		try{
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentLogin($_data);
			if ($row['status']){
				if (!empty($row['value'])){
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
	public function profileAction($search){
		try{
			$stu_id = empty($search['stu_id'])?46:$search['stu_id'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Api_Model_DbTable_DbApi();
			$row = $db->getStudentInformation($stu_id,$currentLang);
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
	public function scoreAction($search){
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
	
}