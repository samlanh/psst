<?php

class Foundation_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($formData,$data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	for($i=2; $i<=$count; $i++){
    		$stu_code = $dbg->getnewStudentId($formData['branch'],1);
    		// $remark = $data[$i]['P'];
    		if(!empty($data[$i]['Q'])){
    			// $remark = "(".$data[$i]['Q'].") ".$data[$i]['P'];
    		}
    		$arr = array(
    				'branch_id'		=>$formData['branch'],
    				'user_id'		=>1,
    				'stu_code'		=>$data[$i]['B'],
    				'stu_khname'	=>$data[$i]['C'],
    				'stu_enname'	=>trim($data[$i]['D']),
    				'last_name'		=>trim($data[$i]['E']),
    				'sex'			=>($data[$i]['F']=="M")?1:2,
    				'tel'			=>$data[$i]['G'],
    				'dob'			=>date("Y-m-d",strtotime($data[$i]['H'])),
    				'pob'			=>$data[$i]['I'],
    				
    				'father_enname'	=>$data[$i]['J'],
    				'father_khname'	=>$data[$i]['J'],
    				'father_phone'	=>$data[$i]['K'],
    				
    				'mother_khname'	=>$data[$i]['L'],
    				'mother_enname'	=>$data[$i]['L'],
    				'mother_phone'	=>$data[$i]['M'],
    				
    				'guardian_first_name'=>$data[$i]['N'],
    				'guardian_enname'=>$data[$i]['N'],
    				'guardian_khname'=>$data[$i]['N'],
    				'guardian_tel'	=>$data[$i]['O'],
    				'customer_type'	=>1,
    				'status'		=>1,
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'create_date'	=>date("Y-m-d",strtotime($data[$i]['S']))
    		);
     		$this->_name='rms_student';
    		$studentId = $this->insert($arr);

			$degreeId = $this->degreeList($data[$i]['P']);
			$gradeId =$data[$i]['Q'];
			$param = array(
					'branch_id'=>$formData['branch'],
					'group_code'=>$data[$i]['R'],
					'academic_year'=>1,
					'degree'=>$degreeId,
					'grade'=>$gradeId,
					'is_pass'=>1,
					'is_use'=>1,
					'tuitionfee_id'=>1,
					'school_option'=>1,

			);
			$groupResult = $this->checkGroupExits($param);
			if (!empty($groupResult)) {
				$groupId = $groupResult['id'];
			}else{
				$groupId = $this->insertGroup($param);
			}

			$this->_name='rms_group_detail_student';
			$arr = array(
					'branch_id'	=>$formData['branch'],
					'studentId'		=>$studentId,//$_data['stu_id_'.$k],
					'itemType'		=>1,
					'groupId'		=>$groupId,
					'academicYear'	=>1,
					'feeId'			=>1,//feeId
					'oldFeeId'		=>1,//Old Fee Id
					'schoolOption'  =>1,
					'degree'		=>$degreeId,
					'grade'			=>$gradeId,
					'session'		=>'',
					'startDate'		=>'',
					'endDate'		=>'',
					'balance'		=>0,
					'discountType'	=>'',
					'discountAmount'=>'',
					'user_id'		=>$this->getUserId(),
					'status'		=>1,
					'create_date'	=>date('Y-m-d H:i:s'),
					'modify_date'	=>date('Y-m-d H:i:s'),
					'old_group'		=>'',
					'isSetGroup'	=>empty($groupId)?0:1,
					'stopType'		=>0,
					'isCurrent'		=>1,
					'isNewStudent'	=>0,
					'isMaingrade'	=>1,//not sure
					'entryFrom'	=>4,//not sure
					'remark'	=>'grade upgrade'
			);
			if($data[$i]['V']=='quit'){
				$arr['stopType'] = 1;
			}
				$dbg->AddItemToGroupDetailStudent($arr);
    	}
    }
	
	function degreeList($strDegree)
	{
		$degreeListArray = array(
			'Primary'=>1,
			'Secondary'=>2,
			'High School'=>3,
			'Kindergarten'=>4,
		);
		return $degreeListArray[$strDegree];

	}
	function insertGroup($_data)
	{

		$this->_name = 'rms_group';
		$_arr = array(
			'branch_id' 	=> $_data['branch_id'],
			'group_code' 	=> $_data['group_code'],
			'academic_year' => $_data['academic_year'],
			'degree' 		=> $_data['degree'],
			'grade' 		=> $_data['grade'],
			'school_option' => 1,
			'date' 			=> date("Y-m-d"),
			'status'   		=> 1,
			'user_id'	 	=> $this->getUserId(),
			'is_use' 		=> 1,
			'is_pass'		=> 1,
			'note'			=>'1'
		);
		return $this->insert($_arr);
	}
	function checkGroupExits($_data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group where 1";
		if(!empty($_data['branch_id'])){
			$sql .= " AND branch_id=".$_data['branch_id'];
		}
		if(!empty($_data['academic_year'])){
			$sql .= " AND academic_year=".$_data['academic_year'];
		}
		if(!empty($_data['group_code'])){
			$sql .= " AND group_code='".$_data['group_code']."'";
		}
		if(!empty($_data['degree'])){
			$sql .= " AND degree='".$_data['degree']."'";
		}
		if(!empty($_data['grade'])){
			$sql .= " AND grade='".$_data['grade']."'";
		}
		// echo $sql;
		// exit();
		return $db->fetchRow($sql);
	}
}   

