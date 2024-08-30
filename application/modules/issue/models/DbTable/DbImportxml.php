<?php

class Issue_Model_DbTable_DbImportxml extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group_schedule';
	function ImportXMLSchedule($data){

		
		$dbg = new Application_Model_DbTable_DbGlobal();
		
		$urlPart= PUBLIC_PATH.'/xml/';
		if (!file_exists($urlPart)) {
			mkdir($urlPart, 0777, true);
		}	
		
		$fileName = $_FILES['xml_file']['name'];
		// if (!empty($_data['uploaded'])){
		// 	$photo=$_data['uploaded'];
		// }else if (!empty($name)){
			// $ss = 	explode(".", $name);
			// $image_name = "profile_student_".date("Y").date("m").date("d").time().".".end($ss);
			$tmp = $_FILES['xml_file']['tmp_name'];
			if(move_uploaded_file($tmp, $urlPart.$fileName)){
			$string = "upload succussed";
			}
			else{
				$string = "Image Upload failed";
			}
		echo $string;
		exit();

		$xml = simplexml_load_file($urlPart.$fileName,'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
		$dbxml = new Issue_Model_DbTable_DbImportxml;
		$array = json_decode(json_encode((array)$xml), TRUE);
		/**
		 * check subject
		 */
		$tableData = $array["subjects"]["subject"];
		$subjectColumn = $array["subjects"]["@attributes"]["columns"];
		$columnList = explode(',', $subjectColumn);
		if(!empty($tableData)) foreach($tableData as $row){//use
			$subjectTitle =null;//$row["@attributes"]['name'];
			$strId =$row["@attributes"]['id'];
			$shortcut =$row["@attributes"]['short'];
			$Subject = $this->getSubjectId($subjectTitle,$strId,$shortcut);
		}


		/**
		 * check teacher
		 */
		$tableData = $array["teachers"]["teacher"];
		$periodsColumn = $array["teachers"]["@attributes"]["columns"];
		$columnList = explode(',', $periodsColumn);
		if(!empty($tableData)) foreach($tableData as $row){//use

			$teacherTitle =null;//$row["@attributes"]['name'];
			$strId =$row["@attributes"]['id'];
			$gender =$row["@attributes"]['gender'];
			$shortcut = $row['@attributes']['short'];
			$this->getTeacherId($teacherTitle,$gender,$strId,$shortcut);
		}


		/**
		 * Summary of getUserId
		 *check group
		 */

		 $tableData = $array["classes"]["class"];
		$periodsColumn = $array["classes"]["@attributes"]["columns"];
		$columnList = explode(',', $periodsColumn);

		if(!empty($tableData)) foreach($tableData as $row){//use
			$groupCode = str_replace('Grade ','',$row["@attributes"]['name']);
			$strId =$row["@attributes"]['id'];
			$groupId = $this->getGroupId($groupCode,$strId);
		}

		/**
		 * Summary of getUserId
		 * add card
		 */

		 $tableData = $array["cards"]["card"];
		 if(!empty($tableData)) foreach($tableData as $row){//use
			 $lessionId = $row["@attributes"]['lessonid'];
			 $period = $row["@attributes"]['period'];
			 $days = $row["@attributes"]['days'];
			 $this->addcard($lessionId,$period,$days);
		 }

		/**
		 * Summary of getUserId
		 * update existing schedule
		 */

		 
		$tableData = $array["lessons"]["lesson"];
		$periodsColumn = $array["classes"]["@attributes"]["columns"];
		$columnList = explode(',', $periodsColumn);

		if(!empty($tableData)) foreach($tableData as $row){
			$subjectId= $dbxml->getSubjectIdbyStrId($row["@attributes"]['subjectid']);
			$teacherId = $dbxml->getTeacherIdbyStrId($row["@attributes"]['teacherids']);
			$groupId = $dbxml->getGroupIdbyStrId($row["@attributes"]['classids']);
			
			$lessionId = $row["@attributes"]['id'];
			$data = array(
				'subject_id'=>$subjectId,
				'techer_id' =>$teacherId,
				'note'=>'abc',
				);
		$this->updateExistingSchedule($lessionId,$data,$groupId);
		}
	}
	function getDataFromCard($lessionId)
	{
		$db = $this->getAdapter();
		$sql = "select lessionstrId,`periodstrId`,daystrId,`fromhr`,tohr from `rms_cards` where lessionstrId='" . $lessionId . "'";
		return $db->fetchAll($sql);
	}
	function uploadXMLFile($data){
		
		$urlPart= PUBLIC_PATH.'/xml/';
		if (!file_exists($urlPart)) {
			mkdir($urlPart, 0777, true);
		}
		$fileName = $_FILES['xml_file']['name'];
		
			$tmp = $_FILES['xml_file']['tmp_name'];
			if(move_uploaded_file($tmp, $urlPart.$fileName)){
				$sessionXml=new Zend_Session_Namespace('xmlFile');
				$sessionXml->xml_FileName=$fileName;
				return 1;
			}
			else{
				return 0;
			}
		
	}
	function getXmlDataFromFile(){
	
		$sessionXml = new Zend_Session_Namespace('xmlFile');
		$fileName = $sessionXml->xml_FileName;//for creat sess
		if(!empty($fileName)){
			$urlPart= PUBLIC_PATH.'/xml/';
			$xml = simplexml_load_file($urlPart.$fileName,'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
			$dbxml = new Issue_Model_DbTable_DbImportxml;
			return json_decode(json_encode((array)$xml), TRUE);
		} else {
			return false;
		}
    	

	}
	function importxmlSubject($data)
	{
		$array = $this->getXmlDataFromFile();
		/**
		 * check subject
		 */
		
		$step = $data['step'];
		$branchId = 1;
		$academicYear = 8;
		if(!empty($array)){
			//$step = 7;

			if ($step == 2) {
				$tableData = $array["subjects"]["subject"];
				$subjectColumn = $array["subjects"]["@attributes"]["columns"];
				if (!empty($tableData)) {
					foreach ($tableData as $row) {//use
						$subjectTitle = null;//$row["@attributes"]['name'];
						$strId = $row["@attributes"]['id'];
						$shortcut = $row["@attributes"]['short'];
						$this->getSubjectId($subjectTitle, $strId, $shortcut);
					}
					return 3;
				}
			}elseif($step==3){
				/**
			 * check teacher
			 */
				$tableData = $array["teachers"]["teacher"];
				$periodsColumn = $array["teachers"]["@attributes"]["columns"];
				if(!empty($tableData)) foreach($tableData as $row){//use

					$teacherTitle =$row["@attributes"]['name'];
					$strId =$row["@attributes"]['id'];
					$gender =$row["@attributes"]['gender'];
					$shortcut = $row['@attributes']['short'];
					$teacher = $this->getTeacherId($teacherTitle,$gender,$strId,$shortcut);
				}
				return 4;
			} elseif ($step == 4) {
				$tableData = $array["classes"]["class"];
				
				if(!empty($tableData)) foreach($tableData as $row){//use
					$groupCode = str_replace('Grade ','',$row["@attributes"]['name']);
					$strId =$row["@attributes"]['id'];
					$groupId = $this->getGroupId($groupCode,$strId);
				}
				return 5;
			} elseif ($step == 5) {
				/**
			 * Summary of getUserId
			 *check group
			*/

			$tableData = $array["classes"]["class"];
			$periodsColumn = $array["classes"]["@attributes"]["columns"];
			$columnList = explode(',', $periodsColumn);

			if(!empty($tableData)) foreach($tableData as $row){//use
				$groupCode = str_replace('Grade ','',$row["@attributes"]['name']);
				$strId =$row["@attributes"]['id'];
				$groupId = $this->getGroupId($groupCode,$strId);
				$param = array(
					'groupId'=>$groupId,
				);
				$this->addSchedule($param);
			}

			/**
			 * Summary of getUserId
			 * add card
			 */

				return 6;
			} elseif ($step == 6){
				// $db = $this->getAdapter();
				// $db->fetchRow($sql);
				$tableData = $array["cards"]["card"];
				if(!empty($tableData)) foreach($tableData as $row){//use
					$lessionId = $row["@attributes"]['lessonid'];
					$period = $row["@attributes"]['period'];
					$days = $row["@attributes"]['days'];
						$this->addcard($lessionId,$period,$days);
				}
				return 7;
			} elseif ($step == 7) {
				$tableData = $array["lessons"]["lesson"];
				$periodsColumn = $array["classes"]["@attributes"]["columns"];
				$columnList = explode(',', $periodsColumn);

				if (!empty($tableData))
					foreach ($tableData as $row) {
						$subjectId = $this->getSubjectIdbyStrId($row["@attributes"]['subjectid']);
						$teacherId = $this->getTeacherIdbyStrId($row["@attributes"]['teacherids']);
						$groupId = $this->getGroupIdbyStrId($row["@attributes"]['classids']);

						$lessionId = $row["@attributes"]['id'];

						$resultCards = $this->getDataFromCard($lessionId);

						if (!empty($resultCards)) {
							$this->_name = 'rms_group_reschedule';
							foreach ($resultCards as $card) {
								$arr = array(
									'branch_id' => $branchId,
									'group_id' => $groupId,
									'year_id' => $academicYear,
									'day_id' => $card['daystrId'],
									'from_hour' => $card['fromhr'],
									'to_hour' => $card['tohr'],
									'subject_id' => $subjectId,
									'techer_id' => $teacherId,
									'create_date' => date('Y-m-d'),
								);
								$this->insert($arr);
							}
						}
					}
				return 8;
			}
		}else{
			return 0;
		}
	}
	function addSchedule($data)
	{
		$this->_name = "rms_group_schedule";
		$arr = array(
			'academic_year'=>8,
			'group_id'=>$data['groupId'],
			'create_date'=>date("Y-m-d")
		);
		return $this->insert($arr);
	}
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	public function getSubjectId($title=null,$strId,$shortcut=null){
		
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE 1 ";
		
		if(!empty($title)){
			$sublang = !empty(strpos($title,'(EN)'))?2:1;
			$title = str_replace('(EN)','', $title);
			$title = str_replace('(KH)','', $title);
			
			$sql.=" AND subject_titlekh = '".$title."' OR  subject_titleen='".$title."'";
			$sql.=" AND subject_lang= $sublang AND type_subject=1";
		}
		if(!empty($shortcut)){
			$sql.=" AND shortcut='".$shortcut."'";
		}

		$subject_id =  $db->fetchOne($sql);
        
		if(empty($subject_id)){
			
			$arr = array(
				'subject_titlekh'=>$title,
				'subject_titleen'=>$title,
				'shortcut'		=>$shortcut,
				'schoolOption'	=>1,
				'is_parent'		=>0,
				'subject_lang'	=>$sublang ,
				'type_subject'	=>1,
				'date'			=>date("Y-m-d"),
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
			);
			$this->_name='rms_subject';
			$subject_id = $this->insert($arr); 
		}else{
			$this->_name='rms_subject';
			$array = array(
				'strId' => $strId,
			);
			$where = "id =".$subject_id;

			$this->update($array,$where);
		}
		return $subject_id;
	}
	public function getTeacherId($title,$gender,$strId,$shortcut=null){
	
		$db = $this->getAdapter();
		
			$sql="SELECT id FROM `rms_teacher` WHERE 1 ";
			if(!empty($title)){
				$sql.=" AND (teacher_name_kh = '".$title."' OR teacher_name_en = '".$title."')"; 
			}
			elseif(!empty($shortcut)){
				$sql.=" AND teacher_code = '".$shortcut."'";
			}
			
			$teacherId =  $db->fetchOne($sql);

			$dbg = new Application_Model_DbTable_DbGlobal();
				
			if(empty($teacherId)){
				$sex= ($gender=='M')?1:2;
				
				$code = $dbg->getTeacherCode(1);

				$_arr=array(
						'branch_id' 		 => 1,
						'strId'	 			 => $strId,
						'teacher_name_en'	 => $title,
						'teacher_name_kh'	 => $title,
						'teacher_code'		 => $shortcut,
						'sex'				 => $sex,
						'nation' 			 => 1,
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);
				$this->_name = "rms_teacher";
				$teacherId =  $this->insert($_arr);
			}else{
				$this->_name='rms_teacher';
				$array = array(
						'strId' => $strId,
				);
				$where = "id =".$teacherId;
				$this->update($array,$where);
			}
		
		return $teacherId;
	}
	
	public function getGroupId($title,$strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_group` WHERE school_option=1 AND group_code like '%".$title."%' AND academic_year=8";
		$groupId =  $db->fetchOne($sql);
		$dbg = new Application_Model_DbTable_DbGlobal();
		if(empty($groupId)){
				$_arr=array(
					'group_code' 		 => $title,
					'strId' 		 	=> $strId,
					'create_date' 		 => date("Y-m-d"),
					'user_id'	  		 => $this->getUserId(),
			);
			$this->_name = "rms_group";
			return $this->insert($_arr);
		}else{
			$this->_name='rms_group';
			$array = array(
					'strId' => $strId,
			);
			$where = "id =".$groupId;
			$this->update($array,$where);
		}
	
		return $groupId;
	}
	public function getSubjectIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getTeacherIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_teacher` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getGroupIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_group` WHERE academic_year =8 AND strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getPeriodData($index){
		$rs = array(
				1=>array('fromhr'=>'7.30','tohr'=>'8.20'),
				2=>array('fromhr'=>'8.40','tohr'=>'9.30'),
				3=>array('fromhr'=>'9.40','tohr'=>'10.30'),
				4=>array('fromhr'=>'10.40','tohr'=>'11.30'),
				5=>array('fromhr'=>'13.10','tohr'=>'14.00'),
				6=>array('fromhr'=>'14.10','tohr'=>'15.00'),
				7=>array('fromhr'=>'15.10','tohr'=>'16.00')
				);
		return $rs[$index];
	}
	function addcard($lessionId,$period,$day){
			$db = $this->getAdapter();
			$rs = $this->getPeriodData($period);
					$_arr=array(
						'lessionstrId' 		 => $lessionId,
						'periodstrId'	 => $period,
						'fromhr'		=>$rs['fromhr'],
						'tohr'		=>$rs['tohr'],
						'daystrId'	 =>$this->dayofWeek($day),
				);
				$this->_name = "rms_cards";
				$this->insert($_arr);
	}
	
	function dayofWeek($str){
		$days = array(
				'10000'=>1,//mon
				'01000'=>2,
				'00100'=>3,
				'00010'=>4,
				'00001'=>5,//fri
				);
		return $days[$str];
	}
	
	function getCardList($strId){
		$db = $this->getAdapter();
		$sql="SELECT
			periodstrId,
			fromhr,
			tohr,
			daystrId
		FROM `rms_cards`
			WHERE lessionstrId='".$strId."'";
		$sql.=" ORDER BY daystrId ASC,fromhr ASC";
		return $db->fetchAll($sql);
	}
	
	function updateExistingSchedule($lessionId,$data,$groupId){
		$db = $this->getAdapter();
		$results = $this->getCardList($lessionId);
		foreach($results as $rs){
			$this->_name="rms_group_reschedule";
			$where = "year_id=8 AND group_id=".$groupId." AND day_id=".$rs['daystrId']." AND from_hour=".$rs['fromhr']." AND to_hour=".$rs['tohr'];
			 $this->update($data, $where);
		}
	}
	
}   

