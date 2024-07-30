<?php

class Foundation_Model_DbTable_DbFamily extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_family';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	function getFamilyCode($data=array()){
		$db=$this->getAdapter();
		$sql="select count(id) FROM rms_family ";
		$result = $db->fetchOne($sql);
		$code='F';
		$date = new DateTime();
		$fullDate = $date->format('Ymd');
		$code .= $fullDate;
		$new_acc = $result + 1 ;
		$length = strlen((int)$new_acc);
		for($i=$length;$i<4;$i++){
			$code .= "0";
		}
		return $code.$new_acc;
	}
	public function addNewFamily($_data){
		$_db= $this->getAdapter();		
		$_db->beginTransaction();
			try{
				$familyCode = $this->getFamilyCode();
				$_arr= array(
				
						'familyCode'	=>$familyCode,
						'familyType'	=>$_data['familyType'],
						'laonNumber'	=>$_data['laonNumber'],
						
						'fatherName'	=>$_data['fatherName'],
						'fatherNameKh'	=>$_data['fatherNameKh'],
						'fatherPhone'	=>$_data['fatherPhone'],
						'fatherDob'		=>empty($_data['fatherDob']) ? "" : $_data['fatherDob'],
						'fatherNation'	=>$_data['fatherNation'],
						'fatherJob'		=>$_data['fatherJob'],
						
						'motherName'	=>$_data['motherName'],
						'motherNameKh'	=>$_data['motherNameKh'],
						'motherPhone'	=>$_data['motherPhone'],
						'motherDob'		=>empty($_data['motherDob']) ? "" : $_data['motherDob'],
						'motherNation'	=>$_data['motherNation'],
						'motherJob'		=>$_data['motherJob'],

						'guardianName'	=>$_data['guardianName'],
						'guardianNameKh'=>$_data['guardianNameKh'],
						'guardianPhone'	=>$_data['guardianPhone'],
						'guardianDob'	=>empty($_data['guardianDob']) ? "" : $_data['guardianDob'],
						'guardianNation'	=>$_data['guardianNation'],
						'guardianJob'	=>$_data['guardianJob'],
						
						'street'		=>$_data['street'],
						'houseNo'		=>$_data['houseNo'],
						'villageId'		=>$_data['villageId'],
						'communeId'		=>$_data['communeId'],
						'districtId'	=>$_data['districtId'],
						'provinceId'	=>$_data['provinceId'],
						'status'		=>1,
						'createDate'	=>date("Y-m-d H:i:s"),
						'modifyDate'	=>date("Y-m-d H:i:s"),
						'userId'		=>$this->getUserId(),
				);
				
				if(empty($_data['fromCompare'])){
					$part= PUBLIC_PATH.'/images/photo/';
					if (!file_exists($part)) {
						mkdir($part, 0777, true);
					}
					$dbg = new Application_Model_DbTable_DbGlobal();
					$name = $_FILES['fatherPhoto']['name'];
					if (!empty($name)){
						$tem =explode(".", $name);
						$newImageName = "father".$familyCode.time().".".end($tem);
						$photopj = $dbg->resizeImase($_FILES['fatherPhoto'], $part,$newImageName);
						$_arr['fatherPhoto']=$photopj;
					}
					
					$motherPhoto = $_FILES['motherPhoto']['name'];
					if (!empty($motherPhoto)){
						$tem =explode(".", $motherPhoto);
						$newImageName = "mother".$familyCode.time().".".end($tem);
						$photoM = $dbg->resizeImase($_FILES['motherPhoto'], $part,$newImageName);
						$_arr['motherPhoto']=$photoM;
					}
					
					$guardianPhoto = $_FILES['guardianPhoto']['name'];
					if (!empty($guardianPhoto)){
						$tem =explode(".", $guardianPhoto);
						$newImageName = "guardian".$familyCode.time().".".end($tem);
						$photoG = $dbg->resizeImase($_FILES['guardianPhoto'], $part,$newImageName);
						$_arr['guardianPhoto']=$photoG;
					}
				}else{
					$_arr['fatherPhoto']=$_data['fatherPhoto'];
					$_arr['motherPhoto']=$_data['motherPhoto'];
					$_arr['guardianPhoto']=$_data['guardianPhoto'];
				}
				$this->_name = 'rms_family';
				$familyId = $this->insert($_arr);
					
				$_db->commit();
				return $familyId;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$_db->rollBack();
	    		
	    	}
	}
	public function updateFamily($_data){
		$_db= $this->getAdapter();		
		$_db->beginTransaction();
		try{	
			$familyCode = $_data['familyCode'];
			$status = empty($_data['status']) ? 0 : 1;
			$_arr= array(
					'familyType'	=>$_data['familyType'],
					'laonNumber'	=>$_data['laonNumber'],
					
					'fatherName'	=>$_data['fatherName'],
					'fatherNameKh'	=>$_data['fatherNameKh'],
					'fatherPhone'	=>$_data['fatherPhone'],
					'fatherDob'		=>empty($_data['fatherDob']) ? "" : $_data['fatherDob'],
					'fatherNation'	=>$_data['fatherNation'],
					'fatherJob'		=>$_data['fatherJob'],
					
					'motherName'	=>$_data['motherName'],
					'motherNameKh'	=>$_data['motherNameKh'],
					'motherPhone'	=>$_data['motherPhone'],
					'motherDob'		=>empty($_data['motherDob']) ? "" : $_data['motherDob'],
					'motherNation'	=>$_data['motherNation'],
					'motherJob'		=>$_data['motherJob'],

					'guardianName'	=>$_data['guardianName'],
					'guardianNameKh'=>$_data['guardianNameKh'],
					'guardianPhone'	=>$_data['guardianPhone'],
					'guardianDob'	=>empty($_data['guardianDob']) ? "" : $_data['guardianDob'],
					'guardianNation'	=>$_data['guardianNation'],
					'guardianJob'	=>$_data['guardianJob'],
					
					'street'		=>$_data['street'],
					'houseNo'		=>$_data['houseNo'],
					'villageId'		=>$_data['villageId'],
					'communeId'		=>$_data['communeId'],
					'districtId'	=>$_data['districtId'],
					'provinceId'	=>$_data['provinceId'],
					'status'		=>$status,
					'modifyDate'	=>date("Y-m-d H:i:s"),
					'userId'		=>$this->getUserId(),
			);
			
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$name = $_FILES['fatherPhoto']['name'];
			if (!empty($name)){
				$tem =explode(".", $name);
				$newImageName = "father".$familyCode.time().".".end($tem);
				$photopj = $dbg->resizeImase($_FILES['fatherPhoto'], $part,$newImageName);
				$_arr['fatherPhoto']=$photopj;
			}
			
			$motherPhoto = $_FILES['motherPhoto']['name'];
			if (!empty($motherPhoto)){
				$tem =explode(".", $motherPhoto);
				$newImageName = "mother".$familyCode.time().".".end($tem);
				$photoM = $dbg->resizeImase($_FILES['motherPhoto'], $part,$newImageName);
				$_arr['motherPhoto']=$photoM;
			}
			
			$guardianPhoto = $_FILES['guardianPhoto']['name'];
			if (!empty($guardianPhoto)){
				$tem =explode(".", $guardianPhoto);
				$newImageName = "guardian".$familyCode.time().".".end($tem);
				$photoG = $dbg->resizeImase($_FILES['guardianPhoto'], $part,$newImageName);
				$_arr['guardianPhoto']=$photoG;
			}
			$this->_name = 'rms_family';
			$id = empty($_data['id']) ? 0 : $_data['id'];
			$where = 'id = '.$id;
			$this->update($_arr, $where);	
			$_db->commit();
		}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
    	}
	}
	public function getFamilyById($id){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		$sql = "
			SELECT 
				t.*
			FROM rms_family AS t WHERE t.id =$id 
			";
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	
	function getAllFamily($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				f.id
				,f.`familyCode`
				,f.`fatherNameKh`
				,f.`fatherPhone`
				,f.`motherNameKh`
				,(SELECT v.shortcut FROM `rms_view` AS v WHERE v.key_code = f.`familyType` AND v.type=41 LIMIT 1) AS familyTypeTitle
				,f.`laonNumber`
				,f.`houseNo`
				,f.`createDate`
				,(SELECT u.first_name FROM rms_users AS u WHERE u.id= f.`userId` LIMIT 1 ) AS userName
			
		";
		$sql.=$dbp->caseStatusShowImage("f.`status`");
		
		
		$sql.="
		FROM `rms_family` AS f 
			WHERE 1
		";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(f.`laonNumber`,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`familyCode`,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`fatherNameKh`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`fatherName`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`fatherPhone`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`motherName`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`motherNameKh`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`motherPhone`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`guardianName`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`guardianNameKh`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`guardianPhone`,' ','')  	LIKE '%{$s_search}%'";
				
			$s_where[]=" REPLACE(f.`street`,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(f.`houseNo`,' ','')  	LIKE '%{$s_search}%'";
			
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['familyType'])){
			$sql.=" AND COALESCE(f.familyType,0) = ".$search['familyType'];
		}
		if(!empty($search['provinceId'])){
			$sql.=" AND COALESCE(f.provinceId,0) = ".$search['provinceId'];
		}
		if(!empty($search['districtId'])){
			$sql.=" AND COALESCE(f.districtId,0) = ".$search['districtId'];
		}
		if($search['status']>-1){
			$sql.=" AND f.status=".$search['status'];
		}
		$sql.=" ORDER BY f.createDate DESC,f.id DESC ";
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	
	function getDistinctParentFatherKhNameStudent(){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				DISTINCT s.`father_khname`
				,s.*
			FROM `rms_student` AS s 
			WHERE s.`father_khname` IS NOT NULL AND s.`father_khname` !=''
			AND COALESCE(s.`familyId`,0) = 0
			GROUP BY s.`father_khname`
		";
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	function getDistinctParentFatherEnNameStudent(){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				DISTINCT s.`father_enname`
				,s.*
			FROM `rms_student` AS s 
			WHERE s.`father_enname` IS NOT NULL AND s.`father_enname` !='' AND (s.`father_khname` = '' OR s.`father_khname` IS NULL)
			AND COALESCE(s.`familyId`,0) = 0
			GROUP BY s.`father_enname`
		";
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	function getDistinctParentGuardianStudent(){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				DISTINCT s.`guardian_khname`
				,s.*
			FROM `rms_student` AS s 
			WHERE s.`guardian_khname` IS NOT NULL AND s.`guardian_khname` !='' AND (s.`father_khname` = '' OR s.`father_khname` IS NULL) AND (s.`father_enname` = '' OR s.`father_enname` IS NULL)
			AND COALESCE(s.`familyId`,0) = 0
			GROUP BY s.`guardian_khname`
		";
		$rs = $db->fetchAll($sql);
		return $rs;
	}
	
	function checkFatherHasInFamilyId($fatherNameKh){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				s.*
			FROM `rms_family` AS s 
			WHERE 1
			AND s.fatherNameKh = '$fatherNameKh' 
			LIMIT 1
		";
		$rs = $db->fetchRow($sql);
		return $rs;
	}
	
	function updateFamilyIdInStudentOldData(){
		$rs = $this->getDistinctParentFatherKhNameStudent();
		if(!empty($rs)){
			foreach($rs as $row){
				
				$check = $this->checkFatherHasInFamilyId($row["father_khname"]);
				if(!empty($check)){
					$_arr= array(
						'familyId'	=>$check['id'],
					);
					$this->_name = "rms_student";
					$where = " father_khname = '".$row["father_khname"]."'";
					$this->update($_arr, $where);	
				}else{
					
					$_arr= array(
						'familyType'	=>0,
						'laonNumber'	=>"",
						
						'fatherName'	=>$row['father_enname'],
						'fatherNameKh'	=>$row['father_khname'],
						'fatherPhone'	=>$row['father_phone'],
						'fatherDob'		=>empty($row['father_dob']) ? "" : $row['father_dob'],
						'fatherNation'	=>$row['father_nation'],
						'fatherJob'		=>$row['father_job'],
						'fatherPhoto'	=>$row['father_photo'],
						
						'motherName'	=>$row['mother_enname'],
						'motherNameKh'	=>$row['mother_khname'],
						'motherPhone'	=>$row['mother_phone'],
						'motherDob'		=>empty($row['mother_dob']) ? "" : $row['mother_dob'],
						'motherNation'	=>$row['mother_nation'],
						'motherJob'		=>$row['mother_job'],
						'motherPhoto'	=>$row['mother_photo'],

						'guardianName'	=>$row['guardian_enname'],
						'guardianNameKh'=>$row['guardian_khname'],
						'guardianPhone'	=>$row['guardian_tel'],
						'guardianDob'	=>empty($row['guardian_dob']) ? "" : $row['guardian_dob'],
						'guardianNation'	=>$row['guardian_nation'],
						'guardianJob'	=>$row['guardian_job'],
						'guardianPhoto'	=>$row['guardian_photo'],
						
						'street'		=>$row['street_num'],
						'houseNo'		=>$row['home_num'],
						'villageId'		=>$row['village_name'],
						'communeId'		=>$row['commune_name'],
						'districtId'	=>$row['district_name'],
						'provinceId'	=>$row['province_id'],
						'fromCompare'	=>1,
					);
					$familyId = $this->addNewFamily($_arr);
					
					$_arr= array(
						'familyId'	=>$familyId,
					);
					$this->_name = "rms_student";
					$where = " father_khname = '".$row["father_khname"]."'";
					$this->update($_arr, $where);	
				}
			}
		}
		
		$rsEn = $this->getDistinctParentFatherEnNameStudent();
		if(!empty($rsEn)){
			foreach($rsEn as $row){
				$check = $this->checkFatherHasInFamilyId($row["father_enname"]);
				if(!empty($check)){
					$_arr= array(
						'familyId'	=>$check['id'],
					);
					$this->_name = "rms_student";
					$where = " father_enname = '".$row["father_enname"]."' AND (`father_khname` = '' OR `father_khname` IS NULL)";
					$this->update($_arr, $where);	
				}else{
					
					$_arr= array(
						'familyType'	=>0,
						'laonNumber'	=>"",
						
						'fatherName'	=>$row['father_enname'],
						'fatherNameKh'	=>$row['father_enname'],
						'fatherPhone'	=>$row['father_phone'],
						'fatherDob'		=>empty($row['father_dob']) ? "" : $row['father_dob'],
						'fatherNation'	=>$row['father_nation'],
						'fatherJob'		=>$row['father_job'],
						'fatherPhoto'	=>$row['father_photo'],
						
						'motherName'	=>$row['mother_enname'],
						'motherNameKh'	=>$row['mother_khname'],
						'motherPhone'	=>$row['mother_phone'],
						'motherDob'		=>empty($row['mother_dob']) ? "" : $row['mother_dob'],
						'motherNation'	=>$row['mother_nation'],
						'motherJob'		=>$row['mother_job'],
						'motherPhoto'	=>$row['mother_photo'],

						'guardianName'	=>$row['guardian_enname'],
						'guardianNameKh'=>$row['guardian_khname'],
						'guardianPhone'	=>$row['guardian_tel'],
						'guardianDob'	=>empty($row['guardian_dob']) ? "" : $row['guardian_dob'],
						'guardianNation'	=>$row['guardian_nation'],
						'guardianJob'	=>$row['guardian_job'],
						'guardianPhoto'	=>$row['guardian_photo'],
						
						'street'		=>$row['street_num'],
						'houseNo'		=>$row['home_num'],
						'villageId'		=>$row['village_name'],
						'communeId'		=>$row['commune_name'],
						'districtId'	=>$row['district_name'],
						'provinceId'	=>$row['province_id'],
						'fromCompare'	=>1,
					);
					$familyId = $this->addNewFamily($_arr);
					
					$_arr= array(
						'familyId'	=>$familyId,
					);
					$this->_name = "rms_student";
					$where = " father_enname = '".$row["father_enname"]."' AND (`father_khname` = '' OR `father_khname` IS NULL)";
					$this->update($_arr, $where);	
				}
			}
		}
		
		$rsGuard = $this->getDistinctParentGuardianStudent();
		if(!empty($rsGuard)){
			foreach($rsGuard as $row){
				$check = $this->checkFatherHasInFamilyId($row["guardian_khname"]);
				if(!empty($check)){
					$_arr= array(
						'familyId'	=>$check['id'],
					);
					$this->_name = "rms_student";
					$where = " guardian_khname = '".$row["guardian_khname"]."' AND (`father_khname` = '' OR `father_khname` IS NULL) AND (`father_enname` = '' OR `father_enname` IS NULL)";
					$this->update($_arr, $where);	
				}else{
					
					$_arr= array(
						'familyType'	=>0,
						'laonNumber'	=>"",
						
						'fatherName'	=>$row['guardian_khname'],
						'fatherNameKh'	=>$row['guardian_khname'],
						'fatherPhone'	=>$row['father_phone'],
						'fatherDob'		=>empty($row['father_dob']) ? "" : $row['father_dob'],
						'fatherNation'	=>$row['father_nation'],
						'fatherJob'		=>$row['father_job'],
						'fatherPhoto'	=>$row['father_photo'],
						
						'motherName'	=>$row['mother_enname'],
						'motherNameKh'	=>$row['mother_khname'],
						'motherPhone'	=>$row['mother_phone'],
						'motherDob'		=>empty($row['mother_dob']) ? "" : $row['mother_dob'],
						'motherNation'	=>$row['mother_nation'],
						'motherJob'		=>$row['mother_job'],
						'motherPhoto'	=>$row['mother_photo'],

						'guardianName'	=>$row['guardian_enname'],
						'guardianNameKh'=>$row['guardian_khname'],
						'guardianPhone'	=>$row['guardian_tel'],
						'guardianDob'	=>empty($row['guardian_dob']) ? "" : $row['guardian_dob'],
						'guardianNation'	=>$row['guardian_nation'],
						'guardianJob'	=>$row['guardian_job'],
						'guardianPhoto'	=>$row['guardian_photo'],
						
						'street'		=>$row['street_num'],
						'houseNo'		=>$row['home_num'],
						'villageId'		=>$row['village_name'],
						'communeId'		=>$row['commune_name'],
						'districtId'	=>$row['district_name'],
						'provinceId'	=>$row['province_id'],
						'fromCompare'	=>1,
					);
					$familyId = $this->addNewFamily($_arr);
					
					$_arr= array(
						'familyId'	=>$familyId,
					);
					$this->_name = "rms_student";
					$where = " guardian_khname = '".$row["guardian_khname"]."' AND (`father_khname` = '' OR `father_khname` IS NULL) AND (`father_enname` = '' OR `father_enname` IS NULL)";
					$this->update($_arr, $where);	
				}
			}
		}
	}
	
	
}