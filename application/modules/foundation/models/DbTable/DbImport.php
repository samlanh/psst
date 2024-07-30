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

			$param = array(
				'student_code'=>$data[$i]['B'],
				'stu_khname'=>$data[$i]['C']
			);

			$studentId = $this->checkStudentIdExist($param);

			if (!empty($studentId)) {
				if ($data[$i]['V'] == 'quit') {
					$array=array(
						'stop_type'=>1,
						'note'=>'version3'
					);
					$where="stu_id=".$studentId." AND academic_year=2";
					$this->_name = 'rms_group_detail_student';
					$this->update($array,$where);
				}
			}

			// 	$arr = array(
			// 		'branch_id' => $formData['branch'],
			// 		'user_id' => 1,
			// 		'stu_code' => $data[$i]['B'],
			// 		'stu_khname' => $data[$i]['C'],
			// 		'stu_enname' => trim($data[$i]['D']),
			// 		'last_name' => trim($data[$i]['E']),
			// 		// 'sex' => ($data[$i]['F'] == "M") ? 1 : 2,
			// 		'tel' => $data[$i]['G'],
			// 		'dob' => date("Y-m-d", strtotime($data[$i]['H'])),
			// 		'pob' => $data[$i]['I'],

			// 		'father_enname' => $data[$i]['J'],
			// 		'father_khname' => $data[$i]['J'],
			// 		'father_phone' => $data[$i]['K'],

			// 		'mother_khname' => $data[$i]['L'],
			// 		'mother_enname' => $data[$i]['L'],
			// 		'mother_phone' => $data[$i]['M'],

			// 		'guardian_first_name' => $data[$i]['N'],
			// 		'guardian_enname' => $data[$i]['N'],
			// 		'guardian_khname' => $data[$i]['N'],
			// 		'guardian_tel' => $data[$i]['O'],
			// 		'remark' => 'version2',
			// 		'customer_type' => 1,
			// 		'status' => 1,
			// 		'modify_date' => date("Y-m-d H:i:s"),
			// 		'create_date' => date("Y-m-d", strtotime($data[$i]['S']))
			// 	);
			// 	if (!empty($data[$i]['F'])) {
			// 		$arr['sex'] = ($data[$i]['F'] == "M") ? 1 : 2;
			// 	}
			// 	$this->_name = 'rms_student';
			// 	$studentId = $this->insert($arr);
			}

			// $degreeId = $this->degreeList($data[$i]['P']);
			// $gradeId =$data[$i]['Q'];

			// $param = array(
			// 	'room_name'=>$data[$i]['W']
			// );
			// $roomId = $this->getRoomId($param);

			// $param = array(
			// 		'branch_id'=>$formData['branch'],
			// 		'group_code'=>$data[$i]['R'],
			// 		'room_id'=>$roomId,
			// 		'academic_year'=>2,
			// 		'degree'=>$degreeId,
			// 		'grade'=>$gradeId,
			// 		'is_pass'=>1,
			// 		'is_use'=>1,
			// 		'tuitionfee_id'=>2,
			// 		'school_option'=>1,
			// 		'note'=>'version2',

			// );
			// $groupResult = $this->checkGroupExits($param);
			// if (!empty($groupResult)) {
			// 	$groupId = $groupResult['id'];
			// }else{
			// 	$groupId = $this->insertGroup($param);
			// }

			// $this->_name='rms_group_detail_student';
			// $arr = array(
			// 		'branch_id'		=>$formData['branch'],
			// 		'studentId'		=>$studentId,//$_data['stu_id_'.$k],
			// 		'itemType'		=>1,
			// 		'groupId'		=>$groupId,
			// 		'academicYear'	=>2,
			// 		'feeId'			=>2,//feeId
			// 		'oldFeeId'		=>1,//Old Fee Id
			// 		'schoolOption'  =>1,
			// 		'degree'		=>$degreeId,
			// 		'grade'			=>$gradeId,
			// 		'session'		=>'',
			// 		'startDate'		=>'',
			// 		'endDate'		=>'',
			// 		'balance'		=>0,
			// 		'discountType'	=>'',
			// 		'discountAmount'=>'',
			// 		'user_id'		=>$this->getUserId(),
			// 		'status'		=>1,
			// 		'create_date'	=>date('Y-m-d H:i:s'),
			// 		'modify_date'	=>date('Y-m-d H:i:s'),
			// 		'old_group'		=>'',
			// 		'isSetGroup'	=>empty($groupId)?0:1,
			// 		'stopType'		=>0,
			// 		'isCurrent'		=>1,
			// 		'isNewStudent'	=>0,
			// 		'isMaingrade'	=>1,//not sure
			// 		'entryFrom'	=>4,//not sure
			// 		'remark'	=>'version2'
			// );
			// if($data[$i]['V']=='quit'){
			// 	$arr['stopType'] = 1;
			// }
			// $dbg->AddItemToGroupDetailStudent($arr);
    	// }
    }
	function getRoomId($data){
		$sql = "SELECT room_id FROM rms_room WHERE 1 ";
		
		if(!empty($data['room_name'])){
			$sql.=" AND room_name='".$data['room_name']."'";
		}
		$db = $this->getAdapter();
		$roomId = $db->fetchOne($sql);
		if(empty($result)){
			$this->_name='rms_room';
			$arr = array(
				'branch_id'=>1,
				'room_name'=>$data['room_name']
			);
			return $this->insert($arr);

		}else{
			return $roomId;
		}
	}
	function checkStudentIdExist($data)
	{
		$sql = "SELECT stu_id From rms_student where 1";
		if(!empty($data['student_code'])){
			$sql .= " AND stu_code='" . $data['student_code']."'";
		}
		if(!empty($data['stu_khname'])){
			$sql .= " AND stu_khname='" . $data['stu_khname']."'";
		}
		return $this->getAdapter()->fetchOne($sql);
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
	function importProduct($formData,$data)
	{
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$param = array(
				'title'=>$data[$i]['D'],
				'title_en'=>$data[$i]['D'],
				'schoolOption'=>'1',

			);
			$categoryId = $this->checkProductCategory($param);

				$array = array(
					'items_id' => $categoryId,
					'code' => $data[$i]['B'],
					'title' => $data[$i]['C'],
					'title_en' => $data[$i]['C'],
					'product_type' => 1,
					'items_type' => 3,//product
					'schoolOption' => '1',
					'is_onepayment' => 1,
					'degreeOption' => '1,2,3,4',
				);

				$this->_name='rms_itemsdetail';
				$proId = $this->insert($array);

				$arr = array(
					'pro_id'=>$proId,
					'branch_id'=>$data['branch_id'],
					'pro_qty'=>0,
					'costing'=>$data[$i]['E'],
					'price'=>$data[$i]['F'],
					'price_set'=>$data[$i]['G'],
				);
				$this->_name='rms_product_location';
			$this->insert($arr);
		}
	}
	function checkProductCategory($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_items AS i
			WHERE 1 ";
		
		if (!empty($data['title'])){
			$sql.=" AND i.title= '".$data['title']."'";
		}
		if (!empty($data['title_en'])){
			$sql.=" AND i.title_en = '".$data['title_en']."'";
		}
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if (empty($itemId)) {
			$arr = array(
				'title'=>$data['title'],
				'title_en'=>$data['title_en'],
				'schoolOption'=>$data['schoolOption'],
				'type'=>3,
			);
			$this->_name = 'rms_items';
			return $this->insert($arr);
		} else {
			return $itemId;
		}
	}
	
	
	function checkFamilyInfo($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_family AS i
			WHERE 1 ";
		$sql.=" AND i.fatherNameKh= '".$data['fatherNameKh']."'";
		$sql.=" AND i.fatherPhone = '".$data['fatherPhone']."'";
		$sql.=" AND i.motherNameKh = '".$data['motherNameKh']."'";
		$sql.=" AND i.motherPhone = '".$data['motherPhone']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		return $itemId;
	}
	function importFamily($formData,$data)
	{
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$fatherPhone = empty($data[$i]['E']) ? "" : $data[$i]['E'];
			$fatherPhone = empty($data[$i]['F']) ? $fatherPhone : $fatherPhone." / ".$data[$i]['F'];
			
			$motherPhone = empty($data[$i]['I']) ? "" : $data[$i]['I'];
			$motherPhone = empty($data[$i]['J']) ? $motherPhone : $motherPhone." / ".$data[$i]['J'];
		
			
			$param = array(
				'familyType'	=>$data[$i]['P'],
				'laonNumber'	=>$data[$i]['O'],
				'familyCode'	=>$data[$i]['B'],
				
				'fatherName'	=>$data[$i]['C'],
				'fatherNameKh'	=>$data[$i]['D'],
				'fatherPhone'	=>$fatherPhone,
				'fatherNation'	=>1,
				'fatherJob'		=>0,
				
				'motherName'	=>$data[$i]['G'],
				'motherNameKh'	=>$data[$i]['H'],
				'motherPhone'	=>$motherPhone,
				'motherNation'	=>1,
				'motherJob'		=>0,
				
				'street'		=>$data[$i]['M'],
				'houseNo'		=>$data[$i]['N'],
				'villageId'		=>0,
				'communeId'		=>0,
				'districtId'	=>0,
				'provinceId'	=>12,
				'note'	=>$data[$i]['R'],
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
				'userId'		=>1,

			);
			$familyId = $this->checkFamilyInfo($param);
			if(empty($familyId)){
				$this->_name = 'rms_family';
				$this->insert($param);
			}
		}
	}
}   

