<?php
class Accounting_Model_DbTable_DbDiscountSetting extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_dis_setting';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllDiscountset($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch= $dbp->getBranchDisplay();
		
		$currentLang = $dbp->currentlang();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";

		$sqlPeriod = "(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=39 AND v.key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=37 AND v.key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT 
					ds.id 
					,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=ds.branchId LIMIT 1) AS branch
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM rms_academicyear AS ac WHERE ac.id=ds.academicYear LIMIT 1) as academicYear
					,ds.discountCode
					,ds.discountTitle
					,$sqlDiscountFor AS discountForText
					,(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=38 AND v.key_code=ds.discountForType LIMIT 1) AS discountForOption
					,(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,ds.degree)) as degreeList
					,(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName
					,CONCAT(ds.discountValue, (CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType
					,CONCAT(COALESCE($sqlPeriod),'',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),'/',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) AS discountPeriod
					,(SELECT u.first_name FROM rms_users AS u WHERE u.id=ds.userId LIMIT 1 ) AS user_name
					,ds.createDate
				";

		$sql .= $dbp->caseStatusShowImage("ds.status");
		$sql .= " FROM rms_dis_setting AS ds ";

		$order = " ORDER BY ds.id DESC ";
		$where = " WHERE 1";

		if (!empty($search['title'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " ds.discountCode LIKE '%{$s_search}%'";
			$s_where[] = " ds.discountTitle LIKE '%{$s_search}%'";
			$s_where[] = " ds.discountValue LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (($search['academic_year']) > 0) {
			$where .= ' AND ds.academicYear=' . $search['academic_year'];
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND ds.branchId=' . $search['branch_id'];
		}
		if (!empty($search['studentId'])) {
			$where .= ' AND ds.studentId=' . $search['studentId'];
		}
		if (!empty($search['discountId'])) {
			$where .= ' AND ds.discountId=' . $search['discountId'];
		}
		if (!empty($search['discountFor'])) {
			$where .= ' AND ds.discountFor=' . $search['discountFor'];
		}
		if (!empty($search['discountPeriod'])) {
			$where .= ' AND ds.discountPeriod=' . $search['discountPeriod'];
		}
		if ($search['status_search'] > -1) {
			$where .= ' AND ds.status=' . $search['status_search'];
		}
		$where .= $dbp->getAccessPermission('ds.branchId');
		return $db->fetchAll($sql . $where . $order);
	}
	public function addNewDiscountset($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
		
			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ 
					$dept = $dept.",".$rs;
	    		}
	    	}

			$_arr = array(
				'branchId' => $_data['branch_id'],
				'academicYear' => $_data['academic_year'],
				'discountTitle' => $_data['discountTitle'],
				'discountCode' => $_data['discountCode'],
				'discountFor' => $_data['discountFor'],
				// 'studentId' => $_data['studentId'],
				'discountForType' => $_data['discountforType'],
				'degree' => $dept,
				'discountId' => $_data['discount_id'],
				'DisValueType' => $_data['DisValueType'],
				'discountValue' => $_data['discountValue'],
				'discountPeriod' => $_data['discountPeriod'],
				'startDate' => $_data['start_date'],
				'endDate' => $_data['end_date'],
				'createDate' => Zend_Date::now(),
				'modifyDate' => Zend_Date::now(),
				'status' => 1,
				'userId' => $this->getUserId()
			);
			$id=$this->insert($_arr);
			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	public function addNewDiscountPopup($_data)
	{
		$_arr = array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status' => $_data['status_j'],
			'user_id' => $this->getUserId()
		);
		$this->_name = "rms_discount";
		return $this->insert($_arr);
	}
	public function getDiscountsetById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dis_setting WHERE id=" . $db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branch_id');
		$sql .= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function updateDiscountset($_data)
	{
		$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ 
					$dept = $dept.",".$rs;
	    		}
	    	}

		$_arr = array(
			'branchId' => $_data['branch_id'],
			'academicYear' => $_data['academic_year'],
			'discountTitle' => $_data['discountTitle'],
			'discountCode' => $_data['discountCode'],
			'discountFor' => $_data['discountFor'],
			// 'studentId' => $_data['studentId'],
			'discountForType' => $_data['discountforType'],
			'degree' => $dept,
			'discountId' => $_data['discount_id'],
			'DisValueType' => $_data['DisValueType'],
			'discountValue' => $_data['discountValue'],
			'discountPeriod' => $_data['discountPeriod'],
			'startDate' => $_data['start_date'],
			'endDate' => $_data['end_date'],
			'createDate' => Zend_Date::now(),
			'modifyDate' => Zend_Date::now(),
			'status' => $_data['status'],
			'userId' => $this->getUserId()
		);
		$where = $this->getAdapter()->quoteInto("id=?", $_data["id"]);
		$this->update($_arr, $where);
		
	}

	public function addDiscounttionset($_data)
	{//ajax
		$_arr = array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status' => 1,
			'user_id' => $this->getUserId()
		);
		return $this->insert($_arr);
	}

	function getStudentDiscount($data){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$discountGroupId = empty($data["discountGroupId"]) ? 0 : $data["discountGroupId"];
		$sql = "
			SELECT 
			    s.stu_id,
				s.stu_code AS stu_code
				,s.stu_khname AS stuNameKH
				,CONCAT(s.last_name,' ' ,s.stu_enname) AS stuNameLatin
				,CONCAT(s.stu_khname,'- ',s.last_name,' ' ,s.stu_enname) AS stu_name
				,s.sex AS sex
				
			FROM  `rms_discount_student` AS ds 
			INNER JOIN rms_student AS s ON ds.studentId = s.stu_id
		";
		$sql.=" WHERE ds.isCurrent=1 AND  ds.discountGroupId = $discountGroupId  ";
		$sql.=" GROUP BY s.stu_id ";
		return $db->fetchAll($sql);
	}
	
	public function getDiscountSetting($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
				id AS id,
				CONCAT(discountTitle,' (',(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=academicYear LIMIT 1) ,')' ) AS name
			FROM rms_dis_setting WHERE isCurrent=1 AND status=1  ";
		if (!empty($data['branchId'])) {
			$sql .= ' AND branchId =' . $data['branchId'];
		}
		if (!empty($data['academicYear'])) {
			$sql .= ' AND academicYear =' . $data['academicYear'];
		}
		$sql.=" ORDER BY id DESC ";
		return  $db->fetchAll($sql);
	}
	function getSearchStudent($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				CASE
				    WHEN s.sex =1  THEN 'M'
				    WHEN s.sex =2  THEN 'F'
				    ELSE ''
				END AS sex,
				sd.degree,
				sd.grade,
				sd.feeId AS fee_id,
				sd.academic_year,
				(SELECT `title` FROM `rms_items` WHERE `id`=sd.degree AND TYPE=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=sd.grade AND items_type=1 LIMIT 1) AS grade_title,
				COALESCE((SELECT `group_code` FROM `rms_group` WHERE `id`=sd.group_id  LIMIT 1),'') AS groupCode,
				COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.academicYearEnroll LIMIT 1),'') AS startYear,
				COALESCE((SELECT name_kh FROM `rms_view` WHERE key_code= s.studentType AND TYPE=40  LIMIT 1),'') AS studentType,
				(SELECT name_kh FROM rms_view WHERE TYPE=5 AND key_code=sd.stop_type LIMIT 1) AS status_student,
				sd.stop_type
			  FROM 
			    rms_student AS s,
			  	rms_group_detail_student AS sd
		 	  WHERE 
				sd.itemType=1 
				AND s.stu_id = sd.stu_id
				AND s.`status`=1 
				AND s.customer_type = 1 
				AND sd.stop_type=0
				AND sd.is_pass=0
				AND s.stu_id=sd.stu_id
				AND sd.is_current=1 ";

		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$sql.=" AND sd.academic_year =".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND sd.degree =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND sd.grade =".$search['grade'];
		}
		if(!empty($search['academicYearEnroll'])){
			$sql.=" AND s.academicYearEnroll =".$search['academicYearEnroll'];
		}

		if(!empty($search['studentType'])){
			$sql.=" AND s.studentType =".$search['studentType'];
		}
		$where=" ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.last_name,s.stu_enname) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		$where.=" GROUP BY s.stu_id,sd.degree,sd.grade";
		$where.=" ORDER BY sd.degree,sd.grade,s.stu_id DESC ";
		if(!empty($search['limit'])){
			$where.=" LIMIT  ".$search['limit'];
		}
		return $db->fetchAll($sql.$where);
	}
	function getSearchStudentbyDiscount($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				CASE
				    WHEN s.sex =1  THEN 'M'
				    WHEN s.sex =2  THEN 'F'
				    ELSE ''
				END AS sex,

				( SELECT sd.degree FROM rms_group_detail_student AS sd WHERE sd.stu_id = dc.studentId AND sd.itemType=1  AND sd.is_current=1 AND sd.stop_type=0 AND is_pass =0 limit 1 ) AS degree,
				( SELECT sd.grade FROM rms_group_detail_student AS sd WHERE  sd.stu_id = dc.studentId AND sd.itemType=1  AND sd.is_current=1 AND sd.stop_type=0 AND is_pass =0 limit 1 ) AS grade,

				(SELECT `title` FROM `rms_items` WHERE `id`=dc.degreeId AND TYPE=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=dc.grade AND items_type=1 LIMIT 1) AS grade_title,
				COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.academicYearEnroll LIMIT 1),'') AS startYear,
				COALESCE((SELECT shortcut FROM `rms_view` WHERE key_code= s.studentType AND TYPE=40  LIMIT 1),'') AS studentType,
				(SELECT name_kh FROM rms_view WHERE TYPE=5 AND key_code=dd.stop_type LIMIT 1) AS status_student,
				dd.stop_type
			   FROM 
			  	rms_student AS s INNER JOIN 
			  	rms_discount_student AS dc ON s.`stu_id` = dc.studentId
				INNER JOIN rms_group_detail_student AS dd ON dd.stu_id = dc.studentId 
				
		 	  WHERE s.`status`=1 
			  	 AND dd.itemType=1
				 AND dd.degree= dc.degreeId 
				 AND dd.grade= dc.grade 
				
				AND s.customer_type = 1 
				AND dc.isCurrent=1 
				
			";

		if(!empty($search['discountSettengId'])){
			$sql.=" AND dc.discountGroupId =".$search['discountSettengId'];
		}
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$sql.=" AND dd.academic_year =".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND dc.degreeId =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND dc.grade =".$search['grade'];
		}
		if(!empty($search['academicYearEnroll'])){
			$sql.=" AND s.academicYearEnroll =".$search['academicYearEnroll'];
		}
		if(!empty($search['studentType'])){
			$sql.=" AND s.studentType =".$search['studentType'];
		}
		if(!empty($search['student_status'])){
			if($search['student_status']==1){
				$sql.="  AND dd.stop_type = 0 ";
			}elseif($search['student_status']==2){
				$sql.=" AND dd.is_current=1  AND dd.stop_type > 0 ";
			}
		}
		//AND sd.stop_type=0
		$where="";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.last_name,s.stu_enname) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		$where.=" GROUP BY dc.studentId ";
		$where.=" ORDER BY dc.degreeId,dc.grade ASC ";

		if(!empty($search['limit'])){
			$where.=" LIMIT ".$search['limit'];
		}
		return $db->fetchAll($sql.$where);
	}
	public function updateStudentDiscount($_data){
		$db = $this->getAdapter();
		$dbg = new Application_Model_DbTable_DbGlobal();
	//	print_r($_data);exit();
		try{
			if($_data['type']==2){  // chnage Discount Student
				
				$oldDiscountSettengId = empty($_data['oldDiscountSettengId'])?0:$_data['oldDiscountSettengId'];
				$toDiscountId = empty($_data['toDiscountId'])?0:$_data['toDiscountId'];

				if(!empty($_data['identity'])){
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $k){

						if(!empty($_data['oldDiscountSettengId'])){

							//Update Old Student in old Discount
							$this->_name = 'rms_discount_student';
							$data_gro = array(
									'isCurrent'=> 0,
							);
							$where = ' studentId = '.$_data['stu_id_'.$k]."  AND isCurrent=1 AND discountGroupId = $oldDiscountSettengId ";
							$this->update($data_gro, $where);

							if($_data['discountStatus']==1){  
								
								//Check Exist Student Discount
								$param = array(
									'discountGroupId'=> $toDiscountId,
									'studentId'      => $_data['stu_id_'.$k],
								);
								$existDiscount=$this->IfExistDiscount($param);

								if(empty($existDiscount)){  /// empty Student Discount
									
									if(!empty($_data['toDiscountId'])){
										$arr = array(
											'discountGroupId'=>$toDiscountId,
											'studentId'      =>$_data['stu_id_'.$k],
											'degreeId'       =>$_data['degree_'.$k],
											'grade'          =>$_data['grade_'.$k],
											'isCurrent'		 => 1,
											'oldDiscountId'	 =>$oldDiscountSettengId,
											'createDate'     => date("Y-m-d"),
											'modifyDate'     => date("Y-m-d"),
											'userId'         => $this->getUserId()
										);
										$this->_name ='rms_discount_student';
										$this->insert($arr);
									}
								}else{  /// exist Student Discount

									$this->_name = 'rms_discount_student';
									$data_gro = array(
											'oldDiscountId'	 =>$oldDiscountSettengId,
											'isCurrent'	 => 1,
											'degreeId'   =>$_data['degree_'.$k],
											'grade'      =>$_data['grade_'.$k],
									);
									$where = ' studentId = '.$_data['stu_id_'.$k]."  AND isCurrent=0 AND discountGroupId = $toDiscountId ";
									$this->update($data_gro, $where);
								}	
							}
						}
					}
				}

			}elseif($_data['type']==1){ /// Add Student to Discount

				$toDiscountId = empty($_data['toDiscountId'])?0:$_data['toDiscountId'];
				if(!empty($_data['identity'])){
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $k){

					//Check Exist Student Discount
						$param = array(
							'discountGroupId'=> $toDiscountId,
							'studentId'      => $_data['stu_id_'.$k],
						);
						$existDiscount=$this->IfExistDiscount($param);

						if(empty($existDiscount)){  /// empty Student Discount

							if(!empty($_data['toDiscountId'])){
								$arr = array(
									'discountGroupId'=>$toDiscountId,
									'studentId'      =>$_data['stu_id_'.$k],
									'degreeId'       =>$_data['degree_'.$k],
									'grade'          =>$_data['grade_'.$k],
									'isCurrent'		 => 1,
									'createDate'     => date("Y-m-d"),
									'modifyDate'     => date("Y-m-d"),
									'userId'         => $this->getUserId()
								);
								$this->_name ='rms_discount_student';
								$this->insert($arr);
							}
						}else{  /// exist Student Discount
							$this->_name = 'rms_discount_student';
							$data_gro = array(
								'degreeId'       =>$_data['degree_'.$k],
								'grade'          =>$_data['grade_'.$k],
								'isCurrent'=> 1,
							);
							$where = ' studentId = '.$_data['stu_id_'.$k]."  AND discountGroupId = $toDiscountId ";
							$this->update($data_gro, $where);
						}
					}
				}
			}elseif($_data['type']==3){ // Move Discount

				$oldDiscountSettengId = empty($_data['oldDiscountSettengId'])?0:$_data['oldDiscountSettengId'];
				$toDiscountId = empty($_data['toDiscountId'])?0:$_data['toDiscountId'];

				if(!empty($_data['identity'])){
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $k){

						if(!empty($_data['oldDiscountSettengId'])){

							if($_data['discountStatus']==0){
								//Update Old Student in old Discount
								$this->_name = 'rms_discount_student';
								$data_gro = array(
										'isCurrent'=> 0,
								);
								$where = ' studentId = '.$_data['stu_id_'.$k]."  AND isCurrent=1 AND discountGroupId = $oldDiscountSettengId ";
								$this->update($data_gro, $where);

							}elseif($_data['discountStatus']==1){  

								$this->_name = 'rms_discount_student';
								$where = ' studentId = '.$_data['stu_id_'.$k]."  AND isCurrent=1 AND discountGroupId = $oldDiscountSettengId ";
								$this->delete($where);
								
								//Check Exist Student Discount
								$param = array(
									'discountGroupId'=> $toDiscountId,
									'studentId'      => $_data['stu_id_'.$k],
								);
								$existDiscount=$this->IfExistDiscount($param);

								if(empty($existDiscount)){  /// empty Student Discount
									
									if(!empty($_data['toDiscountId'])){
										$arr = array(
											'discountGroupId'=>$toDiscountId,
											'studentId'      =>$_data['stu_id_'.$k],
											'degreeId'       =>$_data['degree_'.$k],
											'grade'          =>$_data['grade_'.$k],
											'oldDiscountId'	 =>$oldDiscountSettengId,
											'isCurrent'		 => 1,
											'createDate'     => date("Y-m-d"),
											'modifyDate'     => date("Y-m-d"),
											'userId'         => $this->getUserId()
										);
										$this->_name ='rms_discount_student';
										$this->insert($arr);
									}
								}else{  /// exist Student Discount

									$this->_name = 'rms_discount_student';
									$data_gro = array(
											'isCurrent'	 => 1,
											'degreeId'   =>$_data['degree_'.$k],
											'grade'      =>$_data['grade_'.$k],
											'oldDiscountId'	 =>$oldDiscountSettengId,
									);
									$where = ' studentId = '.$_data['stu_id_'.$k]."  AND isCurrent=0 AND discountGroupId = $toDiscountId ";
									$this->update($data_gro, $where);
								}	
							}
						}
					}
				}
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			echo $e->getMessage(); exit();
		}
	}
	function IfExistDiscount($data){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();	
		$discountGroupId = empty($data["discountGroupId"]) ? 0 : $data["discountGroupId"];
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$sql = " SELECT * FROM  `rms_discount_student` ";
		$sql.=" WHERE discountGroupId = $discountGroupId  ";
		$sql.=" AND studentId = $studentId  ";
		
		return $db->fetchRow($sql);
	}
	function getDiscountInforByID($discountId,$string=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";
		$sqlPeriod = "(SELECT $colunmname FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT  ds.id, 
					ds.academicYear as academicYearId,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountTitle,
					(CASE 
						WHEN ds.discountFor=2 THEN $sqlDiscountFor
						WHEN ds.discountFor=1 THEN $strStudent
					END) AS discountForText,
					(SELECT $colunmname FROM `rms_view` WHERE TYPE=38 AND key_code=ds.discountFor LIMIT 1) AS discountForOption,
					(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,degree)) as degreeList,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,	
					CONCAT(COALESCE($sqlPeriod),': ',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),' / ',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) AS discountPeriod, 
					ds.createDate ";

		$sql .= " FROM rms_dis_setting AS ds where  ds.id= ".$discountId;

		$row =  $db->fetchRow($sql);
		if (empty($string)){
			return $row;
		}else{
			$string="";
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if (!empty($row)){
			
				$string='
					<div class="form-group">
						<label class="control-label  col-md-5 col-sm-5 col-xs-12 bold " >
							'.$tr->translate("TITLE").'
						</label>
						<div class="col-md-7 col-sm-7 col-xs-12 text-primary">
							: '.$row['discountTitle'].'				
						</div>
					</div>
					<div class="form-group">
						<label class="control-label  col-md-5 col-sm-5 col-xs-12 bold" >
							'.$tr->translate("STUDY_YEAR").'
						</label>
						<div class="col-md-7 col-sm-7 col-xs-12 text-primary bold">
							: '.$row['academicYear'].'				
						</div>
					</div>	
					<div class="form-group">
						<label class="control-label  col-md-5 col-sm-5 col-xs-12 bold" >
							'.$tr->translate("DISCOUNT_TYPE").'
						</label>
						<div class="col-md-7 col-sm-7 col-xs-12 text-primary bold">
							: '.$row['discName'].'				
						</div>
					</div>	
					<div class="form-group">
						<label class="control-label  col-md-5 col-sm-5 col-xs-12 bold" >
							'.$tr->translate("DIS_MAX").'
						</label>
						<div class="col-md-7 col-sm-7 col-xs-12 text-primary bold">
							: '.$row['DisValueType'].'				
						</div>
					</div>
					<div class="form-group">
						<label class="control-label  col-md-5 col-sm-5 col-xs-12 bold" >
							'.$tr->translate("DISCOUNT_PERIOD").'
						</label>
						<div class="col-md-7 col-sm-7 col-xs-12 text-primary bold">
							: '.$row['discountPeriod'].'				
						</div>
					</div>			
				';
			}
			return $string;
		}
	}
	function getAllStudentDiscount($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch= $dbp-> getBranchDisplay();
		$currentLang = $dbp->currentlang();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		$strStudent = "(SELECT CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,''),'-',COALESCE(s.stu_enname,'')) FROM rms_student AS s WHERE s.stu_id=ds.studentId LIMIT 1) ";

		$sqlPeriod = "(SELECT $colunmname FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT ds.id, 
					(SELECT $branch FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountCode,
					discountTitle
					,$sqlDiscountFor AS discountForText
					,(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,	
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id LIMIT 1 ) studentAmount,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=1  LIMIT 1 ) IsUsed,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=0  LIMIT 1 ) IsStopUsed,
					(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=38 AND v.key_code=ds.discountForType LIMIT 1) AS discountForOption,
					CONCAT(COALESCE($sqlPeriod),'',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),'/',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) AS discountPeriod, 
					(SELECT first_name FROM rms_users WHERE id=ds.userId LIMIT 1 ) AS user_name,
					ds.createDate";

		$sql .= $dbp->caseStatusShowImage("ds.status");
		$sql .= " FROM rms_dis_setting AS ds ";

		$order = " ORDER BY id DESC ";
		$where = " WHERE 1";

		if (!empty($search['title'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " discountTitle LIKE '%{$s_search}%'";
			$s_where[] = " discountValue LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (($search['academic_year']) > 0) {
			$where .= ' AND ds.academicYear=' . $search['academic_year'];
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND ds.branchId=' . $search['branch_id'];
		}
		if (!empty($search['studentId'])) {
			$where .= ' AND ds.studentId=' . $search['studentId'];
		}
		if (!empty($search['discountId'])) {
			$where .= ' AND ds.discountId=' . $search['discountId'];
		}
		if (!empty($search['discountFor'])) {
			$where .= ' AND ds.discountFor=' . $search['discountFor'];
		}
		if (!empty($search['discountPeriod'])) {
			$where .= ' AND ds.discountPeriod=' . $search['discountPeriod'];
		}
		if ($search['status_search'] > -1) {
			$where .= ' AND status=' . $search['status_search'];
		}
		$where .= $dbp->getAccessPermission('ds.branchId');
		return $db->fetchAll($sql . $where . $order);
	}
}