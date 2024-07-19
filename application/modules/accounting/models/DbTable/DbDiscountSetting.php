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
				'discountFor' => $_data['discountFor'],
				'studentId' => $_data['studentId'],
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

			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr = array(

						'discountGroupId'=>$id,
						'studentId'      =>$_data['student_id'.$i],
						'createDate'     => date("Y-m-d"),
						'modifyDate'     => date("Y-m-d"),
						'userId'         => $this->getUserId()
					);
					$this->_name ='rms_discount_student';
					$this->insert($arr);
				}
			}

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
			'discountFor' => $_data['discountFor'],
			'studentId' => $_data['studentId'],
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

		$this->_name ='rms_discount_student';
		$this->delete("discountGroupId=".$_data['id']);
	   
		 if(!empty($_data['identity'])){
			 $ids = explode(',', $_data['identity']);
			 if(!empty($ids))foreach ($ids as $i){
				 $arr = array(
					'discountGroupId'=>$_data['id'],
					'studentId'      =>$_data['student_id'.$i],
					'createDate'     => date("Y-m-d"),
					'modifyDate'     => date("Y-m-d"),
					'userId'         => $this->getUserId()
				 );
				 $this->_name ='rms_discount_student';
				 $this->insert($arr);
			 }
		 }
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
			FROM rms_dis_setting WHERE isCurrent=1 ";
		if (!empty($data['branchId'])) {
			$sql .= ' AND branchId =' . $data['branchId'];
		}
		if (!empty($data['academicYear'])) {
			$sql .= ' AND academicYear =' . $data['academicYear'];
		}
		return  $db->fetchAll($sql);
	}
	function getSearchStudentbyDiscount($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				s.sex,
				sd.degree,
				sd.grade,
				sd.feeId AS fee_id,
				sd.academic_year,
				(SELECT `title` FROM `rms_items` WHERE `id`=sd.degree AND TYPE=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=sd.grade AND items_type=1 LIMIT 1) AS grade_title
			  FROM 
			  	rms_student AS s,
			  	`rms_group_detail_student` AS sd,
			  	`rms_discount_student` AS dc
		 	  WHERE 
				sd.itemType=1 
				AND s.stu_id = sd.stu_id
				AND s.`status`=1 
				AND s.customer_type = 1 
				AND sd.stop_type=0
				AND s.stu_id=sd.stu_id
				AND sd.is_current=1
				AND dc.isCurrent=1 
				AND s.`stu_id` = dc.studentId ";

		if(!empty($search['discountSettengId'])){
			$sql.=" AND dc.discountGroupId =".$search['discountSettengId'];
		}
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
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
		
		$where.=" GROUP BY s.stu_id,sd.degree,sd.grade";
		$where.=" ORDER BY s.stu_id DESC ";
		$where.=" LIMIT 200 ";
		return $db->fetchAll($sql.$where);
	}
	public function updateStudentDiscount($_data){
		$db = $this->getAdapter();
		try{
			if(!empty($_data['public-methods'])){
				
				if(!empty($_data['toDiscountId'])){

					$oldDiscountSettengId = empty($_data['oldDiscountSettengId'])?0:$_data['oldDiscountSettengId'];
					$toDiscountId = empty($_data['toDiscountId'])?0:$_data['toDiscountId'];
				
					$all_stu_id = $_data['public-methods'];
					
					foreach ($all_stu_id as $stu_id){
						if(!empty($_data['oldDiscountSettengId'])){
							$this->_name = 'rms_discount_student';
							$data_gro = array(
									'isCurrent'=> 0,
							);
							$where = ' studentId = '.$stu_id."  AND isCurrent=1 AND discountGroupId = $oldDiscountSettengId ";
							$this->update($data_gro, $where);

							if(!empty($_data['toDiscountId'])){
								
								$arr = array(
									'discountGroupId'=>$toDiscountId,
									'studentId'      =>$stu_id,
									'isCurrent'		 => 1,
									'createDate'     => date("Y-m-d"),
									'modifyDate'     => date("Y-m-d"),
									'userId'         => $this->getUserId()
								 );
								 $this->_name ='rms_discount_student';
								 $this->insert($arr);
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

		$sql = "SELECT ds.id, 
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
					<ul class="optListRow">
						<li class="opt-items"><span class="lbl-tt"><span class="text-value">'.$row['discountTitle'].'</span></li>
						<li class="opt-items"><span class="lbl-tt">'.$tr->translate("STUDY_YEAR").'</span>: <span class="text-value">'.$row['academicYear'].'</span></li>
						<li class="opt-items"><span class="lbl-tt">'.$tr->translate("DISCOUNT_TYPE").'</span>: <span class="text-value">'.$row['discName'].'</span></li>
						<li class="opt-items"><span class="lbl-tt">'.$tr->translate("DIS_MAX").'</span>: <span class="text-value">'.$row['DisValueType'].'</span></li>
						<li class="opt-items"><span class="lbl-tt"><span class="text-value">'.$row['discountPeriod'].'</span></li>
					</ul>
				';
			}
			return $string;
		}
	}
	function getAllStudentDiscount($search)
	{
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

		$sql = "SELECT ds.id, 
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academicYear LIMIT 1) as academicYear,
					discountTitle,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName,
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,	
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=1  LIMIT 1 ) IsUsed,
					(SELECT COUNT(dc.studentId) FROM `rms_discount_student` AS dc WHERE dc.discountGroupId=ds.id AND dc.isCurrent=0  LIMIT 1 ) IsStopUsed,
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