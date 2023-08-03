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
		

		$model = new Application_Model_DbTable_DbGlobal();
    	$allDay = $model->getAllDay(1);
    		
    	

    	for($i=1; $i<=$count; $i++){

			$sql=" SELECT id FROM `rms_teacher` WHERE teacher_name_kh = '".$sheetData[$i]['A']." ' ";
			$teacherId =  $db->fetchOne($sql);
			if(empty($teacherId)){
					$_arr=array(
						'branch_id' 		 => $data['branch_id'],
						'teacher_name_en'	 => $sheetData[$i]['A'],
						'teacher_name_kh'	 => $sheetData[$i]['A'],
						'tel'  				 => $sheetData[$i]['B'],
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);
				$this->_name = "rms_teacher";
				$teacherId =  $this->insert($_arr);
			}

			$sql=" SELECT id FROM `rms_group` WHERE group_code = '".$sheetData[$i]['C']." ' ";
			$groupId =  $db->fetchOne($sql);
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
			
			$sql=" SELECT id FROM `rms_group_schedule` WHERE group_id = '.$groupId.' ";
			$scheduleId =  $db->fetchOne($sql);
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

			$day = array( $sheetData[$i]['E'], $sheetData[$i]['F'], $sheetData[$i]['G'], $sheetData[$i]['H'], $sheetData[$i]['I'], $sheetData[$i]['J']);
            if(!empty($day )){

				if(!empty($day)){
					foreach ($day as $j){
						$arr = array(
    						'main_schedule_id'	=>$scheduleId,
    						'branch_id'			=>$data['branch_id'],
    						'group_id'			=>$groupId,
    						'year_id'			=>$data['academic_year'],
							'day_id'			=>$j,
							'techer_id'			=>$teacherId ,
							//'subject_id'		=>$data['academic_year'],
    						//'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    						// 'from_hour'		=>$_data['from_hour_'.$i],
    						// 'to_hour'		=>$_data['to_hour_'.$i],
    						
    						'create_date'		=>date("Y-m-d H:i:s"),
    						'status'			=>1,
    						'user_id'			=>$this->getUserId(),
    						
    					);
	                    
					}
			   }

			}
		  
			

			// if(!empty($_data['identity1'])){
    		// 	$ids = explode(',', $_data['identity1']);
    		// 	foreach ($ids as $i){
    		// 		$arr = array(
    		// 				'main_schedule_id'		=>$id,
    		// 				'branch_id'		=>$_data['branch_id'],
    		// 				'group_id'		=>$_data['group_code'],
    		// 				'year_id'		=>$_data['academic_year'],
    						
    		// 				'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    		// 				'from_hour'		=>$_data['from_hour_'.$i],
    		// 				'to_hour'		=>$_data['to_hour_'.$i],
    						
    		// 				'create_date'	=>date("Y-m-d H:i:s"),
    		// 				'status'		=>1,
    		// 				'user_id'		=>$this->getUserId(),
    						
    		// 		);
    		// 		if (!empty($allDay)) foreach ($allDay as $day){
    		// 			$arr['study_type'] =  $_data['type_'.$i."_".$day['id']];
    		// 			if ( $_data['type_'.$i."_".$day['id']] ==1){
		    // 				$arr['day_id'] = $day['id'];
		    // 				$arr['subject_id'] =  $_data['subject_'.$i."_".$day['id']];
		    // 				$arr['techer_id'] =  $_data['teacher_'.$i."_".$day['id']];
    		// 			}else{
    		// 				$arr['day_id'] = $day['id'];
    		// 				$arr['subject_id'] =  "";
    		// 				$arr['techer_id'] =  "";
    		// 			}
	    	// 			$this->_name='rms_group_reschedule';
	    	// 			if($_data['type_'.$i."_".$day['id']]==1){ // 1=study , 2=no study
	    	// 				$this->insert($arr);
	    	// 			}
    		// 		}
    		// 	}
			// }


			



		
            // if(!empty($teacherId)){
			// 	echo $teacherId;
			// }
			// if(!empty($groupId)){
			// 	echo $groupId;
			// }
		
		
			
			
			
			// if(empty($cateId)){
			// 		$isMaterial = ($data[$i]['B']=="បេតុង")?1:0;
			// 		$_arr=array(
						
			// 			'categoryName'   => $data[$i]['B'],
			// 			'parentId'     	 => 0,
			// 			'status'     	 => 1,
			// 			'isMaterial'     => $isMaterial,
			// 			'createDate' 	=> date("Y-m-d H:i:s"),
			// 			'userId'	 	=> $this->getUserId()
			// 	);
			// 	$this->_name = "st_category";
			// 	$cateId =  $this->insert($_arr);
			// }

			// $sql=" SELECT id FROM `st_measure` WHERE name = '".$data[$i]['D']." ' ";
			// $measureId =  $db->fetchOne($sql);
			
			// if(empty($measureId)){
			// 	$_arr=array(
						
			// 			'name'       => $data[$i]['D'],
			// 			'status'     => 1,
			// 			'date'     	=> date("Y-m-d H:i:s"),
			// 			'user_id'	 => $this->getUserId()
			// 	);
			// 	$this->_name = "st_measure";
			// 	$measureId =  $this->insert($_arr);

			// }

			// $isCountStock = ($data[$i]['H']=="មិនរាប់")?0:1;
			// $dbp = new Product_Model_DbTable_DbProduct();
			// $proCode = $dbp->generateProductCode();
         
			// $str_Replace = str_replace( $data[$i]['B'] , ' ', $data[$i]['C']);
			// $proName = $data[$i]['B'].' '.$str_Replace;
			
			// $this->_name = "st_product";
			// $_arr=array(
			// 	'proName'  	  => $proName,
			// 	'proCode'  	  => $proCode ,
			// 	'categoryId'  	  => $cateId,
			// 	'measureId'   	 => $measureId,
			// 	'isConvertMeasure' => 0,
			// 	'measureLabel' => $data[$i]['D'],
			// 	'measureValue' => 1,
			// 	'isCountStock'   => $isCountStock,
			// 	'createDate' 	=> date("Y-m-d H:i:s"),
			// 	'userId'	 	=> $this->getUserId()
			// );

    	}
		exit();
    }
}   

