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

			echo  $fromHourValue ;
			echo  $toHourValue ;
			exit();

			$sql1=" SELECT id FROM `rms_teacher` WHERE teacher_name_kh = '".$sheetData[$i]['A']." ' ";
			$teacherId =  $db->fetchOne($sql1);
			if(empty($teacherId)){
					$_arr=array(
						'branch_id' 		 => $data['branch_id'],
						'teacher_name_en'	 => $sheetData[$i]['A'],
						'teacher_name_kh'	 => $sheetData[$i]['A'],
						'tel'  				 => $sheetData[$i]['B'],
						'nation' 			 => 1,
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);
				$this->_name = "rms_teacher";
				$teacherId =  $this->insert($_arr);
			}

			$sql2=" SELECT id FROM `rms_group` WHERE group_code = '".$sheetData[$i]['C']." ' ";
			$groupId =  $db->fetchOne($sql2);
			if(empty($groupId)){
					$_arr=array(
						'branch_id'   	 => $data['branch_id'],
						'group_code'  	 => $sheetData[$i]['C'],
						'academic_year'  => $data['academic_year'],
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
					'branch_id'      =>$data['branch_id'],
					'academic_year'  =>$data['academic_year'],
					'group_id'		 =>$groupId,
					//'schedule_setting'=>$_data['schedule_setting'],
					'status'=>1,
					'create_date'	=>date("Y-m-d H:i:s"),
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'		=>$this->getUserId(),
				);
				$this->_name='rms_group_schedule';
				$scheduleId = $this->insert($_arr);
			}
		
			$dayData = array( 
				$sheetData[$i]['E'],
				$sheetData[$i]['F'],
				$sheetData[$i]['G'],
				$sheetData[$i]['H'],
				$sheetData[$i]['I'],
				$sheetData[$i]['J'],
			);
			print_r($dayData);

			$allDay = $dbg->getAllDay(1);
			if(!empty($allDay)){
				foreach ($allDay as $day){
					$arr = array(
    					'main_schedule_id'	=>$scheduleId,
    					'branch_id'			=>$data['branch_id'],
    					'group_id'			=>$groupId,
    					'year_id'			=>$data['academic_year'],
						'day_id'			=>$day['id'],
						'techer_id'			=>$teacherId ,
						//'subject_id'		=>$data['academic_year'],
    					//'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    					'from_hour'			=>$fromHourValue,
    					'to_hour'			=>	$toHourValue,	
    					'create_date'		=>date("Y-m-d H:i:s"),
    					'status'			=>1,
    					'user_id'			=>$this->getUserId(),
    						
    				);
					$this->_name='rms_group_reschedule';
					$this->insert($arr); 
				}
			}

    	}
		echo "Success";
		exit();
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
}   

