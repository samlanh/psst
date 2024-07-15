<?php
class Mobileapp_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_about';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	function getLastActiveDeviceInfo($data){
		
		$apiKey = "OWY1Nzg2YjgtMjhhZi00Njk1LTg5YTQtNTQwY2NkM2U2NTQ1";
		$appId = "3c593dd9-4549-422c-ac11-4e202953100c";
		$curl = curl_init();
		$playerId = "928b7d15-22df-44c8-8202-52b4bc6d8b13";
		$playerId = empty($data["token"]) ? $playerId : $data["token"];
		//$urlAllPlayer = "https://onesignal.com/api/v1/players?app_id=$appId";
		$urlByOnePlayer = "https://onesignal.com/api/v1/players/$playerId?app_id=$appId";
		curl_setopt_array($curl, [
		  CURLOPT_URL => $urlByOnePlayer,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => [
			"Authorization: Basic $apiKey",
			"Content-Type: application/json; charset=utf-8",
			"accept: application/json"
		  ],
		]);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		if ($err) {
		  //echo "cURL Error #:" . $err;
		  return null;
		} else {
			$convertArr =Zend_Json::decode($response);
			return $convertArr;
		}
	
	}
	function updateDeviceInfo($rs){
		if(!empty($rs)) foreach($rs as $key => $row){
			$result = $this->getLastActiveDeviceInfo($row);
			if(!empty($result)){
				$arr = array(
					'device_os'			=>$result["device_os"],
					'device_model_name'	=>$result["device_model"],
					'timezone'			=>$result["timezone"],
					'last_active'		=>$result["last_active"],
					'created_at'		=>$result["created_at"],
				);
				$this->_name="mobile_mobile_token";
				$whereRs=" id=".$row['deviceId'];
				$this->update($arr,$whereRs);
			}
		}
		return $rs;
	}
	function getCountingDownloadedDevice($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$date = new DateTime();
		$currentDate = $date->format('Y-m-d');
		$dateFiltering = empty($search['dateFiltering']) ? $currentDate : $search['dateFiltering'];
		
		$sql="
			SELECT
				COUNT(mtk.id) AS totalDownloaded
				,COUNT(IF(DATE_FORMAT(mtk.`date`,'%Y/%m') = DATE_FORMAT('$dateFiltering','%Y/%m'),mtk.id,NULL)) AS totalDownloadThisMonth 
				,COUNT(IF(DATE_FORMAT(mtk.`date`,'%Y/%m/%d') = DATE_FORMAT('$dateFiltering','%Y/%m/%d'),mtk.id,NULL)) AS totalDownloadToday
				,COUNT(IF(mtk.`device_type` = 1,mtk.id,NULL)) AS iosDeviceDownloaded
				,COUNT(IF(mtk.`device_type` = 2,mtk.id,NULL)) AS androidDeviceDownloaded
			FROM `mobile_mobile_token` AS mtk 
			WHERE 1 
		";
		return $db->fetchRow($sql);
	}
	
	function getCountingUserAccount($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$date = new DateTime();
		$currentDate = $date->format('Y-m-d');
		$dateFiltering = empty($search['dateFiltering']) ? $currentDate : $search['dateFiltering'];
		
		$sql="
			SELECT 
				mtk.`tokenType`
				,COUNT(DISTINCT(IF(mtk.`tokenType` = 1,mtk.`stu_id`,NULL))) AS countStudent
				,COUNT(DISTINCT(IF(mtk.`tokenType` = 3,mtk.`stu_id`,NULL))) AS countTeacher
				,COUNT(DISTINCT(IF(mtk.`tokenType` = 2,mtk.`stu_id`,NULL))) AS countSchoolBus
				,COUNT(DISTINCT(IF(mtk.`tokenType` = 0,mtk.`stu_id`,NULL))) AS countUnknow
			FROM `mobile_mobile_token` AS mtk 
			WHERE 1 
		";
		return $db->fetchRow($sql);
	}
	
	
	function getDeviceAndAccountInfo($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$degree = "title_en";
		$grade = "title_en";
		if($currentLang==1){
			$degree = "title";
			$grade = "title";
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$date = new DateTime();
		$currentDate = $date->format('Y-m-d');
		$dateFiltering = empty($search['dateFiltering']) ? $currentDate : $search['dateFiltering'];
		$search['forDeviceCondiction'] = empty($search['forDeviceCondiction']) ? 1 : $search['forDeviceCondiction'];
		
		$querySelect="
					,'' AS userCode
					,'' AS userNameInKh
					,'' AS userNameInEn
				";
		$queryJoinTable="";
		$queryWhere="";
		if(!empty($search['tokenType'])){
			if($search['tokenType']==1){
				$querySelect="
					,s.stu_code AS userCode
					,s.stu_khname AS userNameInKh
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS userNameInEn
					,gds.degree
					,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = gds.`group_id` LIMIT 1) AS groupCode
					,(SELECT i.$degree FROM `rms_items` AS i WHERE i.id = gds.`degree` AND i.type=1 LIMIT 1) AS degreeTitle
					,(SELECT it.$grade FROM `rms_itemsdetail` AS it WHERE it.id = gds.`grade` AND it.items_type=1 LIMIT 1) AS gradeTitle
				";
				$queryJoinTable="
				LEFT JOIN (rms_student AS s JOIN rms_group_detail_student AS gds ON gds.stu_id = s.stu_id AND gds.itemType=1 AND gds.is_current=1 AND gds.is_maingrade=1) 
				  ON s.stu_id = mtk.stu_id AND mtk.tokenType = 1
				";
				$queryWhere="";
				if(!empty($search['adv_search'])){
					$s_where = array();
					$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
					$s_where[]=" REPLACE(mtk.device_model_name,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(s.stu_code,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(s.stu_khname,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id = gds.`group_id` LIMIT 1),' ','') LIKE '%{$s_search}%'";
					$queryWhere.=' AND ( '.implode(' OR ',$s_where).')';
				}
				
				if(!empty($search['degree'])){
					$queryWhere.=" AND gds.degree = ".$search['degree'];
				}
				if(!empty($search['gradeId'])){
					$queryWhere.=" AND gds.`grade` = ".$search['gradeId'];
				}
				$queryWhere.=$dbp->getAccessPermission('s.branch_id');
				$queryWhere.=$dbp->getDegreePermission('COALESCE(gds.degree,0)');
			}else if($search['tokenType']==2){
			}else if($search['tokenType']==3){
				$querySelect="
					,t.teacher_code AS userCode
					,t.teacher_name_kh AS userNameInKh
					,t.teacher_name_en AS userNameInEn
				";
				$queryJoinTable="
					LEFT JOIN rms_teacher AS t ON t.id = mtk.stu_id AND mtk.tokenType = 3
				";
				$queryWhere="";
				if(!empty($search['adv_search'])){
					$s_where = array();
					$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
					$s_where[]=" REPLACE(mtk.device_model_name,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(t.teacher_code,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(t.teacher_name_kh,' ','') LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(t.teacher_name_en,' ','') LIKE '%{$s_search}%'";
					$queryWhere.=' AND ( '.implode(' OR ',$s_where).')';
				}
			}
		}
		
		$sql="
			SELECT 
				mtk.`id` AS deviceId
				,mtk.stu_id AS userId
				,mtk.tokenType AS tokenType
				,CASE 
				  WHEN mtk.tokenType = 1 THEN '".$tr->translate("STUDENT")."'
				  WHEN mtk.tokenType = 2 THEN '".$tr->translate("BUS")."'
				  WHEN mtk.tokenType = 3 THEN '".$tr->translate("TEACHER")."'
				  ELSE '".$tr->translate("Unknow")."' 
				END AS accountType";
		$sql.=	$querySelect;
		
		$sql.="		
				,mtk.token
				,mtk.device_os
				,mtk.device_model_name
				,mtk.last_active
				,DATE_FORMAT(FROM_UNIXTIME(mtk.last_active), '%Y-%m-%d') AS lastActiveDate
				,mtk.created_at
				,mtk.date
				
		";
		$sql.="
			FROM 
				  mobile_mobile_token AS mtk 
		";
		$sql.=	$queryJoinTable;
		$sql.="
			WHERE 1 
		";
		$sql.=	$queryWhere;
		if(!empty($search['tokenType'])){
			$sql.=" AND mtk.tokenType = ".$search['tokenType'];
		}
		$from_date =(empty($search['start_date']))? '1': " DATE_FORMAT(FROM_UNIXTIME(mtk.last_active), '%\Y-%\m-%\d %\H:%\i:%\s') >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " DATE_FORMAT(FROM_UNIXTIME(mtk.last_active), '%\Y-%\m-%\d %\H:%\i:%\s') <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		
		$sql.="";
		$order=" ORDER BY mtk.last_active DESC ";
		
		$sql.=$order;
		
		if(!empty($search['limitRecord'])){
			$sql.=" LIMIT ".$search['limitRecord'];
		}
		return $db->fetchAll($sql);
	}
	
	function updateStudentPassword($data){
		$_db= $this->getAdapter();		
		$_db->beginTransaction();
		try{
			$arr = array(
				'password'			=>md5($data["password"]),
			);
			$this->_name="rms_student";
			$whereRs=" stu_id=".$data['userId'];
			$this->update($arr,$whereRs);
			
			$_db->commit();
			return 1;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			return 0;
    	}
	}
	
	function updateTeacherPassword($data){
		$_db= $this->getAdapter();		
		$_db->beginTransaction();
		try{
			$arr = array(
				'password'			=>md5($data["password"]),
			);
			$this->_name="rms_teacher";
			$whereRs=" id=".$data['userId'];
			$this->update($arr,$whereRs);
			
			$_db->commit();
			return 1;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			return 0;
    	}
	}

}