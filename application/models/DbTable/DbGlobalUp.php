<?php

class Application_Model_DbTable_DbGlobalUp extends Zend_Db_Table_Abstract
{
	// set name value
	public function setName($name)
	{
		$this->_name = $name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	public function getAllStudentsList($data){
		$db = $this->getAdapter();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$labelShortCut= $_dbgb->getViewLabelDisplay("shortcut");
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql = " 
			SELECT 
				s.stu_id AS id
				,CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,'')) AS `name`
				,stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
				,s.`photo`
				,s.`tel`
				,s.`sex` AS genderId
				
				,(SELECT v.$labelShortCut FROM `rms_view` AS v WHERE v.type = 2 AND v.key_code = s.`sex` LIMIT 1) AS genderTitle
			
		";
		$fromStatment = " FROM rms_student AS s  ";
		$where = " WHERE s.`status`= 1 ";
		if (!empty($data['joinGroup'])) {
			$sql .= "
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id =g.`academic_year` LIMIT 1) AS academicYear
				,COALESCE(g.`group_code`,'N/A') as groupCode
			";
			$sql .= $fromStatment;
			$sql .= ' JOIN rms_group_detail_student AS gds ON s.`stu_id` =  gds.`stu_id` ';
			$sql .= ' LEFT JOIN `rms_group` AS g ON g.`id` =  gds.`group_id` ';
			$sql .= $where;
			if (!empty($data['groupId'])) {
				$sql .= " AND gds.group_id=" . $data['groupId'];
			}
			if (!empty($data['degree'])) {
				$sql .= " AND gds.degree=" . $data['degree'];
			}
			if (!empty($data['itemType'])) {
				$sql .= " AND gds.itemType=" . $data['itemType'];
			}
			if (!empty($data['activStudent'])) {
				$sql .= " AND (gds.stop_type=0 OR gds.stop_type=3 OR gds.stop_type=4) ";
			}
			if (isset($data['isCurrent'])) {
				$sql .= " AND gds.is_current=" . $data['isCurrent'];
			}
			if (isset($data['isMaingrad'])) {
				$sql .= " AND gds.is_maingrade=" . $data['isMaingrad'];
			}
			
		}else{
			$sql .= $fromStatment;
			$sql .= $where;
		}
		if (!empty($data['branchId'])) {
			$sql .= " AND s.branch_id=" . $data['branchId'];
		}
		if (!empty($data['customerType'])) {
			$sql .= " AND s.customer_type=" . $data['customerType'];
		}
		if (!empty($data['photoStatus'])) {
			if($data['photoStatus']==1){
				$sql .= " AND (s.`photo` IS NULL OR s.`photo` ='') ";
			}else if($data['photoStatus']==2){
				$sql .= " AND (s.`photo` IS NOT NULL AND s.`photo` !='') ";
			}
		}
		
		$sql .= $_dbgb->getAccessPermission("s.branch_id");
		
		$sql.= " GROUP BY s.stu_id ";
		$ordering = " ORDER BY stu_code ASC, stu_khname ASC ";
		$search['stuOrderBy'] = empty($search['stuOrderBy']) ? 0 : $search['stuOrderBy'];
		if($search['stuOrderBy'] > 0){
			if($search['stuOrderBy']==1){
				$ordering=" ORDER BY s.stu_code ASC ";
			}elseif($search['stuOrderBy']==2){
				$ordering=" ORDER BY s.stu_khname  ASC ";
			}elseif($search['stuOrderBy']== 3){
				$ordering=" ORDER BY s.stu_enname  ASC, s.last_name ASC ";
			}
    	}
		$sql.=$ordering;
		
		
		$rows = $db->fetchAll($sql);
		
		if (!empty($data['option'])) {
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$initializeImage = $baseUrl.'/images/no-profile.png';
			$options = '<option value=""></option>';
			$options.= '<option value="0">' . $this->tr->translate("PLEASE_SELECT") . '</option>';
			if (!empty($rows))
				foreach ($rows as $value) {
					$photoUrl = $initializeImage;
					if(!empty($value['photo'])){
						if (file_exists(PUBLIC_PATH."/images/photo/".$value['photo'])){
							$photoUrl = $baseUrl.'/images/photo/'.$value['photo'];
						}
						
					}
					$value["photoUrl"] = $photoUrl;
					$options .= '<option data-student-code="aaaaaa"  data-record-info="' . htmlspecialchars(Zend_Json::encode($value)) . '" value="' . $value['id'] . '" >' . htmlspecialchars($value['name']) . '</option>';
				}
			return $options;
		} else {
			return $rows;
		}
	}
	public function getAllStaffsAndTeachersList($data){
		$db = $this->getAdapter();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$labelShortCut= $_dbgb->getViewLabelDisplay("shortcut");
		$labelFull= $_dbgb->getViewLabelDisplay("full");
		
		$currentLang = $_dbgb->currentlang();
		$depCol = 'depart_nameen';
		if ($currentLang == 1) {
			$depCol = 'depart_namekh';
		}
		
		
		$sql = " 
			SELECT 
				t.`id` AS id
				,t.`teacher_name_kh` AS `name`
				,t.`teacher_code` AS staffCode
				,t.`teacher_name_kh` AS staffNameKh
				,t.`teacher_name_en` AS staffNameEn
				,t.`photo`
				,t.`tel`
				,t.`sex` AS genderId
				,(SELECT v.$labelFull FROM rms_view AS v WHERE v.type=24 AND v.key_code=t.teacher_type LIMIT 1) AS staffTypeTitle
				,(SELECT v.$labelShortCut FROM `rms_view` AS v WHERE v.type = 2 AND v.key_code = t.`sex` LIMIT 1) AS genderTitle
				,(SELECT dep.$depCol FROM `rms_department` AS dep WHERE dep.depart_id = t.`department` LIMIT 1) AS departmentTitle
			
		";
		$fromStatment = " FROM `rms_teacher` AS t  ";
		$where = " WHERE t.`status`=1 AND t.teacher_name_kh != '' AND t.active_type=0 ";
		$staffType = empty($data["staffType"]) ? 1 : $data["staffType"];
		$groupBY="";
		if($staffType==1){
			if (!empty($data["groupId"])) {
				$sql .= $fromStatment;
				$sql .= " JOIN rms_group_subject_detail AS gsd ON gsd.teacher = t.id ";
				$sql .= $where;
				$sql .= " AND gsd.group_id =" . $data["groupId"];
				$groupBY = " GROUP BY gsd.teacher ";
			}else{
				$sql .= $fromStatment;
				$sql .= $where;
			}
		}else{
			$sql .= $fromStatment;
			$sql .= $where;
		}
		$sql .= " AND t.staff_type = ".$staffType;
		if (!empty($data["department"])) {
			$sql .= " AND t.department = " . $data["department"];
		}
		if (!empty($data['photoStatus'])) {
			if($data['photoStatus']==1){
				$sql .= " AND (t.`photo` IS NULL OR t.`photo` ='') ";
			}else if($data['photoStatus']==2){
				$sql .= " AND (t.`photo` IS NOT NULL AND t.`photo` !='') ";
			}
		}
		$sql .= $_dbgb->getAccessPermission("t.branch_id");
		$sql .= $groupBY;
		
		
		$rows = $db->fetchAll($sql);
		if (!empty($data['option'])) {
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$initializeImage = $baseUrl.'/images/no-profile.png';
			$options = '<option value=""></option>';
			$options.= '<option value="0">' . $this->tr->translate("PLEASE_SELECT") . '</option>';
			if (!empty($rows))
				foreach ($rows as $value) {
					$photoUrl = $initializeImage;
					if(!empty($value['photo'])){
						if (file_exists(PUBLIC_PATH."/images/photo/".$value['photo'])){
							$photoUrl = $baseUrl."/images/photo/".$value['photo'];
						}
						
					}
					$value["photoUrl"] = $photoUrl;
					$options .= '<option  data-record-info="' . htmlspecialchars(Zend_Json::encode($value)) . '"  value="' . $value['id'] . '" >' . htmlspecialchars($value['name']) . '</option>';
				}
			return $options;
		} else {
			return $rows;
		}
	}
	
	public function getAllFamilyList($data){
		$db = $this->getAdapter();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$labelShortCut= $_dbgb->getViewLabelDisplay("shortcut");
		$labelFull= $_dbgb->getViewLabelDisplay("full");
		
		$currentLang = $_dbgb->currentlang();
		$provinceCol = 'province_en_name';
		$districtCol = 'district_name';
		if ($currentLang == 1) {
			$provinceCol = 'province_kh_name';
			$districtCol = 'district_namekh';
		}
		$recordType = empty($data["recordType"]) ? 2 : $data["recordType"];
		
		$concate=" 
			CASE 
				WHEN (fam.fatherNameKh IS NULL OR fam.fatherNameKh ='') AND (fam.motherNameKh IS NOT NULL OR fam.motherNameKh !='') 
					THEN  CONCAT(fam.`familyCode`,' ',COALESCE(fam.motherNameKh,''))
				WHEN (fam.motherNameKh IS NULL OR fam.motherNameKh ='') AND (fam.fatherNameKh IS NOT NULL OR fam.fatherNameKh !='' )
					THEN  CONCAT(fam.`familyCode`,' ',COALESCE(fam.fatherNameKh,''))
				ELSE CONCAT(fam.`familyCode`,' ',COALESCE(fam.fatherNameKh,''),' / ',COALESCE(fam.motherNameKh,''))
			END 
		";
		$familyConcat = "fatherNameKh";
		$familyConcatEn = "fatherName";
		$familyPhone = "fatherPhone";
		$photoColumn = "fatherPhoto";
		$whereFamily=" ";
		if($recordType==2){
			$familyConcat = "fatherNameKh";
			$familyConcatEn = "fatherName";
			$familyPhone = "fatherPhone";
			$photoColumn = "fatherPhoto";
			$whereFamily=" AND fam.`fatherNameKh` !='' ";
			$concate=" CONCAT(fam.`familyCode`,' ',COALESCE(fam.fatherNameKh,''))";
		}else if($recordType==3){
			$familyConcat = "motherNameKh";
			$familyConcatEn = "motherName";
			$familyPhone = "motherPhone";
			$photoColumn = "motherPhoto";
			$whereFamily=" AND fam.`motherNameKh` !='' ";
			$concate=" CONCAT(fam.`familyCode`,' ',COALESCE(fam.motherNameKh,''))";
		}else if($recordType==4){
			$familyConcat = "guardianNameKh";
			$familyConcatEn = "guardianName";
			$familyPhone = "guardianPhone";
			$photoColumn = "guardianPhoto";
			$whereFamily=" AND fam.`guardianNameKh` !='' ";
			$concate=" CONCAT(fam.`familyCode`,' ',COALESCE(fam.guardianNameKh,''))";
		}
		
		$sql = " 
			SELECT 
				fam.`id`
				,$concate AS `name`
				,fam.$familyConcat AS familyName
				,fam.$familyConcatEn AS familyNameEn
				,fam.$familyPhone AS familyPhone
				,fam.$photoColumn AS photo
				,fam.`familyCode`
				,fam.`fatherName`
				,fam.`fatherNameKh`
				,fam.`fatherPhone`
				,fam.`fatherPhoto`
				,fam.`motherName`
				,fam.`motherNameKh`
				,fam.`motherPhone`
				,fam.`motherPhoto`
				,fam.`guardianName`
				,fam.`guardianNameKh`
				,fam.`guardianPhone`
				,fam.`guardianPhoto`
				,fam.`laonNumber`
				,fam.`familyType`
				,fam.`provinceId`
				,fam.`districtId`
				,fam.`communeId`
				,fam.`villageId`
				
				,COALESCE((SELECT v.$labelShortCut FROM `rms_view` AS v WHERE v.type =41 AND v.key_code = fam.`familyType` LIMIT 1),'N/A') AS familyTypeTitle
				,COALESCE(fam.`houseNo`,'N/A') AS houseNo
				,COALESCE(fam.`street`,'N/A') AS street
				,COALESCE((SELECT p.`province_en_name` FROM `rms_province` AS p WHERE p.`province_id` = fam.`provinceId` LIMIT 1),'N/A') AS provinceName
				,COALESCE((SELECT d.$districtCol FROM `ln_district` AS d WHERE d.`dis_id` = fam.`districtId` LIMIT 1),'N/A') AS districtName
			
		";
		$fromStatment = " FROM `rms_family` AS fam   ";
		$where = " WHERE fam.`status` = 1 ";
		$sql .= $fromStatment;
		$sql .= $where;
		$sql .= $whereFamily;
		
		if (!empty($data['photoStatus'])) {
			if($data['photoStatus']==1){
				$sql .= " AND (fam.$photoColumn IS NULL OR fam.$photoColumn ='') ";
			}else if($data['photoStatus']==2){
				$sql .= " AND (fam.$photoColumn IS NOT NULL AND fam.$photoColumn !='') ";
			}
		}
		
		$rows = $db->fetchAll($sql);
		if (!empty($data['option'])) {
			
			$data['formType'] = empty($data['formType']) ? "" : $data['formType'];
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$initializeImage = $baseUrl.'/images/no-profile.png';
			$options = '<option value=""></option>';
			
			$options.= '<option value="0">' . $this->tr->translate("PLEASE_SELECT") . '</option>';
			if($data['formType']=="input"){
				$dbUser = new Application_Model_DbTable_DbUsers();
				$checkPermission = $dbUser->getAccessUrl("foundation","family","add");
				if(!empty($checkPermission)){
					$options.= '<option value="-1">' . $this->tr->translate("ADD_NEW") . '</option>';
				}
			}
			if (!empty($rows))
				foreach ($rows as $value) {
					$photoUrl = $initializeImage;
					if(!empty($value['photo'])){
						if (file_exists(PUBLIC_PATH."/images/photo/".$value['photo'])){
							$photoUrl = $baseUrl."/images/photo/".$value['photo'];
						}
						
					}
					$value["photoUrl"] = $photoUrl;
					$options .= '<option  data-record-info="' . htmlspecialchars(Zend_Json::encode($value)) . '"  value="' . $value['id'] . '" >' . htmlspecialchars($value['name']) . '</option>';
				}
			return $options;
		} else {
			return $rows;
		}
	}
	

}
?>