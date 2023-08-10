<?php

class Issue_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group_schedule';
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

    public function ScheduleByImport($sheetData,$data){
    	$db = $this->getAdapter();
    	$count = count($sheetData);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$allDay = $dbg->getAllDay(1);
	
    	for($i=1; $i<=$count; $i++){

			$time = $sheetData[$i]['D'];
			list($from_hour_title, $to_hour_title) = explode("-",$time);
			
            $fromHourValue = $this->getTimeValue($from_hour_title);
			$toHourValue = $this->getTimeValue($to_hour_title);

			$scheduleid_detail=$this->getScheduleSettingDetail($data['schedule_setting'],$fromHourValue,$toHourValue);


			$sql2=" SELECT id FROM `rms_group` WHERE group_code = '".$sheetData[$i]['C']." ' ";
			$groupId =  $db->fetchOne($sql2);
			if(empty($groupId)){

					$teacher_id = 0;
					if(!empty($sheetData[$i]['A'])){
						$teacher_id = $this->getTeacherId($sheetData[$i]['A'], $data);
					}

					$_arr=array(
						'branch_id'   	 => $data['branch_id'],
						'group_code'  	 => $sheetData[$i]['C'],
						'academic_year'  => $data['academic_year'],
						'teacher_id'  	 => $teacher_id,
						'is_use'     	 => 0,
						'is_pass'     	 => 0,
						'status'     	 => 1,
						'create_date' 	 => date("Y-m-d H:i:s"),
						'user_id'	 	 => $this->getUserId()
				);
				$this->_name = "rms_group";
				$groupId =  $this->insert($_arr);
			}
			
			$sql2="SELECT id FROM `rms_group_schedule` WHERE group_id = ".$groupId." AND  academic_year=".$data['academic_year']." ";
			$scheduleId =  $db->fetchOne($sql2);
			
			if(empty($scheduleId)){
				$_arr = array(
					'branch_id'     	=>$data['branch_id'],
					'academic_year'  	=>$data['academic_year'],
					'group_id'		 	=>$groupId,
					'schedule_setting'	=>$data['schedule_setting'],
					'status'			=>1,
					'create_date'		=>date("Y-m-d H:i:s"),
					'modify_date'		=>date("Y-m-d H:i:s"),
					'user_id'			=>$this->getUserId(),
				);
				$this->_name='rms_group_schedule';
				$scheduleId = $this->insert($_arr);
			}
		
			$dayData = array( 
				$sheetData[$i]['E'],
				$sheetData[$i]['G'],
				$sheetData[$i]['I'],
				$sheetData[$i]['K'],
				$sheetData[$i]['M'],
				$sheetData[$i]['O'],
			);
			
			$teacherData = array( 
				$sheetData[$i]['F'],
				$sheetData[$i]['H'],
				$sheetData[$i]['J'],
				$sheetData[$i]['L'],
				$sheetData[$i]['N'],
				$sheetData[$i]['P'],
			);
			

			$dayId=1;
			for($j=0; $j<count($dayData); $j++){

					$subject_id='';
					if(!empty($dayData[$j])){
						$subject_id = $this->getSubjectId($dayData[$j]);
					}
					$teacherId = 0;
					if(!empty($teacherData[$j])){
						$teacherId = $this->getTeacherId($teacherData[$j], $data);
					}
					
					$arr = array(
    					'main_schedule_id'		=>$scheduleId,
    					'branch_id'				=>$data['branch_id'],
    					'group_id'				=>$groupId,
    					'year_id'				=>$data['academic_year'],
						'day_id'				=>$dayId,
						'techer_id'				=>$teacherId ,
						'subject_id'			=>$subject_id,
    					'schedule_setting_id'	=>$scheduleid_detail['id'],
    					'from_hour'				=>$scheduleid_detail['from_hour'],
    					'to_hour'				=>$scheduleid_detail['to_hour'],	
    					'create_date'			=>date("Y-m-d H:i:s"),
    					'study_type'			=>1,
						'status'				=>1,
    					'user_id'				=>$this->getUserId(),
    				);
					$this->_name='rms_group_reschedule';
					$this->insert($arr); 
				$dayId++;
			}
    	}
		
    }

	public function getSubjectId($title){
		$subject_type=1;
		$subject_lang=1;
		if($title=="ម៉ោងសម្រាក"){
			$subject_type=2;
			$subject_lang='';
		}

		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE subject_titlekh LIKE '%".$title."%' ";
		if($subject_type==1){
			$sql.="  AND subject_lang=1 AND type_subject=1 ";
		}elseif($subject_type==2){
			$sql.=" AND type_subject=2 ";
		}
		$subject_id =  $db->fetchOne($sql);
        
		if(empty($subject_id)){
			$arr = array(
				'title'	        =>$title,
				'title_en'		=>$title,
				'schoolOption'	=>1,
				'is_parent'		=>0,
				'subject_lang'	=>$subject_lang,
				'type_subject'	=>$subject_type,
				'note'		    =>"From Import",
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
			);
			$this->_name='rms_subject';
			$subject_id = $this->insert($arr); 
		}
		return $subject_id;
	}
	
	public function getTeacherId($title,$data){
		$db = $this->getAdapter();
		if(!empty(($title))){
			$sql=" SELECT id FROM `rms_teacher` WHERE teacher_name_kh LIKE '%".$title."%' ";
			$teacherId =  $db->fetchOne($sql);
			if(empty($teacherId)){
					$_arr=array(
						'branch_id' 		 => $data['branch_id'],
						'teacher_name_en'	 => $title,
						'teacher_name_kh'	 => $title,
						'nation' 			 => 1,
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);
				$this->_name = "rms_teacher";
				$teacherId =  $this->insert($_arr);
			}
			
		}else{
			$teacherId=0;
		}
		return $teacherId;
	}
  

	public function getTimeValue($title){
		$db = $this->getAdapter();
		$sql=" SELECT value FROM `rms_timeseting` WHERE title LIKE '%".$title."%' ";
		$timeValue =  $db->fetchOne($sql);
        
		if(empty($timeValue)){
			
			$value = str_replace("AM","",$title);
			$value1 = str_replace("PM","",$value);
			$value2 = str_replace(":",".",$value1);

            $valueTime = floatval($value2); // Convert String To Float

            if (strpos($title, 'PM')==true) {
				$valueTime = $valueTime + 12;
			} 
			$arr = array(
				'title'	        =>$title,
				'title_en'		=>$title,
				'value'			=>$valueTime,
				'note'		    =>"From Import",
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
			);
			$this->_name='rms_timeseting';
			$this->insert($arr); 
			$timeValue = $valueTime;
		}

		return $timeValue;
	}

	public function getScheduleSettingDetail($id, $fromhour, $tohour){
		$db = $this->getAdapter();
		$sql="SELECT s.*
		 FROM `rms_schedulesetting_detail` AS s WHERE s.setting_id=".$id." AND from_hour=".$fromhour." AND to_hour=".$tohour." ";
		return $db->fetchRow($sql);
	}
}   

