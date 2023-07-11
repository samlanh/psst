<?php

class Api_Model_DbTable_DbPushNotification extends Zend_Db_Table_Abstract
{
	
	function getPreRegisterByID($registerId){
		$db = $this->getAdapter();
		$sql="SELECT 
						pre.*
						,'0' AS isFromStudentTB 
					FROM  rms_mobile_pre_register AS pre
					WHERE pre.status = 1 AND pre.id = $registerId
					LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getMobileToken($_data)
	{

		$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
		$db = $this->getAdapter();
		$sql = "SELECT mb.`token`
				FROM `mobile_mobile_token` AS mb
				WHERE mb.stu_id != 0 ";
		return  $db->fetchCol($sql);
	}
	function pushNotificationAPI($_data)
	{
		try{
		
			$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
			
	
			$notificationId = empty($_data['notificationId']) ? 0 : $_data['notificationId'];
			$notificationTitle = "Notification Title";
			$notificationSubTitle = "Notification Sub Title";
			$notificationDescription = "";
			$typeNotify = empty($_data['typeNotify']) ? "successfulRegister" : $_data['typeNotify'];
			
			
			$androidToken = $this->getMobileToken($_data);
			$recordDetail = array();
			if($typeNotify == "successfulRegister"){
				$info = $this->getPreRegisterByID($_data['notificationId']);
				$firstName = $info["firstName"];
				$lastName = $info["lastName"];
				$fullKhName = $info["fullKhName"];
				$androidToken = array($info["deviceToken"]);
				
				$notificationTitle = "ស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សា - Register to Study";
				$notificationSubTitle = "ការស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សាបានដោយជោគជ័យ ការចុះឈ្មោះត្រូវបានដាក់បញ្ជូនដើម្បីធ្វើការត្រួតពិនិត្យ";
				$notificationDescription = "សួស្ដី $fullKhName ការស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សាបានដោយជោគជ័យ សូមធ្វើការរង់ចាំការទំនាក់ទំនងត្រឡប់ទៅវិញ បន្ទាប់ពីក្រុមការងារត្រួតពិនិត្យរួចរាល់។";
				$recordDetail = array($info);
			}
	
	
			$dataNotify = array(
				"notificationId" 	=> $notificationId,
				"title" 			=> $notificationTitle,
				"subTitle" 			=> $notificationDescription,
				"typeNotify" 		=> $typeNotify,
				"recordDetail" 		=> $recordDetail,
			);
			$headings = array(
				"en" => $notificationTitle,
			);
			$content = array(
				"en" => $notificationSubTitle,
			);
	
			
			$fields = array(
				'app_id' => APP_ID,
				'include_player_ids' => $androidToken,
				'data' => $dataNotify,
				'headings' => $headings,
				'contents' => $content,
				"external_id" => null,
				"ios_badgeType" => "Increase",
				"ios_badgeCount" => 1,
				
	
			);
	
			$fields = json_encode($fields);
	
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic OGY3MGQ2M2EtMmQ3OS00MjZhLTk2MjYtYjYzMzExYTg5YWRm'
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	
			$response = curl_exec($ch);
			
			curl_close($ch);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	

	
}
